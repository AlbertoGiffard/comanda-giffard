<?php
require_once './models/Pedido.php';

class CandyController extends Pedido
{
    public function TraerTodosCandy($request, $response, $args)
    {
        $lista = Pedido::ObtenerTodosCandy();
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

    public function TraerPendientesCandy($request, $response, $args)
    {
        $lista = Pedido::ObtenerPendientesCandy();
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
}
