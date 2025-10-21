<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;

class ForgotPasswordController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Password Reset Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password reset emails and
    | includes a trait which assists in sending these notifications from
    | your application to your users. Feel free to explore this trait.
    |
    */

    use SendsPasswordResetEmails;

    public function __construct()
    {
        $this->middleware('guest');
    }

    /**
     * Validar el campo de solicitud (usamos 'correo' en vez de 'email').
     */
    protected function validateEmail(Request $request)
    {
        $request->validate([
            'correo' => ['required', 'email'],
        ]);
    }

    /**
     * Credenciales que el broker usará para enviar el enlace.
     * El broker espera la clave 'email', por eso mapeamos desde 'correo'.
     */
    protected function credentials(Request $request)
    {
        return ['email' => $request->input('correo')];
    }
}
