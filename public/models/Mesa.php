<?php

class Mesa
{
    public $codigo_mesa;
    public $fecha_creacion;
    public $estado;
    public $total_facturado;
    public $fecha_actualizacion;
    public $uso;

    public function __construct()
    {
    }

    public function SetearValores($codigo_mesa, $fecha_creacion, $estado, $total_facturado, $fecha_actualizacion, $uso)
    {
        $this->codigo_mesa = $codigo_mesa;
        $this->fecha_creacion = $fecha_creacion;
        $this->estado = $estado;
        $this->total_facturado = $total_facturado;
        $this->fecha_actualizacion = $fecha_actualizacion;
        $this->uso = $uso;
    }
    public function crearMesa(&$mensaje)
    {
        $resultado = false;

        //genera un codigo random de 5 caracteres
        $this->codigo_mesa = substr(str_shuffle('0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ'), 0, 5);
        //setea la fecha actual como fecha de creacion
        $this->fecha_creacion = date('Y-m-d');
        $this->fecha_actualizacion = date('Y-m-d');
        $this->total_facturado = 0;

        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("INSERT INTO mesas (codigo_mesa, fecha_creacion, estado, total_facturado, fecha_actualizacion) VALUES (:codigo_mesa, :fecha_creacion, :estado, :total_facturado, :fecha_actualizacion)");

        $consulta->bindValue(':codigo_mesa', $this->codigo_mesa, PDO::PARAM_STR);
        $consulta->bindValue(':fecha_creacion', $this->fecha_creacion, PDO::PARAM_STR);
        $consulta->bindValue(':estado', $this->estado, PDO::PARAM_STR);
        $consulta->bindValue(':total_facturado', $this->total_facturado, PDO::PARAM_INT);
        $consulta->bindValue(':fecha_actualizacion', $this->fecha_actualizacion, PDO::PARAM_STR);

        $consulta->execute();

        if ($consulta->rowCount() > 0) {
            $mensaje = array("mensaje" => "mesa creada con exito", "codigo mesa" => $this->codigo_mesa);
            $resultado = true;
        }

        return $resultado;
    }

    public static function obtenerTodos()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT * FROM mesas");
        $consulta->execute();

