<?php
require_once './models/Pedido.php';

class TragoController extends Pedido
{
    public function TraerTodosTrago($request, $response, $args)
    {
        $lista = Pedido::ObtenerTodosTrago();
        //me lo trae como un json
        $payload = $lista;

        $response->getBody()->write(json_encode($payload));
        return $response
            ->withHeader('Content-Type', 'application/json');
    }

    public function TraerPendientesTrago($request, $response, $args)
    {
        $lista = Pedido::ObtenerPendientesTrago();
        //me lo trae como un json
        $payload = $lista;

        $response->getBody()->write(json_encode($payload));
        return $response
            ->withHeader('Content-Type', 'application/json');
    }
}
