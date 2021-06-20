<?php
namespace App\Controller;

require_once "./models/Pedido.php";
require_once './interfaces/IApiUsable.php';
require_once "GeneratorController.php";

use \app\Models\Pedido;
use App\Interface\IApiUsable;
use App\Controller\GeneratorController as GENERADOR;

class PedidoController implements IApiUsable
{
    public function Alta($request, $response, $args)
    {
        $parametros = $request->getParsedBody();

        $codigo = GENERADOR::GenerarCodigo_5_Caracteres();
        $codigoMesa = $parametros['codigo_mesa'];
        $idCliente = $parametros['id_cliente'];
        $idMozo= $parametros['id_mozo'];
        $lista = $parametros['lista'];
        
        // Creamos el Pedido
        $pdd = new Pedido();
        $pdd->codigo = $codigo;
        $pdd->codigo_mesa = $codigoMesa;
        $pdd->id_cliente = $idCliente;
        $pdd->id_mozo = $idMozo;
        $pdd->lista = $lista;
        $pdd->estado = 1;

        if($pdd->save())
            $payload = json_encode(array("mensaje" => "Exito en el guardado del pedido", "codigo_pedido" => $codigo));
        else
            $payload = json_encode(array("mensaje" => "Error en el guardado del pedido"));

        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function TraerUno($request, $response, $args)
    {
        // Buscamos Pedido por id
        $id= $args['id_pedido'];
        
        $pdd = Pedido::where('id', $id)->first();

        $payload = json_encode($pdd);

        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function TraerTodos($request, $response, $args)
    {
        $lista = Pedido::all();
        $payload = json_encode(array("listapedido" => $lista));

        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }
    
    public function ModificarEstado($request, $response, $args)
    {
        $parametros = $request->getParsedBody();

        $id = $parametros['id'];
        $estado = $parametros['estado_new'];

        $pdd = new Pedido();
        $pdd::find($id);
        $pdd->estado = $estado;

        $payload = json_encode(array("mensaje" => "Estado de pedido modificado"));

        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function BorrarUno($request, $response, $args)
    {
        $parametros = $request->getParsedBody();

        $pedidoId = $parametros['id_pedido'];

        $payload = json_encode(array("mensaje" => "Pedido borrado con exito"));

        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

}