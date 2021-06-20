<?php
namespace App\Controller;
require_once "../models/Jwt.php";
require_once "../models/Usuario.php";

use App\Models\Token;
use App\Models\Usuario;
use stdClass;

class LoginController 
{
    public function InicioSesion($request, $response, $args)
    {
        $parametros = $request->getParsedBody();
        $usr = new Usuario();
        $check = $usr->where("nombre","=",$parametros['nombre'])->where("clave","=",$parametros['clave'])->get();
        if($check != null)
        {
            $usuario = new stdClass();
            $usuario->nombre = $parametros['nombre'];
            $usuario->perfil = $parametros['perfil'];
            $usuario->estadoId = $parametros['estado_id'];

            $payload = json_encode(array("mensaje" => "Sesion iniciada", "token" => Token::Crear($usuario)));
        }
        else
            $payload = json_encode(array("mensaje" => "Usuario no existente"));
        
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

}