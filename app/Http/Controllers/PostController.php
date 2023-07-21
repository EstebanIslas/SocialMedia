<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\Post;
use App\Helpers\JwtAuth;

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

    public function store(Request $request)
    {
        #Obtener datos por Post
        $json = $request->input('json', null);
        $params = json_decode($json);
        $params_array = json_decode($json, true);

        if (!empty($params_array)) {
            
            #Obtener usuario identificado por token
            $jwtAuth = new JwtAuth();
            $token = $request->header('Authorization', null);
            $user = $jwtAuth->checkToken($token, true);

            #Validar datos
            $validate = \Validator::make($params_array, [
                'title'         => 'required',
                'content'   => 'required',
                'category_id'   => 'required',
                'image'   => 'required'
            ]);

            if ($validate->fails()) {
                $data = [
                    'code'      => 400,
                    'status'    => 'error',
                    'message'   => 'Error al guardar post, faltan datos'
                ];
            } else {
                #Guardar datos en Bd
                $post = new Post();
                $post->user_id = $user->sub;
                $post->category_id = $params->category_id;
                $post->title = $params->title;
                $post->content = $params->content;
                $post->image = $params->image;

                $post->save();

                $data = array(
                    'code'       => 200,
                    'status'     => 'success',
                    'posts'      => $post
                );
            }
                
        } else {
            $data = [
                'code'      => 400,
                'status'    => 'error',
                'message'   => 'Error al guardar post, faltan datos'
            ];
        }
        
        #Retornar Resultado
        return response()->json($data, $data['code']);
    }

    public function update($id, Request $request)
    {
        #Obtener datos por post
        $json = $request->input('json', null);
        $params_array = json_decode($json, true);

        if (!empty($params_array)) {
            
            #Validar los datos
            $validate = \Validator::make($params_array, [
                'title' => 'required',
                'content' => 'required',
                'category_id' => 'required'
            ]);

            if ($validate->fails()) {
                $data = [
                    'code'       => 404,
                    'status'     => 'error',
                    'message'    => 'El post no se actualizó faltan datos'
                ];
            } else {
                
                #Omitir campos para no actualizar
                unset($params_array['id']);
                unset($params_array['user_id']);
                unset($params_array['created_at']);
                unset($params_array['user']);
        
                #actualizar datos en la Bd
                $post = Post::where('id', $id)->update($params_array);
        
                $data = [
                    'code'      => 200,
                    'status'    => 'success',
                    'message'   => $params_array
                ];
            }
            
        } else {
            $data = [
                'code'      => 400,
                'status'    => 'error',
                'message'   => 'Error al actualizar post'
            ];
        }
        
        #Retornar Resultado
        return response()->json($data, $data['code']);
    }
}
