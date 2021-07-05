<?php
namespace App\Controller;
require_once "../models/EstadoUsuarios.php";
require_once "../models/EstadoMesa.php";
require_once "../models/EstadoPedido.php";

use App\Models\EstadoMesas as EMesa;
use App\Models\EstadoPedidos as EPedido;
use App\Models\EstadoUsuarios as EUsuario;

class EstadosController
{

    /**
     * Retorna el ID correspondiente al estado pasado como parámetro.
     *
     * @return Int/Null 
     */
    static function ReturnIdSegunEstado_Mesa($estadoBuscado)
    {
        $estado = new EMesa();

        $retorno = $estado::where("estado",$estadoBuscado)->first();

        return $retorno->id;
    }

    /**
     * Retorna el estado correspondiente al ID pasado como parámetro
     * 
     * @return String/Null
     */
    static function ReturnEstadoSegunId_Mesa($id)
    {
        $estado = new EMesa();

        $retorno = $estado::where("id",$id)->first();

        return $retorno->estado;
    }

    /**
     * Retorna el ID correspondiente al estado pasado como parámetro.
     *
     * @return Int/Null 
     */
    static function ReturnIdSegunEstado_Pedido($estadoBuscado)
    {
        $estado = new EPedido();

        $retorno = $estado::where("estado",$estadoBuscado)->first();

        return $retorno->id;
    }

    /**
     * Retorna el estado correspondiente al ID pasado como parámetro
     * 
     * @return String/Null
     */
    static function ReturnEstadoSegunId_Pedido($id)
    {
        $estado = new EPedido();

        $retorno = $estado::where("id",$id)->first();

        return $retorno->estado;
    }

    /**
     * Retorna el ID correspondiente al estado pasado como parámetro.
     *
     * @return Int/Null 
     */
    static function ReturnIdSegunEstado_Usuario($estadoBuscado)
    {
        $estado = new EUsuario();

        $retorno = $estado::where("estado",$estadoBuscado)->first();

        return $retorno->id;
    }

    /**
     * Retorna el estado correspondiente al ID pasado como parámetro
     * 
     * @return String/Null
     */
    static function ReturnEstadoSegunId_Usuario($id)
    {
        $estado = new EUsuario();

        $retorno = $estado::where("id",$id)->first();

        return $retorno->estado;
    }
}