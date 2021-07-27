<?php
namespace App\Controller;
require_once __DIR__ ."/../models/HistorialUsuarios.php";
require_once __DIR__ ."/../models/HistorialMesa.php";
require_once __DIR__ ."/../models/HistorialPedido.php";
require_once __DIR__ ."/../models/HistorialLista.php";
require_once "EstadosController.php";

use App\Models\HistorialMesa as HMesa;
use App\Models\HistorialPedido as HPedido;
use App\Models\HistorialUsuarios as HUsuarios;
use App\Models\HistorialLista as HLista;
use App\Controller\EstadosController as Estado;
use DateTime;
use DateTimeZone;

class HistorialesController 
{
    /**
     * @param Int $idObjeto id del objeto a cambiar
     * @param Int $viejoEstado id del viejo estado
     * @param Int $nuevoEstado id del nuevo estado
     * @param String $caso "mesa","pedido","usuario" para determinar en que tabla de historial se hace el insert
     * @return Bool true si fue guardado con éxito / false si falló el guardado
     */
    static function AltaEnHistorial($idObjeto,$viejoEstado,$nuevoEstado,$caso)
    {
        switch($caso)
        {
            case "mesa":
                $Objeto = new HMesa();
                $Objeto->id_mesa = $idObjeto;
                $Objeto->id_estado = $viejoEstado;
                $Objeto->id_estado_new = $nuevoEstado;
                break;
            case "pedido":
                $Objeto = new HPedido();
                $Objeto->id_pedido = $idObjeto;
                $Objeto->id_estado = $viejoEstado;
                $Objeto->id_estado_new = $nuevoEstado;
                break;
            case "usuario":
                $Objeto = new HUsuarios();
                $Objeto->id_usuario = $idObjeto;
                $Objeto->id_estado = $viejoEstado;
                $Objeto->id_estado_new = $nuevoEstado;
                break;
        }

        $Objeto->fecha_cambio = new DateTime('now', new DateTimeZone("America/Argentina/Buenos_Aires"));

        if($Objeto->save())
            $retorno = true;
        else
            $retorno = false;

        return $retorno;
    }

    /**
     * Realiza el alta en el historial de productos_empleados
     * @param Int $idEmpleado 
     * @param Int $idPedido
     * @param Int $idProductoPedido
     * @param Int $idEstado
     * @param Int $idEstadoNew
     * @param Datetime $fechaCambio
     * 
     * @return True-False true si se guardó correctamente, caso contrario: false
     */
    static function AltaEnHistorialLEA($idEmpleado,$idPedido,$idProductoPedido,$idEstado,$idEstadoNew,$fechaCambio)
    {
        $objeto = new HLista();

        $objeto->id_empleado = $idEmpleado;
        $objeto->id_pedido = $idPedido;
        $objeto->id_producto_pedido = $idProductoPedido;
        $objeto->id_estado = $idEstado;
        $objeto->id_estado_new = $idEstadoNew;
        $objeto->fecha_cambio = $fechaCambio;

        if($objeto->save())
            $ret = true;
        else
            $ret = false;

        return $ret;
    }
}