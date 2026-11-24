<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureTechnician
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();
        if (! $user) return redirect('/technicien/connexion');
        abort_unless($user->is_technician === true || $user->is_admin === true, 403);
        return $next($request);
    }
}
