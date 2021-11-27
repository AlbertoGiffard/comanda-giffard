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

        switch ($_SERVER['REQUEST_METHOD']) {
            case 'POST':
                if (isset($parametros['mail']) && isset($parametros['clave']) && isset($parametros['nombre']) && isset($parametros['sector']) && isset($parametros['puesto']) && isset($parametros['estado']) && isset($parametros['nivel_acceso'])) {

                    //guarda las validaciones
                    $validaciones = Usuario::Validaciones($parametros['sector'], $parametros['estado'], $parametros['nivel_acceso'], $parametros['mail']);

                    if ($validaciones === "validado") {
                        //armamos el usuario con estos datos
                        $user = new Usuario();
                        $user->SetearValores(null, $parametros['mail'], $parametros['clave'], $parametros['nombre'], null, $parametros['sector'], $parametros['puesto'], null, null, $parametros['estado'], $parametros['nivel_acceso']);
                        //envio el objeto usuario a usuarioController
                        $request = $request->withAttribute('usuario', $user);
                        //Todo lo que esta antes de esta linea se ejecuta previo de que continue la logica, una vez que se llama a la funcion de handler, lo deja seguir
                        $response = $handler->handle($request);
                        $ingreso = true;
                    } else {
                        $mensaje = $validaciones;
                    }
                }


                if (isset($parametros['mail_empleado']) && isset($parametros['nuevo_sector']) && isset($parametros['nuevo_puesto']) && isset($parametros['nuevo_estado']) && isset($parametros['nuevo_nivel_acceso'])) {
                    //guarda las validaciones
                    $validaciones = Usuario::ValidacionesModificar($parametros['nuevo_sector'], $parametros['nuevo_estado'], $parametros['nuevo_nivel_acceso'], $parametros['mail_empleado']);

                    if ($validaciones === "validado") {
                        //armamos el usuario con estos datos
                        $usuario = new Usuario();
                        $usuario->SetearValores(null, $parametros['mail_empleado'], null, null, null, $parametros['nuevo_sector'], $parametros['nuevo_puesto'], null, null, $parametros['nuevo_estado'], $parametros['nuevo_nivel_acceso']);
                        //envio el objeto usuario a usuarioController
                        $request = $request->withAttribute('usuario', $usuario);
                        //Todo lo que esta antes de esta linea se ejecuta previo de que continue la logica, una vez que se llama a la funcion de handler, lo deja seguir
                        $response = $handler->handle($request);
                        $ingreso = true;
                    } else {
                        $mensaje = $validaciones;
                    }
                }

                if (isset($parametros['mail']) && isset($parametros['clave']) && isset($parametros['movimiento'])) {

                    //guarda las validaciones
                    $validaciones = Movimiento::ValidarMovimiento($parametros['movimiento']);

                    if ($validaciones === "validado") {
                        //armamos el movimiento con estos datos
                        $movimiento = new Movimiento();
                        $movimiento->SetearValores(null, null, $parametros['movimiento']);
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
            case 'DELETE':
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
                if (isset($parametros['nombre']) && isset($parametros['sector']) && isset($parametros['estado']) && isset($parametros['stock'])) {

                    //guarda las validaciones
                    $validaciones = Producto::Validaciones($parametros['sector'], $parametros['estado']);

                    if ($validaciones === "validado") {
                        //armamos el usuario con estos datos
                        $producto = new Producto();
                        $producto->SetearValores(null, $parametros['nombre'], null, $parametros['sector'], 0, null, $parametros['estado'], $parametros['stock']);
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
                if (isset($parametros['estado'])) {

                    //guarda las validaciones
                    $validaciones = Mesa::ValidarEstado($parametros['estado']);

                    if ($validaciones === "validado") {
                        //armamos la mesa con estos datos
                        $mesa = new Mesa();
                        $mesa->SetearValores(null, null, $parametros['estado'], null, null);
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
                if (isset($parametros['codigo_mesa']) && isset($parametros['nombre_cliente']) && isset($parametros['producto']) && isset($parametros['cantidad']) && isset($parametros['importe']) && isset($parametros['demora'])) {
                    //instanciamos el pedido
                    $pedido = new Pedido();
                    $fotoMesa = null;
                    //guarda las validaciones
                    $validaciones = Pedido::Validaciones($parametros['codigo_mesa'], $parametros['producto'], $parametros['cantidad'], $pedido);

                    if ($validaciones === "validado") {
                        //si viene la foto la carga
                        if (isset($_FILES['foto_mesa'])) {
                            $fotoMesa = $_FILES['foto_mesa'];
                        }
                        $pedido->SetearValores(null, $parametros['codigo_mesa'], $parametros['nombre_cliente'], $parametros['producto'], $parametros['cantidad'], $parametros['importe'], $pedido->sector, null, null, $fotoMesa, $parametros['demora'], null, null, null);
                        //envio el objeto pedido a PedidoController
                        $request = $request->withAttribute('pedido', $pedido);
                        //Todo lo que esta antes de esta linea se ejecuta previo de que continue la logica, una vez que se llama a la funcion de handler, lo deja seguir
                        $response = $handler->handle($request);
                        $ingreso = true;
                    } else {
                        $mensaje = $validaciones;
                    }
                }

                if (isset($parametros['codigo_pedido']) && isset($_FILES['foto_mesa'])) {
                    //le paso el valor del codigo y la foto
                    $request = $request->withAttribute('codigo_pedido', $parametros['codigo_pedido']);
                    $request = $request->withAttribute('foto_mesa', $_FILES['foto_mesa']);

                    $response = $handler->handle($request);
                    $ingreso = true;
                }

                if (isset($_FILES['cargar'])) {
                    //le paso el csv
                    $request = $request->withAttribute('csv', $_FILES['cargar']);

                    $response = $handler->handle($request);
                    $ingreso = true;
                }

                if (isset($parametros['codigo_pedido']) && isset($parametros['id_responsable']) && isset($parametros['demora']) && isset($parametros['estado'])) {

                    if (Usuario::obtenerUsuario($parametros['id_responsable']) != false) {
                        if (Pedido::ValidarEstado($parametros['estado'])) {
                            $pedido = new Pedido();
                            $pedido->codigo_pedido = $parametros['codigo_pedido'];
                            $pedido->id_responsable = $parametros['id_responsable'];
                            $pedido->demora = $parametros['demora'];
                            $pedido->estado = $parametros['estado'];

                            $request = $request->withAttribute('pedido', $pedido);
                            $response = $handler->handle($request);
                            $ingreso = true;
                        } else {
                            $mensaje = array("mensaje" => "El estado no es valido debe ser: 'en preparacion' o 'listo para servir'");
                        }
                    } else {
                        $mensaje = array("mensaje" => "no existe el usuario por ese id");
                    }
                }

                if (isset($parametros['codigo_pedido']) && isset($parametros['nuevo_estado'])) {
                    if (Pedido::ValidarEstado($parametros['nuevo_estado'])) {
                        $pedido = new Pedido();
                        $pedido->codigo_pedido = $parametros['codigo_pedido'];
                        $pedido->estado = $parametros['nuevo_estado'];

                        $request = $request->withAttribute('pedido', $pedido);
                        //Todo lo que esta antes de esta linea se ejecuta previo de que continue la logica, una vez que se llama a la funcion de handler, lo deja seguir
                        $response = $handler->handle($request);
                        $ingreso = true;
                    } else {
                        $mensaje = array("mensaje" => "El estado no es valido debe ser: 'en preparacion', 'listo para servir', 'servido', 'cobrado' o 'cancelado'");
                    }
                }

                if (isset($parametros['pedido_para_cerrar'])) {
                    $pedido = new Pedido();
                    $pedido->codigo_pedido = $parametros['pedido_para_cerrar'];
                    $pedido->estado = 'cerrado';

                    $request = $request->withAttribute('pedido', $pedido);
                    //Todo lo que esta antes de esta linea se ejecuta previo de que continue la logica, una vez que se llama a la funcion de handler, lo deja seguir
                    $response = $handler->handle($request);
                    $ingreso = true;
                }
                break;

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

    public static function LogEncuesta($request, $handler)
    {
        $mensaje = array("mensaje" => "Error! faltan datos");
        $parametros = $request->getParsedBody();
        $ingreso = false;

        switch ($_SERVER['REQUEST_METHOD']) {
            case 'POST':
                if (isset($parametros['codigo_pedido']) && isset($parametros['codigo_mesa']) && isset($parametros['mesa']) && isset($parametros['mozo']) && isset($parametros['restaurante']) && isset($parametros['preparador']) && isset($parametros['experiencia'])) {

                    if (Encuesta::Validaciones($parametros['mesa'], $parametros['mozo'], $parametros['restaurante'], $parametros['preparador'], $parametros['experiencia'])) {
                        $encuesta = new Encuesta();
                        $encuesta->SetearValores(null, $parametros['codigo_pedido'], $parametros['codigo_mesa'], $parametros['mesa'], $parametros['mozo'], $parametros['restaurante'], $parametros['preparador'], $parametros['experiencia'], null);


                        $request = $request->withAttribute('encuesta', $encuesta);
                        //Todo lo que esta antes de esta linea se ejecuta previo de que continue la logica, una vez que se llama a la funcion de handler, lo deja seguir
                        $response = $handler->handle($request);
                        $ingreso = true;
                    } else {
                        $mensaje = array("mensaje" => "Las valoraciones deben ser del uno al diez, y la experiencia no tan larga, verifique nuevamente");
                    }
                }
                break;

            case 'GET':
                $response = $handler->handle($request);
                $ingreso = true;
                break;
        }


        if (!$ingreso) {
            $response = new Response();

            $response->getBody()->write(json_encode($mensaje));
        }

        //Despues
        return $response;
    }
}
