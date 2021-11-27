<?php
require_once './models/Mesa.php';
require_once './interfaces/IApiUsable.php';

class MesaController extends Mesa implements IApiUsable
{
  public function CargarUno($request, $response, $args)
  {
    $mesa = $request->getAttribute('mesa');
    $payload = array("mensaje" => "Error! no se pudo cargar la mesa, intente mas tarde");

    // Creamos la mesa
    $mesa->crearMesa($payload);

    $response->getBody()->write(json_encode($payload));
    return $response
      ->withHeader('Content-Type', 'application/json');
  }

  public function TraerTodos($request, $response, $args)
  {
    $lista = Mesa::obtenerTodos();
    //me lo trae como un json
    $payload = $lista;

    $response->getBody()->write(json_encode($payload));
    return $response
      ->withHeader('Content-Type', 'application/json');
  }

  public function MayorUso($request, $response, $args)
  {
    $lista = Mesa::MasUsada();
    //me lo trae como un json
    $payload = $lista;

    $response->getBody()->write(json_encode($payload));
    return $response
      ->withHeader('Content-Type', 'application/json');
  }

  public function MenorUso($request, $response, $args)
  {
    $lista = Mesa::MenosUsada();
    //me lo trae como un json
    $payload = $lista;

    $response->getBody()->write(json_encode($payload));
    return $response
      ->withHeader('Content-Type', 'application/json');
  }

  public function MayorFacturacion($request, $response, $args)
  {
    $lista = Mesa::MayorImporte();
    //me lo trae como un json
    $payload = $lista;

    $response->getBody()->write(json_encode($payload));
    return $response
      ->withHeader('Content-Type', 'application/json');
  }

  public function MenorFacturacion($request, $response, $args)
  {
    $lista = Mesa::MenorImporte();
    //me lo trae como un json
    $payload = $lista;

    $response->getBody()->write(json_encode($payload));
    return $response
      ->withHeader('Content-Type', 'application/json');
  }
  
  public function TraerUno($request, $response, $args)
  {
    // Buscamos usuario por id
    //$codigo_mesa = $request->getAttribute('codigo_mesa');
    $codigo_mesa = $args['codigo_mesa'];

    $mesa = Mesa::obtenerMesa($codigo_mesa);
    //valida que el usuario exista
    if ($mesa == false) {
      $payload = json_encode(array("mensaje" => "Error! no se encontró ninguna mesa por ese codigo"));
    } else{
      $payload = json_encode($mesa);
    }

    $response->getBody()->write($payload);
    return $response
      ->withHeader('Content-Type', 'application/json');
  }
  public function CargarCsv($request, $response, $args){
    $csv = $request->getAttribute('csv');
    $mensaje = "El fichero no es válido";

    // Creamos la entrada
    if(Mesa::GuardarMesaEnCsv($csv, $mensaje)){
      $payload = json_encode(array("mensaje" => "Cargado csv con usuarios correctamente"));
    } else{
      $payload = json_encode(array("mensaje" => "Error!" . $mensaje));
    }

    $response->getBody()->write($payload);
    return $response
      ->withHeader('Content-Type', 'application/json');
  }

  public function TraerTodosCsv($request, $response, $args)
  {
    header('Content-Type: text/plain;charset=UTF-8');
    $stream = fopen('php://output', 'w');

    $lista = Mesa::obtenerTodos();

    fputcsv($stream, array("codigo_mesa", "fecha_creacion", "estado", "total_facturado", "fecha_actualizacion"));

    foreach($lista as $mesa){
      fputcsv($stream, array($mesa->codigo_mesa, $mesa->fecha_creacion, $mesa->estado, $mesa->total_facturado, $mesa->fecha_actualizacion));
    }
    // more complex codes would be here. It may cause error.

    header("Content-Type: application/text");
    header("Content-Disposition: attachment; filename=output.csv");

    $response->getBody()->write(mb_convert_encoding(ob_get_clean(), 'SJIS', 'UTF-8'));
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