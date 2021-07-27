<?php
namespace App\Controller;

require_once __DIR__ ."/../models/Producto.php";
require_once __DIR__ ."/../../vendor/autoload.php";

use App\Models\Producto as Producto;
use App\Models\TipoProducto;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class ProductoController
{
    public function Alta(Request $request, Response $response, array $args) : Response
    {
        $parametros = $request->getParsedBody();

        $nombre = $parametros['nombre'];
        $tipo = $parametros['tipo'];
        $precio = $parametros['precio'];
        $matchTipo = TipoProductoController::RetornarIdSegunTipo($tipo);
        if($matchTipo != null)
        {
            // Creamos el Producto
            $prd = new Producto();
            $prd->nombre = $nombre;
            $prd->tipo = $matchTipo;
            $prd->precio = $precio;
            if($prd->save())
                $payload = json_encode(array("mensaje" => "Exito en el guardado del producto"));
            else
                $payload = json_encode(array("mensaje" => "Error en el guardado del producto"));
        }
        else
        {
            $payload = json_encode(array("mensaje" => "Error en el guardado, tipo no valido"));
        }


        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function TraerUno(Request $request, Response $response, array $args) : Response
    {
        // Buscamos Producto por id
        $id_prd = $args['id_producto'];
        
        $prd = Producto::where('id', $id_prd)->first();

        $payload = json_encode($prd);

        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function TraerTodos(Request $request, Response $response, array $args) : Response
    {
        $lista = Producto::all();
        $payload = json_encode(array("listaProducto" => $lista));

        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }
    
    public function BorrarUno(Request $request, Response $response, array $args) : Response
    {
        $parametros = $request->getParsedBody();
        $ProductoId = $parametros['id_producto'];
        
        $producto = Producto::find($ProductoId);
        if($producto->delete())
            $payload = json_encode(array("mensaje" => "Producto borrado con exito"));
        else
            $payload = json_encode(array("mensaje" => "Borrado de producto fallido"));
        
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function CargarProductoCsv(Request $request, Response $response, array $args) : Response
    {
        $archivo = fopen(__DIR__ ."/../resources/productos.csv","r");
        $flag = true;
        $aux2 = true;
        
        while(($array = fgetcsv($archivo)) !== false)
        {
            $prd = new Producto();
            $prd->nombre = $array[0];
            $prd->precio = $array[1];
            $prd->tipo = $array[2];
            if(!$prd->save())
                $aux2 = false; 
        }

        fclose($archivo);

        if($aux2 == false)
            $payload = json_encode(array("mensaje" => "Ha ocurrido un error en el guardado, puede que uno o mas productos no se hayan guardado"));
        else
            $payload = json_encode(array("mensaje" => "Exito en el guardado del producto"));
        
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }
}
