<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\ResetsPasswords;

class ResetPasswordController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Password Reset Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password reset requests
    | and uses a simple trait to include this behavior. You're free to
    | explore this trait and override any methods you wish to tweak.
    |
    */

    use ResetsPasswords;


    /**
     * Where to redirect users after resetting their password.
     *
     * @var string
     */
    protected $redirectTo = '/home';

    public function __construct()
    {
        $this->middleware('guest');
    }

    /**
     * Reglas adaptadas: 'correo' y 'contrasena' (con confirmación).
     */
    protected function rules()
    {
        return [
            'token'                  => ['required'],
            'correo'                 => ['required', 'email'],
            'contrasena'             => ['required', 'string', 'min:8', 'confirmed'],
        ];
    }

    /**
     * Las credenciales que el broker usa para resetear.
     * Ojo: el broker espera 'email' y 'password', por eso mapeamos.
     */
    protected function credentials(Request $request)
    {
        return [
            'email'                 => $request->input('correo'),
            'password'              => $request->input('contrasena'),
            'password_confirmation' => $request->input('contrasena_confirmation'),
            'token'                 => $request->input('token'),
        ];
    }

    /**
     * Sobrescribimos para guardar en 'contrasena' (no 'password'),
     * y como no tienes remember_token, no lo tocamos.
     */
    protected function resetPassword($user, $password)
    {
        $user->forceFill([
            'contrasena' => Hash::make($password),
        ])->save();

        event(new PasswordReset($user));

        // Iniciar sesión automáticamente tras reset
        $this->guard()->login($user);
    }

    /**
     * (Opcional) Redirección dinámica según rol tras el reset:
     */
    protected function redirectTo()
    {
        $u = auth()->user();
        if ($u && $u->rol === 'Administrador') {
            return route('dashboard-admin');
        }
        return route('vendedor.dashboard');
    }
}
