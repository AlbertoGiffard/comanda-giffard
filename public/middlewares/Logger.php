<?php

use Slim\Psr7\Response as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;

class MWLogger
{
    public static function LogUsuario($request, $handler)
    {
        $mensaje = "Faltan datos";
        $parametros = $request->getParsedBody();
        $ingreso = false;

        //FALTA VALIDAR QUE EL USAURIO PUEDA CARGAR NUEVOS USUARIOS
        //$header = $request->getHeaderLine('Authorization');

        switch ($_SERVER['REQUEST_METHOD']) {
            case 'POST':

                if (isset($_POST['mail']) && isset($_POST['clave']) && isset($_POST['nombre']) && isset($_POST['sector']) && isset($_POST['puesto']) && isset($_POST['estado']) && isset($_POST['nivel_acceso'])) {

                    //guarda las validaciones
                    $validaciones = Usuario::Validaciones($_POST['sector'], $_POST['estado'], $_POST['nivel_acceso'], $_POST['mail']);

                    if ($validaciones === "validado") {
                        //armamos el usuario con estos datos
                        $user = new Usuario();
                        $user->SetearValores(null, $_POST['mail'], $_POST['clave'], $_POST['nombre'], null, $_POST['sector'], $_POST['puesto'], null, null, $_POST['estado'], $_POST['nivel_acceso']);
                        //envio el objeto usuario a usuarioController
                        $request = $request->withAttribute('usuario', $user);
                        //Todo lo que esta antes de esta linea se ejecuta previo de que continue la logica, una vez que se llama a la funcion de handler, lo deja seguir
                        $response = $handler->handle($request);
                        $ingreso = true;
                    } else {
                        $mensaje = $validaciones;
                    }
                }

                if (isset($_POST['mail']) && isset($_POST['clave']) && isset($_POST['movimiento'])) {

                    //FALTA VALIDAR QUE EL USAURIO PUEDA CARGAR NUEVOS USUARIOS

                    //guarda las validaciones
                    $validaciones = Movimiento::ValidarMovimiento($_POST['movimiento']);

                    if ($validaciones === "validado") {
                        //armamos el movimiento con estos datos
                        $movimiento = new Movimiento();
                        $movimiento->SetearValores(null, null, $_POST['movimiento']);
                        //envio el objeto movimiento a usuarioController
                        $request = $request->withAttribute('movimiento', $movimiento);
                        //Todo lo que esta antes de esta linea se ejecuta previo de que continue la logica, una vez que se llama a la funcion de handler, lo deja seguir
                        $response = $handler->handle($request);
                        $ingreso = true;
                    } else {
                        $mensaje = $validaciones;
                    }
                }

                if (isset($_FILES['cargar'])) {
                    //le paso el csv
                    $request = $request->withAttribute('csv', $_FILES['cargar']);

                    $response = $handler->handle($request);
                    $ingreso = true;
                }
                break;

            case 'GET':
                if (isset($_GET['id_usuario'])) {
                    $id = $_GET['id_usuario'];
                    //le paso el valor del id
                    $request = $request->withAttribute('id_usuario', $id);
                }

                $response = $handler->handle($request);
                $ingreso = true;
                break;
        }

        //valido que haya logrado ingresar
        //aqui no llamo al handler porque indica que no logro continuar
        if (!$ingreso) {
            $response = new Response();

            $response->getBody()->write(json_encode(array("Error" => $mensaje)));
        }

        //Despues
        return $response;
    }

