<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\Post;

class PostController extends Controller
{
    public function __construct()
    {
        $this->middleware('api.auth', ['except' => ['index', 'show']]);
    }

    public function index()
    {
        $post = Post::all()->load('category');
        
        if (is_object($post)) {
            $data = array(
                'code'       => 200,
                'status'     => 'success',
                'posts' => $post
            );
        }else{
            $data = array(
                'code'       => 404,
                'status'     => 'error',
                'message'    => 'Los posts no existen'
            );
        }

        return response()->json($data, $data['code']);
    }

    public function show($id){
        $post = Post::find($id)->load('category');

        if (is_object($post)) {
            $data = array(
                'code'       => 200,
                'status'     => 'success',
                'posts' => $post
            );
        }else{
            $data = array(
                'code'       => 404,
                'status'     => 'error',
                'message'    => 'El posts no existe'
            );
        }

        return response()->json($data, $data['code']);

    }

}