        return $consulta->fetchAll(PDO::FETCH_CLASS, 'Mesa');
    }

    public static function obtenerMesa($codigo_mesa)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT * FROM mesas WHERE codigo_mesa = :codigo_mesa");
        $consulta->bindValue(':codigo_mesa', $codigo_mesa, PDO::PARAM_STR);
        $consulta->execute();

        return $consulta->fetchObject('Mesa');
    }

    public static function MasUsada()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT * FROM mesas ORDER BY uso DESC limit 1");
        $consulta->execute();

        return $consulta->fetchObject('Mesa');
    }

    public static function MenosUsada()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT * FROM mesas ORDER BY uso ASC limit 1");
        $consulta->execute();

        return $consulta->fetchObject('Mesa');
    }

    public static function MayorImporte()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT * FROM mesas ORDER BY total_facturado DESC limit 1");
        $consulta->execute();

        return $consulta->fetchObject('Mesa');
    }

    public static function MenorImporte()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT * FROM mesas ORDER BY total_facturado ASC limit 1");
        $consulta->execute();

        return $consulta->fetchObject('Mesa');
    }

    public function modificarMesa()
    {
        try {
            $this->fecha_actualizacion = date('Y-m-d');

            $objAccesoDato = AccesoDatos::obtenerInstancia();
            //realizar validacion
            $consulta = $objAccesoDato->prepararConsulta("UPDATE mesas SET estado = :nuevoEstado, total_facturado = :nuevoTotalFacturado, fecha_actualizacion = :fecha_actualizacion, uso = :nuevoUso WHERE codigo_mesa = :codigo_mesa");
            $consulta->bindValue(':nuevoEstado', $this->estado, PDO::PARAM_STR);
            $consulta->bindValue(':nuevoTotalFacturado', $this->total_facturado, PDO::PARAM_INT);
            $consulta->bindValue(':fecha_actualizacion', $this->fecha_actualizacion, PDO::PARAM_STR);
            $consulta->bindValue(':nuevoUso', $this->uso, PDO::PARAM_STR);
            $consulta->bindValue(':codigo_mesa', $this->codigo_mesa, PDO::PARAM_STR);
            $consulta->execute();
            $resultado = true;
        } catch (\Throwable $th) {
            $resultado = false;
        }

        return $resultado;
    }

    public static function ComprobarEstado($pedido, &$payload)
    {
        $mesaActualizar = Mesa::obtenerMesa($pedido->codigo_mesa);
        $resultado = false;

        if ($mesaActualizar != false) {
            if ($pedido->estado == "servido") {
                $mesaActualizar->estado = "con cliente comiendo";
            }

            if ($pedido->estado == "cobrado") {
                $mesaActualizar->estado = "con cliente pagando";
                $mesaActualizar->total_facturado += $pedido->importe;
            }

            if ($pedido->estado == "cerrado") {
                $mesaActualizar->estado = "cerrada";
            }

            if ($mesaActualizar->modificarMesa()) {
                $resultado = true;
            } else {
                $resultado = false;
                $payload = array("mensaje" => "no se logro actualizar el estado de la mesa, intente mas tarde");
            }
        } else {
            $resultado = false;
            $payload = array("mensaje" => "no se logro encontrar la mesa");
        }

        return $resultado;
    }

    public static function ActualizarMesa($codigo_mesa, $estado, $venta)
    {
        $resultado = false;
        $mesa = Mesa::obtenerMesa($codigo_mesa);

        if ($mesa != false) {
            $mesa->estado = $estado;
            $mesa->total_facturado += $venta;
            $mesa->uso++;

            if ($mesa->modificarMesa()) {
                $resultado = true;
            }
        }

        return $resultado;
    }

    public function borrarMesa()
    {
        $resultado = false;

        $objAccesoDato = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDato->prepararConsulta("DELETE FROM mesas WHERE codigo_mesa = :codigo_mesa");
        $consulta->bindValue(':codigo_mesa', $this->codigo_mesa, PDO::PARAM_STR);
        $consulta->execute();

        if ($consulta->rowCount() > 0) {
            $resultado = true;
        }

        return $resultado;
    }

    public static function ValidarEstado($valor)
    {
        $match = false;
        $estados = array('con cliente esperando pedido', 'con cliente comiendo', 'con cliente pagando', 'cerrada');
        $resultado = "Error!<ul>El movimiento debe ser alguno de estos:<li>con cliente esperando pedido</li><li>con cliente comiendo</li><li>con cliente pagando</li><li>cerrada</li></ul>";

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

    public static function VerificarEstadoMesa($codigo_mesa)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT estado FROM mesas WHERE codigo_mesa = :codigo_mesa");
        $consulta->bindValue(':codigo_mesa', $codigo_mesa, PDO::PARAM_STR);
        $consulta->execute();
        return $consulta->fetchColumn();
    }

    public static function GuardarMesaEnCsv($csv, &$mensaje)
    {
        $resultado = false;

        if (Mesa::ValidarCsv($csv, $mensaje)) {
            $archivotmp = $csv['tmp_name'];

            //cargamos el archivo
            $filas = file($archivotmp);

            //inicializamos variable a 0, esto nos ayudará a indicarle que no lea la primera línea
            $i = 0;

            //Recorremos el bucle para leer línea por línea
            foreach ($filas as $mesa) {
                //abrimos bucle
                /*si es diferente a 0 significa que no se encuentra en la primera línea 
   (con los títulos de las columnas) y por lo tanto puede leerla*/
                if ($i != 0) {
                    //abrimos condición, solo entrará en la condición a partir de la segunda pasada del bucle.
                    /* La funcion explode nos ayuda a delimitar los campos, por lo tanto irá 
       leyendo hasta que encuentre un ; */
                    $datos = explode(",", $mesa);
                    $mesa = new Mesa();
                    //usamos la función utf8_encode para leer correctamente los caracteres especiales
                    $mesa->SetearValores(null, null, utf8_encode($datos[0]), null, null);

                    if ($mesa->crearMesa() == false) {
                        $resultado = false;
                        $mensaje = "Problemas al intentar cargar la fila: " . ++$i;
                        break;
                    }
                }
                /*Cuando pase la primera pasada se incrementará nuestro valor y a la siguiente pasada ya 
   entraremos en la condición, de esta manera conseguimos que no lea la primera línea.*/
                $i++;
                $resultado = true;
            }
        }

        return $resultado;
    }

    //Para csv
    public static function ValidarCsv($csv, &$mensaje)
    {
        $result = false;
        $extension = pathinfo($csv['name'], PATHINFO_EXTENSION);

        //verifico el tamaño maximo
        if ($csv['size'] < 500000) {
            //verifico que sea un csv
            if ($extension == "csv") {
                $result = true;
            } else {
                $mensaje = "Solo son permitidos archivos CSV.";
            }
        } else {
            $mensaje = "El CSV es muy pesado intente con uno mas liviano";
        }

        return $result;
    }
}
