<?php

namespace App\Helpers;

use Firebase\JWT\JWT;
use Illuminate\Support\Facades\DB;
use App\User;

/**
 * 
 * JSON Web Token (JWT) es un estándar para crear un token que sirva 
 * para enviar datos entre aplicaciones o servicios y garantizar que 
 * sean válidos y seguros.
 * 
 */

class JwtAuth
{


    public $key;

    public function __construct()
    {
        $this->key = 'est0_3s_un4_clv3_supe7_secre7a';
    }

    public function signUp($email, $password, $token = null)
    {
        // Buscar usuario.
        $user = User::where([
            'email'     => $email,
            'password'  => $password
        ])->first();

        if (is_object($user)) { // Login correcto.

            if (is_null($token)) {

                $token = [
                    'sub'       => $user->id,
                    'email'     => $user->email,
                    'name'      => $user->name,
                    'surname'   => $user->surname,
                    'iat'       => time(),
                    'exp'       => time() + (7 * 24 * 60 * 60)
                ];

                // crear token
                $data = JWT::encode($token, $this->key, 'HS256');
            } else {
                $data = JWT::decode($token, $this->key, ['HS256']);
            }
        } else { // Login fallido.

            $data = [
                'status' => 'error',
                'message' => 'Los datos ingresados son incorrectos.'
            ];
        }

        return $data;
    }

    public function checkToken($jwt, $getIdentity = false)
    {
        $auth = false;

        try {
            $jwt = str_replace('"', '', $jwt);
            $decode = JWT::decode($jwt, $this->key, ['HS256']);
        } catch (\UnexpectedValueException $e) {
            $auth = false;
        } catch (\DomainException $e) {
            $auth = false;
        }

        if (!empty($decode) && is_object($decode) && isset($decode->sub)) {
            $auth = true;
        } else {
            $auth = false;
        }

        if ($getIdentity) {
            return $decode;
        }

        return $auth;
    }
}
