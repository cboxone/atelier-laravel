<?php

namespace App\Http\Controllers\Atelier;

use App\Http\Controllers\Controller;
use App\Models\{ContractVisit, Intervention, Invoice, ServiceRequest};

class DashboardController extends Controller
{
    public function index()
    {
        $unpaid = Invoice::with('payments')->where('type', 'FV')->where('status', 'issued')->get()->filter(fn ($i) => $i->balance_fcfa > 0);

        return view('atelier.dashboard', [
            'newRequests' => ServiceRequest::where('status', 'new')->orderByDesc('is_urgent')->latest()->take(8)->get(),
            'today' => Intervention::with('customer')->whereDate('scheduled_at', today())->whereIn('status', ['planned', 'in_progress'])->orderBy('scheduled_at')->get(),
            'unpaidCount' => $unpaid->count(),
            'unpaidSum' => $unpaid->sum('balance_fcfa'),
            'failed' => Invoice::where('status', 'failed')->count(),
            'visits' => ContractVisit::where('status', 'planned')->whereBetween('due_on', [today(), today()->addDays(14)])->count(),
        ]);
    }
}
