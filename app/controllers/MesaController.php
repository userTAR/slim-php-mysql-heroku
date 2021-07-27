<?php
namespace App\Controller;

require_once __DIR__ ."/../models/Mesa.php";
require_once __DIR__ ."/../interfaces/IMesa.php";
require_once __DIR__ ."/../../vendor/autoload.php";
require_once "GeneratorController.php";
require_once "EstadosController.php";
require_once "HistorialesController.php";

use App\Models\Mesa;
use App\Models\Pedido;
use App\Controller\EstadosController as ESTADOS;
use App\Controller\GeneratorController as GENERADOR;
use App\Controller\HistorialesController;
use App\Interfaces\IMesa;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class MesaController implements Imesa
{
    public function Alta(Request $request, Response $response, Array $args): Response
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
        {
            $mesa = Mesa::where('codigo',$codigo)->first();
            HistorialesController::AltaEnHistorial($mesa->id,ESTADOS::ReturnIdSegunEstado_Mesa("cerrada"),ESTADOS::ReturnIdSegunEstado_Mesa("cerrada"),"mesa");
            $payload = json_encode(array("mensaje" => "Exito en el guardado del Mesa", "codigo_mesa" => $codigo));
        }
        else
            $payload = json_encode(array("mensaje" => "Error en el guardado del Mesa"));
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function TraerUno(Request $request, Response $response, Array $args): Response
    {
        // Buscamos Mesa por id
        $id_msa = $args['id_mesa'];
        
        $msa = Mesa::where('id', $id_msa)->first();

        $payload = json_encode($msa);

        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function TraerTodos(Request $request, Response $response, Array $args): Response
    {
        $lista = Mesa::all();
        $payload = json_encode(array("listaMesa" => $lista));

        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function BorrarUno(Request $request, Response $response, Array $args): Response
    {
        $parametros = $request->getParsedBody();

        $MesaId = $parametros['id_mesa'];
        $mesa = Mesa::find($MesaId);
        if($mesa->delete())
            $payload = json_encode(array("mensaje" => "Mesa borrado con exito"));
        else
            $payload = json_encode(array("mensaje" => "Borrado de mesa fallido"));

        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    //solo deberían poder acceder los socios
    /**
     * Se cambia el estado de la mesa a "cerrada" y se guarda el movimiento en el historial de mesas
     */
    public function CerrarMesa(Request $request, Response $response, array $args): Response
    {
        $parametros = $request->getParsedBody();

        $idMesa = $parametros["id_mesa"];

        $mesa = Mesa::where("id",$idMesa)->where("id_estado",ESTADOS::ReturnIdSegunEstado_Mesa("con cliente pagando"))->first();
        HistorialesController::AltaEnHistorial($mesa->id,$mesa->id_estado,ESTADOS::ReturnIdSegunEstado_Mesa("cerrada"),"mesa");
        $mesa->id_estado = ESTADOS::ReturnIdSegunEstado_Mesa("cerrada");
        if($mesa->save())
            $payload = json_encode(array("mensaje" => "Mesa cerrada"));
        else
            $payload = json_encode(array("mensaje" => "Fallo en el cierre de mesa"));

        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }
}
