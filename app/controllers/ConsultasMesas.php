<?php

namespace App\Controller;

require_once __DIR__ ."/../../vendor/autoload.php";
require_once __DIR__ ."/../models/Mesa.php";
require_once __DIR__ ."/../models/Pedido.php";
require_once __DIR__ ."/../models/Encuesta.php";
require_once "EstadosController.php";

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Controller\EstadosController;
use App\Models\Pedido;
use App\Models\Mesa;
use App\Models\Encuesta;


class ConsultasMesas
{
    public function MasUsada(Request $request, Response $response, array $args) : Response
    {
        $parametros = $request->getParsedBody();

        $fechaAntes = isset($parametros["fecha_anterior"]) ? $parametros["fecha_anterior"] : null;
        $fechaDespues = isset($parametros["fecha_posterior"]) ? $parametros["fecha_posterior"] : null;
        $flag = true;
        $mayor = 0;

        if($fechaAntes == null && $fechaDespues == null)
        {
            $masUsada = "error, datos vacios";
        }
        else if($fechaDespues == null)
        {
            $filter = Pedido::where('pedido_time',$fechaAntes)->get()->countBy(function ($value){
                return $value->codigo_mesa;
            });

            foreach ($filter as $key => $value) {
                if($value>$mayor || $flag == true)
                {
                    $mayor = $value;
                    $flag = false;
                    $llave = $key;
                }
            }
            $masUsada = Mesa::where('codigo', $llave)->first();                     
        }
        else
        {
            $filter = Pedido::whereBetween('pedido_time',[$fechaAntes,$fechaDespues])->get()->countBy(function($value){
                return $value->codigo_mesa;
            });
            
            foreach ($filter as $key => $value) {
                if($value>$mayor || $flag == true)
                {
                    $mayor = $value;
                    $flag = false;
                    $llave = $key;
                }
            }

            $masUsada = Mesa::where('codigo', $llave)->first();
        }

        $payload = json_encode(array("mensaje" => $masUsada));
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type','application/json');
    }

    public function MenosUsada(Request $request, Response $response, array $args) : Response
    {
        $parametros = $request->getParsedBody();

        $fechaAntes = isset($parametros["fecha_anterior"]) ? $parametros["fecha_anterior"] : null;
        $fechaDespues = isset($parametros["fecha_posterior"]) ? $parametros["fecha_posterior"] : null;
        $flag = true;
        $mayor = 0;

        if($fechaAntes == null && $fechaDespues == null)
        {
            $menosUsada = "error, datos vacios";
        }
        else if($fechaDespues == null)
        {
            $filter = Pedido::where('pedido_time',$fechaAntes)->get()->countBy(function ($value){
                return $value->codigo_mesa;
            });

            foreach ($filter as $key => $value) {
                if($value<$mayor || $flag == true)
                {
                    $mayor = $value;
                    $flag = false;
                    $llave = $key;
                }
            }
            $menosUsada = Mesa::where('codigo', $llave)->first();
        }
        else
        {
            $filter = Pedido::whereBetween('pedido_time',[$fechaAntes,$fechaDespues])->get()->countBy(function($value){
                return $value->codigo_mesa;
            });   
            
            foreach ($filter as $key => $value) {
                if($value<$mayor || $flag == true)
                {
                    $mayor = $value;
                    $flag = false;
                    $llave = $key;
                }
            }
            $menosUsada = Mesa::where('codigo', $llave)->first();
        }

        $payload = json_encode(array("mensaje" => $menosUsada));
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type','application/json');
    }

    public function MasFactura(Request $request, Response $response, array $args) : Response
    {
        $parametros = $request->getParsedBody();

        $fechaAntes = isset($parametros["fecha_anterior"]) ? $parametros["fecha_anterior"] : null;
        $fechaDespues = isset($parametros["fecha_posterior"]) ? $parametros["fecha_posterior"] : null;

        if($fechaAntes == null && $fechaDespues == null)
        {
            $mesas = json_encode(array("mensaje" => "error, datos vacios"));
            $response->getBody()->write($mesas);
            return $response->withHeader('Content-Type','application/json');
        }
        else if($fechaDespues == null)
        {
            $mesas = Pedido::where('pedido_time',$fechaAntes)->get();
        }
        else
        {
            $mesas = Pedido::whereBetween('pedido_time',[$fechaAntes,$fechaDespues])->get();
        }

        $masFacturo = [];
        $aux = [];
        $flag = true;

        foreach ($mesas as $value) {
            $facturaMesa = Pedido::where('estado',EstadosController::ReturnIdSegunEstado_Pedido("abonado"))->where('codigo_mesa',$value->codigo_mesa)->get()->sum('factura');
            $masFacturo[$value->codigo_mesa] = $facturaMesa;            
        }

        foreach ($masFacturo as $key => $value) {
            if($flag == true || $value > $aux["mayor"])
            {
                $aux["mayor"] = $value;
                $aux["key"] = $key;
                $flag = false;
            }
        }

        $payload = json_encode(array("mensaje" => "mesa: " .$aux["key"] ." / monto: " .$aux["mayor"]));
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type','application/json');
    }

