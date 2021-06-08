<?php
require_once "./models/Usuario.php";
require_once './interfaces/IApiUsable.php';

use \app\Models\Usuario as Usuario;

class UsuarioController extends Usuario implements IApiUsable
{
    public function Alta($request, $response, $args)
    {
        $parametros = $request->getParsedBody();

        $nombre = $parametros['nombre'];
        $clave = $parametros['clave'];
        $sector = $parametros['sector'];
        $tipo = $parametros['tipo'];


        // Creamos el usuario
        $usr = new Usuario();
        $usr->nombre = $nombre;
        $usr->clave = $clave;
        $usr->sector = $sector;
        $usr->tipo = $tipo;

        if($usr->save())
            $payload = json_encode(array("mensaje" => "Exito en el guardado del usuario"));
        else
            $payload = json_encode(array("mensaje" => "Error en el guardado del usuario"));
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function TraerUno($request, $response, $args)
    {
        // Buscamos usuario por id
        $id_usr = $args['id_usuario'];
        
        $usr = Usuario::where('id', $id_usr)->first();

        $payload = json_encode($usr);

        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function TraerTodos($request, $response, $args)
    {
        $lista = Usuario::all();
        $payload = json_encode(array("listaUsuario" => $lista));

        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }
    
    public function ModificarUno($request, $response, $args)
    {
        $parametros = $request->getParsedBody();

        $id = $parametros["id"];
        $nombre = $parametros["nombre"];
        $tipo = $parametros["tipo"];

        $usr = Usuario::find($id);
        $usr->nombre = $nombre;
        $usr->tipo = $tipo;

        if($usr->save())
            $payload = json_encode(array("mensaje" => "Éxito en la modificación del usuario"));
        else
            $payload = json_encode(array("mensaje" => "Error en la modificación del usuario"));
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function BorrarUno($request, $response, $args)
    {
        $parametros = $request->getParsedBody();

        $usuarioId = $parametros['id_usuario'];

        $usr = Usuario::find($usuarioId);
        $usr->delete();

        $payload = json_encode(array("mensaje" => "Usuario borrado con exito"));
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }
}
