<?php
error_reporting(-1);
ini_set('display_errors', 1);

require __DIR__ . "/../vendor/autoload.php";
require_once "./controllers/UsuarioController.php";
require_once "./controllers/MesaController.php";
require_once "./controllers/ProductoController.php";
require_once "./controllers/PedidoController.php";
require_once "./controllers/LoginController.php";
require_once "./controllers/pruebaEta.php";
require_once "./controllers/ConsultasMesas.php";
require_once "./controllers/ConsultasPedidos.php";
require_once "./controllers/ConsultasUsuarios.php";
require_once "./controllers/EncuestasController.php";
require_once "./middlewares/AutentificadorMW.php";

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;
use Slim\Routing\RouteCollectorProxy;
use Illuminate\Database\Capsule\Manager as Capsule;
use App\Controller\MesaController;
use App\Controller\PedidoController;
use App\Controller\ProductoController;
use App\Controller\UsuarioController;
use App\Controller\LoginController;
use App\Controller\ConsultasMesas;
use App\Controller\ConsultasPedidos;
use App\Controller\ConsultasUsuarios;
use App\Controller\EncuestasController;
use App\controllers\prueba; // chequear si funciona el fuera de tiempo y dps borrarlo
use App\middlewares\AutentificadorMW;
use App\Models\Encuesta;
use App\Models\Pedido;

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
$app->post('/iniciarSesion', LoginController::class .":InicioSesion");
$app->get('/pruebaETA', prueba::class .':PruebaETA');


$app->group('/pedido', function (RouteCollectorProxy $group) {
  $group->post('/alta', PedidoController::class . ':Alta')/* ->add(new AutentificadorMW("mozo")) */;
  $group->post('/tomar', PedidoController::class . ':TomarPedido')/* ->add(new AutentificadorMW("cocinero","mozo")) */;
  $group->post('/listo', PedidoController::class . ':PedidoListo')/* ->add(new AutentificadorMW("cocinero")) */;
  $group->post('/entregar', PedidoController::class . ':EntregarPedido')/* ->add(new AutentificadorMW("socio","mozo")) */;
  $group->post('/abonar', PedidoController::class . ':AbonarPedido')/* ->add(new AutentificadorMW("socio","mozo")) */;
  $group->post('/cancelar', PedidoController::class . ':CancelarPedido')/* ->add(new AutentificadorMW("socio")) */;
  $group->post('/cerrarMesa', MesaController::class . ':CerrarMesa')/* ->add(new AutentificadorMW("socio")) */;
  $group->post('/consultarTiempo', ConsultasMesas::class . ':ConsultarTiempo')/* ->add(new AutentificadorMW("socio","cliente")) */;
  $group->get('/generarCSV/{id_pedido}', PedidoController::class . ':GenerarPedidoCSV')/* ->add(new AutentificadorMW("socio","cliente")) */;
  $group->get('/generarPDF/{id_pedido}', PedidoController::class . ':GenerarPedidoPDF')/* ->add(new AutentificadorMW("socio","cliente")) */;
  $group->post('/encuesta', EncuestasController::class . ':AltaEncuesta')/* ->add(new AutentificadorMW("cliente")) */;
}); 

$app->group('/alta', function (RouteCollectorProxy $group) {
    $group->post('/usuario', UsuarioController::class .":Alta");
    $group->post('/producto', ProductoController::class . ':Alta');
    $group->post('/productoDesdeCSV', ProductoController::class . ':CargarProductoCsv');
    $group->post('/mesa', MesaController::class .':Alta');
  })/* ->add(new AutentificadorMW("socio")) */; 

  

$app->group('/listar', function (RouteCollectorProxy $group){
    $group->get('/usuario/{id_usuario}', UsuarioController::class . ':TraerUno')/* ->add(new AutentificadorMW("socio")) */;
    $group->get('/producto/{id_producto}', ProductoController::class . ':TraerUno');
    $group->get('/mesa/{id_mesa}', MesaController::class . ':TraerUno')/* ->add(new AutentificadorMW("socio")) */;
  });

$app->group('/listarTodos', function (RouteCollectorProxy $group) {
  $group->get('/usuarios', UsuarioController::class . ':TraerTodos')/* ->add(new AutentificadorMW("socio")) */;
  $group->get('/productos', ProductoController::class . ':TraerTodos');
  $group->get('/mesas', MesaController::class . ':TraerTodos')/* ->add(new AutentificadorMW("socio")) */;
});

$app->group('/modificar', function (RouteCollectorProxy $group) {
  $group->put('/usuario', UsuarioController::class . ':ModificarUno');
  $group->put('/mesa', MesaController::class . ':ModificarUno');
})/* ->add(new AutentificadorMW("socio")) */;

$app->group('/eliminar', function (RouteCollectorProxy $group) {
  $group->delete('/usuario', UsuarioController::class . ':BorrarUno');
  $group->delete('/producto', ProductoController::class . ':BorrarUno');
  $group->delete('/mesa', MesaController::class . ':BorrarUno');
})/* ->add(new AutentificadorMW("socio")) */;

$app->group('/consulta/mesa', function (RouteCollectorProxy $group) {
  $group->post('/masUsada', ConsultasMesas::class . ':MasUsada');
  $group->post('/menosUsada', ConsultasMesas::class . ':MenosUsada');
  $group->post('/masFactura', ConsultasMesas::class . ':MasFactura');
  $group->post('/menosFactura', ConsultasMesas::class . ':MenosFactura');
  $group->post('/mayorImporte', ConsultasMesas::class . ':FacturaMayorImporte');
  $group->post('/menorImporte', ConsultasMesas::class . ':FacturaMenorImporte');
  $group->post('/facturaEntreFechas', ConsultasMesas::class . ':TotalFactura_DosFechas');
  $group->post('/mejorComentario', ConsultasMesas::class . ':MejorComentario');
  $group->post('/peorComentario', ConsultasMesas::class . ':PeorComentario');
});

$app->group('/consulta/pedido', function (RouteCollectorProxy $group) {
  $group->post('/productoMasPedido', ConsultasPedidos::class . ':MasVendido');
  $group->post('/productoMenosPedido', ConsultasPedidos::class . ':MenosVendido');
  $group->post('/fueraDeTiempo', ConsultasPedidos::class . ':FueraDeTiempo');
  $group->post('/cancelados', ConsultasPedidos::class . ':Cancelados');
})/* ->add(new AutentificadorMW("socio")) */;

$app->group('/consulta/usuario', function (RouteCollectorProxy $group) {
  $group->post('/ingresosRegistrados', ConsultasUsuarios::class . ':IngresosRegistrados');
  $group->post('/opsPorSector', ConsultasUsuarios::class . ':OperacionesPorSector');
  $group->post('/opsPorSectorEmpleado', ConsultasUsuarios::class . ':OperacionesPorSector_Empleado');
  $group->post('/opsDeTodos', ConsultasUsuarios::class . ':OperacionesPorEmpleado');
})/* ->add(new AutentificadorMW("socio")) */;



$app->get('/mostrarMensaje', function (Request $request, Response $response) {    
    $response->getBody()->write("Mensaje por default nuevo");
    return $response;
});

$app->run();