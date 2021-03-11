<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    public function login(Request $req)
    {

        $data = json_decode($req->json);
        $data_array = json_decode($req->json, true);

        $validate = Validator::make($data_array, [
            'email'     => 'required|email',
            'password'  => 'required'
        ]);

        $data_array = array_map('trim', $data_array);

        if ($validate->fails()) {
            $signUp = [
                'status'    => 'error',
                'code'      => 400,
                'message'   => 'Datos ingresados incorrectos.',
                'errors'    => $validate->errors()
            ];
        } else {

            $JwtAuth = new \JwtAuth();

            $email = $data_array['email'];
            $pwd = hash('sha256', $data_array['password']);

            if (!empty($data->getToken)) {
                $signUp = $JwtAuth->signUp($email, $pwd, $data->getToken);
            } else {
                $signUp = $JwtAuth->signUp($email, $pwd);
            }
        }

        return response()->json($signUp, 200);
    }

    public function register(Request $req)
    {
        $data = json_decode($req->json);
        $data_array = json_decode($req->json, true);

        $res = [];
        if (!empty($data_array)) {

            $validate = Validator::make($data_array, [
                'name'      => 'required|alpha',
                'surname'   => 'required|alpha',
                'email'     => 'required|email|unique:users',
                'password'  => 'required'
            ]);

            $data_array = array_map('trim', $data_array);

            if ($validate->fails()) {
                $res = [
                    'status'    => 'error',
                    'code'      => 400,
                    'message'   => 'No se pudo guardar el usuario',
                    'errors'    => $validate->errors()
                ];
            } else {

                $pwd = hash('sha256', $data->password);

                $user = new User();
                $user->name = $data_array['name'];
                $user->surname = $data_array['surname'];
                $user->email = $data_array['email'];
                $user->password = $pwd;
                $user->role = 'USER';

                $user->save();

                $res = [
                    'status'    => 'succes',
                    'code'      => 200,
                    'message'   => 'Se registro el usuario correctamente',
                    'user'    => $user
                ];
            }
        } else {
            $res = [
                'status'    => 'error',
                'code'      => 200,
                'message'   => 'Los datos enviados no son correctos',
                'errors'    => null
            ];
        }

        return response()->json($res, $res['code']);
    }

    public function update(Request $req)
    {
        $token = $req->header('Authorization');
        $JwtAuth = new \JwtAuth();
        $checkToken = $JwtAuth->checkToken($token);
        if ($checkToken) {
            echo 'Login correcto';
        } else {
            echo 'Login incorrecto';
        }
    }
}
