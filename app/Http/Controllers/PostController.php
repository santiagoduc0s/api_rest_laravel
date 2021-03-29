<?php

namespace App\Http\Controllers;


use Illuminate\Http\Request;
use Illuminate\Http\Response;
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

    public function store(Request $req) // Guardar un posteo.
    {
        // Extraer datos enviados.
        $json = $req->input('json', null);
        $data_array = json_decode($json, true);

        // Recibir token.
        $token = $req->header('Authorization');

        // Decodificar token.
        $JwtAuth = new \JwtAuth();
        $user = $JwtAuth->checkToken($token, true);

        // Validar campos.
        if (!is_null($json)) {

            $validate = \Validator::make($data_array, [
                'title'  => 'required',
                'content'  => 'required',
                'category_id'  => 'required'
            ]);

            if ($validate->fails()) { // Campos invalidos.

                $res = [
                    'code'      => 400,
                    'status'    => 'error',
                    'message'   => 'Faltan enviar datos.',
                    'errors' => $validate->errors()
                ];
            } else { // Campos validos.

                $post = new Post();
                $post->title = $data_array['title'];
                $post->content = $data_array['content'];
                $post->category_id = $data_array['category_id'];
                $post->user_id = $user->sub;
                $post->save(); // Guardar posteo.

                $res = [
                    'code' => 200,
                    'status' => 'succes',
                    'message' => 'Se ha creado el posteo.',
                    'post' => $post
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

    public function update($id, Request $req) // Actualizar post.
    {
        // Recibir usuario
        $user = $this->getUser($req);

        // Buscar post.
        $where = [
            'id'        => $id,
            'user_id'   => $user->sub
        ];
        $post = Post::where($where)->first();

        // Extraer campos.
        $data_array = json_decode($req->json, true);

        if (is_object($post)) { // Categoria encontrada.

            // Validar campos.
            $validate = \Validator::make($data_array, [
                'title'  => 'required',
                'content'  => 'required',
                'category_id'  => 'required'
            ]);

            if ($validate->fails()) { // Campos erroneos.

                $res = [
                    'code' => 400,
                    'status' => 'error',
                    'message' => 'Los datos ingresados no son correctos.',
                    'errors' => $validate->errors()
                ];
            } else { // Campos correctos.

                unset($data_array['id']);
                unset($data_array['user_id']);
                unset($data_array['created_at']);
                unset($data_array['updated_at']);

                // Actualizar posteo.
                $post_update = Post::where('id', $id)->update($data_array);

                if ($post_update) { // Categoria actualizada.

                    $res = [
                        'code' => 200,
                        'status' => 'succes',
                        'message' => 'Se ha actualizado la categoría correctamente.',
                        'post' => $post,
                        'changes' => $data_array
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

    public function destroy($id, Request $req) // Eliminar post.
    {
        // Recibir usuario
        $user = $this->getUser($req);

        // Buscar post.
        $where = [
            'id'        => $id,
            'user_id'   => $user->sub
        ];
        $post = Post::where($where)->first();

        if (is_object($post)) {

            // Eliminar post.
            Post::destroy($id);
            //$post->destroy();

            $res = [
                'code'      => 200,
                'status'    => 'succes',
                'message'   => 'La categoría fue eliminada.',
                'post'      => $post
            ];
        } else {

            $res = [
                'code'      => 400,
                'status'    => 'error',
                'message'   => 'El post no existe.'
            ];
        }


        return response()->json($res, $res['code']);
    }

    public function upload(Request $req) // Subir imagen de posteo
    {
        // Recibir imagen
        $image = $req->file('file0');

        // Validar imagen.
        $validate = \Validator::make($req->all(), [
            'file0' => 'required|mimes:png,jpg,jpeg' // Comprueba imagen.
        ]);

        if ($validate->fails()) { // Validacion fallida.

            $res = [
                'code' => 400,
                'status' => 'error',
                'message' => 'Ocurrió un error en la subida del archivo.'
            ];
        } else { // Validacion correcta.

            $image_name = time() . $image->getClientOriginalName();

            /**
             * Para usuar este disco, primero debe ser registrado creado y registrado 
             * en \config\filesystems.php
             */
            \Storage::disk('posts')->put($image_name, \File::get($image)); // Guardar imagen.

            $res = [
                'code' => 200,
                'status' => 'succes',
                'message' => 'La imagen del post fue subida correctamente.',
                'image' => $image_name
            ];
        }

        return response()->json($res, $res['code']);
    }

    public function getImage($filename)
    {
        // Existe el archivo.
        $exists = \Storage::disk('posts')->exists($filename);

        if ($exists) { // Si existe.

            $file = \Storage::disk('posts')->get($filename);
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

    private function getUser($requst)
    {
        // Recibir token.
        $token = $requst->header('Authorization');
        // Decodificar token.
        $JwtAuth = new \JwtAuth();
        $user = $JwtAuth->checkToken($token, true);
        return $user;
    }
}
