<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

class LoginController extends Controller
{
    use AuthenticatesUsers;

    protected $redirectTo = '/home';

    public function __construct()
    {
        $this->middleware('guest')->except('logout');
        $this->middleware('auth')->only('logout');
    }

    public function username()
    {
        return 'correo';
    }

    protected function validateLogin(Request $request)
    {
        $request->validate([
            'correo'     => ['required', 'string', 'email'],
            'contrasena' => ['required', 'string'],
        ]);
    }

    protected function credentials(Request $request)
    {
        return [
            'correo'   => $request->input('correo'),
            'password' => $request->input('contrasena'),
        ];
    }

    /**
     * IMPORTANTE: aquí SOLO devolvemos un string (ruta/URL), NADA de logout/redirect.
     */
    protected function redirectTo()
    {
        $u = auth()->user();

        if ($u && $u->rol === 'Vendedor') {
            // Usa una ruta que EXISTE; si no tienes vendedor.home, manda a /home
            return Route::has('vendedor.home') ? route('vendedor.home') : route('home');
        }

        // Superadmin y Administrador al mismo home por ahora
        return route('home');
    }

    protected function sendFailedLoginResponse(Request $request)
    {
        return back()
            ->withInput($request->only('correo', 'remember'))
            ->withErrors(['correo' => __('auth.failed')]);
    }

    /**
     * Aquí sí podemos hacer logout/redirect según ESTADO.
     */
    protected function authenticated(Request $request, $user)
    {
        \Log::info('LOGIN OK', [
            'id'      => $user->id,
            'correo'  => $user->correo,
            'rol'     => $user->rol,
            'estado'  => $user->estado,
            'session' => session()->getId(),
        ]);

        if ($user->estado === 'Pendiente') {
            Auth::logout();
            return redirect()->route('estado.pendiente');
        }
        if ($user->estado === 'Rechazado') {
            Auth::logout();
            return redirect()->route('estado.rechazado');
        }

        // Redirección final SIN usar RedirectResponse en redirectTo()
        return redirect($this->redirectPath());
    }
}
