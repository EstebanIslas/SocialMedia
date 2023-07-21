<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\Category;

class CategoryController extends Controller
{

    public function __construct()
    {
        $this->middleware('api.auth', ['except' => ['index', 'show']]);
    }

    public function index()
    {
        $categories = Category::all();

        return response()->json([
            'code'       => 200,
            'status'     => 'success',
            'categories' => $categories
        ]);
    }

    public function show($id)
    {
        $category = Category::find($id);

        if (is_object($category)) {
            $data = array(
                'code'       => 200,
                'status'     => 'success',
                'categories' => $category
            );
        }else{
            $data = array(
                'code'       => 404,
                'status'     => 'error',
                'message'    => 'La categoria no existe'
            );
        }
        return response()->json($data, $data['code']);
    }

    public function store(Request $request)
    {
        #Obtener datos por post
        $json = $request->input('json', null);
        $params_array = json_decode($json, true);

        if (!empty($params_array)) {
            #Validar Datos
            $validate = \Validator::make($params_array, [
                'name' => 'required'
            ]);
    
            #Guardar en la bd
            if ($validate->fails()) {
                $data = [
                    'code'       => 404,
                    'status'     => 'error',
                    'message'    => 'La categoria no se guardo correctamente'
                ];
            }else{
                $category = new Category();
                $category->name = $params_array['name'];
                $category->save();
    
                $data = [
                    'code'       => 200,
                    'status'     => 'success',
                    'categories' => $category
                ];
            }

        } else {
            $data = [
                'code'       => 404,
                'status'     => 'error',
                'message'    => 'Los campos de categoria estan vacios'
            ];
        }
        
        #Retornar Resultado
        return response()->json($data, $data['code']);
    }

    public function update($id, Request $request)
    {
        #Obtener Datos por post
        $json = $request->input('json', null);
        $params_array = json_decode($json, true);

        if (!empty($params_array)) {
            
            #Validar Datos
            $validate = \Validator::make($params_array,[
                'name' => 'required'
            ]);
            
            #Omitir lo que no se va a actualizar
            unset($params_array['id']);
            unset($params_array['created_at']);
            
            #Actualizar registro en la bd
            $category = Category::where('id', $id)->update($params_array);

            $data = [
                'code'       => 200,
                'status'     => 'success',
                'category'   => $params_array
            ];
            
        } else {
            $data = [
                'code'       => 404,
                'status'     => 'error',
                'message'    => 'Los campos de categoria estan vacios'
            ];
        }

        #Retornar Resultado
        return response()->json($data, $data['code']);
        
    }
}
