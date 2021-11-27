<?php

class Pedido
{
    public $codigo_pedido;
    public $codigo_mesa;
    public $nombre_cliente;
    public $producto;
    public $cantidad;
    public $importe;
    public $sector;
    public $id_responsable;
    public $fecha_creacion;
    public $foto_mesa;
    public $demora;
    public $retrasado;
    public $fecha_entrega;
    public $estado;

    public function __construct()
    {
    }

    public function SetearValores($codigo_pedido, $codigo_mesa, $nombre_cliente, $producto, $cantidad, $importe, $sector, $id_responsable, $fecha_creacion, $foto_mesa, $demora, $retrasado, $fecha_entrega, $estado)
    {
        $this->codigo_pedido = $codigo_pedido;
        $this->codigo_mesa = $codigo_mesa;
        $this->nombre_cliente = $nombre_cliente;
        $this->producto = $producto;
        $this->cantidad = $cantidad;
        $this->importe = $importe;
        $this->sector = $sector;
        $this->id_responsable = $id_responsable;
        $this->fecha_creacion = $fecha_creacion;
        $this->foto_mesa = $foto_mesa;
        $this->demora = $demora;
        $this->retrasado = $retrasado;
        $this->fecha_entrega = $fecha_entrega;
        $this->estado = $estado;
    }
    public function crearPedido(&$mensaje)
    {
        $resultado = false;
        $mensaje;

        if (Mesa::ActualizarMesa($this->codigo_mesa, "con cliente esperando pedido", 0)) {
            //genera un codigo random de 5 caracteres
            $this->codigo_pedido = substr(str_shuffle('0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ'), 0, 5);

            if (Pedido::GuardarFoto($this->codigo_pedido, $this->foto_mesa, $mensaje)) {
                //setea la fecha actual como fecha de creacion
                $this->fecha_creacion = date('Y-m-d H:i:s');
                $this->fecha_entrega = date("Y-m-d H:i:s", strtotime($this->fecha_creacion . "+" . $this->demora . " minutes"));
                $this->estado = "pendiente";
                $this->retrasado = false;

                $objAccesoDatos = AccesoDatos::obtenerInstancia();
                $consulta = $objAccesoDatos->prepararConsulta("INSERT INTO pedidos (codigo_pedido, codigo_mesa, nombre_cliente, producto, foto_mesa, importe, sector, id_responsable, demora, fecha_creacion, cantidad, retrasado, fecha_entrega, estado) VALUES (:codigo_pedido, :codigo_mesa, :nombre_cliente, :producto, :foto_mesa, :importe, :sector, :id_responsable, :demora, :fecha_creacion, :cantidad, :retrasado, :fecha_entrega, :estado)");

                $consulta->bindValue(':codigo_pedido', $this->codigo_pedido, PDO::PARAM_STR);
                $consulta->bindValue(':codigo_mesa', $this->codigo_mesa, PDO::PARAM_STR);
                $consulta->bindValue(':nombre_cliente', $this->nombre_cliente, PDO::PARAM_STR);
                $consulta->bindValue(':producto', $this->producto, PDO::PARAM_STR);
                $consulta->bindValue(':foto_mesa', $this->foto_mesa, PDO::PARAM_STR);
                $consulta->bindValue(':importe', $this->importe, PDO::PARAM_INT);
                $consulta->bindValue(':sector', $this->sector, PDO::PARAM_STR);
                $consulta->bindValue(':id_responsable', $this->id_responsable, PDO::PARAM_INT);
                $consulta->bindValue(':demora', $this->demora, PDO::PARAM_INT);
                $consulta->bindValue(':fecha_creacion', $this->fecha_creacion, PDO::PARAM_STR);
                $consulta->bindValue(':cantidad', $this->cantidad, PDO::PARAM_INT);
                $consulta->bindValue(':retrasado', $this->retrasado, PDO::PARAM_BOOL);
                $consulta->bindValue(':fecha_entrega', $this->fecha_entrega, PDO::PARAM_STR);
                $consulta->bindValue(':estado', $this->estado, PDO::PARAM_STR);
                $consulta->execute();

                if ($consulta->rowCount() > 0) {
                    $mensaje = array("Codigo Pedido" => $this->codigo_pedido, "mensaje" => "Pedido creado con exito");
                    $resultado = true;
                }
            } else {
                $mensaje = array("mensaje" => "Error! problemas con cargar la imagen, intente mas tarde");
            }
        } else {
            $mensaje = array("mensaje" => "Error! problemas con cargar la mesa, intente mas tarde");
        }



        return $resultado;
    }

    public static function obtenerTodos()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT * FROM pedidos");
        $consulta->execute();

