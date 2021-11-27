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
require_once './controllers/EncuestaController.php';
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
    $groupUsuario->get('/', \UsuarioController::class . ':TraerTodos')->add(\MWLogger::class . ':LogUsuario')->add(\AutentificadorJWT::class .':AutenticacionEmpleado');
    $groupUsuario->get('/csv', \UsuarioController::class . ':TraerTodosCsv')->add(\MWLogger::class . ':LogUsuario')->add(\AutentificadorJWT::class . ':AutenticacionEmpleado');
    $groupUsuario->get('/movimientos', \UsuarioController::class . ':TraerMovimientos')->add(\MWLogger::class . ':LogUsuario')->add(\AutentificadorJWT::class . ':AutenticacionSupervisor'); 
    $groupUsuario->get('/{id_usuario}', \UsuarioController::class . ':TraerUno')->add(\MWLogger::class . ':LogUsuario')->add(\AutentificadorJWT::class . ':AutenticacionEmpleado'); 
    $groupUsuario->get('/entreFechas/{desde}/{hasta}', \UsuarioController::class . ':TraerMovimientosEntreFechas')->add(\MWLogger::class . ':LogUsuario')->add(\AutentificadorJWT::class . ':AutenticacionSupervisor'); 
    $groupUsuario->post('/modificar', \UsuarioController::class . ':ModificarUno')->add(\MWLogger::class . ':LogUsuario')->add(\AutentificadorJWT::class . ':AutenticacionSupervisor');
    $groupUsuario->delete('/eliminar/{id}', \UsuarioController::class . ':BorrarUno')->add(\AutentificadorJWT::class . ':AutenticacionAdmin');
    $groupUsuario->post('/cargar', \UsuarioController::class . ':CargarUno')->add(\MWLogger::class . ':LogUsuario')->add(\AutentificadorJWT::class . ':AutenticacionSupervisor');
    $groupUsuario->post('/csv/cargar', \UsuarioController::class . ':CargarCsv')->add(\MWLogger::class . ':LogUsuario')->add(\AutentificadorJWT::class . ':AutenticacionSupervisor'); 
    $groupUsuario->post('/nuevo/movimiento', \UsuarioController::class . ':CargarMovimiento')->add(\MWLogger::class . ':LogUsuario')->add(\AutentificadorJWT::class . ':AutenticacionEmpleado'); 
    $groupUsuario->post('/login', \UsuarioController::class . ':LoginUsuario')->add(\AutentificadorJWT::class . ':GenerarToken');
});

$app->group('/productos', function (RouteCollectorProxy $groupProducto) {
  $groupProducto->get('/', \ProductoController::class . ':TraerTodos')->add(\MWLogger::class . ':LogProducto')->add(\AutentificadorJWT::class . ':AutenticacionEmpleado');
  $groupProducto->get('/masvendidos', \ProductoController::class . ':TraerMasVendidos')->add(\MWLogger::class . ':LogProducto')->add(\AutentificadorJWT::class . ':AutenticacionSupervisor');
  $groupProducto->get('/menosvendidos', \ProductoController::class . ':TraerMenosVendidos')->add(\MWLogger::class . ':LogProducto')->add(\AutentificadorJWT::class . ':AutenticacionSupervisor');
  $groupProducto->get('/csv', \ProductoController::class . ':TraerTodosCsv')->add(\MWLogger::class . ':LogProducto')->add(\AutentificadorJWT::class . ':AutenticacionEmpleado');
  $groupProducto->get('/{id_producto}', \ProductoController::class . ':TraerUno')->add(\MWLogger::class . ':LogProducto')->add(\AutentificadorJWT::class . ':AutenticacionEmpleado');
  /* $groupProducto->put('/editar', \ProductoController::class . ':ModificarUno')->add(\MWLogger::class . ':LogProducto')->add(\AutentificadorJWT::class . ':AutenticacionSupervisor');
  $groupProducto->delete('/eliminar', \ProductoController::class . ':BorrarUno')->add(\MWLogger::class . ':LogProducto')->add(\AutentificadorJWT::class . ':AutenticacionAdmin'); */
  $groupProducto->post('/cargar', \ProductoController::class . ':CargarUno')->add(\MWLogger::class . ':LogProducto')->add(\AutentificadorJWT::class . ':AutenticacionSupervisor');
  $groupProducto->post('/csv/cargar', \ProductoController::class . ':CargarCsv')->add(\MWLogger::class . ':LogProducto')->add(\AutentificadorJWT::class . ':AutenticacionSupervisor'); 
}); 

