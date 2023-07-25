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
        $categories = Category::all();

        if (is_object($categories)) {
            $data = $this->getData($categories, '200');
        } else {
            $data = $this->getData('Error al consultar las categorias', '400');
        }
        

        return response()->json($data, $data['code']);
    }

    public function show($id)
    {
        $category = Category::find($id);

        if (is_object($category)) {
            $data = $this->getData($category, '200');
        }else{
            $data = $this->getData('Error La categoria no existe', '400');
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
                $data = $this->getData('Error La categoria no se guardo correctamente', '400');
            }else{
                $category = new Category();
                $category->name = $params_array['name'];
                $category->save();
    
                $data = $this->getData($category, '200');
            }

        } else {
            $data = $this->getData('Error los campos estan vacios', '400');
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

            $data = $this->getData($params_array, '200');
            
        } else {
            $data = $this->getData('Error Los campos estan vacios', '400');
        }

        #Retornar Resultado
        return response()->json($data, $data['code']);
        
    }

    public function destroy($id)
    {
        #Obtener datos por post
        $category = Category::find($id);

        #Comprobar que existan los datos
        if (!empty($category)) {
            
            #Eliminar registro
            $category->delete();

            $data = $this->getData($category, '200');
        } else {
            $data = $this->getData('Error al eliminar los datos', '400');
        }
        
        #Retornar el resultado
        return response()->json($data, $data['code']);
    }
}
