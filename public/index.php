<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Routing\RouteCollectorProxy;
use Slim\Routing\RouteContext;

require __DIR__ . '/../vendor/autoload.php';
require_once './controllers/UsuarioController.php';
require_once './controllers/ProductoController.php';
require_once './controllers/MesaController.php';
require_once './controllers/PedidoController.php';
require_once './controllers/JwtController.php';
require_once './controllers/CocinaController.php';
require_once './controllers/TragoController.php';
require_once './controllers/CervezaController.php';
require_once './controllers/CandyController.php';
require_once './db/AccesoDatos.php';
require_once './middlewares/Logger.php';
require_once './middlewares/AutenticadorJWT.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

$app = AppFactory::create();
//$app->setBasePath('/public');
$app->addRoutingMiddleware();
$app->addBodyParsingMiddleware();

$errorMiddleware = $app->addErrorMiddleware(true, true, true);

// peticiones
$app->group('/usuarios', function (RouteCollectorProxy $groupUsuario) {
    //listo
    $groupUsuario->get('/', \UsuarioController::class . ':TraerTodos')->add(\MWLogger::class . ':LogUsuario')->add(\AutentificadorJWT::class .':AutenticacionEmpleado');
    //listo
    $groupUsuario->get('/csv', \UsuarioController::class . ':TraerTodosCsv')->add(\MWLogger::class . ':LogUsuario')->add(\AutentificadorJWT::class . ':AutenticacionEmpleado');
    $groupUsuario->get('/{id_usuario}', \UsuarioController::class . ':TraerUno')->add(\MWLogger::class . ':LogUsuario')->add(\AutentificadorJWT::class . ':AutenticacionEmpleado'); 
    $groupUsuario->put('/editar', \UsuarioController::class . ':ModificarUno')->add(\AutentificadorJWT::class . ':AutenticacionSupervisor');
    $groupUsuario->delete('/eliminar', \UsuarioController::class . ':BorrarUno')->add(\AutentificadorJWT::class . ':AutenticacionAdmin');
    //listo
    $groupUsuario->post('/cargar', \UsuarioController::class . ':CargarUno')->add(\MWLogger::class . ':LogUsuario')->add(\AutentificadorJWT::class . ':AutenticacionSupervisor');
    $groupUsuario->post('/csv/cargar', \UsuarioController::class . ':CargarCsv')->add(\MWLogger::class . ':LogUsuario')->add(\AutentificadorJWT::class . ':AutenticacionSupervisor'); 
    $groupUsuario->post('/movimiento', \UsuarioController::class . ':CargarMovimiento')->add(\MWLogger::class . ':LogUsuario')->add(\AutentificadorJWT::class . ':AutenticacionEmpleado'); 
    $groupUsuario->post('/login', \UsuarioController::class . ':LoginUsuario')->add(\AutentificadorJWT::class . ':GenerarToken');
});

$app->group('/productos', function (RouteCollectorProxy $groupProducto) {
  $groupProducto->get('/', \ProductoController::class . ':TraerTodos')->add(\MWLogger::class . ':LogProducto')->add(\AutentificadorJWT::class . ':AutenticacionEmpleado');
  $groupProducto->get('/csv', \ProductoController::class . ':TraerTodosCsv')->add(\MWLogger::class . ':LogProducto')->add(\AutentificadorJWT::class . ':AutenticacionEmpleado');
  $groupProducto->get('/{id_producto}', \ProductoController::class . ':TraerUno')->add(\MWLogger::class . ':LogProducto')->add(\AutentificadorJWT::class . ':AutenticacionEmpleado');
  $groupProducto->put('/editar', \ProductoController::class . ':ModificarUno')->add(\MWLogger::class . ':LogProducto')->add(\AutentificadorJWT::class . ':AutenticacionSupervisor');
  $groupProducto->delete('/eliminar', \ProductoController::class . ':BorrarUno')->add(\MWLogger::class . ':LogProducto')->add(\AutentificadorJWT::class . ':AutenticacionAdmin');
  $groupProducto->post('/cargar', \ProductoController::class . ':CargarUno')->add(\MWLogger::class . ':LogProducto')->add(\AutentificadorJWT::class . ':AutenticacionSupervisor');
  $groupProducto->post('/csv/cargar', \ProductoController::class . ':CargarCsv')->add(\MWLogger::class . ':LogProducto')->add(\AutentificadorJWT::class . ':AutenticacionSupervisor'); 
}); 

