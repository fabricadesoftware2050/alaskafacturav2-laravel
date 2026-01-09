<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Mail;

class AuthController extends Controller
{
    /**
     * Create a new AuthController instance.
     *
     * @return void
     */

    /**
     * Get a JWT via given credentials.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login()
    {
        try {
            $credentials = request(['email', 'password']);

        // Agregar más datos al token
        $user = User::where('email', $credentials['email'])->where('active', true)->first();
        if (!$user) {
            return response()->json(['error' => 'Unauthorized','message' => 'Datos de acceso incorrectos o usuario inactivo'], Http::UNAUTHORIZED);
        }elseif ($user->role=='gmail'){
            return response()->json(['error' => 'Unauthorized','message' => 'Su inicio de sesión es con Gmail'], Http::BAD_REQUEST);
        }
        $customClaims = [
            'role' => $user->role,
            'user_id' => $user->id,
            'email' => $user->email,
            'name' => $user->name,
            'login_type' => $user->login_type,
            'verified' => $user->verified,
            'current_plan' => $user->current_plan,
            'expires_in' => auth()->factory()->getTTL() * 60

        ];
        if (! $token = auth()->claims($customClaims)->attempt($credentials)) {
        //if (! $token = auth()->attempt($credentials)) {
            return response()->json(['error' => 'Unauthorized','message' => 'Datos de acceso incorrectos'], 401);
        }

        return $this->respondWithToken($token);
        } catch (Exception $ex) {
            return response()->json(['error' => 'Login failed', 'message' => $ex->getMessage()], 500);
            //throw $th;
        }
    }

    public function register(Request $request)
{
    try {

        // VALIDACIÓN
        $validated = $request->validate([
            'name' => 'required|string|max:120',
            'email' => 'required|string|email|max:255',
            'password' => 'required|string|min:6|confirmed',
            'login_type' => 'nullable|string|in:email,gmail',
            'role' => 'nullable|string|in:admin,accountant,operative',
        ]);
        // BUSCAR USUARIO EXISTENTE
        $user = User::where('email', $request->email)->first();
        if ($user) {

            // SI ES UN USUARIO QUE FUE REGISTRADO POR GOOGLE → LOGIN DIRECTO
            if ($user->login_type === 'gmail' && $validated['login_type'] === 'gmail') {

                $customClaims = [
                    'role' => $user->role,
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'name' => $user->name,
                    'login_type' => $user->login_type,
                    'verified' => $user->verified,
                    'current_plan' => $user->current_plan,
                    'expires_in' => auth()->factory()->getTTL() * 60,
                ];

                $token = auth()->claims($customClaims)->login($user);

                return $this->respondWithToken($token);
            }

            // SI EXISTE PERO NO ES GOOGLE → ERROR
            return response()->json([
                'error' => 'Email in use',
                'message' => 'Este correo debe iniciar sesión vía Gmail.'
            ], 400);
        }

        // CREAR USUARIO
        $user = User::create([
            'name'     => $validated['name'],
            'email'    => $validated['email'],
            'password' => bcrypt($validated['password']),
            'role'     => isset($validated['role']) ? $validated['role'] : 'admin',   // Puedes modificarlo
            'active'   => true,
            'verified' => false,
            'verification_code' => rand(100000, 999999),
            'login_type' => $validated['login_type'] === 'gmail' ? 'gmail' : 'email',

        ]);

        // CUSTOM CLAIMS (mismos que login)
        $customClaims = [
                    'role' => $user->role,
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'name' => $user->name,
                    'login_type' => $user->login_type,
                    'verified' => $user->verified,
                    'current_plan' => $user->current_plan,
                    'expires_in' => auth()->factory()->getTTL() * 60,
                ];

        //enviamos correo de verificación aquí (pendiente)
        

        // GENERAR TOKEN
        $token = auth()->claims($customClaims)->login($user);

        Mail::html(
            "
            <di style='background:#f8fafa; font-family:-apple-system,BlinkMacSystemFont,Segoe UI,Roboto,Helvetica,Arial,sans-serif; margin:0; padding:32px 0; color:#1a2e44;'>

            <table width='100%' cellpadding='0' cellspacing='0'>
                <tr>
                <td align='center'>

                    <!-- Encabezado -->
                    <table width='580' cellpadding='0' cellspacing='0' style='margin-bottom:20px;'>
                    <tr>
                        <td align='center' style='font-size:22px; font-weight:700; color:#00bfa5;'>
                        Alaska Factura
                        </td>
                    </tr>
                    </table>

                    <!-- Cuerpo -->
                    <table width='580' cellpadding='0' cellspacing='0' style='background:#ffffff; border-radius:8px; border:1px solid #e5e7eb;'>
                    <tr>
                        <td style='padding:32px;'>
                        <img src='https://alaskafactura.cloud/alaskaia.png' alt='Alaska Factura' width='100%'> 

                        <h1 style='margin:0 0 12px; font-size:20px;'>
                            ¡Bienvenidos a Facturación de Servicios Públicos!
                        </h1>

                        <p style='margin:0 0 16px; font-size:14px;'>
                            Hola <strong>" . auth()->user()->name . "</strong>,
                        </p>

                        <p style='margin:0 0 16px; font-size:14px;'>
                            Gracias por crear tu cuenta en <strong>Alaska Factura</strong>.
                            Para completar el proceso y verificar tu correo electrónico,
                            ingresa el siguiente código de verificación:
                        </p>

                        <!-- Código -->
                        <div style='
                            text-align:center;
                            font-size:34px;
                            letter-spacing:6px;
                            font-weight:700;
                            padding:18px 0;
                            margin:24px 0;
                            background:#f1f5f9;
                            border-radius:6px;
                            color:#2563eb;
                        '>
                            ".auth()->user()->verification_code."
                        </div>

                        <p style='margin:0 0 16px; font-size:14px;'>
                            Este código es válido por tiempo limitado.
                            Si no solicitaste esta cuenta, puedes ignorar este mensaje.
                        </p>

                        <hr style='border:none; border-top:1px solid #e5e7eb; margin:24px 0;'>

                        <p style='font-size:12px; color:#6b7280; margin:0;'>
                            © " . date('Y') . " Alaska Factura<br>
                            Correo enviado a " . auth()->user()->email . "
                        </p>

                        </td>
                    </tr>
                    </table>

                </td>
                </tr>
            </table>

            </di>
            ",
            function ($msg) {
                $msg->to(auth()->user()->email)
                    ->from('no-responder@alaskafactura.cloud', 'Alaska Factura')
                    ->subject('Verifica tu cuenta');
            }
        );

        return $this->respondWithToken($token);

    } catch (\Exception $ex) {
        return response()->json([
            'error' => 'Register failed',
            'message' => $ex->getMessage()
        ], 500);
    }
}

    /**
     * Get the authenticated User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function me()
    {
        try {
            $user = auth()->user();
            return response()->json([
                'data' => $user,
                'message' => 'Operación exitosa'
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to get user', 'message' => $e->getMessage()], 500);
        }
    }

    public function verify_account(Request $request)
    {
        try {
            $user = auth()->user();
            if($user->verification_code == "VERIFIED"){
                return response()->json(['error' => 'Account Already Verified','message' => 'Cuenta ya verificada'], 400);
            }
            if($user->verification_code != $request->code){
                return response()->json(['error' => 'Invalid code','message' => 'Código de verificación incorrecto'], 400);
            }
            $user->verified = true;
            $user->verification_code = 'VERIFIED';
            $user->save();
            return response()->json([
                'message' => 'Operación exitosa'
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to get user', 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        try {
            auth()->logout();
            return response()->json([
                'data' => null,
                'message' => 'Operación exitosa'
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to logout', 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        return $this->respondWithToken(auth()->refresh());
    }

    /**
     * Get the token array structure.
     *
     * @param  string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithToken($token)
    {
        return response()->json([
            'data' => array(
                'access_token' => $token,
                'token_type' => 'bearer',
                'expires_in' => auth()->factory()->getTTL() * 60
            ),
            'message' => 'Operación exitosa'
        ]);
    }
}
