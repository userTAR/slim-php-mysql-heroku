<?php
require_once "./models/Mesa.php";
require_once './interfaces/IApiUsable.php';

use \app\Models\Mesa as Mesa;

class MesaController implements IApiUsable
{
    public function Alta($request, $response, $args)
    {
        $parametros = $request->getParsedBody();

        $sector = $parametros['sector'];


        // Creamos la Mesa
        $msa = new Mesa();
        $msa->sector = $sector;
        if($msa->save())
            $payload = json_encode(array("mensaje" => "Exito en el guardado del Mesa"));
        else
            $payload = json_encode(array("mensaje" => "Error en el guardado del Mesa"));
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function TraerUno($request, $response, $args)
    {
        // Buscamos Mesa por id
        $id_msa = $args['id_mesa'];
        
        $msa = Mesa::where('id', $id_msa)->first();

        $payload = json_encode($msa);

        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function TraerTodos($request, $response, $args)
    {
        $lista = Mesa::all();
        $payload = json_encode(array("listaMesa" => $lista));

        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }
    
    public function ModificarUno($request, $response, $args)
    {
        $parametros = $request->getParsedBody();

        $nombre = $parametros['nombre'];
        Mesa::modificarMesa($nombre);

        $payload = json_encode(array("mensaje" => "Mesa modificado con exito"));

        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function BorrarUno($request, $response, $args)
    {
        $parametros = $request->getParsedBody();

        $MesaId = $parametros['id_mesa'];
        Mesa::borrarMesa($MesaId);

        $payload = json_encode(array("mensaje" => "Mesa borrado con exito"));

        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }
}
