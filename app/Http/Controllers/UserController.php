<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

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
        $token = $request->header('Authorization');
        $jwtAuth = new \JwtAuth();
        $checkToken = $jwtAuth->checkToken($token);

        if ($checkToken) {
            echo "<h3>Login correcto</h3>";
        }else{
            echo "<h3>Login incorrecto</h3>";
        }

        die();
    }
}