    public static function LogProducto($request, $handler)
    {
        $mensaje = "Faltan datos";
        $parametros = $request->getParsedBody();
        $ingreso = false;

        switch ($_SERVER['REQUEST_METHOD']) {
            case 'POST':
                if (isset($_POST['nombre']) && isset($_POST['sector']) && isset($_POST['estado']) && isset($_POST['stock'])) {

                    //FALTA VALIDAR QUE EL USUARIO PUEDA CARGAR NUEVOS USUARIOS

                    //guarda las validaciones
                    $validaciones = Producto::Validaciones($_POST['sector'], $_POST['estado']);

                    if ($validaciones === "validado") {
                        //armamos el usuario con estos datos
                        $producto = new Producto();
                        $producto->SetearValores(null, $_POST['nombre'], null, $_POST['sector'], 0, null, $_POST['estado'], $_POST['stock']);
                        //envio el objeto producto a productoController
                        $request = $request->withAttribute('producto', $producto);
                        //Todo lo que esta antes de esta linea se ejecuta previo de que continue la logica, una vez que se llama a la funcion de handler, lo deja seguir
                        $response = $handler->handle($request);
                        $ingreso = true;
                    } else {
                        $mensaje = $validaciones;
                    }
                }

                if (isset($_FILES['cargar'])) {
                    //le paso el csv
                    $request = $request->withAttribute('csv', $_FILES['cargar']);

                    $response = $handler->handle($request);
                    $ingreso = true;
                }
                break;

            case 'GET':
                if (isset($_GET['id_producto'])) {
                    $id = $_GET['id_producto'];

                    //le paso el valor del id
                    $request = $request->withAttribute('id_producto', $id);
                }

                $response = $handler->handle($request);
                $ingreso = true;
                break;
        }

        //valido que haya logrado ingresar
        //aqui no llamo al hanlder porque indica que no logro continuar
        if (!$ingreso) {
            $response = new Response();

            $response->getBody()->write($mensaje);
        }

        //Despues
        return $response;
    }


    public static function LogMesa($request, $handler)
    {
        $mensaje = "Faltan datos";
        $parametros = $request->getParsedBody();
        $ingreso = false;

        switch ($_SERVER['REQUEST_METHOD']) {
            case 'POST':
                if (isset($_POST['estado'])) {

                    //FALTA VALIDAR QUE EL USUARIO PUEDA CARGAR NUEVAS MESAS

                    //guarda las validaciones
                    $validaciones = Mesa::ValidarEstado($_POST['estado']);

                    if ($validaciones === "validado") {
                        //armamos la mesa con estos datos
                        $mesa = new Mesa();
                        $mesa->SetearValores(null, null, $_POST['estado'], null, null);
                        //envio el objeto mesa a mesaController
                        $request = $request->withAttribute('mesa', $mesa);
                        //Todo lo que esta antes de esta linea se ejecuta previo de que continue la logica, una vez que se llama a la funcion de handler, lo deja seguir
                        $response = $handler->handle($request);
                        $ingreso = true;
                    } else {
                        $mensaje = $validaciones;
                    }
                }

                if (isset($_FILES['cargar'])) {
                    //le paso el csv
                    $request = $request->withAttribute('csv', $_FILES['cargar']);

                    $response = $handler->handle($request);
                    $ingreso = true;
                }
                break;

            case 'GET':
                if (isset($_GET['id_producto'])) {
                    $id = $_GET['id_producto'];

                    //le paso el valor del id
                    $request = $request->withAttribute('id_producto', $id);
                }

                $response = $handler->handle($request);
                $ingreso = true;
                break;
        }

        //valido que haya logrado ingresar
        //aqui no llamo al hanlder porque indica que no logro continuar
        if (!$ingreso) {
            $response = new Response();

            $response->getBody()->write($mensaje);
        }

        //Despues
        return $response;
    }

