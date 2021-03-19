<?php

namespace App\Http\Middleware;

use Closure;

/**
 * 
 * Este Middleware debe ser registrado para poder ser utilizado, 
 * para registrarlo hay que acceder a App\http\Kernel.php.
 * 
 */

class ApiAuthMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        // Recibir token.
        $token = $request->header('Authorization');

        // Validar token.
        $JwtAuth = new \JwtAuth();
        if ($JwtAuth->checkToken($token)) { // Token valido.

            return $next($request);
        } else {

            $res = [
                'code' => 400,
                'status' => 'error',
                'message' => 'El usuario no esta identificado.'
            ];
        }

        return response()->json($res, $res['code']);
    }
}
