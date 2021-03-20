<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

use App\Category;

class CategoryController extends Controller
{

    public function __construct()
    {
        // Cargar middleware.
        $this->middleware('api.auth', ['except' => ['index', 'show']]);
    }

    public function index() // Listar categorias.
    {
        $categories = Category::all();

        return response()->json([
            'code' => 200,
            'status' => 'succes',
            'categories' => $categories
        ]);
    }

    public function show($id) // Detalle de una categoria.
    {
        $category = Category::find($id);

        if (is_object($category)) {
            $res = [
                'code' => 200,
                'status' => 'succes',
                'category' => $category
            ];
        } else {

            $res = [
                'code'      => 404,
                'status'    => 'error',
                'message'   => 'La categoría no existe.'
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
}
