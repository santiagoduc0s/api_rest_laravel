<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

use Illuminate\Support\Facades\Route;

// Middlewares
use \App\http\Middleware\ApiAuthMiddleware;

// User
Route::post('/api/user/login', 'UserController@login');
Route::post('/api/user/register', 'UserController@register');
Route::put('/api/user/update', 'UserController@update');
Route::post('/api/user/upload_avatar', 'UserController@uploadAvatar')->middleware(ApiAuthMiddleware::class);
Route::get('/api/user/avatar/{filename}', 'UserController@getAvatar');
Route::get('/api/user/{id}', 'UserController@getUser');



/**
 * 
 * Resource crea una seria de metodos definidos:
 *  index => lista las categorias.
 *  store => guarda una categoria.
 *  create => muestra una vista para crear una categoria.
 *  show => muestra el detalle de una categoria.
 *  update => actualiza una categoria.
 *  destroy => elimina una categoria.
 *  edit => muestra una vista para editar una categoria.
 * 
 */

// Category
Route::resource('/api/category', 'CategoryController');

// Posts
Route::resource('/api/post', 'PostController');
Route::post('/api/post/upload_image', 'PostController@upload');