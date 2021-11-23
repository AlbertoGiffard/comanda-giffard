<?php

class Encuesta{
    public $id_encuesta;
    public $id_pedido;
    public $id_mesa;
    public $mesa;
    public $mozo;
    public $restaurante;
    public $preparador;
    public $experiencia;
    public $fecha;

    public function __construct()
    {
    }

    public function SetearValores($id_encuesta, $id_pedido, $id_mesa, $mesa, $mozo, $restaurante, $preparador, $experiencia, $fecha){
        $this->id_encuesta = $id_encuesta;
        $this->id_pedido = $id_pedido;
        $this->id_mesa = $id_mesa;
        $this->mesa = $mesa;
        $this->mozo = $mozo;
        $this->restaurante = $restaurante;
        $this->preparador = $preparador;
        $this->experiencia = $experiencia;
        $this->fecha = $fecha;
    }

    public function crearEncuesta()
    {
        $resultado = false;

        //setea la fecha actual como fecha de creacion
        $this->fecha = date('Y-m-d H:i:s');

        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("INSERT INTO encuestas (codigo_pedido, codigo_mesa, mesa, mozo, restaurante, preparador, experiencia, fecha) VALUES (:codigo_pedido, :codigo_mesa, :mesa, :mozo, :restaurante, :preparador, :experiencia, :fecha)");

        $consulta->bindValue(':codigo_pedido', $this->codigo_pedido, PDO::PARAM_STR);
        $consulta->bindValue(':codigo_mesa', $this->codigo_mesa, PDO::PARAM_STR);
        $consulta->bindValue(':mesa', $this->mesa, PDO::PARAM_INT);
        $consulta->bindValue(':mozo', $this->mozo, PDO::PARAM_INT);
        $consulta->bindValue(':restaurante', $this->restaurante, PDO::PARAM_INT);
        $consulta->bindValue(':preparador', $this->preparador, PDO::PARAM_INT);
        $consulta->bindValue(':experiencia', $this->experiencia, PDO::PARAM_STR);
        $consulta->bindValue(':fecha', $this->fecha, PDO::PARAM_STR);
        $consulta->execute();

        if ($consulta->rowCount() > 0) {
            $resultado = true;
        }

        return $resultado;
    }

    public static function obtenerTodos()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT * FROM encuestas");
        $consulta->execute();

        return $consulta->fetchAll(PDO::FETCH_CLASS, 'Encuesta');
    }
    public static function obtenerPedido($codigo_pedido, $codigo_mesa)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT * FROM encuestas WHERE codigo_mesa = :codigo_mesa AND codigo_pedido = :codigo_pedido");
        $consulta->bindValue(':codigo_mesa', $codigo_mesa, PDO::PARAM_STR);
        $consulta->bindValue(':codigo_pedido', $codigo_pedido, PDO::PARAM_STR);
        $consulta->execute();

        return $consulta->fetchObject('Encuesta');
    }

}

?>