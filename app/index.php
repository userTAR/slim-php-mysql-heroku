<?php
error_reporting(-1);
ini_set('display_errors', 1);

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;
use Slim\Routing\RouteCollectorProxy;
use Illuminate\Database\Capsule\Manager as Capsule;


require __DIR__ . '/../vendor/autoload.php';

require_once './controllers/UsuarioController.php';
require_once './controllers/MesaController.php';
require_once './controllers/ProductoController.php';

// Load ENV
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->safeLoad();

// Instantiate App
$app = AppFactory::create();
/* $app->setBasePath('/app'); */

// Add error middleware
$app->addErrorMiddleware(true, true, true);
$app->addBodyParsingMiddleware();
$app->addRoutingMiddleware();

// Eloquent
$container=$app->getContainer();

$capsule = new Capsule;
$capsule->addConnection([
    'driver'    => 'mysql',
    'host'      => $_ENV['MYSQL_HOST'],
    'database'  => $_ENV['MYSQL_DB'],
    'username'  => $_ENV['MYSQL_USER'],
    'password'  => $_ENV['MYSQL_PASS'],
    'charset'   => 'utf8',
    'collation' => 'utf8_unicode_ci',
    'prefix'    => '',
]);

$capsule->setAsGlobal();
$capsule->bootEloquent();


// Routes
$app->group('/alta', function (RouteCollectorProxy $group) {
    $group->post('/usuario', \UsuarioController::class . ':Alta');
    $group->post('/producto', \ProductoController::class . ':Alta');
    $group->post('/mesa', \MesaController::class . ':Alta');
  });
$app->group('/listar', function (RouteCollectorProxy $group){
    $group->get('/usuario/{id_usuario}', \UsuarioController::class . ':TraerUno');
    $group->get('/producto/{id_producto}', \ProductoController::class . ':TraerUno');
    $group->get('/mesa/{id_mesa}', \MesaController::class . ':TraerUno');
  });
$app->group('/listarTodos', function (RouteCollectorProxy $group) {
  $group->get('/usuarios', \UsuarioController::class . ':TraerTodos');
  $group->get('/productos', \ProductoController::class . ':TraerTodos');
  $group->get('/mesas', \MesaController::class . ':TraerTodos');
});
$app->group('/modificar', function (RouteCollectorProxy $group) {
  $group->put('/usuario/{id_usuario}', \UsuarioController::class . ':ModificarUno');
  $group->put('/producto/{id_producto}', \ProductoController::class . ':ModificarUno');
  $group->put('/mesa/{id_mesaa}', \MesaController::class . ':ModificarUno');
});
$app->group('/eliminar', function (RouteCollectorProxy $group) {
  $group->delete('/usuario/{id_usuario}', \UsuarioController::class . ':BorrarUno');
  $group->delete('/producto/{id_producto}', \ProductoController::class . ':BorrarUno');
  $group->delete('/mesa/{id_mesaa}', \MesaController::class . ':BorrarUno');
});



$app->get('/mostrarMensaje', function (Request $request, Response $response) {    
    $response->getBody()->write("Mensaje por default nuevo");
    return $response;
});

$app->run();