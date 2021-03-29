<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

use App\User;

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

                // Solicitar token.
                $email = $data_array['email'];
                $password = hash('sha256', $data_array['password']);
                $res = $JwtAuth->signUp($email, $password);
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
        // Recibir token.
        $token = $req->header('Authorization');

        // Validar token.
        $JwtAuth = new \JwtAuth();
        if ($JwtAuth->checkToken($token)) { // Token valido.

            // Recibir data enviada.
            $data_array = json_decode($req->json, true);

            // Decodificar token.
            $user = $JwtAuth->checkToken($token, true);

            // Validar datos enviados.
            $validate = \Validator::make($data_array, [
                'name'      => 'required|alpha',
                'surname'   => 'required|alpha',
                'email'     => 'required|email|unique:users,email,' . $user->sub
            ]);

            if ($validate->fails()) { // Validacion fallida.

                $res = [
                    'code' => 400,
                    'status' => 'succes',
                    'message' => 'Los datos enviados son invalidos.',
                    'errors' => $validate->errors()
                ];
            } else { // Validacion correcta.

                // No actualizar
                unset($data_array['id']);
                unset($data_array['role']);
                unset($data_array['password']);
                unset($data_array['created_at']);
                unset($data_array['updated_at']);
                unset($data_array['remember_token']);

                // Actualizar usuario.
                $user_update = User::where('id', $user->sub)->update($data_array);

                if ($user_update) { // Usuario actualizado.
                    $res = [
                        'code' => 200,
                        'status' => 'succes',
                        'message' => 'El usuario se actualizo correctamente.',
                        'user' => $user,
                        'changes' => $data_array
                    ];
                } else { // Error al actualizar.
                    $res = [
                        'code' => 400,
                        'status' => 'succes',
                        'message' => 'No fue posible actualizar el usuario.',
                    ];
                }
            }
        } else { // Token invalido.

            $res = [
                'code' => 400,
                'status' => 'error',
                'message' => 'El usuario no esta identificado.'
            ];
        }

        return response()->json($res, $res['code']);
    }

    public function uploadAvatar(Request $req)
    {
        /**
         * Un Middleware es un metodo que se ejecuta antes de la accion del controlador.
         */

        // Recibir imagen
        $image = $req->file('file0');

        // Validar imagen.
        if (!$image) { // Validacion fallida.

            $validate = \Validator::make($req->all(), [
                'file0' => 'required|mimes:png,jpg,jpeg' // Comprueba imagen.
            ]);

            if ($validate->fails()) { // No es una imagen.

                $res = [
                    'code' => 400,
                    'status' => 'error',
                    'message' => 'El formato del archivo seleccionado es invalida.'
                ];
            } else { // Error en la imagen.

                $res = [
                    'code' => 400,
                    'status' => 'error',
                    'message' => 'Ocurrió un error en la subida del avatar.'
                ];
            }
        } else { // Validacion correcta.

            $image_name = time() . $image->getClientOriginalName();

            /**
             * Para usuar este disco, primero debe ser registrado creado y registrado 
             * en \config\filesystems.php
             */
            \Storage::disk('users')->put($image_name, \File::get($image)); // Guardar imagen.

            $res = [
                'code' => 200,
                'status' => 'succes',
                'message' => 'El avatar fue subido correctamente.',
                'image' => $image_name
            ];
        }

        return response()->json($res, $res['code']);
    }

    public function getAvatar($filename)
    {
        // Existe el archivo.
        $exists = \Storage::disk('users')->exists($filename);

        if ($exists) { // Si existe.

            $file = \Storage::disk('users')->get($filename);
            return new Response($file, 200);
        } else { // No existe.

            $res = [
                'code' => 404,
                'status' => 'error',
                'message' => 'No existe ningun archivo con ese nombre.'
            ];
        }

        return response()->json($res, $res['code']);
    }

    public function getUser($id)
    {
        $user = User::find($id);

        // Validacion
        if (is_object($user)) { // Usuario encontrado.

            $res = [
                'code' => 200,
                'status' => 'succes',
                'user' => $user
            ];
        } else { // Usuario no encontrado.

            $res = [
                'code' => 404,
                'status' => 'error',
                'message' => 'No se encontro ningún usuario con ese id.'
            ];
        }

        return response()->json($res, $res['code']);
    }
}
