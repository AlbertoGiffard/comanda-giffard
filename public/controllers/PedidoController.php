<?php
require_once './models/Pedido.php';
require_once './models/Encuesta.php';
require_once './interfaces/IApiUsable.php';

class PedidoController extends Pedido implements IApiUsable
{
  public function CargarUno($request, $response, $args)
  {
    $pedido = $request->getAttribute('pedido');
    $payload = array("mensaje" => "Error! no se pudo cargar el pedido, intente mas tarde");

    // Creamos el pedido
    $pedido->crearPedido($payload);

    $response->getBody()->write(json_encode($payload));
    return $response
      ->withHeader('Content-Type', 'application/json');
  }

  public function TraerTodos($request, $response, $args)
  {
    $lista = Pedido::obtenerTodos();
    foreach ($lista as $pedido) {
      $pedido->retrasado = $pedido->fecha_entrega < date("Y-m-d H:i:s");
    }
    //me lo trae como un json
    $payload = $lista;

    $response->getBody()->write(json_encode($payload));
    return $response
      ->withHeader('Content-Type', 'application/json');
  }

  public function TraerFueraDeTiempo($request, $response, $args)
  {
    $lista = Pedido::TraerFueraTiempo();

    //me lo trae como un json
    $payload = $lista;

    $response->getBody()->write(json_encode($payload));
    return $response
      ->withHeader('Content-Type', 'application/json');
  }

  public function TraerPedidosCancelados($request, $response, $args)
  {
    $lista = Pedido::TraerCancelados();

    //me lo trae como un json
    $payload = $lista;

    $response->getBody()->write(json_encode($payload));
    return $response
      ->withHeader('Content-Type', 'application/json');
  }

  public function TraerUno($request, $response, $args)
  {
    /* $codigo_pedido = $request->getAttribute('codigo_pedido');
    $codigo_mesa = $request->getAttribute('codigo_mesa'); */
    $codigo_mesa = $args['codigo_mesa'];
    $codigo_pedido = $args['codigo_pedido'];

    $pedido = Pedido::ObtenerPedido($codigo_pedido, $codigo_mesa);
    if ($pedido == false) {
      $payload = json_encode(array("mensaje" => "Error! no se encontro ningun pedido por esos codigos"));
    } else {
      $pedido->retrasado = $pedido->fecha_entrega < date("Y-m-d H:i:s");
      $payload = json_encode(array("codigo pedido" => $pedido->codigo_pedido, "codigo mesa" => $pedido->codigo_mesa, "producto" => $pedido->producto, "cantidad" => $pedido->cantidad, "tiempo de demora" => $pedido->demora, "fecha aprox. de entrega" => $pedido->fecha_entrega, "estado" => $pedido->estado));
    }

    $response->getBody()->write($payload);
    return $response
      ->withHeader('Content-Type', 'application/json');
  }
  public function CargarFoto($request, $response, $args)
  {
    $codigo_pedido = $request->getAttribute('codigo_pedido');
    $foto_mesa = $request->getAttribute('foto_mesa');
    $mensaje = "No se encuentra el pedido con ese código, verifique e intente nuevamente";

    if (Pedido::ActualizarFotoPedido($codigo_pedido, $foto_mesa, $mensaje)) {
      $payload = json_encode(array("mensaje" => "Foto de pedido actualizada y cargada con exito"));
    } else {
      $payload = json_encode(array("mensaje" => "Error! " . $mensaje));
    }

    $response->getBody()->write($payload);
    return $response
      ->withHeader('Content-Type', 'application/json');
  }
  public function TraerTodosCsv($request, $response, $args)
  {
    header('Content-Type: text/plain;charset=UTF-8');
    $stream = fopen('php://output', 'w');

    $lista = Pedido::obtenerTodos();

    fputcsv($stream, array("codigo_pedido", "codigo_mesa", "nombre_cliente", "producto", "cantidad", "importe", "sector", "id_responsable", "fecha_creacion", "foto_mesa", "demora", "retrasado", "fecha_entrega", "estado"));

    foreach ($lista as $pedido) {
      $pedido->retrasado = $pedido->fecha_entrega < date("Y-m-d H:i:s");
      fputcsv($stream, array($pedido->codigo_pedido, $pedido->codigo_mesa, $pedido->nombre_cliente, $pedido->producto, $pedido->cantidad, $pedido->importe, $pedido->sector, $pedido->id_responsable, $pedido->fecha_creacion, $pedido->foto_mesa, $pedido->demora, $pedido->retrasado, $pedido->fecha_entrega, $pedido->estado));
    }
    // more complex codes would be here. It may cause error.

    header("Content-Type: application/text");
    header("Content-Disposition: attachment; filename=output.csv");

    $response->getBody()->write(mb_convert_encoding(ob_get_clean(), 'SJIS', 'UTF-8'));
    return $response
      ->withHeader('Content-Type', 'application/json');
  }
  public function CargarCsv($request, $response, $args)
  {
    $csv = $request->getAttribute('csv');
    $mensaje = "El fichero no es válido";

    // Creamos la entrada
    if (Pedido::GuardarPedidoEnCsv($csv, $mensaje)) {
      $payload = json_encode(array("mensaje" => "Cargado csv con pedidos correctamente"));
    } else {
      $payload = json_encode(array("mensaje" => "Error!" . $mensaje));
    }

    $response->getBody()->write($payload);
    return $response
      ->withHeader('Content-Type', 'application/json');
  }

