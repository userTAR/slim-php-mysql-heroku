<?php

namespace App\Controller;

require_once __DIR__ ."/../models/Encuesta.php";

use App\Models\Encuesta;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class EncuestasController
{
    public function AltaEncuesta(Request $request, Response $response, array $args) : Response
    {
        $parametros = $request->getParsedBody();

        $codigoPedido = $parametros["codigo_pedido"];
        $valueMesa = $parametros["mesa"];
        $valueRest = $parametros["restaurante"];
        $valueMozo = $parametros["mozo"];
        $valueCocina = $parametros["cocina"];
        $resumen = $parametros["resumen"];

        if(($valueCocina>0 && $valueCocina<11) && ($valueMozo>0 && $valueMozo<11) && ($valueRest>0 && $valueRest<11) && ($valueMesa>0 && $valueMesa<11))
        {
            $encuesta = new Encuesta();
            $encuesta->codigo_pedido = $codigoPedido;
            $encuesta->mesa = $valueMesa;
            $encuesta->restaurante = $valueRest;
            $encuesta->mozo = $valueMozo;
            $encuesta->cocina = $valueCocina;
            $encuesta->resumen = $resumen;
            if($encuesta->save())
                $payload = json_encode(array("mensaje" => "Encuesta guardada con exito"));
            else
                $payload = json_encode(array("mensaje" => "Fallo en el guardado de la encuesta"));
            }
        else
            $payload = json_encode(array("mensaje" => "Error en los parámetros, puntuacion fuera de limites"));

        $response->getBody()->write($payload);
        return $response->withHeader("Content-Type","application/json");

    }
}