        return $consulta->fetchAll(PDO::FETCH_CLASS, 'Pedido');
    }

    public static function TraerFueraTiempo()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT * FROM pedidos WHERE retrasado = true");
        $consulta->execute();

        return $consulta->fetchAll(PDO::FETCH_CLASS, 'Pedido');
    }

    public static function TraerCancelados()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT * FROM pedidos WHERE estado = cancelado");
        $consulta->execute();

        return $consulta->fetchAll(PDO::FETCH_CLASS, 'Pedido');
    }

    public static function obtenerPedidosAsignados($id_responsable)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT * FROM pedidos WHERE id_responsable = :id_responsable");
        $consulta->bindValue(':id_responsable', $id_responsable, PDO::PARAM_INT);
        $consulta->execute();

        return $consulta->fetchAll(PDO::FETCH_CLASS, 'Pedido');
    }

    public static function obtenerPedidosPendientesAsignados($id_responsable)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT * FROM pedidos WHERE id_responsable = :id_responsable AND estado = 'pendiente'");
        $consulta->bindValue(':id_responsable', $id_responsable, PDO::PARAM_INT);
        $consulta->execute();

        return $consulta->fetchAll(PDO::FETCH_CLASS, 'Pedido');
    }

    public static function ObtenerPedidosEstado($estado)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT * FROM pedidos WHERE estado = :estado");
        $consulta->bindValue(':estado', $estado, PDO::PARAM_STR);
        $consulta->execute();

        return $consulta->fetchAll(PDO::FETCH_CLASS, 'Pedido');
    }

    public static function ObtenerPedido($codigo_pedido, $codigo_mesa)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT * FROM pedidos WHERE codigo_mesa = :codigo_mesa AND codigo_pedido = :codigo_pedido");
        $consulta->bindValue(':codigo_mesa', $codigo_mesa, PDO::PARAM_STR);
        $consulta->bindValue(':codigo_pedido', $codigo_pedido, PDO::PARAM_STR);
        $consulta->execute();

        return $consulta->fetchObject('Pedido');
    }

    public static function ObtenerPedidoSoloCodigo($codigo_pedido)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT * FROM pedidos WHERE codigo_pedido = :codigo_pedido limit 1");
        $consulta->bindValue(':codigo_pedido', $codigo_pedido, PDO::PARAM_STR);
        $consulta->execute();

        return $consulta->fetchObject('Pedido');
    }

    public function ModificarPedido()
    {
        try {
            $objAccesoDato = AccesoDatos::obtenerInstancia();
            //realizar validacion
            $consulta = $objAccesoDato->prepararConsulta("UPDATE pedidos SET foto_mesa = :nuevaFoto, importe = :nuevoImporte, demora = :nuevaDemora, retrasado = :nuevoRetraso, fecha_entrega = :nuevaFechaEntrega, estado = :nuevoEstado, id_responsable = :nuevoIdResponsable WHERE codigo_pedido = :codigo_pedido");
            $consulta->bindValue(':nuevaFoto', $this->foto_mesa, PDO::PARAM_STR);
            $consulta->bindValue(':nuevoImporte', $this->importe, PDO::PARAM_INT);
            $consulta->bindValue(':nuevaDemora', $this->demora, PDO::PARAM_INT);
            $consulta->bindValue(':nuevoRetraso', $this->retrasado, PDO::PARAM_BOOL);
            $consulta->bindValue(':nuevaFechaEntrega', $this->fecha_entrega, PDO::PARAM_STR);
            $consulta->bindValue(':nuevoEstado', $this->estado, PDO::PARAM_STR);
            $consulta->bindValue(':nuevoIdResponsable', $this->id_responsable, PDO::PARAM_INT);
            $consulta->bindValue(':codigo_pedido', $this->codigo_pedido, PDO::PARAM_STR);
            $consulta->execute();

            $resultado = true;
        } catch (\Throwable $th) {
            $resultado = false;
        }

        return $resultado;
    }

    public function BorrarPedido()
    {
        $resultado = false;

        $objAccesoDato = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDato->prepararConsulta("UPDATE pedidos SET estado = 'cancelado' WHERE codigo_pedido = :codigo_pedido");
        $consulta->bindValue(':codigo_pedido', $this->codigo_pedido, PDO::PARAM_STR);
        $consulta->execute();

        if ($consulta->rowCount() > 0) {
            $resultado = true;
        }

        return $resultado;
    }

    //TODAS LAS VALIDACIONES QUE SE DEBEN CUMPLIR PARA GENERAR UN PEDIDO
    /* 
       Mesas:
       - Existencia(id) y estado
       Productos:
       - Existencia(nombre), indica el sector, estado, stock
       (veremos)
       Usuarios:
       - existencia(id), estado
    */
    public static function Validaciones($codigo_mesa, $nombreProducto, $cantidad, &$pedido)
    {
        $resultado = json_encode(array('Failed' => "producto inexistente, verifique e intente nuevamente"));

        $respuestaProducto = Producto::VerificarExistencia($nombreProducto, $cantidad, $pedido);

        if ($respuestaProducto == "ok") {
            $resultado = "validado";
        } else {
            $resultado = $respuestaProducto;
        }

        return $resultado;
    }

    public static function ActualizarFotoPedido($codigo_pedido, $foto_mesa, &$mensaje)
    {
        $resultado = false;

        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT * FROM pedidos WHERE codigo_pedido = :codigo_pedido");
        $consulta->bindValue(':codigo_pedido', $codigo_pedido, PDO::PARAM_STR);
        $consulta->execute();

        $pedido = $consulta->fetchObject('Pedido');

        if ($pedido != false) {
            if (Pedido::GuardarFoto($codigo_pedido, $foto_mesa, $mensaje)) {
                //ahora foto mesa es igual al path
                $pedido->foto_mesa = $foto_mesa;
                $resultado = true;
                /* //si lo logra modificar entonces todo va ok
                if ($pedido->ModificarPedido()) {
                } */
            }
        }

        return $resultado;
    }

    //Para Imagenes
    public static function GuardarFoto($codigo_pedido, &$foto_mesa, &$mensaje)
    {
        $parentPath = '../imagenesDePedidos/';
        $uploadPath = $parentPath . basename($foto_mesa['name']);
        $mensaje = "El fichero no es válido";
        $result = false;
        $extension = pathinfo($uploadPath, PATHINFO_EXTENSION);
        $nuevoNombre = $parentPath . $codigo_pedido . "." . $extension;

        //verifico que la ruta exista
        if (!file_exists($parentPath)) {
            mkdir($parentPath, 0777, true);
        }
        //verifico el tamaño maximo
        if ($foto_mesa['size'] < 500000) {
            //verifico que sea una imagen
            if (getimagesize($foto_mesa['tmp_name']) != false) {
                //verifico la extension
                if (
                    $extension == "jpg" || $extension == "jpeg" || $extension == "gif"
                    || $extension == "png"
                ) {
                    //verifico que el nombre no esté repetido
                    if (!file_exists($nuevoNombre)) {
                        //verifico que se haya movido con exito
                        if (move_uploaded_file($foto_mesa['tmp_name'], $nuevoNombre)) {
                            $foto_mesa = $nuevoNombre;
                            $result = true;
                        } else {
                            $mensaje = "No se logro subir el archivo, intente nuevamente";
                        }
                    } else {
                        unlink($nuevoNombre);
                        if (move_uploaded_file($foto_mesa['tmp_name'], $nuevoNombre)) {
                            $foto_mesa = $nuevoNombre;
                            $result = true;
                        } else {
                            $mensaje = "No se logro subir el archivo, intente nuevamente";
                        }
                    }
                } else {
                    $mensaje = "Solo son permitidas imagenes con extension JPG, JPEG, PNG o GIF.";
                }
            } else {
                $mensaje = "Solo son permitidas imagenes con extension JPG, JPEG, PNG o GIF.";
            }
        } else {
            $mensaje = "La imagen es muy pesada intente con una mas liviana";
        }

        return $result;
    }

    public static function GuardarPedidoEnCsv($csv, &$mensaje)
    {
        $resultado = false;

        if (Pedido::ValidarCsv($csv, $mensaje)) {
            $archivotmp = $csv['tmp_name'];

            //cargamos el archivo
            $filas = file($archivotmp);

            //inicializamos variable a 0, esto nos ayudará a indicarle que no lea la primera línea
            $i = 0;

            //Recorremos el bucle para leer línea por línea
            foreach ($filas as $pedido) {
                //abrimos bucle
                /*si es diferente a 0 significa que no se encuentra en la primera línea 
   (con los títulos de las columnas) y por lo tanto puede leerla*/
                if ($i != 0) {
                    //abrimos condición, solo entrará en la condición a partir de la segunda pasada del bucle.
                    /* La funcion explode nos ayuda a delimitar los campos, por lo tanto irá 
       leyendo hasta que encuentre un ; */
                    $datos = explode(",", $pedido);
                    $pedido = new Pedido();
                    //usamos la función utf8_encode para leer correctamente los caracteres especiales
                    $pedido->SetearValores(null, utf8_encode($datos[0]), utf8_encode($datos[1]), utf8_encode($datos[2]), $datos[3], $datos[4], utf8_encode($datos[5]), null, null, null, $datos[6], null, null, utf8_encode($datos[7]));

                    if ($pedido->crearPedido($mensaje) == false) {
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

    //COCINA    
    public static function ObtenerTodosCocina()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT * FROM pedidos WHERE sector = 'cocina'");
        $consulta->execute();

        return $consulta->fetchAll(PDO::FETCH_CLASS, 'Pedido');
    }

    public static function ObtenerPendientesCocina()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT * FROM pedidos WHERE sector = 'cocina' AND estado = 'pendiente'");
        $consulta->execute();

        return $consulta->fetchAll(PDO::FETCH_CLASS, 'Pedido');
    }
    ////

    //Trago    
    public static function ObtenerTodosTrago()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT * FROM pedidos WHERE sector = 'tragos'");
        $consulta->execute();

        return $consulta->fetchAll(PDO::FETCH_CLASS, 'Pedido');
    }

    public static function ObtenerPendientesTrago()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT * FROM pedidos WHERE sector = 'tragos' AND estado = 'pendiente'");
        $consulta->execute();

        return $consulta->fetchAll(PDO::FETCH_CLASS, 'Pedido');
    }
    ////

    //CERVEZA    
    public static function ObtenerTodosCerveza()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT * FROM pedidos WHERE sector = 'cervezas'");
        $consulta->execute();

        return $consulta->fetchAll(PDO::FETCH_CLASS, 'Pedido');
    }

    public static function ObtenerPendientesCerveza()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT * FROM pedidos WHERE sector = 'cervezas' AND estado = 'pendiente'");
        $consulta->execute();

        return $consulta->fetchAll(PDO::FETCH_CLASS, 'Pedido');
    }
    ////

    //CANDY    
    public static function ObtenerTodosCandy()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT * FROM pedidos WHERE sector = 'candy'");
        $consulta->execute();

        return $consulta->fetchAll(PDO::FETCH_CLASS, 'Pedido');
    }

    public static function ObtenerPendientesCandy()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT * FROM pedidos WHERE sector = 'candy' AND estado = 'pendiente'");
        $consulta->execute();

        return $consulta->fetchAll(PDO::FETCH_CLASS, 'Pedido');
    }
    ////

    public static function PedidoTomado(&$pedido, &$payload, $idUsuarioToken)
    {
        $resultado = false;
        $pedidoDB = Pedido::ObtenerPedidoSoloCodigo($pedido->codigo_pedido);

        if ($pedidoDB != false) {
            $pedidoDB->fecha_entrega = date("Y-m-d H:i:s", strtotime($pedidoDB->fecha_creacion . "+" . intval($pedido->demora) . " minutes"));
            $pedidoDB->retrasado = $pedidoDB->fecha_entrega < date("Y-m-d H:i:s");
            $pedidoDB->id_responsable = intval($pedido->id_responsable);
            $pedidoDB->demora = $pedido->demora;
            $pedidoDB->estado = $pedido->estado;
            $pedidoDB->retrasado = $pedidoDB->fecha_entrega > date("Y-m-d H:i:s");
            //actualizo en base
            if ($pedidoDB->ModificarPedido()) {
                $pedido->fecha_entrega = date("H:i:s", strtotime($pedidoDB->fecha_entrega));
                $resultado = Usuario::SumarOperacion($pedido, $payload, $idUsuarioToken);
                $resultado = Mesa::ComprobarEstado($pedidoDB, $payload);
            } else {
                $payload = array("mensaje" => "no se logro modificar el pedido, intente mas tarde");
            }
        } else {
            $payload = array("mensaje" => "no existe ningun pedido por ese codigo, verifique nuevamente");
        }

        return $resultado;
    }

    public static function ModificarEstado($pedido, &$payload, $idUsuarioToken)
    {
        $resultado = false;
        $pedidoDB = Pedido::ObtenerPedidoSoloCodigo($pedido->codigo_pedido);

        if ($pedidoDB != false) {
            if ($pedidoDB->id_responsable != null) {
                $pedidoDB->estado = $pedido->estado;
                $pedidoDB->retrasado = $pedidoDB->fecha_entrega < date("Y-m-d H:i:s");
                //actualizo en base
                if ($pedidoDB->ModificarPedido()) {
                    $resultado = Usuario::SumarOperacion($pedidoDB, $payload, $idUsuarioToken);
                    $resultado = Mesa::ComprobarEstado($pedidoDB, $payload);
                } else {
                    $payload = array("mensaje" => "no se logro modificar el pedido, intente mas tarde");
                }
            } else {
                $payload = array("mensaje" => "Error! primero debe ser tomado por algun responsable");
            }
        } else {
            $payload = array("mensaje" => "no existe ningun pedido por ese codigo, verifique nuevamente");
        }

        return $resultado;
    }

    public static function ValidarEstado($estado)
    {
        $resultado = false;

        if ($estado == "en preparacion" || $estado == "listo para servir" || $estado == "servido" || $estado == "cobrado" || $estado == "cancelado") {
            $resultado = true;
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
