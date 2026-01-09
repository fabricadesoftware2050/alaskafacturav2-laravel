## Crear proyecto

laravel new

## instalar api
php artisan install:api

## migracion users

## jwt
composer require tymon/jwt-auth
php artisan vendor:publish --provider="Tymon\JWTAuth\Providers\LaravelServiceProvider"
php artisan jwt:secret

## config/auth.php

'defaults' => [
        'guard' => env('AUTH_GUARD', 'api'),
        'passwords' => env('AUTH_PASSWORD_BROKER', 'users'),
    ],

'guards' => [
    'api' => [
        'driver' => 'jwt',
        'provider' => 'users',
    ],
],

## CORS
php artisan config:publish cors

ir a : config/cors.php

    'allowed_origins' => ['https://alaskafactura.cloud','http://localhost:3000','http://localhost:3001'],

## migracion y usuario por defecto
php artisan migrate 
//crear usuario ´por defecto DB
        DB::table('users')->insert([
            'name' => 'Admin',
            'email' => 'admin@admin.com',
            'role' => 'admin',
            'login' => 'email',
            'active' => true,
            'password' => bcrypt('1234'), // Cambia 'admin123' por la contraseña que desees
        ]);

## validaciones en español

php artisan lang:publish 
crea /lang/es/validation.php a partor de /lang/en/validation.php

## preparar para envío de email .env
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="no-reply@chrisgamez.com"
MAIL_FROM_NAME="Alaska Factura"
MAIL_MAILER=smtp
MAIL_HOST=live.smtp.mailtrap.io
MAIL_PORT=587
MAIL_USERNAME=api
MAIL_PASSWORD=token

## crear middleware para login de api

php artisan make:middleware ApiAuthenticate

<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Facades\Auth;

class ApiAuthenticate
{
    public function handle($request, Closure $next, ...$guards)
    {
        try {
            // Si falla, lanzará AuthenticationException
            Auth::shouldUse($guards[0] ?? 'api');

            if (Auth::guard($guards[0] ?? 'api')->check() === false) {
                throw new AuthenticationException();
            }

        } catch (AuthenticationException $e) {
            return response()->json([
                'status' => false,
                'message' => 'No autorizado'
            ], 401);
        }

        return $next($request);
    }
}

## agregar middleware a la app

boostrap/app.php

<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'auth' => \App\Http\Middleware\ApiAuthenticate::class,
            'auth:api' => \App\Http\Middleware\ApiAuthenticate::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();

## se agrega el .htaccess
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteBase /

    RewriteRule ^(public)\/ - [L]
    RewriteRule ^(.*)$ public/$1 [L]
</IfModule>

<IfModule mod_headers.c>
    Header unset X-Powered-By
</IfModule>
