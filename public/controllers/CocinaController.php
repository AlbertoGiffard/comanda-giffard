<?php
require_once './models/Pedido.php';

class CocinaController extends Pedido
{
    public function TraerTodosCocina($request, $response, $args)
    {
        $lista = Pedido::ObtenerTodosCocina();
        //me lo trae como un json
        $payload = $lista;

        $response->getBody()->write(json_encode($payload));
        return $response
            ->withHeader('Content-Type', 'application/json');
    }

    public function TraerPendientesCocina($request, $response, $args)
    {
        $lista = Pedido::ObtenerPendientesCocina();
        //me lo trae como un json
        $payload = $lista;

        $response->getBody()->write(json_encode($payload));
        return $response
            ->withHeader('Content-Type', 'application/json');
    }
}
