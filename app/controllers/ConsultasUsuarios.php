<?php

namespace App\Controller;

require_once __DIR__ ."/../models/HistorialUsuarios.php";
require_once __DIR__ ."/../models/ListaEmpleadosProductos.php";
require_once __DIR__ ."/../controllers/UsuarioController.php";
require_once __DIR__ ."/../../vendor/autoload.php";


use App\Models\HistorialUsuarios as HU;
use App\Models\ListaEmpleadosProductos as LEA;
use App\Controller\UsuarioController;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class ConsultasUsuarios
{
    public function IngresosRegistrados(Request $request, Response $response, array $args)
    {
        $parametros = $request->getParsedBody();

        $fechaAntes = isset($parametros["fecha_anterior"]) ? $parametros["fecha_anterior"] : null;
        $fechaDespues = isset($parametros["fecha_posterior"]) ? $parametros["fecha_posterior"] : null;

        if($fechaAntes == null && $fechaDespues == null)
        {
            $payload = json_encode(array("mensaje" => "error, datos vacios"));
        }
        else if($fechaDespues == null)
        {
            $lista = HU::where('fecha',$fechaAntes)->get(); //revisar si el campo "fecha" es el correcto en la base de datos 
            $payload = json_encode(array("mensaje" => "solo una fecha ingresada", "lista"=>$lista));
        }
        else
        {
            $lista = HU::whereBetween('fecha', [$fechaAntes,$fechaDespues]); //revisar si el campo "fecha" es el correcto en la base de datos
            $payload = json_encode(array("mensaje" => "dos fechas ingresadas", "lista" => $lista));
        }

        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type','application/json');
    }

    public function OperacionesPorSector(Request $request, Response $response, array $args)
    {
        $parametros = $request->getParsedBody();

        $sector = isset($parametros["sector"]);
        $fechaAntes = isset($parametros["fecha_anterior"]) ? $parametros["fecha_anterior"] : null;
        $fechaDespues = isset($parametros["fecha_posterior"]) ? $parametros["fecha_posterior"] : null;

        if($fechaAntes == null && $fechaDespues == null)
        {
            $payload = json_encode(array("mensaje" => "error, datos vacios"));
        }
        else if($fechaDespues == null)
        {
            $listaIDs = UsuarioController::ListaIdEmpleados_Sector($sector);
            $listaSector = LEA::where('fecha', $fechaAntes);

            $payload = json_encode(array("mensaje" => "sector:" .$sector , "cantidad" => $cantSector));
        }
        else
        {
            $cantSector = HU::where('sector', $sector)->whereBetween('fecha', [$fechaAntes,$fechaDespues])->count();
            $mensaje = "sector: " .$sector ."/ entre fechas: " .$fechaAntes ." / " .$fechaDespues; 
            $payload = json_encode(array("mensaje" => $mensaje, "cantidad" => $cantSector));
        }

        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type','application/json');
    }

    public function OperacionesPorSector_Empleado(Request $request, Response $response, array $args)
    {
        
    }
}