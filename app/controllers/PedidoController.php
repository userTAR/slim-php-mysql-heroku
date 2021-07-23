<?php
namespace App\Controller;

require_once __DIR__ ."/../models/Pedido.php";
require_once __DIR__ ."/../models/Mesa.php";
require_once __DIR__ ."/../models/ListaEmpleadosProductos.php";
require_once  __DIR__ ."/../interfaces/IPedido.php";
require_once __DIR__ ."/../../vendor/autoload.php";
require_once "GeneratorController.php";
require_once "HistorialesController.php";
require_once "EstadosController.php";
require_once "PerfilUsuarioController.php";
require_once "TipoProductoController.php";
require_once "UsuarioController.php";

use \app\Models\Pedido;
use App\Models\Usuario; //solo se usa en "TomarPedido"
use App\Models\Mesa;
use App\Interfaces\IPedido;
use App\Models\ListaEmpleadosProductos as LEA;
use App\Controller\GeneratorController as GENERADOR;
use App\Controller\EstadosController as ESTADOS;
use App\Controller\UsuarioController;
use App\Controller\PerfilUsuarioController as PERFILUSUARIO;
use App\Controller\HistorialesController;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class PedidoController implements IPedido
{
    public function Alta(Request $request, Response $response, Array $args): Response
    {
        $parametros = $request->getParsedBody();
        $archivo = $request->getUploadedFiles();

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
            if($archivo != null)
            {
                $nombreViejo = $archivo["foto"]->getClientFilename();
                $destino = __DIR__ ."/../resources/fotosPedidos/";
                $extension = explode(".",$nombreViejo);
                $nuevoNombre = $codigo ."." .$extension[1];
                $archivo["foto"]->MoveTo( $destino .$nuevoNombre);
            }
            //recuperamos de nuevo el objeto guardado en la base para obtener el id que se setea automaticamente
            $match = $pdd::where("codigo",$codigo)->first();
            HistorialesController::AltaEnHistorial($match->id,$estado,$estado,"pedido");
            //cambio de estado de mesa y guardado en su historial
            $mesa = Mesa::where('codigo',$codigoMesa)->first();
            HistorialesController::AltaEnHistorial($mesa->id,$mesa->estado,ESTADOS::ReturnIdSegunEstado_Mesa("con cliente esperando pedido"),"mesa");
            $mesa->id_estado = ESTADOS::ReturnIdSegunEstado_Mesa("con cliente esperando pedido");
            $mesa->save();
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

    /**
     * @param String $lista String de JSON con la lista de productos del pedido 
     * @param Int $idPedido Id del pedido
     * @return Bool True si el producto se asignó a un empleado y se guardó en la base de datos, False si no pudo hacerlo 
     */
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

    //solo deberían poder acceder los mozos y los socios // POST
    
    /**
     * Se cambia el estado de: -Pedidos // -ListaEmpleadoProductos a "en preparacion". Y se calcula la ETA del pedido 
     */
    public function TomarPedido(Request $request, Response $response, Array $args): Response
    {
        $parametros = $request->getParsedBody();
        
        $idPedido = $parametros["id_pedido"];
        $user = new Usuario();
        $eta = 0;

        $matchPedido = Pedido::where("id",$idPedido)->first();
        $leas = LEA::where("id_pedido",$idPedido)->get();
        $cantidadCocineros = $user::where("tipo",PERFILUSUARIO::RetornarIdPorPerfil("cocinero"))->count();
        $cantidadBartenders = $user::where("tipo",PERFILUSUARIO::RetornarIdPorPerfil("bartender"))->count();
        $cantidadCerveceros = $user::where("tipo",PERFILUSUARIO::RetornarIdPorPerfil("cervecero"))->count();
        $productos = json_decode($matchPedido->lista);
        
        //calculo de ETA para PEDIDOS
        foreach($productos as $producto)
        {
            switch($producto->tipo)
            {
                //bar
                case 1:
                    $aux = 10/$cantidadBartenders;
                    if($aux > 0)
                        $eta += $aux;
                    else
                        $eta += 2;
                    break;
                //cerveza
                case 2:
                    $aux = 8/$cantidadCerveceros;
                    if($aux > 0)
                        $eta += $aux;
                    else
                        $eta += 3;
                    break;
                //cocina
                case 3:
                    $aux = 60/$cantidadCocineros;
                    if($aux > 0)
                        $eta += $aux;
                    else
                        $eta += 10;
                    break;
            }
        }

        //cambio de estado para todos los elementos de Lista Empleado Pedidos asociados al código de pedido
        $leas->each(function ($value)
        {
            $value->estado = ESTADOS::ReturnIdSegunEstado_Pedido("en preparacion");
            $value->save();
        });
        
        HistorialesController::AltaEnHistorial($matchPedido->id,$matchPedido->estado,ESTADOS::ReturnIdSegunEstado_Pedido("en preparacion"),"pedido");
        $matchPedido->estado = ESTADOS::ReturnIdSegunEstado_Pedido("en preparacion");
        $matchPedido->eta = $eta;

        if($matchPedido->save())
            $payload = json_encode(array("mensaje" => "ETA seteada, estado cambiado y movimiento guardado en el historial"));
        else
            $payload = json_encode(array("mensaje" => "Error al tomar el pedido"));

        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    //solo deberían poder acceder los socios y los cocineros // POST
    /**
     * Cambia el estado del pedido a listo, guarda el movimiento en el historial de pedidos y cambia el estado de la lista de productos-empleados a "listo para servir"
     */
    public function PedidoListo(Request $request, Response $response, Array $args): Response
    {
        $parametros = $request->getParsedBody();

        $idPedido = $parametros["id_pedido"];

        $leas = LEA::where("id_pedido",$idPedido)->get();
        $matchPedido = Pedido::where("id",$idPedido)->where("estado",ESTADOS::ReturnIdSegunEstado_Pedido("en preparacion"))->first();
        $matchPedido->estado = ESTADOS::ReturnIdSegunEstado_Pedido("listo para servir");
        if($matchPedido->save() && HistorialesController::AltaEnHistorial($matchPedido->id,ESTADOS::ReturnIdSegunEstado_Pedido("en preparacion"),ESTADOS::ReturnIdSegunEstado_Pedido("listo para servir"),"pedido"))
        {
            $leas->each(function ($value)
            {
                $value->estado = ESTADOS::ReturnIdSegunEstado_Pedido("listo para servir");
                $value->save();
            });
            $payload = json_encode(array("mensaje" => "Cambio efectuado y guardado en el historial"));
        }
        else
            $payload = json_encode(array("mensaje" => "Falla en el cambio y/o el guardado en el historial"));

        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    //solo deberían poder acceder los mozos y cocineros // POST
    /**
     * Se cambia el estado del pedido a entregado y se guarda el movimiento en el historial de pedidos
     */
    public function EntregarPedido(Request $request, Response $response, Array $args): Response
    {
        $parametros = $request->getParsedBody();

        $idPedido = $parametros["id_pedido"];

        $leas = LEA::where("id_pedido",$idPedido)->get();
        
        //cambio de estado del pedido y guardado en historial
        $matchPedido = Pedido::where("id",$idPedido)->where("estado",ESTADOS::ReturnIdSegunEstado_Pedido("listo para servir"))->first();
        HistorialesController::AltaEnHistorial($matchPedido->id,$matchPedido->estado,ESTADOS::ReturnIdSegunEstado_Pedido("entregado"),"pedido");
        $matchPedido->estado = ESTADOS::ReturnIdSegunEstado_Pedido("entregado");
        //cambio de estado de la mesa y guardado en historial
        $mesa = Mesa::where('codigo',$matchPedido->codigo_mesa)->first();
        HistorialesController::AltaEnHistorial($mesa->id,$mesa->estado,ESTADOS::ReturnIdSegunEstado_Mesa("con cliente comiendo"),"mesa");
        $mesa->id_estado = ESTADOS::ReturnIdSegunEstado_Mesa("con cliente comiendo");
        if($matchPedido->save() && $mesa->save())
        {
            $leas->each(function ($value)
            {
                $value->estado = ESTADOS::ReturnIdSegunEstado_Pedido("entregado");
                $value->save();
            });
            
            $payload = json_encode(array("mensaje" => "Cambio efectuado y guardado en el historial"));
        }
        else
            $payload = json_encode(array("mensaje" => "Falla en el cambio y/o el guardado en el historial"));

        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    /**
     * Se cambia el estado del pedido a "abonado" y se guarda el movimiento en el historial de pedidos
     */
    public function AbonarPedido(Request $request, Response $response, array $args): Response
    {
        $parametros = $request->getParsedBody();

        $idPedido = $parametros["id_pedido"];

        //cambio de estado del pedido y guardado en historial
        $matchPedido = Pedido::where("id",$idPedido)->where("estado",ESTADOS::ReturnIdSegunEstado_Pedido("entregado"))->first();
        HistorialesController::AltaEnHistorial($matchPedido->id,$matchPedido->estado,ESTADOS::ReturnIdSegunEstado_Pedido("abonado"),"pedido");
        $matchPedido->estado = ESTADOS::ReturnIdSegunEstado_Pedido("abonado");
        //cambio de estado de la mesa y guardado en historial
        $mesa = Mesa::where("codigo",$matchPedido->codigo_mesa)->where("estado",ESTADOS::ReturnIdSegunEstado_Mesa("con cliente comiento"))->first();
        HistorialesController::AltaEnHistorial($mesa->id,$mesa->estado,ESTADOS::ReturnIdSegunEstado_Mesa("con cliente pagando"),"mesa");
        $mesa->estado = ESTADOS::ReturnIdSegunEstado_Mesa("con cliente pagando");

        if($matchPedido->save() && $mesa->save())
            $payload = json_encode(array("mensaje" => "Cambio efectuado y guardado en el historial"));
        else
            $payload = json_encode(array("mensaje" => "Falla en el cambio y/o el guardado en el historial"));

        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    /**
     * Se cambia el estado del pedido a "cancelado" y se guarda el movimiento en el historial de pedidos
     */
    public function CancelarPedido(Request $request, Response $response, array $args): Response
    {
        $parametros = $request->getParsedBody();
        $idPedido = $parametros['id_pedido'];
        
        $leas = LEA::where("id_pedido",$idPedido)->get();
        $matchPedido = Pedido::where("id",$idPedido)->first();
        HistorialesController::AltaEnHistorial($matchPedido->id,$matchPedido->estado,ESTADOS::ReturnIdSegunEstado_Pedido("cancelado"),"pedido");
        $matchPedido->estado = ESTADOS::ReturnIdSegunEstado_Pedido("cancelado");
        if($matchPedido->save())
        {
            $leas->each(function ($value)
            {
                $value->estado = ESTADOS::ReturnIdSegunEstado_Pedido("cancelado");
                $value->save();
            });
            //cambio de estado de mesa y guardado en su historial
            $mesa = Mesa::where('codigo',$matchPedido->codigo_mesa)->first();
            HistorialesController::AltaEnHistorial($mesa->id,$mesa->estado,ESTADOS::ReturnIdSegunEstado_Mesa("cerrada"),"mesa");
            $mesa->id_estado = ESTADOS::ReturnIdSegunEstado_Mesa("cerrada");
            $mesa->save();

            $payload = json_encode(array("mensaje" => "Cambio efectuado y guardado en el historial"));
        }
        else
            $payload = json_encode(array("mensaje" => "Falla en el cambio y/o el guardado en el historial"));

        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    /**
     * Trae un solo pedido según ID
     */
    public function TraerUnoRequest(Request $request, Response $response, Array $args): Response
    {
        // Buscamos Pedido por id
        $id= $args['id_pedido'];
        
        $pdd = Pedido::where('id', $id)->first();

        $payload = json_encode($pdd);

        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }
    /**
     * Devuelve todos los pedidos que haya en la base de datos
     */
    public function TraerTodos(Request $request, Response $response, Array $args): Response
    {
        $lista = Pedido::all();
        $payload = json_encode(array("listapedido" => $lista));

        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }
}