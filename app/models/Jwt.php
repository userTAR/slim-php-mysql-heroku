<?php
namespace App\Models;

require_once "./vendor/autoload.php";

use Firebase\JWT\JWT;
use FFI\Exception;
use Firebase\JWT\ExpiredException;

class Token
{
    private static $password = "password123";
    private static $encode = ['HS256'];

    public static function Crear($usuario)
    {
        $data = array("nombre" => $usuario->nombre, "perfil" => $usuario->perfil, "estado" => $usuario->estadoId);
        $time = time();
        $payload = array(
            'iat'=> $time,
            'exp'=> $time + (10*60),
            'data' => $data,    
        );
        return JWT::encode($payload,self::$password,self::$encode);
    }

    public static function Verificar($token){
        if(empty($token)|| $token=="")
            throw new Exception("El token esta vacio.");
        try 
        {
            $decodificado = self::ObtenerDatos($token);
        }
        catch (ExpiredException $e)
        {
           throw new Exception("Clave fuera de tiempo");
        }
        return $decodificado;
    }

    public static function ObtenerDatos($token){
        return JWT::decode($token, self::$password, self::$encode)->data;
    }

}

