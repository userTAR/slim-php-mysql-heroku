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
use Dompdf\Dompdf;
use DateTime;
use DateTimeZone;

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
        
        //se chequea que la mesa esté cerrada para poder tomar el pedido
        $mesa = Mesa::where('codigo',$codigoMesa)->first();
        if($mesa->id_estado != ESTADOS::ReturnIdSegunEstado_Mesa("cerrada"))
            $payload = json_encode(array("mensaje" => "Mesa Ocupada"));
        else
        {
            // Creamos el Pedido
            $pdd = new Pedido();
            $pdd->codigo = $codigo;
            $pdd->codigo_mesa = $codigoMesa;
            $pdd->id_cliente = $idCliente;
            $pdd->id_mozo = $idMozo;
            $pdd->lista = $lista;
            $pdd->factura = self::CalcularFactura($lista);
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
                $match = Pedido::where("codigo",$codigo)->first();
                HistorialesController::AltaEnHistorial($match->id,$estado,$estado,"pedido");
                //cambio de estado de mesa y guardado en su historial
                HistorialesController::AltaEnHistorial($mesa->id,$mesa->id_estado,ESTADOS::ReturnIdSegunEstado_Mesa("con cliente esperando pedido"),"mesa");
                $mesa->id_estado = ESTADOS::ReturnIdSegunEstado_Mesa("con cliente esperando pedido");
                $mesa->save();
                //se asignan los productos a empleados
                self::AsignarProductoAEmpleado($lista,$match->id);
                //response
                $payload = json_encode(array("mensaje" => "Exito en el guardado del pedido", "codigo_pedido" => $codigo));
            }
            else
                $payload = json_encode(array("mensaje" => "Error en el guardado del pedido"));
        }
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
        $ret = true;  
        $id = 0;
        foreach($productos as $producto)
        {
            $lea  = new LEA();
            switch($producto->tipo)
            {
                case "bar":
                    $id = UsuarioController::ReturnIdEmpleadoMenosOcupadoSegun_TipoProducto($producto->tipo);
                    $lea->id_empleado = $id;
                    break;
                case "cerveza":
                    $id = UsuarioController::ReturnIdEmpleadoMenosOcupadoSegun_TipoProducto($producto->tipo);
                    $lea->id_empleado = $id;
                    break;
                case "cocina":
                    $id = UsuarioController::ReturnIdEmpleadoMenosOcupadoSegun_TipoProducto($producto->tipo);
                    $lea->id_empleado = $id;
                    break;
            }
            $lea->id_pedido = $idPedido;
            $lea->id_producto_pedido = $producto->id;
            $lea->estado = ESTADOS::ReturnIdSegunEstado_Pedido("pendiente");
            HistorialesController::AltaEnHistorialLEA($id,$idPedido,$producto->id,ESTADOS::ReturnIdSegunEstado_Pedido("pendiente"),ESTADOS::ReturnIdSegunEstado_Pedido("pendiente"),date("Y-m-d H:i:s"));
            if(!$lea->save())
            {
                $ret = false;
                break;
            }
        }

        return $ret;
    }

    private static function CalcularFactura($lista)
    {
        $retorno = 0;
        $decoded = json_decode($lista);
        foreach ($decoded as $value) {
            $retorno += $value->precio;
        }

        return $retorno;
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

        //cambio de estado para todos los elementos de Lista Empleado Pedidos asociados al código de pedido y guardado en historial "historial_productos_empleados"
        $leas->each(function ($value)
        {
            HistorialesController::AltaEnHistorialLEA($value->id_empleado,$value->id_pedido,$value->id_producto_pedido,$value->estado,ESTADOS::ReturnIdSegunEstado_Pedido("en preparacion"),date("Y-m-d H:i:s"));
            $value->estado = ESTADOS::ReturnIdSegunEstado_Pedido("en preparacion");
            $value->save();
        });
        
        HistorialesController::AltaEnHistorial($matchPedido->id,$matchPedido->estado,ESTADOS::ReturnIdSegunEstado_Pedido("en preparacion"),"pedido");
        $matchPedido->estado = ESTADOS::ReturnIdSegunEstado_Pedido("en preparacion");
        $matchPedido->eta = date_add(new DateTime('now', new DateTimeZone("America/Argentina/Buenos_Aires")),date_interval_create_from_date_string($eta ."minutes"));
    

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
                HistorialesController::AltaEnHistorialLEA($value->id_empleado,$value->id_pedido,$value->id_producto_pedido,$value->estado,ESTADOS::ReturnIdSegunEstado_Pedido("listo para servir"),date("Y-m-d H:i:s"));
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
        $matchPedido->entrega_time = new DateTime('now', new DateTimeZone("America/Argentina/Buenos_Aires"));
        //cambio de estado de la mesa y guardado en historial
        $mesa = Mesa::where('codigo',$matchPedido->codigo_mesa)->first();
        HistorialesController::AltaEnHistorial($mesa->id,$mesa->id_estado,ESTADOS::ReturnIdSegunEstado_Mesa("con cliente comiendo"),"mesa");
        $mesa->id_estado = ESTADOS::ReturnIdSegunEstado_Mesa("con cliente comiendo");
        if($matchPedido->save() && $mesa->save())
        {
            $leas->each(function ($value)
            {
                HistorialesController::AltaEnHistorialLEA($value->id_empleado,$value->id_pedido,$value->id_producto_pedido,$value->estado,ESTADOS::ReturnIdSegunEstado_Pedido("entregado"),date("Y-m-d H:i:s"));
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
        $mesa = Mesa::where("codigo",$matchPedido->codigo_mesa)->where("id_estado",ESTADOS::ReturnIdSegunEstado_Mesa("con cliente comiendo"))->first();
        HistorialesController::AltaEnHistorial($mesa->id,$mesa->id_estado,ESTADOS::ReturnIdSegunEstado_Mesa("con cliente pagando"),"mesa");
        $mesa->id_estado = ESTADOS::ReturnIdSegunEstado_Mesa("con cliente pagando");

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
                HistorialesController::AltaEnHistorialLEA($value->id_empleado,$value->id_pedido,$value->id_producto_pedido,$value->estado,ESTADOS::ReturnIdSegunEstado_Pedido("cancelado"),date("Y-m-d H:i:s"));
                $value->estado = ESTADOS::ReturnIdSegunEstado_Pedido("cancelado");
                $value->save();
            });
            //cambio de estado de mesa y guardado en su historial
            $mesa = Mesa::where('codigo',$matchPedido->codigo_mesa)->first();
            HistorialesController::AltaEnHistorial($mesa->id,$mesa->id_estado,ESTADOS::ReturnIdSegunEstado_Mesa("cerrada"),"mesa");
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

    public function GenerarPedidoCSV(Request $request, Response $response, Array $args): Response
    {
        $idPedido = $args["id_pedido"];
        $array = array();
        $destino = __DIR__ ."/../resources/pedido.csv";
        $archivo = fopen($destino,"w");

        $pdd = Pedido::find($idPedido);

        //cabecera
        fputcsv($archivo,["id","codigo","codigo mesa", "id cliente","id mozo","factura","estado","fecha de pedido","ETA","entrega"]);
        //cuerpo

        array_push($array,[$pdd->id,$pdd->codigo,$pdd->codigo_mesa,$pdd->id_cliente,$pdd->id_mozo,$pdd->lista,$pdd->factura,ESTADOS::ReturnEstadoSegunId_Pedido($pdd->estado),$pdd->pedido_time,$pdd->eta,$pdd->entrega_time]);
        foreach ($array as $key => $value) {
            fputcsv($archivo,$value);
        }

        fclose($archivo);

        $payload = json_encode(array("mensaje" => "archivo guardado"));
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function GenerarPedidoPDF(Request $request, Response $response, Array $args)
    {
        $idPedido = $args["id_pedido"];
        $array = array();

        $payload = Pedido::find($idPedido);
        array_push($array,$payload);
        $encoded = json_encode($array);
        
        self::GenerarPDF(json_decode($encoded));
    }

    private static function GenerarPDF($array)
    {
        $dompfdp = new Dompdf();
        $tabla = self::generarHTML($array);
        $dompfdp->setPaper('A3',"landscape");
        $dompfdp->loadHtml($tabla);
        $dompfdp->render();
        $dompfdp->stream("ventas");
    }

    private static function generarHTML ( array $arrayDatos ) : string {
        $html = '<table style="border: 1px solid black;border-collapse: collapse;">';
        
        foreach ( $arrayDatos as $dato ) {
            
            $html .= '<tr style="border: 1px solid black;">';
            
            foreach ( $dato as $col ) {
                
                if ( $col instanceof \DateTimeInterface ) 
                $colEncoded = $col->format('Y-m-d H:i:s');
                else
                $colEncoded = json_encode($col, true);
                
                $html .= "<td style=\"border:1px solid black;\">$colEncoded</td>";
            }
            
            $html .= "</tr>";
        }
        
        $html .= "</table>";
        return $html;
    }
}