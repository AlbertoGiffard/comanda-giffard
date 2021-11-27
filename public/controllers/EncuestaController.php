<?php
require_once './models/Encuesta.php';

class EncuestaController extends Encuesta
{
  public function TraerTodosEncuesta($request, $response, $args)
  {
    $lista = Encuesta::ObtenerTodos();
    //me lo trae como un json
    $payload = $lista;

    $response->getBody()->write(json_encode($payload));
    return $response
      ->withHeader('Content-Type', 'application/json');
  }

  public function MandarEncuesta($request, $response, $args)
  {
    $encuesta = $request->getAttribute('encuesta');
    $payload = array("mensaje" => "Error! no se pudo cargar la encuesta, intente mas tarde");

    // Creamos la encuesta
    $id = $encuesta->ActualizarEncuesta();

    if ($id != false) {
      $payload = array("mensaje" => "Encuesta cargada gracias por sus comentarios");
    }

    $response->getBody()->write(json_encode($payload));
    return $response
      ->withHeader('Content-Type', 'application/json');
  }

  public function TraerMejores($request, $response, $args)
  {
    $lista = Encuesta::ObtenerMejoresComentarios();
    //me lo trae como un json
    $payload = $lista;

    $response->getBody()->write(json_encode($payload));
    return $response
      ->withHeader('Content-Type', 'application/json');
  }

  public function TraerPeores($request, $response, $args)
  {
    $lista = Encuesta::ObtenerPeoresComentarios();
    //me lo trae como un json
    $payload = $lista;

    $response->getBody()->write(json_encode($payload));
    return $response
      ->withHeader('Content-Type', 'application/json');
  }
}
