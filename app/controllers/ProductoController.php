<?php
namespace App\Controller;

require_once "./models/Producto.php";
require_once './interfaces/IApiUsable.php';

use App\Models\Producto as Producto;
use App\Interface\IApiUsable;

class ProductoController implements IApiUsable
{
    public function Alta($request, $response, $args)
    {
        $parametros = $request->getParsedBody();

        $nombre = $parametros['nombre'];
        $tipo = $parametros['tipo'];


        // Creamos el Producto
        $prd = new Producto();
        $prd->nombre = $nombre;
        $prd->tipo = $tipo;
        if($prd->save())
            $payload = json_encode(array("mensaje" => "Exito en el guardado del producto"));
        else
            $payload = json_encode(array("mensaje" => "Error en el guardado del producto"));


        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function TraerUno($request, $response, $args)
    {
        // Buscamos Producto por id
        $id_prd = $args['id_producto'];
        
        $prd = Producto::where('id', $id_prd)->first();

        $payload = json_encode($prd);

        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function TraerTodos($request, $response, $args)
    {
        $lista = Producto::all();
        $payload = json_encode(array("listaProducto" => $lista));

        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }
    
    public function ModificarUno($request, $response, $args)
    {
        $parametros = $request->getParsedBody();

        $nombre = $parametros['nombre'];
        Producto::modificarProducto($nombre);

        $payload = json_encode(array("mensaje" => "Producto modificado con exito"));

        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function BorrarUno($request, $response, $args)
    {
        $parametros = $request->getParsedBody();

        $ProductoId = $parametros['id_producto'];
        Producto::borrarProducto($ProductoId);

        $payload = json_encode(array("mensaje" => "Producto borrado con exito"));

        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }
}
