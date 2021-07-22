<?php
namespace App\Controller;
require_once "../models/HistorialUsuarios.php";
require_once "../models/HistorialMesa.php";
require_once "../models/HistorialObjeto.php";
require_once "EstadosController.php";

use App\Models\HistorialMesa as HMesa;
use App\Models\HistorialPedido as HPedido;
use App\Models\HistorialUsuarios as HUsuarios;
use App\Controller\EstadosController as Estado;

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
                $Objeto->id_estado = Estado::ReturnIdSegunEstado_Mesa($viejoEstado);
                $Objeto->id_estado_new = Estado::ReturnIdSegunEstado_Mesa($nuevoEstado);
                break;
            case "pedido":
                $Objeto = new HPedido();
                $Objeto->id_pedido = $idObjeto;
                $Objeto->id_estado = Estado::ReturnIdSegunEstado_Pedido($viejoEstado);
                $Objeto->id_estado_new = Estado::ReturnIdSegunEstado_Pedido($nuevoEstado);
                break;
            case "usuario":
                $Objeto = new HUsuarios();
                $Objeto->id_usuario = $idObjeto;
                $Objeto->id_estado = Estado::ReturnIdSegunEstado_Usuario($viejoEstado);
                $Objeto->id_estado_new = Estado::ReturnIdSegunEstado_Usuario($nuevoEstado);
                break;
        }

        $Objeto->fecha_cambio = date("Y-m-d g:i:s",time());

        if($Objeto->save())
            $retorno = true;
        else
            $retorno = false;

        return $retorno;
    }
}