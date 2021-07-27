<?php
namespace App\Controller;

require_once __DIR__ ."/../../vendor/autoload.php";
require_once __DIR__ ."/../models/Usuario.php";
require_once __DIR__ ."/../models/ListaEmpleadosProductos.php";
require_once "PerfilUsuarioController.php";
require_once "HistorialesController.php";
require_once "EstadosController.php";

use App\Models\Usuario as Usuario;
use App\Models\ListaEmpleadosProductos;
use App\Controller\HistorialesController;
use App\Controller\PerfilUsuarioController;
use App\Controller\EstadosController;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class UsuarioController
{
    public function Alta(Request $request, Response $response, array $args) : Response
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
            {
                $usr = Usuario::where('nombre',$nombre)->where('clave',$clave)->first();
                HistorialesController::AltaEnHistorial($usr->id,EstadosController::ReturnIdSegunEstado_Usuario("activo"),EstadosController::ReturnIdSegunEstado_Usuario("activo"),"usuario");
                $payload = json_encode(array("mensaje" => "Exito en el guardado del usuario"));
            }
            else
                $payload = json_encode(array("mensaje" => "Error en el guardado del usuario"));
        }
        else
            $payload = json_encode(array("mensaje" => "Error en el guardado del usuario, tipo de perfil inexistente"));
        
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function TraerUno(Request $request, Response $response, array $args) : Response
    {
        // Buscamos usuario por id
        $id_usr = $args['id_usuario'];
        
        $usr = Usuario::where('id' , $id_usr)->first();

        $payload = json_encode($usr);

        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function TraerTodos(Request $request, Response $response, array $args) : Response
    {
        $lista = Usuario::all();
        $payload = json_encode(array("listaUsuario" => $lista));

        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }
    
    public function ModificarUno(Request $request, Response $response, array $args) : Response
    {
        $parametros = $request->getParsedBody();

        $id = $parametros["id"];
        $nombre = $parametros["nombre"];
        $tipo = $parametros["tipo"];
        $sector = isset($parametros["sector"]) ? $parametros["sector"] : null;

        $usr = Usuario::find($id);
        $usr->nombre = $nombre;
        $usr->tipo = PerfilUsuarioController::RetornarIdPorPerfil("socio");
        $usr->sector = $sector;

        if($usr->save() && HistorialesController::AltaEnHistorial($usr->id,EstadosController::ReturnIdSegunEstado_Usuario("activo"),EstadosController::ReturnIdSegunEstado_Usuario("activo"),"usuario"))
            $payload = json_encode(array("mensaje" => "Éxito en la modificación del usuario"));
        else
            $payload = json_encode(array("mensaje" => "Error en la modificación del usuario"));
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function BorrarUno(Request $request, Response $response, array $args) : Response
    {
        $parametros = $request->getParsedBody();

        $usuarioId = $parametros['id_usuario'];

        $usr = Usuario::find($usuarioId);
        $usr->estado_id = EstadosController::ReturnIdSegunEstado_Usuario("suspendido");
        $usr->baja = date("Y-m-d H:i:s");
    
        if($usr->save() && HistorialesController::AltaEnHistorial($usr->id,EstadosController::ReturnIdSegunEstado_Usuario("activo"),EstadosController::ReturnIdSegunEstado_Usuario("suspendido"),"usuario"))
            $payload = json_encode(array("mensaje" => "Usuario borrado con exito"));
        else
            $payload = json_encode(array("mensaje" => "No se pudo borrar el usuario"));
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    /**
     * Devuelve la llave (EL ID) del empleado menos ocupado según el rubro (bar, cerveza, comida) que se le haya pasado como parámetro
     * @param String $seccion : La seccion de donde se quiere buscar
     * @return Int Devuelve el ID del empleado menos ocupado
     */
    static function ReturnIdEmpleadoMenosOcupadoSegun_TipoProducto($seccion)
    {
        switch($seccion)
        {
            case "bar":
                $listaUsuarios = Usuario::where("tipo",PerfilUsuarioController::RetornarIdPorPerfil("bartender"))->where('estado_id',EstadosController::ReturnIdSegunEstado_Usuario("activo"))->get();
                break;

            case "cerveza":
                $listaUsuarios = Usuario::where("tipo",PerfilUsuarioController::RetornarIdPorPerfil("cervecero"))->where('estado_id',EstadosController::ReturnIdSegunEstado_Usuario("activo"))->get();
                break;
            
            case "cocina":
                $listaUsuarios = Usuario::where("tipo",PerfilUsuarioController::RetornarIdPorPerfil("cocinero"))->where('estado_id',EstadosController::ReturnIdSegunEstado_Usuario("activo"))->get();
                break;
        }

        //obtengo la cantidad de veces que aparece cada empleado y lo guardo en {variable[user->id]} = {cantidad de veces que aparece}
        foreach($listaUsuarios as $user)
        {
            $repeticiones = ListaEmpleadosProductos::where("id_empleado",$user->id)->count();
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
            array_push($retorno,$value->id);
        }

        return $retorno;
    }
    static function ListaIdTodosLosEmpleados()
    {
        $retorno = array();

        $lista = Usuario::whereNotNull('sector')->get();
        foreach ($lista as $key => $value) {
            array_push($retorno,$value->id);
        }

        return $retorno;
    }
}
