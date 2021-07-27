<?php

namespace App\Controller;

require_once __DIR__ ."/../models/HistorialUsuarios.php";
require_once __DIR__ ."/../models/HistorialLista.php";
require_once __DIR__ ."/../controllers/UsuarioController.php";
require_once __DIR__ ."/../../vendor/autoload.php";


use App\Models\HistorialUsuarios as HU;
use App\Models\HistorialLista as HL;
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
            $lista = HU::where('fecha',$fechaAntes)->get();
            $payload = json_encode(array("mensaje" => "solo una fecha ingresada", "lista"=>$lista));
        }
        else
        {
            $lista = HU::whereBetween('fecha', [$fechaAntes,$fechaDespues])->get(); 
            $payload = json_encode(array("mensaje" => "dos fechas ingresadas", "lista" => $lista));
        }

        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type','application/json');
    }

    public function OperacionesPorSector(Request $request, Response $response, array $args)
    {
        $parametros = $request->getParsedBody();

        $sector = isset($parametros["sector"])? $parametros["sector"] : null;
        $fechaAntes = isset($parametros["fecha_anterior"]) ? $parametros["fecha_anterior"] : null;
        $fechaDespues = isset($parametros["fecha_posterior"]) ? $parametros["fecha_posterior"] : null;

        if($fechaAntes == null && $fechaDespues == null)
        {
            $payload = json_encode(array("mensaje" => "error, datos vacios"));
        }
        else if($fechaDespues == null)
        {
            $ops = collect();

            $listaIDs = UsuarioController::ListaIdEmpleados_Sector($sector);
            $listaSector = HL::where('fecha', $fechaAntes)->get();

            foreach ($listaSector as $key => $value) {
                foreach ($listaIDs as $id) {
                    if($id == $value->id_empleado)
                        $ops->push($value);
                }
            }
            $payload = json_encode(array("mensaje" => "sector:" .$sector , "cantidad" => count($ops)));
        }
        else
        {
            $ops = collect();

            $listaIDs = UsuarioController::ListaIdEmpleados_Sector($sector);
            $listaSector = HL::whereBetween('fecha',[$fechaAntes,$fechaDespues])->get();

            foreach ($listaSector as $key => $value) {
                foreach ($listaIDs as $id) {
                    if($id == $value->id_empleado)
                        $ops->push($value);
                }
            }

            $mensaje = "sector: " .$sector ."/ entre fechas: " .$fechaAntes ." / " .$fechaDespues; 
            $payload = json_encode(array("mensaje" => $mensaje, "cantidad" => count($ops)));
        }

        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type','application/json');
    }

    public function OperacionesPorSector_Empleado(Request $request, Response $response, array $args)
    {
        $parametros = $request->getParsedBody();

        $sector = isset($parametros["sector"]) ? $parametros["sector"] : null;
        $fechaAntes = isset($parametros["fecha_anterior"]) ? $parametros["fecha_anterior"] : null;
        $fechaDespues = isset($parametros["fecha_posterior"]) ? $parametros["fecha_posterior"] : null;
        $ops = collect();
        
        if($fechaAntes == null && $fechaDespues == null || $sector == null)
        {
            $payload = json_encode(array("mensaje" => "error, datos vacios"));
        }
        else if($fechaDespues == null)
        {
            $listaIDs = UsuarioController::ListaIdEmpleados_Sector($sector);
            $listaSector = HL::where('fecha', $fechaAntes)->get();

            foreach ($listaSector as $key => $value) {
                foreach ($listaIDs as $id) {
                    if($id == $value->id_empleado)
                        $ops->push($value);
                }
            }

            $opsEmpleados = $ops->countBy(function($value){
                return $value->id_empleado;
            });
            
            $payload = json_encode(array("mensaje" => "sector:" .$sector , "cantidad" => $opsEmpleados));
        }
        else
        {
            $listaIDs = UsuarioController::ListaIdEmpleados_Sector($sector);
            $listaSector = HL::whereBetween('fecha',[$fechaAntes,$fechaDespues])->get();

            foreach ($listaSector as $key => $value) {
                foreach ($listaIDs as $id) {
                    if($id == $value->id_empleado)
                        $ops->push($value);
                }
            }

            $opsEmpleados = $ops->countBy(function($value){
                return $value->id_empleado;
            });

            $mensaje = "sector: " .$sector ."/ entre fechas: " .$fechaAntes ." / " .$fechaDespues; 
            $payload = json_encode(array("mensaje" => $mensaje, "cantidad" => count($ops)));
        }

        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type','application/json');
    }

    public function OperacionesPorEmpleado(Request $request, Response $response, array $args)
    {
        $parametros = $request->getParsedBody();

        $fechaAntes = isset($parametros["fecha_anterior"]) ? $parametros["fecha_anterior"] : null;
        $fechaDespues = isset($parametros["fecha_posterior"]) ? $parametros["fecha_posterior"] : null;
        $ops = collect();
        
        if($fechaAntes == null && $fechaDespues == null)
        {
            $payload = json_encode(array("mensaje" => "error, datos vacios"));
        }
        else if($fechaDespues == null)
        {
            $listaIDs = UsuarioController::ListaIdTodosLosEmpleados();
            $listaSector = HL::where('fecha', $fechaAntes)->get();

            foreach ($listaSector as $key => $value) {
                foreach ($listaIDs as $id) {
                    if($id == $value->id_empleado)
                        $ops->push($value);
                }
            }

            $opsEmpleados = $ops->countBy(function($value){
                return $value->id_empleado;
            });
            
            $payload = json_encode(array("mensaje" => "fecha:" .$fechaAntes , "cantidad" => $opsEmpleados));
        }
        else
        {
            $listaIDs = UsuarioController::ListaIdTodosLosEmpleados();
            $listaSector = HL::whereBetween('fecha', [$fechaAntes,$fechaDespues])->get();

            foreach ($listaSector as $key => $value) {
                foreach ($listaIDs as $id) {
                    if($id == $value->id_empleado)
                        $ops->push($value);
                }
            }

            $opsEmpleados = $ops->countBy(function($value){
                return $value->id_empleado;
            });
            
            $payload = json_encode(array("mensaje" => "fecha:" .$fechaAntes ."fecha:" ,$fechaDespues , "cantidad" => $opsEmpleados));
        }

        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type','application/json');
    }
}