<?php
namespace App\Controller;
require_once __DIR__ ."/../models/EstadoUsuarios.php";
require_once __DIR__ ."/../models/EstadoMesas.php";
require_once __DIR__ ."/../models/EstadoPedidos.php";

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
        $retorno = EMesa::where("estado",$estadoBuscado)->first();

        return $retorno->id;
    }

    /**
     * Retorna el estado correspondiente al ID pasado como parámetro
     * 
     * @return String/Null
     */
    static function ReturnEstadoSegunId_Mesa($id)
    {
        $retorno = EMesa::where("id",$id)->first();

        return $retorno->estado;
    }

    /**
     * Retorna el ID correspondiente al estado pasado como parámetro.
     *
     * @return Int/Null 
     */
    static function ReturnIdSegunEstado_Pedido($estadoBuscado)
    {
        $retorno = EPedido::where("estado",$estadoBuscado)->first();

        return $retorno->id;
    }

    /**
     * Retorna el estado correspondiente al ID pasado como parámetro
     * 
     * @return String/Null
     */
    static function ReturnEstadoSegunId_Pedido($id)
    {
        $retorno = EPedido::where("id",$id)->first();

        return $retorno->estado;
    }

    /**
     * Retorna el ID correspondiente al estado pasado como parámetro.
     *
     * @return Int/Null 
     */
    static function ReturnIdSegunEstado_Usuario($estadoBuscado)
    {
        $retorno = EUsuario::where("estado",$estadoBuscado)->first();
        $id = $retorno->id;

        return $id;
    }

    /**
     * Retorna el estado correspondiente al ID pasado como parámetro
     * 
     * @return String/Null
     */
    static function ReturnEstadoSegunId_Usuario($id)
    {
        $retorno = EUsuario::where("id",$id)->first();

        return $retorno->estado;
    }
}