<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

// Espace atelier : réservé au personnel. (L'application technicien aura son propre rôle à l'étape 3B.)
class EnsureStaff
{
    public function handle(Request $request, Closure $next)
    {
        if (! $request->user()) return redirect('/admin/login');
        abort_unless($request->user()->is_admin === true, 403);
        return $next($request);
    }
}
