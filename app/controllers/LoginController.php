<?php
namespace App\Controller;

require_once "PerfilUsuarioController.php";

use App\Models\Token;
use App\Models\Usuario;
use stdClass;
use App\Controller\PerfilUsuarioController;

class LoginController 
{
    public function InicioSesion($request, $response, $args)
    {
        $parametros = $request->getParsedBody();
        $usr = new Usuario();
        $check = $usr->where("nombre","=",$parametros['nombre'])->where("clave","=",$parametros['clave'])->first();
        $tipoPerfil = PerfilUsuarioController::RetornarPerfilPorId($check->tipo);
        if($check != null)
        {
            $usuario = new stdClass();
            $usuario->nombre = $check->nombre;
            $usuario->tipo = $tipoPerfil;
            $usuario->estadoId = $check->estado_id;

            $payload = json_encode(array("mensaje" => "Sesion iniciada", "token" => Token::Crear($usuario)));
        }
        else
            $payload = json_encode(array("mensaje" => "Usuario no existente"));
        
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

}