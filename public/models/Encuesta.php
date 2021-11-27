<?php

class Encuesta
{
    public $id_encuesta;
    public $codigo_pedido;
    public $codigo_mesa;
    public $mesa;
    public $mozo;
    public $restaurante;
    public $preparador;
    public $experiencia;
    public $fecha;

    public function __construct()
    {
    }

    public function SetearValores($id_encuesta, $codigo_pedido, $codigo_mesa, $mesa, $mozo, $restaurante, $preparador, $experiencia, $fecha)
    {
        $this->id_encuesta = $id_encuesta;
        $this->codigo_pedido = $codigo_pedido;
        $this->codigo_mesa = $codigo_mesa;
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

        $resultado = $objAccesoDatos->obtenerUltimoId();

        return $resultado;
    }

    public function ActualizarEncuesta()
    {
        try {
            //setea la fecha actual como fecha de creacion
            $this->fecha = date('Y-m-d H:i:s');

            $objAccesoDatos = AccesoDatos::obtenerInstancia();
            $consulta = $objAccesoDatos->prepararConsulta("UPDATE encuestas SET mesa = :mesa, mozo = :mozo, restaurante = :restaurante, preparador = :preparador, experiencia = :experiencia, fecha = :fecha WHERE codigo_pedido = :codigo_pedido AND codigo_mesa = :codigo_mesa");

            $consulta->bindValue(':mesa', $this->mesa, PDO::PARAM_INT);
            $consulta->bindValue(':mozo', $this->mozo, PDO::PARAM_INT);
            $consulta->bindValue(':restaurante', $this->restaurante, PDO::PARAM_INT);
            $consulta->bindValue(':preparador', $this->preparador, PDO::PARAM_INT);
            $consulta->bindValue(':experiencia', $this->experiencia, PDO::PARAM_STR);
            $consulta->bindValue(':fecha', $this->fecha, PDO::PARAM_STR);
            $consulta->bindValue(':codigo_pedido', $this->codigo_pedido, PDO::PARAM_STR);
            $consulta->bindValue(':codigo_mesa', $this->codigo_mesa, PDO::PARAM_STR);
            $consulta->execute();

            $resultado = true;
        } catch (\Throwable $th) {
            $resultado = false;
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

    public static function GenerarEncuestaConPedido($pedido)
    {
        $resultado = array("mensaje" => "pedido y mesa cerrada pero no se pudo generar la encuesta");
        $pedidoDB = Pedido::ObtenerPedidoSoloCodigo($pedido->codigo_pedido);

        if ($pedidoDB != false) {
            if ($pedidoDB->id_responsable != null) {
                $encuesta = new Encuesta;
                $encuesta->SetearValores(null, $pedidoDB->codigo_pedido, $pedidoDB->codigo_mesa, 0, 0, 0, 0, null, date('Y-m-d H:i:s'));
                $idEncuesta = $encuesta->crearEncuesta();

                if ($idEncuesta != false) {
                    $resultado = array("mensaje" => "pedido y mesa cerrada y encuesta generada con exito!", "id encuesta" => $idEncuesta, "Codigo pedido" => $pedidoDB->codigo_pedido, "codigo mesa" => $pedidoDB->codigo_mesa);
                }
            } else {
                $payload = array("mensaje" => "Error! primero debe ser tomado por algun responsable");
            }
        }

        return $resultado;
    }

    public static function Validaciones(&$mesa, &$mozo, &$restaurante, &$preparador, &$experiencia)
    {
        $resultado = false;

        if (ctype_digit($mesa) && ctype_digit($mozo) && ctype_digit($restaurante) && ctype_digit($preparador)) {
            $intMesa = intval($mesa);
            $intMozo = intval($mozo);
            $intRestaurante = intval($restaurante);
            $intPreparador = intval($preparador);

            if ($intMesa > 0 && $intMesa <= 10) {
                if ($intMozo > 0 && $intMozo <= 10) {
                    if ($intRestaurante > 0 && $intRestaurante <= 10) {
                        if ($intPreparador > 0 && $intPreparador <= 10) {
                            if (strlen($experiencia) <= 66) {
                                $mesa = $intMesa;
                                $mozo = $intMozo;
                                $restaurante = $intRestaurante;
                                $preparador = $intPreparador;
                                $resultado = true;
                            }
                        }
                    }
                }
            }
        }

        return $resultado;
    }

    public static function ObtenerMejoresComentarios()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT * FROM encuestas ORDER BY restaurante DESC limit 3");
        $consulta->execute();

        return $consulta->fetchObject('Encuesta');
    }

    public static function ObtenerPeoresComentarios()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT * FROM encuestas ORDER BY restaurante ASC limit 3");
        $consulta->execute();

        return $consulta->fetchObject('Encuesta');
    }
}