    public static function LogPedido($request, $handler)
    {
        $mensaje = "Faltan datos";
        $parametros = $request->getParsedBody();
        $ingreso = false;
        switch ($_SERVER['REQUEST_METHOD']) {
            case 'POST':
                if (isset($_POST['codigo_mesa']) && isset($_POST['nombre_cliente']) && isset($_POST['producto']) && isset($_POST['cantidad']) && isset($_POST['importe']) && isset($_POST['demora'])) {

                    //FALTA VALIDAR QUE EL USUARIO PUEDA CARGAR NUEVAS MESAS

                    //instanciamos el pedido
                    $pedido = new Pedido();
                    $fotoMesa = null;
                    //guarda las validaciones
                    $validaciones = Pedido::Validaciones($_POST['codigo_mesa'], $_POST['producto'], $_POST['cantidad'], $pedido);

                    if ($validaciones === "validado") {
                        //si viene la foto la carga
                        if (isset($_FILES['foto_mesa'])) {
                            $fotoMesa = $_FILES['foto_mesa'];
                        }
                        $pedido->SetearValores(null, $_POST['codigo_mesa'], $_POST['nombre_cliente'], $_POST['producto'], $_POST['cantidad'], $_POST['importe'], $pedido->sector, null, null, $fotoMesa, $_POST['demora'], null, null, null);
                        //envio el objeto pedido a PedidoController
                        $request = $request->withAttribute('pedido', $pedido);
                        //Todo lo que esta antes de esta linea se ejecuta previo de que continue la logica, una vez que se llama a la funcion de handler, lo deja seguir
                        $response = $handler->handle($request);
                        $ingreso = true;
                    } else {
                        $mensaje = $validaciones;
                    }
                }

                if (isset($_POST['codigo_pedido']) && isset($_FILES['foto_mesa'])) {
                    //le paso el valor del codigo y la foto
                    $request = $request->withAttribute('codigo_pedido', $_POST['codigo_pedido']);
                    $request = $request->withAttribute('foto_mesa', $_FILES['foto_mesa']);

                    $response = $handler->handle($request);
                    $ingreso = true;
                }

                if (isset($_FILES['cargar'])) {
                    echo "entre";
                    //le paso el csv
                    $request = $request->withAttribute('csv', $_FILES['cargar']);

                    $response = $handler->handle($request);
                    $ingreso = true;
                }

                if (isset($_POST['codigo_pedido']) && isset($_POST['id_responsable']) && isset($_POST['demora']) && isset($_POST['nuevo_estado'])) {

                    if (Usuario::obtenerUsuario($_POST['id_responsable']) != false) {
                        if (Pedido::ValidarEstado($_POST['nuevo_estado'])) {
                            $pedido = new Pedido();
                            $pedido->codigo_pedido = $_POST['codigo_pedido'];
                            $pedido->id_responsable = $_POST['id_responsable'];
                            $pedido->demora = $_POST['demora'];
                            $pedido->estado = $_POST['nuevo_estado'];

                            $request = $request->withAttribute('pedido', $pedido);
                            //Todo lo que esta antes de esta linea se ejecuta previo de que continue la logica, una vez que se llama a la funcion de handler, lo deja seguir
                            $response = $handler->handle($request);
                            $ingreso = true;
                        } else {
                            $mensaje = array("mensaje" => "El estado no es valido debe ser: 'en preparacion' o 'listo para servir'");
                        }
                    } else {
                        $mensaje = array("mensaje" => "no existe el usuario por ese id");
                    }
                }

                if (isset($_POST['codigo_pedido']) && isset($_POST['nuevo_estado'])) {

                    if (Pedido::ValidarEstado($_POST['nuevo_estado'])) {
                        $pedido = new Pedido();
                        $pedido->codigo_pedido = $_POST['codigo_pedido'];
                        $pedido->estado = $_POST['nuevo_estado'];

                        $request = $request->withAttribute('pedido', $pedido);                       
                        //Todo lo que esta antes de esta linea se ejecuta previo de que continue la logica, una vez que se llama a la funcion de handler, lo deja seguir
                        $response = $handler->handle($request);
                        $ingreso = true;
                    } else {
                        $mensaje = array("mensaje" => "El estado no es valido debe ser: 'en preparacion' o 'listo para servir'");
                    }
                }
                break;

            case 'GET':                
                if (isset($_GET['codigo_pedido']) && isset($_GET['codigo_mesa'])) {
                    $codigo_pedido = $_GET['codigo_pedido'];
                    $codigo_mesa = $_GET['codigo_mesa'];

                    //le paso el valor del id
                    $request = $request->withAttribute('codigo_pedido', $codigo_pedido);
                    $request = $request->withAttribute('codigo_mesa', $codigo_mesa);
                }
                if (isset($_GET['id_responsable'])) {
                    $idResponsable = $_GET['id_responsable'];
                    //le paso el valor del id
                    $request = $request->withAttribute('id_responsable', $idResponsable);
                }

                $response = $handler->handle($request);
                $ingreso = true;
                break;
        }

        //valido que haya logrado ingresar
        //aqui no llamo al hanlder porque indica que no logro continuar
        if (!$ingreso) {
            $response = new Response();

            $response->getBody()->write($mensaje);
        }

        //Despues
        return $response;
    }

    public static function LogSectores($request, $handler)
    {
        $mensaje = array("mensaje" => "Error! faltan datos");
        $parametros = $request->getParsedBody();
        $ingreso = false;
        switch ($_SERVER['REQUEST_METHOD']) {
            case 'GET':
                $response = $handler->handle($request);
                $ingreso = true;
                break;
        }

        //valido que haya logrado ingresar
        //aqui no llamo al hanlder porque indica que no logro continuar
        if (!$ingreso) {
            $response = new Response();

            $response->getBody()->write(json_encode($mensaje));
        }

        //Despues
        return $response;
    }
}
