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

    #OBTENER USUARIO IDENTIFICADO CON TOKEN
    private function getIdentity($request){

        $jwtAuth = new JwtAuth();
        $token = $request->header('Authorization', null);
        $user = $jwtAuth->checkToken($token, true);

        return $user;
    }

    private function getData($fill, $status){

        if ($status == '200') {
            $data = array(
                'code'       => 200,
                'status'     => 'success',
                'posts' => $fill
            );
        }else {
            $data = array(
                'code'       => 404,
                'status'     => 'error',
                'message'    => $fill
            );
        }
        return $data;
    }
    
    public function index()
    {
        $post = Post::all()->load('category');
        
        if (is_object($post)) {
            $data = $this->getData($post, '200');
        }else{
            $data = $this->getData('Error al consultar los post', '404');
        }

        return response()->json($data, $data['code']);
    }

    public function show($id){
        $post = Post::find($id)->load('category');

        if (is_object($post)) {
            $data = $this->getData($post, '200');
        }else{
            $data = $this->getData('Error al consultar los post', '404');
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
            $user = $this->getIdentity($request);

            #Validar datos
            $validate = \Validator::make($params_array, [
                'title'         => 'required',
                'content'   => 'required',
                'category_id'   => 'required',
                'image'   => 'required'
            ]);

            if ($validate->fails()) {
                $data = $this->getData('Error al guardar post, faltan datos', '404');
            } else {
                #Guardar datos en Bd
                $post = new Post();
                $post->user_id = $user->sub;
                $post->category_id = $params->category_id;
                $post->title = $params->title;
                $post->content = $params->content;
                $post->image = $params->image;

                $post->save();

                $data = $this->getData($post, '200');
            }
                
        } else {
            $data = $this->getData('Error al guardar post, los campos estan vacios', '404');
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
                $data = $this->getData('Error al guardar post, faltan datos', '404');
            } else {
                
                #Omitir campos para no actualizar
                unset($params_array['id']);
                unset($params_array['user_id']);
                unset($params_array['created_at']);
                unset($params_array['user']);

                #Obtener usuario identificado por token
                $user = $this->getIdentity($request);
        
                #Obtener post que pertenezca al user logueado
                $post = Post::where('id', $id)->where('user_id', $user->sub)->first();

                if (!empty($post) && is_object($post)) {
                  
                    #actualizar datos en la Bd
                    $post->update($params_array);

                    $data = $this->getData($post, '200');
                
                }else{

                    $data = $this->getData('Error al actualizar, el usuario no es propietario del post', '404');

                }
                
                /*#actualizar datos en la Bd
                $where = [
                    'id' => $id,
                    'user_id' =>$user->sub
                ];

                $post = Post::updateOrCreate($where, $params_array);*/
        
            }
            
        } else {
            $data = $this->getData('Error al guardar post, los campos estan vacios', '404');
        }
        
        #Retornar Resultado
        return response()->json($data, $data['code']);
    }

    public function destroy($id, Request $request){

        #Obtener user logueado
        $user = $this->getIdentity($request);
        
        #Obtener datos por post
        $post = Post::where('id', $id)->where('user_id', $user->sub)->first();

        #Comprobar si existe
        if(!empty($post)){
            #Eliminar registro
            $post->delete();

            $data = $this->getData($post, '200');
        }else {
            $data = $this->getData('Error al eliminar post', '404');
        }
        #Retornar resultado
        return response()->json($data, $data['code']);
    }

    #Subir imagenes
    public function upload(Request $request){
        #Obtener imagen de la peticion
        $image = $request->file('file0');

        #Validar la imagen
        $validate = \Validator::make($request->all(), [
            'file0' => 'required|image|mimes:jpg,jpeg,png,gif'
        ]);

        #Guardar imagen en disco images
        if (!$image || $validate->fails()) {
            $data = $this->getData('Error al subir imagen', '400');
        }else{
            $image_name = time().$image->getClientOriginalName();

            \Storage::disk('images')->put($image_name, \File::get($image));

            $data = $this->getData($image_name, '200');
        }

        #Retornar resultado
        return response()->json($data, $data['code']);
    }
}
