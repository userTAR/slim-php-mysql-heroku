<?php
namespace App\Controller;

require_once "./models/Mesa.php";
require_once './interfaces/IApiUsable.php';
require_once "GeneratorController.php";

use App\Models\Mesa;
use App\Interface\IApiUsable;
use App\Controller\GeneratorController as GENERADOR;

class MesaController implements IApiUsable
{
    public function Alta($request, $response, $args)
    {
        $parametros = $request->getParsedBody();

        $sector = $parametros['sector'];
        $codigo = GENERADOR::GenerarCodigo_5_Caracteres();

        // Creamos la Mesa
        $msa = new Mesa();
        $msa->codigo = $codigo;
        $msa->sector = $sector;
        $msa->id_estado = 4;
        if($msa->save())
            $payload = json_encode(array("mensaje" => "Exito en el guardado del Mesa", "codigo_mesa" => $codigo));
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
