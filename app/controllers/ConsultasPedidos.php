<?php

namespace App\Controller;

require_once __DIR__ ."/../../vendor/autoload.php";
require_once __DIR__ ."/../models/Pedido.php";
require_once __DIR__ ."/../models/ListaEmpleadosProductos.php";
require_once __DIR__ ."/../models/Producto.php";
require_once "EstadosController.php";

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Controller\EstadosController as ESTADOS;
use App\Models\Pedido; 
use App\Models\Producto; 
use App\Models\ListaEmpleadosProductos as LEA; 

class ConsultasPedidos
{
    public function MasVendido(Request $request, Response $response, array $args) : Response
    {
        $parametros = $request->getParsedBody();

        $fechaAntes = isset($parametros["fecha_anterior"]) ? $parametros["fecha_anterior"] : null;
        $fechaDespues = isset($parametros["fecha_posterior"]) ? $parametros["fecha_posterior"] : null;
        $flag = true;
        $mayor = 0;
        $pedidosMatcheados = collect();

        if($fechaAntes == null && $fechaDespues == null)
        {
            $masPedido = "error, datos vacios";
        }
        else if($fechaDespues == null)
        {
            $pedidosID = Pedido::where('pedido_time',$fechaAntes)->where('estado',ESTADOS::ReturnIdSegunEstado_Pedido("abonado"))->get();

            foreach ($pedidosID as $key => $value) {
                $pedidosMatcheados->push(LEA::where('id_pedido',$value->id)->get());
            }

            $collapsed = $pedidosMatcheados->collapse();

            $filter = $collapsed->countBy(function ($value){
                return $value->id_producto_pedido;
            });

            foreach ($filter as $key => $value) {
                if($value>$mayor || $flag == true)
                {
                    $mayor = $value;
                    $flag = false;
                    $llave = $key;
                }
            }
            $masPedido = Producto::where('id', $llave)->first();
        }
        else
        {
            $pedidosID = Pedido::whereBetween('pedido_time',[$fechaAntes,$fechaDespues])->where('estado',ESTADOS::ReturnIdSegunEstado_Pedido("abonado"))->get();

            foreach ($pedidosID as $key => $value) {
                $pedidosMatcheados->push(LEA::where('id_pedido',$value->id)->get());
            }

            $collapsed = $pedidosMatcheados->collapse();

            $filter = $collapsed->countBy(function ($value){
                return $value->id_producto_pedido;
            });

            foreach ($filter as $key => $value) {
                if($value>$mayor || $flag == true)
                {
                    $mayor = $value;
                    $flag = false;
                    $llave = $key;
                }
            }
            $masPedido = Producto::where('id', $llave)->first();
        }

        $payload = json_encode(array("mensaje" => $masPedido));
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type','application/json');
    }

    public function MenosVendido(Request $request, Response $response, array $args) : Response
    {
        $parametros = $request->getParsedBody();

        $fechaAntes = isset($parametros["fecha_anterior"]) ? $parametros["fecha_anterior"] : null;
        $fechaDespues = isset($parametros["fecha_posterior"]) ? $parametros["fecha_posterior"] : null;
        $flag = true;
        $mayor = 0;
        $pedidosMatcheados = collect();

        if($fechaAntes == null && $fechaDespues == null)
        {
            $menosPedido = "error, datos vacios";
        }
        else if($fechaDespues == null)
        {
            $pedidosID = Pedido::where('pedido_time',$fechaAntes)->where('estado',ESTADOS::ReturnIdSegunEstado_Pedido("abonado"))->get();

            foreach ($pedidosID as $key => $value) {
                $pedidosMatcheados->push(LEA::where('id_pedido',$value->id)->get());
            }

            $collapsed = $pedidosMatcheados->collapse();

            $filter = $collapsed->countBy(function ($value){
                return $value->id_producto_pedido;
            });

            foreach ($filter as $key => $value) {
                if($value<$mayor || $flag == true)
                {
                    $mayor = $value;
                    $flag = false;
                    $llave = $key;
                }
            }
            $menosPedido = Producto::where('id', $llave)->first();
        }
        else
        {
            $pedidosID = Pedido::whereBetween('pedido_time',[$fechaAntes,$fechaDespues])->where('estado',ESTADOS::ReturnIdSegunEstado_Pedido("abonado"))->get();

            foreach ($pedidosID as $key => $value) {
                $pedidosMatcheados->push(LEA::where('id_pedido',$value->id)->get());
            }

            $collapsed = $pedidosMatcheados->collapse();

            $filter = $collapsed->countBy(function ($value){
                return $value->id_producto_pedido;
            });

            foreach ($filter as $key => $value) {
                if($value<$mayor || $flag == true)
                {
                    $mayor = $value;
                    $flag = false;
                    $llave = $key;
                }
            }
            $menosPedido = Producto::where('id', $llave)->first();
        }

        $payload = json_encode(array("lista" => $menosPedido));
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type','application/json');
    }

    public function FueraDeTiempo(Request $request, Response $response, array $args) : Response
    {
        $parametros = $request->getParsedBody();

        $fechaAntes = isset($parametros["fecha_anterior"]) ? $parametros["fecha_anterior"] : null;
        $fechaDespues = isset($parametros["fecha_posterior"]) ? $parametros["fecha_posterior"] : null;

        if($fechaAntes == null && $fechaDespues == null)
        {
            $lista = "error, datos vacios";
        }
        else if($fechaDespues == null)
        {
            $lista = Pedido::where('pedido_time',$fechaAntes)->where('estado',ESTADOS::ReturnIdSegunEstado_Pedido("abonado"))->get()->filter(function($value){
                if($value->eta < $value->entrega_time)
                    return $value;
            });
        }
        else
        {
            $lista = Pedido::whereBetween('pedido_time',[$fechaAntes,$fechaDespues])->where('estado',ESTADOS::ReturnIdSegunEstado_Pedido("abonado"))->get()->filter(function($value){
                if($value->eta < $value->entrega_time)
                    return $value;
            });
        }

        $payload = json_encode(array("lista" => $lista));
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type','application/json');
    }

    public function Cancelados(Request $request, Response $response, array $args) : Response
    {
        $parametros = $request->getParsedBody();

        $fechaAntes = isset($parametros["fecha_anterior"]) ? $parametros["fecha_anterior"] : null;
        $fechaDespues = isset($parametros["fecha_posterior"]) ? $parametros["fecha_posterior"] : null;

        if($fechaAntes == null && $fechaDespues == null)
        {
            $listaCancelados = "error, datos vacios";
        }
        else if($fechaDespues == null)
        {
            $listaCancelados = Pedido::where('pedido_time',$fechaAntes)->where('estado',ESTADOS::ReturnIdSegunEstado_Pedido("cancelado"))->get();
        }
        else
        {
            $listaCancelados = Pedido::whereBetween('pedido_time',[$fechaAntes,$fechaDespues])->where('estado',ESTADOS::ReturnIdSegunEstado_Pedido("cancelado"))->get();
        }
        $payload = json_encode(array("lista" => $listaCancelados));

        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type','application/json');
    }
}