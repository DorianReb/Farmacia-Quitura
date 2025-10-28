<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckRole
{
    public function handle(Request $request, Closure $next, ...$roles)
    {
        // Verifica si el usuario está autenticado
        if (!Auth::check()) {
            return redirect('/login');
        }

        $rolUsuario = Auth::user()->rol;

        // Si el rol del usuario está dentro de los permitidos, continúa
        if (in_array($rolUsuario, $roles)) {
            return $next($request);
        }

        // Si no coincide, redirige según su rol actual
        switch ($rolUsuario) {
            case 'Superadmin':
                return redirect('/home');
            case 'Administrador':
                return redirect('/home');
            case 'Vendedor':
                return redirect('/home');
            default:
                abort(403, 'Acceso no autorizado.');
        }
    }
}
