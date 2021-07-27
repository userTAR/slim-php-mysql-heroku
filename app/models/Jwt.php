<?php
namespace App\Models;

require_once __DIR__ ."/../../vendor/autoload.php";

use Exception as GlobalException;
use Firebase\JWT\JWT;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\SignatureInvalidException;

class Token
{
    private static $password = "password123";
    private static $encode = array('HS256');

    public static function Crear($usuario)
    {
        $data = array("nombre" => $usuario->nombre, "perfil" => $usuario->tipo, "estado" => $usuario->estadoId);
        $time = time();
        $payload = array(
            'iat'=> $time,
            'exp'=> $time + (10*60),
            'data' => $data,    
        );
        return JWT::encode($payload,self::$password,self::$encode[0]);
    }

    public static function Verificar($token){
        if(empty($token)|| $token=="")
            throw new GlobalException("El token esta vacio.");
        try
        {
            $decodificado = self::ObtenerDatos($token);
        }
        catch (ExpiredException $e)
        {
            throw new GlobalException("Clave fuera de tiempo");
        }
        catch (SignatureInvalidException $e)
        {
            throw new GlobalException("Token incorrecto");
        }
        return $decodificado;
    }

    public static function ObtenerDatos($token){
        return JWT::decode($token, self::$password, self::$encode)->data;
    }

}

