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

// Load ENV
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->safeLoad();

// Instantiate App
$app = AppFactory::create();
$app->setBasePath('/app');

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
    $group->post('/usuario/', \UsuarioController::class . ':Alta');
  
  });
$app->group('/listado', function (RouteCollectorProxy $group) {
    $group->get('/{id_usuario}', \UsuarioController::class . ':TraerUno');
  });

$app->get('/mostrarMensaje', function (Request $request, Response $response) {    
    $response->getBody()->write("Mensaje por default");
    return $response;

});

$app->run();
