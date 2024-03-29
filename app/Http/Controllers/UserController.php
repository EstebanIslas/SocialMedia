<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class UserController extends Controller
{
    public function register(Request $request)
    {
        # Recibir datos de User por post
        $json = $request->input('json', null);
        $params = json_decode($json); //Recibe objetos
        $params_array = json_decode($json, true); //Recibe array

        if (!empty($params) && !empty($params_array)) {
        
            #Limpiar Datos
            $params_array = array_map('trim', $params_array);

            # Validación de datos
            $validate = \Validator::make($params_array, [
                'name'      => 'required|alpha',
                'surname'   => 'required|alpha',
                'email'     => 'required|email|unique:users',
                'password'  => 'required'
            ]);# Verificar duplicidad de datos 'unique'

            if ($validate->fails()) {
                $data = array(
                    'status'    => 'error',
                    'code'      => 404,
                    'message'   => 'El usuario no se ha creado',
                    'errors'    => $validate->errors()
                );
            }else{

                # Cifrado de password
                //password_hash($params->password, PASSWORD_BCRYPT, ['cost' => 4]);
                
                $pwd =  hash('sha256', $params->password);//Hacer que el cifrado siempre retorne el mismo valor

                # Set de User
                $user = new User();
                $user->name     = $params_array['name'];
                $user->surname  = $params_array['surname'];
                $user->email     = $params_array['email'];
                $user->password = $pwd;
                $user->role = 'ROLE_USER';

                # Guardar Usuario en la BD
                $user->save();

                # Devolver Datos en Json
                $data = array(
                    'status'    => 'success',
                    'code'      => 200,
                    'message'   => 'El usuario se ha creado correctamente'
                );
            }
        }else{
            $data = array(
                'status'    => 'error',
                'code'      => 404,
                'message'   => 'Los datos enviados no son correctos'
            );
        }

        return response()->json($data, $data['code']);
    }

    public function login(Request $request)
    {
        $jwtAuth = new \JwtAuth();

        #Recibir datos POST
        $json = $request->input('json', null);
        $params = json_decode($json);
        $params_array = \GuzzleHttp\json_decode($json, true);
        
        #Validar Datos
        $validate = \Validator::make($params_array, [
            'email'     => 'required|email',
            'password'  => 'required'
        ]);# Verificar duplicidad de datos 'unique'

        if ($validate->fails()) {
            $signup = array(
                'status'    => 'error',
                'code'      => 404,
                'message'   => 'El usuario no se ha identificado correctamente',
                'errors'    => $validate->errors()
            );
        }else{
            #Cifrado de pwd
            $pwd =  hash('sha256', $params->password);//Hacer que el cifrado siempre retorne el mismo valor

            #Devolver token o datos
            $signup = $jwtAuth->signup($params->email, $pwd);

            if (!empty($params->gettoken)) {
                $signup = $jwtAuth->signup($params->email, $pwd, true);
            }
        }

        return response()->json($signup, 200);
    }

    public function update(Request $request)
    {

        #Comprobar si el usuario esta identificado

        $token = $request->header('Authorization');
        $jwtAuth = new \JwtAuth();
        $checkToken = $jwtAuth->checkToken($token);

        #Recibir datos Post

        $json = $request->input('json', null);
        $params_array = json_decode($json, true);

        if ($checkToken && !empty($params_array)) {

            #Obtener usuario identificado
            $user = $jwtAuth->checkToken($token, true);

            #Validar Datos
            $validate = \Validator::make($params_array, [
                'name'      => 'required|alpha',
                'surname'   => 'required|alpha',
                'email'     => 'required|email|unique:users'.$user->sub
            ]);# Verificar duplicidad de datos

            #Remover campos que no se van a actualizar
            unset($params_array['id']);
            unset($params_array['role']);
            unset($params_array['password']);
            unset($params_array['created_at']);
            unset($params_array['remember_token']);

            #Actualizar en la Bd
            $user_update = User::where('id', $user->sub)->update($params_array);

            #Retornar resultado
            $data = array(
                'code'     => 200,
                'status'   => 'success',
                'message'  => 'El usuario se ha actualizado exitosamente',
                'user'     => $user,
                'changes' => $params_array
            );

        }else{
            $data = array(
                'code'     => 400,
                'status'   => 'error',
                'message'  => 'El usuario no esta identificado'
            );
        }

        return response()->json($data, $data['code']);
    }

    public function upload(Request $request)
    {
        #Obtener los datos de la peticion
        $image = $request->file('file0');

        #validacion de la imagen
        $validate = \Validator::make($request->all(),[
            'file0' => 'required|image|mimes:jpg,jpeg,png,gif'
        ]);

        #Guardar imagen
        if (!$image || $validate->fails()) {
            
            #Devolver Resultado
            $data = array(
                'code'     => 400,
                'status'   => 'error',
                'message'  => 'Error al subir imagen'
            );

        }else{
            
            $image_name = time().$image->getClientOriginalName();
            \Storage::disk('users')->put($image_name, \File::get($image));

            $data = array(
                'code'    => 200,
                'status'  => 'success',
                'image'   => $image_name
            );
        }

        return response()->json($data, $data['code']);
    }

    public function getImage($filename)
    {
        $isset = \Storage::disk('users')->exists($filename);

        if ($isset) {

            $file = \Storage::disk('users')->get($filename);
            return new Response($file, 200);

        }else {
            
            $data = array(
                'code'    => 404,
                'status'  => 'error',
                'message'   => 'La imagen no existe'
            );
            return response()->json($data, $data['code']);
        }
        
    }

    public function detail($id)
    {
        $user = User::find($id);

        if (is_object($user)) {
            $data = array(
                'code'     => 200,
                'status'   => 'success',
                'user'     => $user
            );
        }else{
            $data = array(
                'code'     => 404,
                'status'   => 'error',
                'message'     => 'El usuario no se encontro'
            );
        }

        return response()->json($data, $data['code']);
    }
}
