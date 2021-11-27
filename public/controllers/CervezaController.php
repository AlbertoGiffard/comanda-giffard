<?php
require_once './models/Pedido.php';

class CervezaController extends Pedido
{
    public function TraerTodosCerveza($request, $response, $args)
    {
        $lista = Pedido::ObtenerTodosCerveza();
        //me lo trae como un json
        $payload = $lista;

        $response->getBody()->write(json_encode($payload));
        return $response
            ->withHeader('Content-Type', 'application/json');
    }

    public function TraerPendientesCerveza($request, $response, $args)
    {
        $lista = Pedido::ObtenerPendientesCerveza();
        //me lo trae como un json
        $payload = $lista;

        $response->getBody()->write(json_encode($payload));
        return $response
            ->withHeader('Content-Type', 'application/json');
    }
}
