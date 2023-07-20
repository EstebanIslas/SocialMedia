<?php

namespace App\Helpers;

use Firebase\JWT\JWT; 
use Firebase\JWT\Key;
#Agregando libreria jwt
use Illuminate\Support\Facades\DB; #Agregar coneccion a la BD
use App\Models\User;

class JwtAuth{

    public $key;
    
    public function __construct()
    {
        $this->key = 'password_very_save98966';
    }
    

    public function signup($email, $password, $getToken = null)
    {
        # Buscar si existe el usuario con las credenciales (email - pwd)
        $user = User::where([
            'email' => $email,
            'password' => $password
        ])->first();

        # Comprobar si son correctas
        $signup = false;
        if (is_object($user)) {
            $signup = true;
        }

        # Generación de token con los datos del user
        if ($signup) {
            
            $token = array(
                'sub'       => $user->id,
                'email'     => $user->email,
                'name'      => $user->name,
                'surname'   => $user->surname,
                'iat'       => time(),
                'exp'       => time() + (7 * 24 * 60 * 60)
            );
            /*
                sub -> hace referencia al id del usuario
                iat -> hace referencia a la fecha de creacion del token
                exp -> hace referencia a la expiracion del token (7 * 24 * 60 * 60) -> una semana
            */

            $jwt = JWT::encode($token, $this->key, 'HS256'); //Algoritmo de cifrado
            $decode = JWT::decode($jwt, new key($this->key, 'HS256'));
            

            # Retornar los datos decodificados o el token en funcion de un parametro
            if (is_null($getToken)) {
                $data = $jwt;
            }else{
                $data = $decode;
            }
            
        }else{
            $data = array(
                'status'    => 'error',
                'message'   => 'login incorrecto'
            );
        }

        return $data;
    }
    
    public function checkToken($jwt, $getIdentity = false)
    {
        $auth = false;

        try {

            $jwt = str_replace('"', '', $jwt); //Quitar comillas del token recibido
            $decoded = JWT::decode($jwt, new key($this->key, 'HS256'));
        } catch (\UnexpectedValueException $e) {
            $auth = false;
        }catch(\DomainException $e){
            $auth = false;
        }

        if (!empty($decoded) && is_object($decoded) && isset($decoded->sub)) {
            $auth = true;
        }else{
            $auth = false;
        }

        if ($getIdentity) {
            return $decoded;
        }

        return $auth;
    }
}
