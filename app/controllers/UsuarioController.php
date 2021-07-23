<?php
namespace App\Controller;

require_once "./models/Usuario.php";
require_once './interfaces/IApiUsable.php';
require_once "PerfilUsuarioController.php";
require_once __DIR__ ."/../models/ListaEmpleadosProductos.php";

use App\Models\Usuario as Usuario;
/* use App\Interfaces\IApiUsable; */
use App\Controller\PerfilUsuarioController;
use App\Models\ListaEmpleadosProductos;

class UsuarioController //implementar API
{
    public function Alta($request, $response, $args)
    {
        $parametros = $request->getParsedBody();

        $nombre = $parametros['nombre'];
        $clave = $parametros['clave'];
        $sector = !empty($parametros['sector']) ? $parametros['sector'] : null;
        $tipo = PerfilUsuarioController::RetornarIdPorPerfil($parametros['tipo']);
        
        if($tipo != null)
        {   
            // Creamos el usuario
            $usr = new Usuario();
            $usr->nombre = $nombre;
            $usr->clave = $clave;
            $usr->sector = $sector;
            $usr->tipo = $tipo;
            $usr->estado_id = 1;
            $usr->alta = date("Y-m-d H:i:s");
            $usr->baja = null;

            if($usr->save())
                $payload = json_encode(array("mensaje" => "Exito en el guardado del usuario"));
            else
                $payload = json_encode(array("mensaje" => "Error en el guardado del usuario"));
        }
        else
            $payload = json_encode(array("mensaje" => "Error en el guardado del usuario, tipo de perfil inexistente"));
        
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function TraerUno($request, $response, $args)
    {
        // Buscamos usuario por id
        $id_usr = $args['id_usuario'];
        
        $usr = Usuario::where('id' , $id_usr)->first();

        $payload = json_encode($usr);

        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function TraerTodos($request, $response, $args)
    {
        $lista = Usuario::all();
        $payload = json_encode(array("listaUsuario" => $lista));

        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }
    
    public function ModificarUno($request, $response, $args)
    {
        $parametros = $request->getParsedBody();

        $id = $parametros["id"];
        $nombre = $parametros["nombre"];
        $tipo = $parametros["tipo"];

        $usr = Usuario::find($id);
        $usr->nombre = $nombre;
        $usr->tipo = $tipo;

        if($usr->save())
            $payload = json_encode(array("mensaje" => "Éxito en la modificación del usuario"));
        else
            $payload = json_encode(array("mensaje" => "Error en la modificación del usuario"));
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function BorrarUno($request, $response, $args)
    {
        $parametros = $request->getParsedBody();

        $usuarioId = $parametros['id_usuario'];

        $usr = Usuario::find($usuarioId);
        if($usr->delete())
            $payload = json_encode(array("mensaje" => "Usuario borrado con exito"));
        else
            $payload = json_encode(array("mensaje" => "No se pudo borrar el usuario"));
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    /**
     * Devuelve la llave (EL ID) del empleado menos ocupado según el rubro (bar, cerveza, comida) que se le haya pasado como parámetro
     * @param Seccion : La seccion de donde se quiere buscar
     * @return Menor : Devuelve el ID del empleado menos ocupado
     */
    static function ReturnIdEmpleadoMenosOcupadoSegun_Seccion($seccion)
    {
        $usuarios = new Usuario();
        $listaUsuarios = $usuarios::where("tipo",$seccion)->get();

        $PedidosLista = new ListaEmpleadosProductos();
        //obtengo la cantidad de veces que aparece cada empleado y lo guardo en {variable[user-id]} = {cantidad de veces que aparece}
        foreach($listaUsuarios as $user)
        {
            $repeticiones = $PedidosLista::where("id_empleado",$user->id)->count();
            $array[$user->id] = $repeticiones;
        }
        $flag = true;
        $menor = null;
        //busco cual es el menor y guardo el ID
        foreach($array as $key => $value)
        {
            if($flag == true || $value < $menor)
            {
                $flag = false;
                $menor = $key;
            }
        }

        return $menor;
    }

    /**
     * Devuelve un array con todos los id de los empleados que pertenezcan al sector pasado por parametro
     * @param String $sector El sector en el que se quiere buscar los empleados
     * @return Array Devuelve un array con todos los ID's de los empleados que esten en ese sector
     */
    static function ListaIdEmpleados_Sector($sector)
    {
        $retorno = array();

        $lista = Usuario::where('sector',$sector)->get();
        foreach ($lista as $key => $value) {
            array_push($retorno,$lista->id);
        }

        return $retorno;
    }
}
