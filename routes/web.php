<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\PruebasController;
use App\Http\Controllers\UserController;
use App\Http\Middleware\ApiAuthMiddleware;
use Illuminate\Support\Facades\Route;

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

Route::get('/', function () {
    return view('welcome');
});


#Rutas de Controladores API

//USUARIO
Route::post('/api/register', [UserController::class, 'register'])->name('api_user_register');
Route::post('/api/login', [UserController::class, 'login'])->name('api_user_login');
Route::put('/api/user/update', [UserController::class, 'update'])->name('api_user_update');
Route::post('/api/user/upload', [UserController::class, 'upload'])
                            ->name('api_user_upload')->middleware(ApiAuthMiddleware::class);

Route::get('/api/user/avatar/{filename}', [UserController::class, 'getImage'])->name('api_user_getimage');
Route::get('/api/user/detail/{id}', [UserController::class, 'detail'])->name('api_user_detail');                  

//CATEGORIAS
Route::resource('/api/category', CategoryController::class);#Rutas resource

//POSTS
Route::resource('/api/post', PostController::class);#Rutas resource

Route::post('/api/post/upload', [PostController::class, 'upload'])->name('api_post_upload');//Subir imagenes en post_table
Route::get('/api/post/image/{filename}', [PostController::class, 'getImage'])->name('api_post_getimage');

/*#Rutas Test

//Se inserta el controlador al que se llama la funcion y se coloca el nombre de la funcion
Route::get('/test', [PruebasController::class, 'testOrm'])->name('test_orm'); #Prueba del orm y relaciones de models

#Route::get('/user/test', [UserController::class, 'test'])->name('user_test');
#Route::get('/category/test', [CategoryController::class, 'test'])->name('post_test');
#Route::get('/post/test', [PostController::class, 'test'])->name('category_test');*/