$app->group('/mesas', function (RouteCollectorProxy $groupMesa) {
  $groupMesa->get('/', \MesaController::class . ':TraerTodos')->add(\MWLogger::class . ':LogMesa')->add(\AutentificadorJWT::class . ':AutenticacionEmpleado');
  $groupMesa->get('/csv', \MesaController::class . ':TraerTodosCsv')->add(\MWLogger::class . ':LogMesa')->add(\AutentificadorJWT::class . ':AutenticacionEmpleado');
  $groupMesa->get('/{codigo_mesa}', \MesaController::class . ':TraerUno')->add(\MWLogger::class . ':LogMesa')->add(\AutentificadorJWT::class . ':AutenticacionEmpleado');
  $groupMesa->put('/editar', \MesaController::class . ':ModificarUno')->add(\MWLogger::class . ':LogMesa')->add(\AutentificadorJWT::class . ':AutenticacionEmpleado');
  $groupMesa->delete('/eliminar', \MesaController::class . ':BorrarUno')->add(\MWLogger::class . ':LogMesa')->add(\AutentificadorJWT::class . ':AutenticacionAdmin');
  $groupMesa->post('/cargar', \MesaController::class . ':CargarUno')->add(\MWLogger::class . ':LogMesa')->add(\AutentificadorJWT::class . ':AutenticacionSupervisor');
  $groupMesa->post('/csv/cargar', \MesaController::class . ':CargarCsv')->add(\MWLogger::class . ':LogMesa')->add(\AutentificadorJWT::class . ':AutenticacionSupervisor'); 
}); 

$app->group('/pedidos', function (RouteCollectorProxy $groupPedidos) {
  $groupPedidos->get('/', \PedidoController::class . ':TraerTodos')->add(\MWLogger::class . ':LogPedido')->add(\AutentificadorJWT::class . ':AutenticacionEmpleado');
  $groupPedidos->get('/csv', \PedidoController::class . ':TraerTodosCsv')->add(\MWLogger::class . ':LogPedido')->add(\AutentificadorJWT::class . ':AutenticacionEmpleado');
  $groupPedidos->get('/{id_responsable}', \PedidoController::class . ':TraerAsignados')->add(\MWLogger::class . ':LogPedido')->add(\AutentificadorJWT::class . ':AutenticacionEmpleado');
  $groupPedidos->get('/{id_responsable}/pendientes', \PedidoController::class . ':TraerAsignadosPendientes')->add(\MWLogger::class . ':LogPedido')->add(\AutentificadorJWT::class . ':AutenticacionEmpleado');
  $groupPedidos->get('/{codigo_pedido}/{codigo_mesa}', \PedidoController::class . ':TraerUno')->add(\MWLogger::class . ':LogPedido')->add(\AutentificadorJWT::class . ':AutenticacionEmpleado');
  $groupPedidos->put('/editar', \PedidoController::class . ':ModificarUno')->add(\MWLogger::class . ':LogPedido')->add(\AutentificadorJWT::class . ':AutenticacionEmpleado');
  $groupPedidos->delete('/eliminar', \PedidoController::class . ':BorrarUno')->add(\MWLogger::class . ':LogPedido')->add(\AutentificadorJWT::class . ':AutenticacionAdmin');
  $groupPedidos->post('/cargar', \PedidoController::class . ':CargarUno')->add(\MWLogger::class . ':LogPedido')->add(\AutentificadorJWT::class . ':AutenticacionEmpleado');
  $groupPedidos->post('/cargar_foto', \PedidoController::class . ':CargarFoto')->add(\MWLogger::class . ':LogPedido')->add(\AutentificadorJWT::class . ':AutenticacionEmpleado');
  $groupPedidos->post('/csv/cargar', \PedidoController::class . ':CargarCsv')->add(\MWLogger::class . ':LogPedido')->add(\AutentificadorJWT::class . ':AutenticacionEmpleado'); 
});

