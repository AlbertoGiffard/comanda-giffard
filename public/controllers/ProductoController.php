<?php
require_once './models/Producto.php';
require_once './interfaces/IApiUsable.php';

class ProductoController extends Producto implements IApiUsable
{
  public function CargarUno($request, $response, $args)
  {
    $producto = $request->getAttribute('producto');

    // Creamos el producto
    if($producto->crearProducto()){
      $payload = json_encode(array("mensaje" => "Producto creado con exito"));
    } else{
      $payload = json_encode(array("mensaje" => "Error! no se pudo cargar el producto, intente mas tarde"));
    }

    $response->getBody()->write($payload);
    return $response
      ->withHeader('Content-Type', 'application/json');
  }

  public function TraerTodos($request, $response, $args)
  {
    $lista = Producto::obtenerTodos();
    //me lo trae como un json
    $payload = $lista;

    $response->getBody()->write(json_encode($payload));
    return $response
      ->withHeader('Content-Type', 'application/json');
  }
  
  public function TraerUno($request, $response, $args)
  {
    // Buscamos usuario por id
    //$id_producto = $request->getAttribute('id_producto');
    $id_producto = $args['id_producto'];

    $producto = Producto::obtenerProducto($id_producto);
    //valida que el usuario exista
    if ($producto == false) {
      $payload = json_encode(array("mensaje" => "Error! no se encontró ningun producto por ese ID"));
    } else{
      $payload = json_encode($producto);
    }

    $response->getBody()->write($payload);
    return $response
      ->withHeader('Content-Type', 'application/json');
  }
  
  public function TraerMasVendidos($request, $response, $args)
  {
    $producto = Producto::obtenerMasVendidos();
    //valida que el usuario exista
    if ($producto == false) {
      $payload = json_encode(array("mensaje" => "Error! no se encontró ningun producto vendido"));
    } else{
      $payload = json_encode($producto);
    }

    $response->getBody()->write($payload);
    return $response
      ->withHeader('Content-Type', 'application/json');
  }
  
  public function TraerMenosVendidos($request, $response, $args)
  {
    $producto = Producto::obtenerMenosVendidos();
    //valida que el usuario exista
    if ($producto == false) {
      $payload = json_encode(array("mensaje" => "Error! no se encontró ningun producto vendido"));
    } else{
      $payload = json_encode($producto);
    }

    $response->getBody()->write($payload);
    return $response
      ->withHeader('Content-Type', 'application/json');
  }

  public function TraerTodosCsv($request, $response, $args)
  {
    header('Content-Type: text/plain;charset=UTF-8');
    $stream = fopen('php://output', 'w');

    $lista = Producto::obtenerTodos();

    fputcsv($stream, array("id_producto", "nombre", "fecha_creacion", "sector", "ultimo_pedido", "cantidad_pedido", "estado", "stock", "fecha_creacion"));

    foreach($lista as $producto){
      fputcsv($stream, array($producto->id_producto, $producto->nombre, $producto->fecha_creacion, $producto->sector, $producto->ultimo_pedido, $producto->cantidad_pedido, $producto->estado, $producto->stock));
    }
    // more complex codes would be here. It may cause error.

    header("Content-Type: application/text");
    header("Content-Disposition: attachment; filename=output.csv");

    $response->getBody()->write(mb_convert_encoding(ob_get_clean(), 'SJIS', 'UTF-8'));
    return $response
      ->withHeader('Content-Type', 'application/json');
  }

  public function CargarCsv($request, $response, $args){
    $csv = $request->getAttribute('csv');
    $mensaje = "El fichero no es válido";

    // Creamos la entrada
    if(Producto::GuardarProductoEnCsv($csv, $mensaje)){
      $payload = json_encode(array("mensaje" => "Cargado csv con productos correctamente"));
    } else{
      $payload = json_encode(array("mensaje" => "Error!" . $mensaje));
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
?>