$app->group('/mesas', function (RouteCollectorProxy $groupMesa) {
  $groupMesa->get('/', \MesaController::class . ':TraerTodos')->add(\MWLogger::class . ':LogMesa')->add(\AutentificadorJWT::class . ':AutenticacionEmpleado');
  $groupMesa->get('/masusada', \MesaController::class . ':MayorUso')->add(\MWLogger::class . ':LogMesa')->add(\AutentificadorJWT::class . ':AutenticacionAdmin');
  $groupMesa->get('/menosusada', \MesaController::class . ':MenorUso')->add(\MWLogger::class . ':LogMesa')->add(\AutentificadorJWT::class . ':AutenticacionAdmin');
  $groupMesa->get('/mayorfacturacion', \MesaController::class . ':MayorFacturacion')->add(\MWLogger::class . ':LogMesa')->add(\AutentificadorJWT::class . ':AutenticacionAdmin');
  $groupMesa->get('/menorfacturacion', \MesaController::class . ':MenorFacturacion')->add(\MWLogger::class . ':LogMesa')->add(\AutentificadorJWT::class . ':AutenticacionAdmin');
  $groupMesa->get('/csv', \MesaController::class . ':TraerTodosCsv')->add(\MWLogger::class . ':LogMesa')->add(\AutentificadorJWT::class . ':AutenticacionEmpleado');
  $groupMesa->get('/{codigo_mesa}', \MesaController::class . ':TraerUno')->add(\MWLogger::class . ':LogMesa')->add(\AutentificadorJWT::class . ':AutenticacionEmpleado');
  /* $groupMesa->put('/editar', \MesaController::class . ':ModificarUno')->add(\MWLogger::class . ':LogMesa')->add(\AutentificadorJWT::class . ':AutenticacionEmpleado');
  $groupMesa->delete('/eliminar', \MesaController::class . ':BorrarUno')->add(\MWLogger::class . ':LogMesa')->add(\AutentificadorJWT::class . ':AutenticacionAdmin'); */
  $groupMesa->post('/cargar', \MesaController::class . ':CargarUno')->add(\MWLogger::class . ':LogMesa')->add(\AutentificadorJWT::class . ':AutenticacionSupervisor');
  $groupMesa->post('/csv/cargar', \MesaController::class . ':CargarCsv')->add(\MWLogger::class . ':LogMesa')->add(\AutentificadorJWT::class . ':AutenticacionSupervisor'); 
}); 

$app->group('/pedidos', function (RouteCollectorProxy $groupPedidos) {
  $groupPedidos->get('/', \PedidoController::class . ':TraerTodos')->add(\MWLogger::class . ':LogPedido')->add(\AutentificadorJWT::class . ':AutenticacionEmpleado');
  $groupPedidos->get('/fueradetiempo', \PedidoController::class . ':TraerFueraDeTiempo')->add(\MWLogger::class . ':LogPedido')->add(\AutentificadorJWT::class . ':AutenticacionAdmin');
  $groupPedidos->get('/cancelados', \PedidoController::class . ':TraerPedidosCancelados')->add(\MWLogger::class . ':LogPedido')->add(\AutentificadorJWT::class . ':AutenticacionAdmin');
  $groupPedidos->get('/csv', \PedidoController::class . ':TraerTodosCsv')->add(\MWLogger::class . ':LogPedido')->add(\AutentificadorJWT::class . ':AutenticacionEmpleado');
  $groupPedidos->get('/{id_responsable}', \PedidoController::class . ':TraerAsignados')->add(\MWLogger::class . ':LogPedido')->add(\AutentificadorJWT::class . ':AutenticacionEmpleado');
  $groupPedidos->get('/{id_responsable}/pendientes', \PedidoController::class . ':TraerAsignadosPendientes')->add(\MWLogger::class . ':LogPedido')->add(\AutentificadorJWT::class . ':AutenticacionEmpleado');
  $groupPedidos->get('/estado/{estado}', \PedidoController::class . ':TraerAsignadosPorEstado')->add(\MWLogger::class . ':LogPedido')->add(\AutentificadorJWT::class . ':AutenticacionEmpleado');
  $groupPedidos->get('/{codigo_mesa}/{codigo_pedido}', \PedidoController::class . ':TraerUno')->add(\MWLogger::class . ':LogPedido');/* 
  $groupPedidos->put('/editar', \PedidoController::class . ':ModificarUno')->add(\MWLogger::class . ':LogPedido')->add(\AutentificadorJWT::class . ':AutenticacionEmpleado');
  $groupPedidos->delete('/eliminar', \PedidoController::class . ':BorrarUno')->add(\MWLogger::class . ':LogPedido')->add(\AutentificadorJWT::class . ':AutenticacionAdmin'); */
  $groupPedidos->post('/cargar', \PedidoController::class . ':CargarUno')->add(\MWLogger::class . ':LogPedido')->add(\AutentificadorJWT::class . ':AutenticacionEmpleado');
  $groupPedidos->post('/cargar_foto', \PedidoController::class . ':CargarFoto')->add(\MWLogger::class . ':LogPedido')->add(\AutentificadorJWT::class . ':AutenticacionEmpleado');
  $groupPedidos->post('/csv/cargar', \PedidoController::class . ':CargarCsv')->add(\MWLogger::class . ':LogPedido')->add(\AutentificadorJWT::class . ':AutenticacionEmpleado'); 
  $groupPedidos->post('/tomar', \PedidoController::class . ':TomarPedido')->add(\MWLogger::class . ':LogPedido')->add(\AutentificadorJWT::class . ':AutenticacionEmpleado');
  $groupPedidos->post('/estado', \PedidoController::class . ':ModificarEstadoPedido')->add(\MWLogger::class . ':LogPedido')->add(\AutentificadorJWT::class . ':AutenticacionEmpleado');
  $groupPedidos->post('/cerrar', \PedidoController::class . ':CerrarPedido')->add(\MWLogger::class . ':LogPedido')->add(\AutentificadorJWT::class . ':AutenticacionAdmin');
});

