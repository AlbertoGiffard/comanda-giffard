<?php

class Movimiento
{
    public $id_usuario;
    public $fecha;
    public $tipo;

    public function __construct()
    {
    }

    public function SetearValores($id_usuario, $fecha, $tipo)
    {
        $this->id_usuario = $id_usuario;
        $this->fecha = $fecha;
        $this->tipo = $tipo;
    }
    public function crearMovimiento()
    {
        $resultado = false;

        //setea la fecha actual como fecha de creacion
        $this->fecha = date('Y-m-d H:i:s');

        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("INSERT INTO movimientos (id_usuario, fecha, tipo) VALUES (:id_usuario, :fecha, :tipo)");

        $consulta->bindValue(':id_usuario', $this->id_usuario, PDO::PARAM_INT);
        $consulta->bindValue(':fecha', $this->fecha, PDO::PARAM_STR);
        $consulta->bindValue(':tipo', $this->tipo, PDO::PARAM_STR);
        $consulta->execute();

        //guardo el ultimo movimiento en la base del usuario
        if ($consulta->rowCount() > 0) {
            $usuario = Usuario::obtenerUsuario($this->id_usuario);
            $usuario->ultimo_movimiento = date('Y-m-d');
            if($usuario->modificarUsuario()){
                $resultado = true;
            }
        }

        return $resultado;
    }

    public static function obtenerTodos()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT * FROM movimientos");
        $consulta->execute();

        return $consulta->fetchAll(PDO::FETCH_CLASS, 'Movimiento');
    }
    public static function obtenerMovimientoEntreFechas($desde, $hasta)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT * FROM movimientos WHERE fecha BETWEEN ':desde' AND ':hasta'");
        $consulta->bindValue(':desde', $desde, PDO::PARAM_STR);
        $consulta->bindValue(':hasta', $hasta, PDO::PARAM_STR);
        $consulta->execute();

        return $consulta->fetchAll(PDO::FETCH_CLASS, 'Movimiento');
    }

    public static function obtenerMovimientoEntreFechasPorUsuario($id_usuario, $desde, $hasta)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT * FROM movimientos WHERE fecha BETWEEN ':desde' AND ':hasta' AND id_usuario = :id_usuario");
        $consulta->bindValue(':desde', $desde, PDO::PARAM_STR);
        $consulta->bindValue(':hasta', $hasta, PDO::PARAM_STR);
        $consulta->bindValue(':id_usuario', $id_usuario, PDO::PARAM_STR);
        $consulta->execute();

        return $consulta->fetchAll(PDO::FETCH_CLASS, 'Movimiento');
    }

    public static function ValidarMovimiento($valor)
    {
        $match = false;
        $estados = array('entrada', 'salida');
        $resultado = "Error!<ul>El movimiento debe ser alguno de estos:<li>entrada</li><li>salida</ul>";

        foreach ($estados as $estado) {
            if ($valor == $estado) {
                $match = true;
                break;
            }
        }

        if ($match) {
            $resultado = "validado";
        }

        return $resultado;
    }
}
