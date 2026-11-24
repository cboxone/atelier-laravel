<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreServiceRequestRequest;
use App\Models\{Service, ServiceRequest, Setting};
use App\Notifications\NewServiceRequest;
use Illuminate\Support\Facades\{Log, Notification};

class ServiceRequestController extends Controller
{
    public function create()
    {
        return view('pages.request', [
            'services'  => Service::where('is_published', true)->orderBy('sort_order')->get(),
            'preselect' => Service::where('slug', request('service'))->value('id'),
            'urgent'    => request()->boolean('urgent'),
        ]);
    }

    public function store(StoreServiceRequestRequest $request)
    {
        $data = $request->safe()->except(['attachments', 'website', 'consent']);
        $data['is_urgent'] = $request->boolean('is_urgent');

        $sr = ServiceRequest::create($data);

        foreach ($request->file('attachments', []) as $file) {
            // Disque privé : les fichiers ne sont pas accessibles directement par URL.
            $path = $file->store("service-requests/{$sr->id}", 'local');
            $sr->attachments()->create([
                'path' => $path,
                'original_name' => $file->getClientOriginalName(),
                'mime_type' => $file->getMimeType(),
                'size_bytes' => $file->getSize(),
            ]);
        }

        $this->notifyManager($sr);

        // La référence passe par la session : la page de remerciement n'est pas consultable par URL.
        return redirect()->route('requests.thanks')->with(['reference' => $sr->reference, 'urgent' => $sr->is_urgent]);
    }

    public function thanks()
    {
        if (! session()->has('reference')) {
            return redirect()->route('requests.create');
        }
        return view('pages.thanks', ['reference' => session('reference'), 'urgent' => session('urgent')]);
    }

    // Une panne d'e-mail ne doit jamais faire perdre la demande : elle est déjà enregistrée.
    private function notifyManager(ServiceRequest $sr): void
    {
        $to = Setting::get('notify_email');
        if (! $to) {
            Log::warning('Réglage notify_email absent : le gérant n’est pas prévenu des nouvelles demandes.');
            return;
        }
        try {
            Notification::route('mail', $to)->notify(new NewServiceRequest($sr));
        } catch (\Throwable $e) {
            report($e);
        }
    }
}