    public function MenosFactura(Request $request, Response $response, array $args) : Response
    {
        $parametros = $request->getParsedBody();

        $fechaAntes = isset($parametros["fecha_anterior"]) ? $parametros["fecha_anterior"] : null;
        $fechaDespues = isset($parametros["fecha_posterior"]) ? $parametros["fecha_posterior"] : null;

        if($fechaAntes == null && $fechaDespues == null)
        {
            $mesas = json_encode(array("mensaje" => "error, datos vacios"));
            $response->getBody()->write($mesas);
            return $response->withHeader('Content-Type','application/json');
        }
        else if($fechaDespues == null)
        {
            $mesas = Pedido::where('pedido_time',$fechaAntes)->get();
        }
        else
        {
            $mesas = Pedido::whereBetween('pedido_time',[$fechaAntes,$fechaDespues])->get();
        }
        
        $masFacturo = [];
        $aux = [];
        $flag = true;

        foreach ($mesas as $value) {
            $facturaMesa = Pedido::where('estado',EstadosController::ReturnIdSegunEstado_Pedido("abonado"))->where('codigo_mesa',$value->codigo_mesa)->sum('factura');
            $masFacturo[$value->codigo_mesa] = $facturaMesa;            
        }
        foreach ($masFacturo as $key => $value) {
            if($flag == true || $value < $aux["menor"])
            {
                $aux["menor"] = $value;
                $aux["key"] = $key;
                $flag = false;
            }
        }

        $payload = json_encode(array("mensaje" => "mesa: " .$aux["key"] ." / monto: " .$aux["menor"]));
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type','application/json');
    }

    public function FacturaMayorImporte(Request $request, Response $response, array $args) : Response
    {
        $parametros = $request->getParsedBody();
        $fechaAntes = isset($parametros["fecha_anterior"]) ? $parametros["fecha_anterior"] : null;
        $fechaDespues = isset($parametros["fecha_posterior"]) ? $parametros["fecha_posterior"] : null;
        $array = array();

        if($fechaAntes == null && $fechaDespues == null)
        {
            $mesas = json_encode(array("mensaje" => "error, datos vacios"));
            $response->getBody()->write($mesas);
            return $response->withHeader('Content-Type','application/json');
        }
        else if($fechaDespues == null)
        {
            $mayorImporte = Pedido::where('pedido_time',$fechaAntes)->get()->max('factura');
            $pedidoConMayorImporte = Pedido::where('factura',$mayorImporte)->where('estado',EstadosController::ReturnIdSegunEstado_Pedido("abonado"))->get();
            foreach ($pedidoConMayorImporte as $key => $value) {
            array_push($array, Mesa::where('codigo',$value->codigo_mesa)->first());    
            }
        }
        else
        {
            $mayorImporte = Pedido::whereBetween('pedido_time',$fechaAntes)->get()->max('factura');
            $pedidoConMayorImporte = Pedido::where('factura',$mayorImporte)->where('estado',EstadosController::ReturnIdSegunEstado_Pedido("abonado"))->get();
            foreach ($pedidoConMayorImporte as $key => $value) {
            array_push($array, Mesa::where('codigo',$value->codigo_mesa)->first());    
            }
        }
        
        $payload = json_encode(array("mensaje" => $array));
        
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type','application/json');
    }

    public function FacturaMenorImporte(Request $request, Response $response, array $args) : Response
    {
        $parametros = $request->getParsedBody();

        $fechaAntes = isset($parametros["fecha_anterior"]) ? $parametros["fecha_anterior"] : null;
        $fechaDespues = isset($parametros["fecha_posterior"]) ? $parametros["fecha_posterior"] : null;
        $array = array();

        if($fechaAntes == null && $fechaDespues == null)
        {
            $mesas = json_encode(array("mensaje" => "error, datos vacios"));
            $response->getBody()->write($mesas);
            return $response->withHeader('Content-Type','application/json');
        }
        else if($fechaDespues == null)
        {
            $mayorImporte = Pedido::where('pedido_time',$fechaAntes)->get()->min('factura');
            $pedidoConMayorImporte = Pedido::where('factura',$mayorImporte)->where('estado',EstadosController::ReturnIdSegunEstado_Pedido("abonado"))->get();
            foreach ($pedidoConMayorImporte as $key => $value) {
            array_push($array, Mesa::where('codigo',$value->codigo_mesa)->first());    
            }
        }
        else
        {
            $mayorImporte = Pedido::whereBetween('pedido_time',$fechaAntes)->get()->min('factura');
            $pedidoConMayorImporte = Pedido::where('factura',$mayorImporte)->where('estado',EstadosController::ReturnIdSegunEstado_Pedido("abonado"))->get();
            foreach ($pedidoConMayorImporte as $key => $value) {
            array_push($array, Mesa::where('codigo',$value->codigo_mesa)->first());    
            }
        }

        $payload = json_encode(array("mensaje" => $array));
        
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type','application/json');
    }