$app->group('/sector/cocina', function (RouteCollectorProxy $groupCocina) {
  $groupCocina->get('/', \CocinaController::class . ':TraerTodosCocina')->add(\MWLogger::class . ':LogSectores')->add(\AutentificadorJWT::class . ':AutenticacionEmpleado');
  $groupCocina->get('/pendientes', \CocinaController::class . ':TraerPendientesCocina')->add(\MWLogger::class . ':LogSectores')->add(\AutentificadorJWT::class . ':AutenticacionEmpleado');
  $groupCocina->post('/tomar', \PedidoController::class . ':TomarPedido')->add(\MWLogger::class . ':LogPedido')->add(\AutentificadorJWT::class . ':AutenticacionEmpleado');
  $groupCocina->post('/estado', \PedidoController::class . ':ModificarEstadoPedido')->add(\MWLogger::class . ':LogPedido')->add(\AutentificadorJWT::class . ':AutenticacionEmpleado');
});

$app->group('/sector/tragos', function (RouteCollectorProxy $groupTragos) {
  $groupTragos->get('/', \TragoController::class . ':TraerTodosTrago')->add(\MWLogger::class . ':LogSectores')->add(\AutentificadorJWT::class . ':AutenticacionEmpleado');
  $groupTragos->get('/pendientes', \TragoController::class . ':TraerPendientesTrago')->add(\MWLogger::class . ':LogSectores')->add(\AutentificadorJWT::class . ':AutenticacionEmpleado');
  $groupTragos->post('/tomar', \TragoController::class . ':TomarPedido')->add(\MWLogger::class . ':LogPedido')->add(\AutentificadorJWT::class . ':AutenticacionEmpleado');
  $groupTragos->post('/estado', \TragoController::class . ':ModificarEstadoPedido')->add(\MWLogger::class . ':LogPedido')->add(\AutentificadorJWT::class . ':AutenticacionEmpleado');
});

$app->group('/sector/cervezas', function (RouteCollectorProxy $groupCervezas) {
  $groupCervezas->get('/', \CervezaController::class . ':TraerTodosCerveza')->add(\MWLogger::class . ':LogSectores')->add(\AutentificadorJWT::class . ':AutenticacionEmpleado');
  $groupCervezas->get('/pendientes', \CervezaController::class . ':TraerPendientesCerveza')->add(\MWLogger::class . ':LogSectores')->add(\AutentificadorJWT::class . ':AutenticacionEmpleado');
  $groupCervezas->post('/tomar', \CervezaController::class . ':TomarPedido')->add(\MWLogger::class . ':LogPedido')->add(\AutentificadorJWT::class . ':AutenticacionEmpleado');
  $groupCervezas->post('/estado', \CervezaController::class . ':ModificarEstadoPedido')->add(\MWLogger::class . ':LogPedido')->add(\AutentificadorJWT::class . ':AutenticacionEmpleado');
});

$app->group('/sector/candy', function (RouteCollectorProxy $groupCandy) {
  $groupCandy->get('/', \CandyController::class . ':TraerTodosCandy')->add(\MWLogger::class . ':LogSectores')->add(\AutentificadorJWT::class . ':AutenticacionEmpleado');
  $groupCandy->get('/pendientes', \CandyController::class . ':TraerPendientesCandy')->add(\MWLogger::class . ':LogSectores')->add(\AutentificadorJWT::class . ':AutenticacionEmpleado');
  $groupCandy->post('/tomar', \CandyController::class . ':TomarPedido')->add(\MWLogger::class . ':LogPedido')->add(\AutentificadorJWT::class . ':AutenticacionEmpleado');
  $groupCandy->post('/estado', \CandyController::class . ':ModificarEstadoPedido')->add(\MWLogger::class . ':LogPedido')->add(\AutentificadorJWT::class . ':AutenticacionEmpleado');
});

// Run app
$app->run();

