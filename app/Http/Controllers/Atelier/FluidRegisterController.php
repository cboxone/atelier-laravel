<?php

namespace App\Http\Controllers\Atelier;

use App\Http\Controllers\Controller;
use App\Models\{Equipment, FluidMovement};
use App\Workshop\{FluidRegister, WorkshopException};
use Illuminate\Http\Request;

class FluidRegisterController extends Controller
{
    public function show(Equipment $equipment, FluidRegister $register)
    {
        $movements = $equipment->fluidMovements()->with(['technician', 'intervention'])->orderBy('occurred_at')->orderBy('id')->get();
        $reversed = $movements->pluck('reverses_id')->filter()->all();
        return view('atelier.fluid.register', ['equipment' => $equipment->load('customer'), 'movements' => $movements, 'reversed' => $reversed, 'balance' => $register->balance($equipment)]);
    }

    public function reverse(Request $request, FluidMovement $movement, FluidRegister $register)
    {
        $reason = $request->validate(['reason' => ['required', 'string', 'max:300']])['reason'];
        try {
            $register->reverse($movement, $request->user(), $reason);
        } catch (WorkshopException $e) {
            return back()->withErrors(['action' => $e->getMessage()]);
        }
        return back();
    }
}
