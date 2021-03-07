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
        $posts = Post::all();

        foreach ($posts as $post) {
            echo "<h2>{$post->title} - {$post->category->name} - {$post->user->name}</h2>";
        }

        echo '<hr>'; // -------------------------------------------------

        $categories = Category::all();

        foreach ($categories as $cat) {
            echo "<h2>{$cat->name}</h2>";
            echo '<ul>';

            if (count($cat->posts) > 0) {
                foreach ($cat->posts as $post) {
                    echo "<li>{$post->title}</li>";
                }
            } else {
                echo '<li>No hay posts</li>';
            }
            
            echo '</ul>';
        }

        echo '<hr>'; // -------------------------------------------------

        $users = User::all();

        foreach ($users as $user) {
            echo "<h2>{$user->name}</h2>";
            echo '<ul>';

            if (count($user->posts) > 0) {
                foreach ($user->posts as $post) {
                    echo "<li>{$post->title}</li>";
                }
            } else {
                echo '<li>No hay posts</li>';
            }
            
            echo '</ul>';
        }


        die();
    }
}
