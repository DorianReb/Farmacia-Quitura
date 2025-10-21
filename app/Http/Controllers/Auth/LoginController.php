<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;


class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/home';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
        $this->middleware('auth')->only('logout');
    }

    public function username()
    {
        return 'correo';
    }

    /**
     * Validación de los campos del login.
     */
    protected function validateLogin(Request $request)
    {
        $request->validate([
            'correo'     => ['required', 'string', 'email'],
            'contrasena' => ['required', 'string'],
        ]);
    }

    /**
     * Mapear las credenciales para Auth::attempt().
     * OJO: la clave debe llamarse 'password' aunque tu columna sea 'contrasena';
     * tu modelo User ya expone getAuthPassword() que devuelve 'contrasena'.
     */
    protected function credentials(Request $request)
    {
        return [
            'correo'   => $request->input('correo'),
            'password' => $request->input('contrasena'),
        ];
    }

    /**
     * (Opcional) Redirección dinámica según rol.
     * Si usas esto, puedes quitar la propiedad $redirectTo.
     */
    protected function redirectTo()
    {
        $user = auth()->user();
        if ($user && $user->rol === 'Administrador') {
            return route('home');          // ajusta a tu ruta real
        }
        return route('vendedor.dashboard');       // ajusta a tu ruta real
    }

    /**
     * (Opcional) Mensaje de error usando 'correo' en vez de 'email'.
     */
    protected function sendFailedLoginResponse(Request $request)
    {
        return back()
            ->withInput($request->only('correo', 'remember'))
            ->withErrors(['correo' => __('auth.failed')]);
    }
}
