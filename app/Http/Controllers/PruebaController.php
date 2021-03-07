<?php

namespace App\Http\Controllers;

use App\Category;
use App\Post;
use App\User;
use Illuminate\Http\Request;

class PruebaController extends Controller
{
    public function listarUsuarios()
    {
        return User::all();
    }

    public function listarCategorias()
    {
        return Category::all();
    }

    public function listarPost()
    {
        return Post::all();
    }
}