    public function TotalFactura_DosFechas(Request $request, Response $response, array $args) : Response
    {
        $parametros = $request->getParsedBody();

        $fechaAnterior = $parametros["fecha_anterior"];
        $fechaPosterior = $parametros["fecha_posterior"];
        $mesa = $parametros["codigo_mesa"];

        $factura = Pedido::where('estado',EstadosController::ReturnIdSegunEstado_Pedido("abonado"))->where('codigo_mesa',$mesa)->whereBetween('pedido_time',[$fechaAnterior,$fechaPosterior])->get()->sum('factura');
        $payload = json_encode(array("mensaje" => $factura));

        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type','application/json');
    }

    public function MejorComentario(Request $request, Response $response, array $args) : Response
    {
        $parametros = $request->getParsedBody();

        $fechaAntes = isset($parametros["fecha_anterior"]) ? $parametros["fecha_anterior"] : null;
        $fechaDespues = isset($parametros["fecha_posterior"]) ? $parametros["fecha_posterior"] : null;
        $mesas = collect();
        $retorno = collect();

        if($fechaAntes == null && $fechaDespues == null)
        {
            $mesas = json_encode(array("mensaje" => "error, datos vacios"));
            $response->getBody()->write($mesas);
            return $response->withHeader('Content-Type','application/json');
        }
        else if($fechaDespues == null)
        {
            $puntuacionMasAlta = Encuesta::all()->max('mesa');

            $encuestas = Encuesta::where('mesa',$puntuacionMasAlta)->get();

            foreach ($encuestas as  $value) 
                $mesas->push(Pedido::where('codigo',$value->codigo_pedido)->where('pedido_time',$fechaAntes)->first());

            foreach ($mesas as $key => $value) {
                $retorno->push(Mesa::where('codigo',$value->codigo_mesa)->first());    
            }
        }
        else
        {
            $puntuacionMasAlta = Encuesta::all()->max('mesa');

            $encuestas = Encuesta::where('mesa',$puntuacionMasAlta)->get();

            foreach ($encuestas as  $value) 
                $mesas->push(Pedido::where('codigo',$value->codigo_pedido)->whereBetween('pedido_time',[$fechaAntes,$fechaDespues])->first());

            foreach ($mesas as $key => $value) {
                $retorno->push(Mesa::where('codigo',$value->codigo_mesa)->first());
            }
        }
        $payload = json_encode(array("mensaje" => $retorno));
        
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type','application/json');
    }

    public function PeorComentario(Request $request, Response $response, array $args) : Response
    {
        $parametros = $request->getParsedBody();

        $fechaAntes = isset($parametros["fecha_anterior"]) ? $parametros["fecha_anterior"] : null;
        $fechaDespues = isset($parametros["fecha_posterior"]) ? $parametros["fecha_posterior"] : null;
        $mesas = collect();
        $retorno = collect();

        if($fechaAntes == null && $fechaDespues == null)
        {
            $mesas = json_encode(array("mensaje" => "error, datos vacios"));
            $response->getBody()->write($mesas);
            return $response->withHeader('Content-Type','application/json');
        }
        else if($fechaDespues == null)
        {
            $puntuacionMasAlta = Encuesta::all()->min('mesa');

            $encuestas = Encuesta::where('mesa',$puntuacionMasAlta)->get();

            foreach ($encuestas as  $value) 
                $mesas->push(Pedido::where('codigo',$value->codigo_pedido)->where('pedido_time',$fechaAntes)->first());

            foreach ($mesas as $key => $value) {
                $retorno->push(Mesa::where('codigo',$value->codigo_mesa)->first());    
            }
        }
        else
        {
            $puntuacionMasAlta = Encuesta::all()->min('mesa');

            $encuestas = Encuesta::where('mesa',$puntuacionMasAlta)->get();

            foreach ($encuestas as  $value) 
                $mesas->push(Pedido::where('codigo',$value->codigo_pedido)->whereBetween('pedido_time',[$fechaAntes,$fechaDespues])->first());

            foreach ($mesas as $key => $value) {
                $retorno->push(Mesa::where('codigo',$value->codigo_mesa)->first());
            }
        }
        $payload = json_encode(array("mensaje" => $retorno));
        
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type','application/json');
    }

    /**
     * El cliente ingresa su número de pedido y el número de mesa y devuelve el tiempo estimado de arribo del pedido
     */
    public function ConsultarTiempo(Request $request, Response $response, array $args): Response
    {
        $parametros = $request->getParsedBody();

        $codigoMesa = $parametros["codigo_mesa"];
        $codigoPedido = $parametros["codigo_pedido"];

        $pedido = Pedido::where('codigo',$codigoPedido)->where('codigo_mesa',$codigoMesa)->first();

        if($pedido->eta == null)
            $payload = json_encode(array("mensaje"=>"su pedido aun no fue tomado, consulte más tarde"));
        else
            $payload = json_encode(array("mensaje" => $pedido->eta));
        
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type','application/json');
    }
}