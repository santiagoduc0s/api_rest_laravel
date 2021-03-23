<?php

namespace App\Http\Controllers;


use Illuminate\Http\Request;
use Illuminate\Http\Response;

use Illuminate\Support\Facades\DB;
use App\Post;

class PostController extends Controller
{
    public function __construct()
    {
        // Cargar middleware.
        $this->middleware('api.auth', ['except' => ['index', 'show']]);
    }

    public function index() // Listar posts.
    {
        $posts = Post::all()->load('category')->load('user');

        return response()->json([
            'code' => 200,
            'status' => 'succes',
            'posts' => $posts
        ], 200);
    }

    public function show($id) // Detalle de un posteo.
    {
        $post = Post::find($id)->load('category')->load('user');

        if (is_object($post)) {
            $res = [
                'code' => 200,
                'status' => 'succes',
                'post' => $post
            ];
        } else {

            $res = [
                'code'      => 404,
                'status'    => 'error',
                'message'   => 'El post no existe.'
            ];
        }

        return response()->json($res, $res['code']);
    }

    public function store(Request $req) // Guardar una categoria.
    {
        // Extraer datos enviados.
        $json = $req->input('json', null);
        $data_array = json_decode($json, true);

        // Validar campos.
        if (!is_null($json)) {

            $validate = \Validator::make($data_array, [
                'name'  => 'required|alpha'
            ]);

            if ($validate->fails()) { // Campos invalidos.

                $res = [
                    'code'      => 400,
                    'status'    => 'error',
                    'message'   => 'Los datos enviados son invalidos.'
                ];
            } else { // Campos validos.

                $category = new Category();
                $category->name = $data_array['name'];
                $category->save(); // Guardar categoria.

                $res = [
                    'code' => 200,
                    'message' => 'succes',
                    'category' => $category
                ];
            }
        } else {

            $res = [
                'code'      => 400,
                'status'    => 'error',
                'message'   => 'Ocurrio un error en el envío de la información.'
            ];
        }


        return response()->json($res, $res['code']);
    }

    public function update($id, Request $req) // Actualizar categoria.
    {
        // Buscar categoria
        $category = Category::find($id);

        // Extraer campos
        $data_array = json_decode($req->json, true);


        if (is_object($category)) { // Categoria encontrada.
            
            // Validar campos.
            $validate = \Validator::make($data_array, [
                'name'  => 'required|alpha'
            ]);

            if ($validate->fails()) { // Campos erroneos.

                $res = [
                    'code' => 400,
                    'status' => 'error',
                    'message' => 'Los datos ingresados no son correctos.'
                ];
            } else { // Campos correctos.

                unset($data_array['id']);
                unset($data_array['created_at']);
                unset($data_array['updated_at']);

                // Actualizar categoria.
                $user_update = Category::where('id', $id)->update($data_array);

                if ($user_update) { // Categoria actualizada.
                    
                    $res = [
                        'code' => 200,
                        'status' => 'succes',
                        'message' => 'Se ha actualizado la categoría correctamente.',
                        'category' => $category
                    ];
                } else { // Error al actualizar.
                    $res = [
                        'code' => 400,
                        'status' => 'succes',
                        'message' => 'No fue posible actualizar la categoria.',
                    ];
                }
            }
        } else {

            $res = [
                'code' => 404,
                'status' => 'error',
                'message' => 'La categoría no existe.'
            ];
        }

        return response()->json($res, $res['code']);
    }

    public function destroy($id) // Eliminar categoria.
    {
        $isDeleted = Category::destroy($id);

        if ($isDeleted) {

            $res = [
                'code'      => 200,
                'status'    => 'error',
                'message'   => 'La categoría fue eliminada.'
            ];
        } else {

            $res = [
                'code'      => 400,
                'status'    => 'error',
                'message'   => 'La categoría no existe.'
            ];
        }

        return response()->json($res, $res['code']);

    }
}
