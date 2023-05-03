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
                $pwd = password_hash($params->password, PASSWORD_BCRYPT, ['cost' => 4]);

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
        echo $jwtAuth->signup();

        return "Action to log in User";
    }
}
