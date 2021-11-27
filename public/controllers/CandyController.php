<?php
require_once './models/Pedido.php';

class CandyController extends Pedido
{
    public function TraerTodosCandy($request, $response, $args)
    {
        $lista = Pedido::ObtenerTodosCandy();
        //me lo trae como un json
        $payload = $lista;

        $response->getBody()->write(json_encode($payload));
        return $response
            ->withHeader('Content-Type', 'application/json');
    }

    public function TraerPendientesCandy($request, $response, $args)
    {
        $lista = Pedido::ObtenerPendientesCandy();
        //me lo trae como un json
        $payload = $lista;

        $response->getBody()->write(json_encode($payload));
        return $response
            ->withHeader('Content-Type', 'application/json');
    }
}
