<?php

namespace App\Controller;

require_once __DIR__ ."/../models/TipoProducto.php";

use App\Models\TipoProducto;

class TipoProductoController
{
    static function RetornarIdSegunTipo($tipo)
    {
        $tipo = new TipoProducto();
        $match = $tipo::where("tipo",$tipo)->first();

        return $match->id;
    }
    static function RetornarTipoSegunId($id)
    {
        $tipo = new TipoProducto();
        $match = $tipo::where("id",$id)->first();

        return $match->tipo;
    }


}