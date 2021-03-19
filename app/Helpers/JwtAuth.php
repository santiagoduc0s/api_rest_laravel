<?php

namespace App\Helpers;

use Firebase\JWT\JWT;
use Illuminate\Support\Facades\DB;
use App\User;
use Exception;

/**
 * 
 * JSON Web Token (JWT) es un estándar para crear un token que sirva 
 * para enviar datos entre aplicaciones o servicios y garantizar que 
 * sean válidos y seguros.
 * 
 */

class JwtAuth
{

    private $key;

    public function __construct()
    {
        $this->key = 'est0_3s_un4_clv3_supe7_secre7a';
    }

    public function signUp($email, $password)
    {
        // Buscar usuario.
        $user = User::where([
            'email'     => $email,
            'password'  => $password
        ])->first();

        if (is_object($user)) { // Login correcto.

            $token = [
                'sub'       => $user->id,
                'email'     => $user->email,
                'name'      => $user->name,
                'surname'   => $user->surname,
                'iat'       => time(),
                'exp'       => time() + (7 * 24 * 60 * 60) // despues de este tiempo el token dejara de funcionar
            ];

            // Crear token.
            $data = JWT::encode($token, $this->key, 'HS256');
        } else { // Login fallido.

            $data = [
                'status' => 'error',
                'message' => 'Los datos ingresados son incorrectos.'
            ];
        }

        return $data;
    }

    public function checkToken($token, $getIdentity = false)
    {
        try {

            // decodificar token
            $token = str_replace('"', '', $token);
            $user = JWT::decode($token, $this->key, ['HS256']);

            if (!empty($user) && is_object($user) && isset($user->sub)) {
                
                if ($getIdentity) {
                    return $user;
                }
                $auth = true;
            } else {
                $auth = false;
            }
        } catch (\UnexpectedValueException $e) {
            $auth = false;
        } catch (\DomainException $e) {
            $auth = false;
        } catch (\Exception $e) {
            $auth = false;
        }

        return $auth;
    }
}
