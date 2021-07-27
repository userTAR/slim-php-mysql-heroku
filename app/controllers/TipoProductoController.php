<?php

namespace App\Controller;

require_once __DIR__ ."/../models/TipoProducto.php";

use App\Models\TipoProducto;

class TipoProductoController
{
    static function RetornarIdSegunTipo($tipo)
    {
        $match = TipoProducto::where("tipo",$tipo)->first();
        return $match->id;
    }
    static function RetornarTipoSegunId($id)
    {
        $match = TipoProducto::where("id",$id)->first();
        return $match->tipo;
    }


}