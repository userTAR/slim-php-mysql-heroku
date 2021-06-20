<?php
namespace App\middlewares;

require_once "./vendor/autoload.php";
require_once "../models/Jwt.php";

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response as ResponseMW;
use FFI\Exception;
use App\Models\Token;

class AutentificadorMW
{
    private static $tipo1;
    private static $tipo2;

    public function __construct($tipo1,$tipo2 = null)
    {
        self::$tipo1 = $tipo1;
        self::$tipo2 = $tipo2;
    }
    public function __invoke(Request $request, RequestHandler $handler) : ResponseMW
    {
        $header = $request->getHeaderLine('Authorization');
        $token = trim(explode("Bearer", $header)[1]);
        $aux = null;
        try
        {
            $tokenVerificado = Token::Verificar($token);
            if(self::$tipo2 == null)
            {
                if($tokenVerificado == self::$tipo1)
                    $response = $handler->handle($request);
            }
            else
                if($tokenVerificado == self::$tipo1 || $tokenVerificado == self::$tipo2)
                    $response = $handler->handle($request);
                    
            $payload = json_encode(array("mensaje" => "Acceso Denegado"));
        }
        catch (Exception $e)
        {
            $aux['mensaje'] = $e->getMessage();
            $response = new ResponseMW();
            //token no valido
            if($aux['flag'] == true)
                $payload = json_encode(array('mensaje' => $aux['mensaje']));
        }
        
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }
}


/* if (!empty($_COOKIE['token'])) 
    {
        $data = Token::ObtenerDatos($_COOKIE['token']);
        if($data->cuenta == self::$_switch)
        {
            $res = $next($req, $res);
            return $res;
        }
    }
    return $res->withJson(json_encode(array("Error"=>"No tiene permisos para acceder a este sitio. Autentifíquese nuevamente.")),400);
        
*/