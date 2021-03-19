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

// Carga Middleware
use \App\http\Middleware\ApiAuthMiddleware;

// User
Route::post('/api/user/login', 'UserController@login');
Route::post('/api/user/register', 'UserController@register');
Route::put('/api/user/update', 'UserController@update');
Route::post('/api/user/upload_avatar', 'UserController@uploadAvatar')->middleware(ApiAuthMiddleware::class);
