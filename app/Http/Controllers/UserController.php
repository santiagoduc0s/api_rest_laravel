<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;
//use Illuminate\Support\Facades\Validator;


class UserController extends Controller
{
    public function login(Request $req)
    {

        // Recibir datos enviados.
        $data_array = json_decode($req->json, true);

        // Validar los datos.
        if (!empty($data_array)) {

            $data_array = array_map('trim', $data_array);

            $validate = \Validator::make($data_array, [
                'email'     => 'required|email',
                'password'  => 'required'
            ]);

            if ($validate->fails()) { // Validacion fallida.

                $res = [
                    'status'    => 'error',
                    'code'      => 400,
                    'message'   => 'Datos ingresados incorrectos.',
                    'errors'    => $validate->errors()
                ];
            } else { // Validacion correcta.

                $JwtAuth = new \JwtAuth();

                $email = $data_array['email'];
                $password = hash('sha256', $data_array['password']);

                if (isset($data_array['token']) && !empty($data_array['token'])) {
                    // solicitar datos
                    $res = $JwtAuth->signUp($email, $password, $data_array['token']);
                } else {
                    // solicitar token
                    $res = $JwtAuth->signUp($email, $password);
                }
            }
        } else { // Datos invalidos.

            $res = [
                'status'    => 'error',
                'code'      => 200,
                'message'   => 'Los datos enviados son invalidos.',
            ];
        }

        return response()->json($res, 200);
    }

    public function register(Request $req)
    {
        // Recibir datos enviados.
        $data_array = json_decode($req->json, true);


        // Validar los datos.
        if (!empty($data_array)) {

            $data_array = array_map('trim', $data_array);

            $validate = \Validator::make($data_array, [
                'name'      => 'required|alpha',
                'surname'   => 'required|alpha',
                'email'     => 'required|email|unique:users',
                'password'  => 'required'
            ]);

            if ($validate->fails()) { // Validacion fallida.

                $res = [
                    'status'    => 'error',
                    'code'      => 400,
                    'message'   => 'No se pudo registar el usuario.',
                    'errors'    => $validate->errors()
                ];
            } else { // Validacion correcta.

                $user = new User();
                $user->name = $data_array['name'];
                $user->surname = $data_array['surname'];
                $user->email = $data_array['email'];
                $user->password = hash('sha256', $data_array['password']); // cifrar contraseña
                $user->role = 'USER';

                $user->save(); // Registrar usuario.

                $res = [
                    'status'    => 'succes',
                    'code'      => 200,
                    'message'   => 'Se registro el usuario correctamente.',
                    'user'      => $user
                ];
            }
        } else { // Datos incorrectos.

            $res = [
                'status'    => 'error',
                'code'      => 200,
                'message'   => 'Los datos enviados son incorrectos.',
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

            $json = $req->input('json', null);
            $params_array = json_decode($json, true);

            $user = $JwtAuth->checkToken($token, true);

            $validate = Validator::make($params_array, [
                'name'      => 'required|alpha',
                'surname'   => 'required|alpha',
                'email'     => 'required|email|unique:users,' . $user->sub,
            ]);

            unset($params_array['id']);
            unset($params_array['role']);
            unset($params_array['password']);
            unset($params_array['created_at']);
            // unset($params_array['updated_at']);
            unset($params_array['remember_token']);

            $user_update = User::where('id', $user->sub)->update($params_array);

            $res = array(
                'code' => 200,
                'status' => 'succes',
                'message' => 'El usuario se actualizo correctamente.',
                'user' => $user
            );
        } else {
            $res = array(
                'code' => 400,
                'status' => 'error',
                'message' => 'El usuario no esta identificado.'
            );
        }

        return response()->json($res, $res['code']);
    }
}
