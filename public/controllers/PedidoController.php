<?php
require_once './models/Pedido.php';
require_once './interfaces/IApiUsable.php';

class PedidoController extends Pedido implements IApiUsable
{
  public function CargarUno($request, $response, $args)
  {
    $pedido = $request->getAttribute('pedido');
    $payload = array("mensaje" => "Error! no se pudo cargar el pedido, intente mas tarde");

    // Creamos el pedido
    $pedido->crearPedido($payload);
    //aca hay que cargar el metodo de la foto

    $response->getBody()->write(json_encode($payload));
    return $response
      ->withHeader('Content-Type', 'application/json');
  }

  public function TraerTodos($request, $response, $args)
  {
    $lista = Pedido::obtenerTodos();
    //me lo trae como un json
    $payload = "<table> <th> Codigo Pedido </th> <th> Codigo Mesa </th><th> Nombre Cliente </th> <th> Producto </th><th> Cantidad </th><th> Importe </th><th> Sector </th><th> ID responsable </th><th> Fecha creacion </th><th> Demora </th><th> Fecha entrega </th><th> Estado </th>";

    foreach ($lista as $pedido) {
      $payload = $payload . "<tr>" . "<td>" . $pedido->codigo_pedido . "</td>" . "<td>" . $pedido->codigo_mesa . "</td>" . "<td>" . $pedido->nombre_cliente . "</td>" . "<td>" . $pedido->producto . "</td>" . "<td>" . $pedido->cantidad . "</td>" . "<td>" . $pedido->importe . "</td>" . "<td>" . $pedido->sector . "</td>" . "<td>" . $pedido->id_responsable . "</td>" . "<td>" . $pedido->fecha_creacion . "</td>" . "<td>" . $pedido->demora . "</td>" . "<td>" . $pedido->fecha_entrega . "</td>" . "<td>" . $pedido->estado . "</td>" . "</tr>";
    }
    $payload = $payload . "</table>";

    $response->getBody()->write($payload);
    return $response
      ->withHeader('Content-Type', 'application/json');
  }

  public function TraerUno($request, $response, $args)
  {
    $codigo_pedido = $request->getAttribute('codigo_pedido');
    $codigo_mesa = $request->getAttribute('codigo_mesa');

    $pedido = Pedido::ObtenerPedido($codigo_pedido, $codigo_mesa);
    if ($pedido == false) {
      $payload = json_encode(array("mensaje" => "Error! no se encontro ningun pedido por ese codigo"));
    } else {
      $payload = json_encode($pedido);
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

    foreach($lista as $pedido){
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
    $pedido = $request->getAttribute('pedido');
    $payload = array("mensaje" => "Error! no se logro modificar el estado del pedido intente nuevamente");

    if (Pedido::PedidoTomado($pedido, $payload)) {
      $payload = array("mensaje" => "Pedido tomado con exito", "tiempo de entrega" => $pedido->fecha_entrega);
    }

    $response->getBody()->write(json_encode($payload));
    return $response
      ->withHeader('Content-Type', 'application/json');
  }

  public function ModificarEstadoPedido($request, $response, $args)
  {
    $pedido = $request->getAttribute('pedido');
    $payload = array("mensaje" => "Error! no se logro modificar el estado del pedido intente nuevamente");

    if (Pedido::ModificarEstado($pedido, $payload)) {
      $payload = array("mensaje" => "Pedido modificado con exito");
    }

    $response->getBody()->write(json_encode($payload));
    return $response
      ->withHeader('Content-Type', 'application/json');
  }

  public function TraerAsignados($request, $response, $args)
  {
    $idResponsable = $request->getAttribute('id_responsable');

    if (Usuario::obtenerUsuario($idResponsable)) {
      $lista = Pedido::obtenerPedidosAsignados($idResponsable);
      if (empty($lista)) {
        $payload = json_encode(array("mensaje" => "No hay ningun pedido pendiente"));
      } else {
        $payload = "<table> <th> Codigo Pedido </th> <th> Codigo Mesa </th><th> Nombre Cliente </th> <th> Producto </th><th> Cantidad </th><th> Importe </th><th> Sector </th><th> ID responsable </th><th> Fecha creacion </th><th> Demora </th><th> Fecha entrega </th><th> Estado </th>";

        foreach ($lista as $pedido) {
          $payload = $payload . "<tr>" . "<td>" . $pedido->codigo_pedido . "</td>" . "<td>" . $pedido->codigo_mesa . "</td>" . "<td>" . $pedido->nombre_cliente . "</td>" . "<td>" . $pedido->producto . "</td>" . "<td>" . $pedido->cantidad . "</td>" . "<td>" . $pedido->importe . "</td>" . "<td>" . $pedido->sector . "</td>" . "<td>" . $pedido->id_responsable . "</td>" . "<td>" . $pedido->fecha_creacion . "</td>" . "<td>" . $pedido->demora . "</td>" . "<td>" . $pedido->fecha_entrega . "</td>" . "<td>" . $pedido->estado . "</td>" . "</tr>";
        }
        $payload = $payload . "</table>";
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
    $idResponsable = $request->getAttribute('id_responsable');

    if (Usuario::obtenerUsuario($idResponsable)) {
      $lista = Pedido::obtenerPedidosPendientesAsignados($idResponsable);
      if (empty($lista)) {
        $payload = json_encode(array("mensaje" => "No hay ningun pedido pendiente"));
      } else {
        $payload = "<table> <th> Codigo Pedido </th> <th> Codigo Mesa </th><th> Nombre Cliente </th> <th> Producto </th><th> Cantidad </th><th> Importe </th><th> Sector </th><th> ID responsable </th><th> Fecha creacion </th><th> Demora </th><th> Fecha entrega </th><th> Estado </th>";

        foreach ($lista as $pedido) {
          $payload = $payload . "<tr>" . "<td>" . $pedido->codigo_pedido . "</td>" . "<td>" . $pedido->codigo_mesa . "</td>" . "<td>" . $pedido->nombre_cliente . "</td>" . "<td>" . $pedido->producto . "</td>" . "<td>" . $pedido->cantidad . "</td>" . "<td>" . $pedido->importe . "</td>" . "<td>" . $pedido->sector . "</td>" . "<td>" . $pedido->id_responsable . "</td>" . "<td>" . $pedido->fecha_creacion . "</td>" . "<td>" . $pedido->demora . "</td>" . "<td>" . $pedido->fecha_entrega . "</td>" . "<td>" . $pedido->estado . "</td>" . "</tr>";
        }
        $payload = $payload . "</table>";
      }
    } else {
      $payload = json_encode(array("mensaje" => "Error! no se encontro ningun usuario por ese id"));
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