$app->group('/sector/cocina', function (RouteCollectorProxy $groupCocina) {
  $groupCocina->get('/', \CocinaController::class . ':TraerTodosCocina')->add(\MWLogger::class . ':LogSectores')->add(\AutentificadorJWT::class . ':AutenticacionEmpleado');
  $groupCocina->get('/pendientes', \CocinaController::class . ':TraerPendientesCocina')->add(\MWLogger::class . ':LogSectores')->add(\AutentificadorJWT::class . ':AutenticacionEmpleado');
});

$app->group('/sector/tragos', function (RouteCollectorProxy $groupTragos) {
  $groupTragos->get('/', \TragoController::class . ':TraerTodosTrago')->add(\MWLogger::class . ':LogSectores')->add(\AutentificadorJWT::class . ':AutenticacionEmpleado');
  $groupTragos->get('/pendientes', \TragoController::class . ':TraerPendientesTrago')->add(\MWLogger::class . ':LogSectores')->add(\AutentificadorJWT::class . ':AutenticacionEmpleado');
});

$app->group('/sector/cervezas', function (RouteCollectorProxy $groupCervezas) {
  $groupCervezas->get('/', \CervezaController::class . ':TraerTodosCerveza')->add(\MWLogger::class . ':LogSectores')->add(\AutentificadorJWT::class . ':AutenticacionEmpleado');
  $groupCervezas->get('/pendientes', \CervezaController::class . ':TraerPendientesCerveza')->add(\MWLogger::class . ':LogSectores')->add(\AutentificadorJWT::class . ':AutenticacionEmpleado');
});

$app->group('/sector/candy', function (RouteCollectorProxy $groupCandy) {
  $groupCandy->get('/', \CandyController::class . ':TraerTodosCandy')->add(\MWLogger::class . ':LogSectores')->add(\AutentificadorJWT::class . ':AutenticacionEmpleado');
  $groupCandy->get('/pendientes', \CandyController::class . ':TraerPendientesCandy')->add(\MWLogger::class . ':LogSectores')->add(\AutentificadorJWT::class . ':AutenticacionEmpleado');
});

$app->group('/encuestas', function (RouteCollectorProxy $groupEncuestas) {
  $groupEncuestas->post('/', \EncuestaController::class . ':MandarEncuesta')->add(\MWLogger::class . ':LogEncuesta');
  $groupEncuestas->get('/mejores', \EncuestaController::class . ':TraerMejores')->add(\MWLogger::class . ':LogEncuesta')->add(\AutentificadorJWT::class . ':AutenticacionAdmin');
  $groupEncuestas->get('/peores', \EncuestaController::class . ':TraerPeores')->add(\MWLogger::class . ':LogEncuesta')->add(\AutentificadorJWT::class . ':AutenticacionAdmin');
});

// Run app
$app->run();