  public function TomarPedido($request, $response, $args)
  {
    $header = $request->getHeaderLine('Authorization');
    $usuarioPorToken = TokenController::ValidacionTokenCompleta($header);
    if ($usuarioPorToken != false) {
      $idUsuarioToken = $usuarioPorToken->{'id_usuario'};
    }

    $pedido = $request->getAttribute('pedido');
    $payload = array("mensaje" => "Error! no se logro modificar el estado del pedido intente nuevamente");

    if (Pedido::PedidoTomado($pedido, $payload, $idUsuarioToken)) {
      $payload = array("mensaje" => "Pedido tomado con exito", "tiempo de entrega" => $pedido->fecha_entrega);
    }

    $response->getBody()->write(json_encode($payload));
    return $response
      ->withHeader('Content-Type', 'application/json');
  }

  public function ModificarEstadoPedido($request, $response, $args)
  {
    $header = $request->getHeaderLine('Authorization');
    $usuarioPorToken = TokenController::ValidacionTokenCompleta($header);
    if ($usuarioPorToken != false) {
      $idUsuarioToken = $usuarioPorToken->{'id_usuario'};
    }

    $pedido = $request->getAttribute('pedido');
    $payload = array("mensaje" => "Error! no se logro modificar el estado del pedido intente nuevamente");

    if (Pedido::ModificarEstado($pedido, $payload, $idUsuarioToken)) {
      $payload = array("mensaje" => "Pedido modificado con exito");
    }

    $response->getBody()->write(json_encode($payload));
    return $response
      ->withHeader('Content-Type', 'application/json');
  }

  public function CerrarPedido($request, $response, $args)
  {
    $header = $request->getHeaderLine('Authorization');
    $usuarioPorToken = TokenController::ValidacionTokenCompleta($header);
    if ($usuarioPorToken != false) {
      $idUsuarioToken = $usuarioPorToken->{'id_usuario'};
    }

    $pedido = $request->getAttribute('pedido');
    $payload = array("mensaje" => "Error! no se logro modificar el estado del pedido intente nuevamente");

    if (Pedido::ModificarEstado($pedido, $payload, $idUsuarioToken)) {
      $payload = Encuesta::GenerarEncuestaConPedido($pedido);
      /* $payload = array("mensaje" => "Pedido modificado con exito"); */
    }

    $response->getBody()->write(json_encode($payload));
    return $response
      ->withHeader('Content-Type', 'application/json');
  }

