<?php
namespace App\Controller;

require_once __DIR__ ."/../models/PerfilesUsuarios.php";

use App\Models\PerfilUsuario;

class PerfilUsuarioController
{
    public static function RetornarPerfilPorId($id)
    {
        $perfil = new PerfilUsuario();

        $retornoPerfil = $perfil::where("id","=",$id)->first();

        return $retornoPerfil->perfil;
    }

    public static function RetornarIdPorPerfil($perfil)
    {
        $perfilLook = new PerfilUsuario();

        $retornoPerfil = $perfilLook::where("perfil","=",$perfil)->first();

        return $retornoPerfil->id;
    }
}