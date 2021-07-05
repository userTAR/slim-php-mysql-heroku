<?php
namespace App\middlewares;

require_once __DIR__ ."/../../vendor/autoload.php";
require_once "./models/Jwt.php";

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response as ResponseMW;
use App\Models\Token;
use Exception as GlobalException;

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
        try
        {
            $flag = false;
            $header = $request->getHeaderLine('Authorization');
            $token = trim(explode("Bearer", $header)[1]);
            $aux = null;
            $tokenVerificado = Token::Verificar($token);
            if(self::$tipo2 == null)
            {
                if($tokenVerificado->perfil == self::$tipo1)
                    $flag = true;
                else
                    $payload = json_encode(array("mensaje" => "Acceso Denegado"));
            }
            else
                if($tokenVerificado->perfil == self::$tipo1 || $tokenVerificado->perfil == self::$tipo2)
                    $flag = true;
                else
                    $payload = json_encode(array("mensaje" => "Acceso Denegado"));
        }
        catch (GlobalException $e)
        {
            $aux['mensaje'] = $e->getMessage();
            $response = new ResponseMW();
            $payload = json_encode(array('mensaje' => $aux['mensaje']));
        }
        if($flag != true)
        {
            $response->getBody()->write($payload);
            $response->withHeader('Content-Type', 'application/json');
        }
        else
            $response = $handler->handle($request);
        
        return $response;
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