  public function TraerAsignados($request, $response, $args)
  {
    //$idResponsable = $request->getAttribute('id_responsable');
    $idResponsable = $args['id_responsable'];

    if (Usuario::obtenerUsuario($idResponsable)) {
      $lista = Pedido::obtenerPedidosAsignados($idResponsable);
      if (empty($lista)) {
        $payload = json_encode(array("mensaje" => "No hay ningun pedido pendiente"));
      } else {
        foreach ($lista as $pedido) {
          $pedido->retrasado = $pedido->fecha_entrega < date("Y-m-d H:i:s");
        }
        $payload = json_encode($lista);
      }
    } else {
      $payload = json_encode(array("mensaje" => "Error! no se encontro ningun usuario por ese id"));
    }

    $response->getBody()->write($payload);
    return $response
      ->withHeader('Content-Type', 'application/json');
  }

  public function TraerAsignadosPendientes($request, $response, $args)
  {
    //$idResponsable = $request->getAttribute('id_responsable');
    $idResponsable = $args['id_responsable'];

    if (Usuario::obtenerUsuario($idResponsable)) {
      $lista = Pedido::obtenerPedidosPendientesAsignados($idResponsable);
      if (empty($lista)) {
        $payload = json_encode(array("mensaje" => "No hay ningun pedido pendiente"));
      } else {
        foreach ($lista as $pedido) {
          $pedido->retrasado = $pedido->fecha_entrega < date("Y-m-d H:i:s");
        }
        $payload = json_encode($lista);
      }
    } else {
      $payload = json_encode(array("mensaje" => "Error! no se encontro ningun usuario por ese id"));
    }

    $response->getBody()->write($payload);
    return $response
      ->withHeader('Content-Type', 'application/json');
  }

  public function TraerAsignadosPorEstado($request, $response, $args)
  {
    $estado = $args['estado'];

    switch ($estado) {
      case 'preparacion':
        $estado = "en preparacion";
        break;

      case 'servir':
        $estado = "listo para servir";
        break;
    }

    if (Pedido::ValidarEstado($estado)) {
      $lista = Pedido::ObtenerPedidosEstado($estado);
      if (empty($lista)) {
        $payload = json_encode(array("mensaje" => "No hay ningun pedido en ese estado"));
      } else {
        foreach ($lista as $pedido) {
          $pedido->retrasado = $pedido->fecha_entrega < date("Y-m-d H:i:s");
        }
        $payload = json_encode($lista);
      }
    } else {
      $payload = json_encode(array("mensaje" => "El estado no es valido debe ser: 'pendiente', 'preparacion', 'servir', 'servido', 'cobrado', 'cerrado' o 'cancelado'"));
    }

    $response->getBody()->write($payload);
    return $response
      ->withHeader('Content-Type', 'application/json');
  }
  // PARTIR DE ACA TODO POR HACER
  public function ModificarUno($request, $response, $args)
  {
    $parametros = $request->getParsedBody();

    $id = $parametros['id'];
    $usuario = $parametros['usuario'];
    $clave = password_hash($parametros['clave'], PASSWORD_DEFAULT);
    // Creamos el usuario
    $usr = new Usuario();
    $usr->id = $id;
    $usr->usuario = $usuario;
    $usr->clave = $clave;

    Usuario::modificarUsuario($usr);

    $payload = json_encode(array("mensaje" => "Usuario modificado con exito"));

    $response->getBody()->write($payload);
    return $response
      ->withHeader('Content-Type', 'application/json');
  }

  public function BorrarUno($request, $response, $args)
  {
    $usuarioId = $args['usuarioId'];
    Usuario::borrarUsuario($usuarioId);

    $payload = json_encode(array("mensaje" => "Usuario borrado con exito"));

    $response->getBody()->write($payload);
    return $response->withHeader('Content-Type', 'application/json');
  }
}
