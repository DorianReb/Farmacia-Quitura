<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckEstado
{
    public function handle(Request $request, Closure $next)
    {
        // Si no está autenticado, lo mandamos al login
        if (!Auth::check()) {
            return redirect('/login');
        }

        $estado = Auth::user()->estado;

        // Bloquea acceso si el usuario no está activo
        switch ($estado) {
            case 'Pendiente':
                return redirect()->route('estado.pendiente')
                    ->with('error', 'Tu cuenta aún está pendiente de aprobación.');
            case 'Rechazado':
                return redirect()->route('estado.rechazado')
                    ->with('error', 'Tu cuenta ha sido rechazada por el administrador.');
            case 'Activo':
                // Todo bien, puede continuar
                return $next($request);
            default:
                // Cualquier otro estado inesperado
                Auth::logout();
                return redirect('/login')->with('error', 'Estado de cuenta inválido.');
        }
    }
}
