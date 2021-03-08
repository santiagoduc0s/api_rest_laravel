<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    public function login(Request $req)
    {
        return 'login';
    }

    public function register(Request $req)
    {
        $data = json_decode($req->json);
        $data_array = json_decode($req->json, true);

        $res = [];
        if (!empty($data_array)) {
            
            $validate = Validator::make($data_array, [
                'name'      => 'required|alpha|min:10',
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

                $pwd = password_hash($data->password, PASSWORD_BCRYPT, ['cost' => 4]);

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
}
