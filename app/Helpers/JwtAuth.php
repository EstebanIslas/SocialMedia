<?php

namespace App\Helpers;

use Firebase\JWT\JWT; #Agregando libreria jwt
use Illuminate\Support\Facades\DB; #Agregar coneccion a la BD
use App\Models\User;

class JwtAuth{

    public function signup()
    {
        # Buscar si existe el usuario con las credenciales (email - pwd)

        # Comprobar si son correctas

        # Generación de token con los datos del user

        # Retornar los datos decodificados o el token en funcion de un parametro

        return 'Metodo signup de la clase JwtAuth';
    }
    
}
