<?php
namespace App\Controller;

require_once __DIR__ ."/../models/Pedido.php";
require_once __DIR__ ."/../models/ListaEmpleadosProductos.php";
require_once './interfaces/IApiUsable.php';
require_once "GeneratorController.php";
require_once "EstadosController.php";
require_once "PerfilUsuarioController.php";
require_once "TipoProductoController.php";
require_once "UsuarioController.php";

use \app\Models\Pedido;
use App\Interface\IApiUsable;
use App\Controller\GeneratorController as GENERADOR;
use App\Controller\EstadosController as ESTADOS;
use App\Controller\UsuarioController;
use App\Controller\PerfilUsuarioController as PERFILUSUARIO;
use App\Controller\TipoProductoController as TIPOPRODUCTO;
use App\Models\ListaEmpleadosProductos as LEA;
use App\Models\Usuario;

class PedidoController implements IApiUsable
{
    public function Alta($request, $response, $args)
    {
        $parametros = $request->getParsedBody();

        $codigo = GENERADOR::GenerarCodigo_5_Caracteres();
        $codigoMesa = $parametros['codigo_mesa'];
        $idCliente = $parametros['id_cliente'];
        $idMozo= $parametros['id_mozo'];
        $lista = $parametros['lista'];
        $estado = ESTADOS::ReturnIdSegunEstado_Pedido("pendiente");

        // Creamos el Pedido
        $pdd = new Pedido();
        $pdd->codigo = $codigo;
        $pdd->codigo_mesa = $codigoMesa;
        $pdd->id_cliente = $idCliente;
        $pdd->id_mozo = $idMozo;
        $pdd->lista = $lista;
        $pdd->estado = $estado;

        if($pdd->save())
        {
            //recuperamos de nuevo el objeto guardado en la base para obtener el id que se setea automaticamente
            $match = $pdd::where("codigo",$codigo)->first();
            HistorialesController::AltaEnHistorial($match->id,$estado,$estado,"pedido");
            //se asignan los productos a empleados
            self::AsignarProductoAEmpleado($lista,$match->id);
            //response
            $payload = json_encode(array("mensaje" => "Exito en el guardado del pedido", "codigo_pedido" => $codigo));
        }
        else
            $payload = json_encode(array("mensaje" => "Error en el guardado del pedido"));

        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    private static function AsignarProductoAEmpleado($lista,$idPedido)
    {
        $productos = json_decode($lista);
        $lea  = new LEA();
        $ret = false;      

        foreach($productos as $producto)
        {
            switch($producto->tipo)
            {
                //bar
                case 1:
                    $id = UsuarioController::ReturnIdEmpleadoMenosOcupadoSegun_Seccion($producto->tipo);
                    $lea->id_empleado = $id;
                    break;
                //cerveza
                case 2:
                    $id = UsuarioController::ReturnIdEmpleadoMenosOcupadoSegun_Seccion($producto->tipo);
                    $lea->id_empleado = $id;
                    break;
                //cocina
                case 3:
                    $id = UsuarioController::ReturnIdEmpleadoMenosOcupadoSegun_Seccion($producto->tipo);
                    $lea->id_empleado = $id;
                    break;
            }
            $lea->id_pedido = $idPedido;
            $lea->id_producto_pedido = $producto->id;
            $lea->estado = ESTADOS::ReturnIdSegunEstado_Pedido("pendiente");
            if(!$lea->save())
            {
                $ret = false;
                break;
            }
        }

        return $ret;
    }

    /**
     * Se cambia el estado de: -Pedidos // -ListaEmpleadoProductos .  Y se calcula la ETA del pedido 
     */


     //ESTO DEBERÍA IR EN SECCION "MANEJO DEL PEDIDO"
    public function TomarPedido($request,$response,$args)
    {
        $idPedido = $args["id_pedido"];
        $user = new Usuario();
        $pdd = new Pedido();
        $lea  = new LEA();
        $eta = 0;

        $matchPedido = $pdd::where("id",$idPedido)->first();
        $leas = $lea::where("id_pedido",$idPedido)->get();
        $cantidadCocineros = $user::where("tipo",PERFILUSUARIO::RetornarIdPorPerfil("cocinero"))->count();
        $productos = json_decode($matchPedido->lista);
        
        //calculo de ETA para PEDIDOS
        foreach($productos as $producto)
        {
            switch($producto->tipo)
            {
                //bar
                case 1:
                    $eta += 5;
                    break;
                //cerveza
                case 2:
                    $eta += 2;
                    break;
                //cocina
                case 3:
                    $eta += 60/$cantidadCocineros;
                    break;
            }
        }

        //cambio de estado para todos los elementos de Lista Empleado Pedidos asociados al código de pedido
        $leas->each(function ($value)
        {
            $value->estado = ESTADOS::ReturnIdSegunEstado_Pedido("en preparacion");
            $value->save();
        });
        
        HistorialesController::AltaEnHistorial($matchPedido->id,ESTADOS::ReturnIdSegunEstado_Pedido($matchPedido->estado),ESTADOS::ReturnIdSegunEstado_Pedido("en preparacion"),"pedido");
        $matchPedido->estado = ESTADOS::ReturnIdSegunEstado_Pedido("en preparacion");
        $matchPedido->eta = $eta;

        if($matchPedido->save())
            $payload = json_encode(array("mensaje" => "ETA seteada y estado cambiado"));
        else
            $payload = json_encode(array("mensaje" => "Error al tomar el pedido"));

        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function TraerUno($request, $response, $args)
    {
        // Buscamos Pedido por id
        $id= $args['id_pedido'];
        
        $pdd = Pedido::where('id', $id)->first();

        $payload = json_encode($pdd);

        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function TraerTodos($request, $response, $args)
    {
        $lista = Pedido::all();
        $payload = json_encode(array("listapedido" => $lista));

        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }
    
    /* public function ModificarEstado($request, $response, $args)
    {
        $parametros = $request->getParsedBody();

        $id = $parametros['id'];
        $estado = $parametros['estado_new'];

        

        $payload = json_encode(array("mensaje" => "Estado de pedido modificado"));

        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }
 */
    public function BorrarUno($request, $response, $args)
    {
        $parametros = $request->getParsedBody();

        $pedidoId = $parametros['id_pedido'];

        $payload = json_encode(array("mensaje" => "Pedido borrado con exito"));

        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }
//-----------------------------MANEJO ESTADO PEDIDO-------------------------------------

    /* public function EstadoPedido($request,$response,$args)
    {
        $parametros = $request->getParsedBody();

        $idPedido = $parametros["id_pedido"];
        $nuevoEstado = $parametros["estado_nuevo"];
        
        $pdd = new Pedido();
        $pdd::find($idPedido);

        HistorialesController::AltaEnHistorial($idPedido,ESTADOS::ReturnIdSegunEstado_Pedido($pdd->estado),ESTADOS::ReturnIdSegunEstado_Pedido($nuevoEstado),"pedido");


        //modificamos el estado de la tabla "pedidos"
        $pdd->estado = ESTADOS::ReturnIdSegunEstado_Pedido($nuevoEstado);
        if($pdd->save())
        {
            if($nuevoEstado == "en preparacion")
            {
                //revisar y seguir por acá
            }
            $payload = json_encode(array("mensaje" => "Estado de pedido modificado"));
        }
        
    } */
}