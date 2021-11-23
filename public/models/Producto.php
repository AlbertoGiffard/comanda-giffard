<?php

class Producto
{
    public $id_producto;
    public $nombre;
    public $fecha_creacion;
    public $sector;
    public $ultimo_pedido;
    public $cantidad_pedido;
    public $estado;
    public $stock;

    public function __construct()
    {
    }

    public function SetearValores($id_producto, $nombre, $fecha_creacion, $sector, $ultimo_pedido, $cantidad_pedido, $estado, $stock){
        $this->id_producto = $id_producto;
        $this->nombre = $nombre;
        $this->fecha_creacion = $fecha_creacion;
        $this->sector = $sector;
        $this->ultimo_pedido = $ultimo_pedido;
        $this->cantidad_pedido = $cantidad_pedido;
        $this->estado = $estado;
        $this->stock = $stock;
    }
    public function crearProducto()
    {
        //setea la fecha actual como fecha de creacion
        $this->fecha_creacion = date('Y-m-d');
        $this->cantidad_pedido = 0;

        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("INSERT INTO productos (nombre, fecha_creacion, sector, ultimo_pedido, cantidad_pedido, estado, stock) VALUES (:nombre, :fecha_creacion, :sector, :ultimo_pedido, :cantidad_pedido, :estado, :stock)");

        $consulta->bindValue(':nombre', $this->nombre, PDO::PARAM_STR);
        $consulta->bindValue(':fecha_creacion', $this->fecha_creacion, PDO::PARAM_STR);
        $consulta->bindValue(':sector', $this->sector, PDO::PARAM_STR);
        $consulta->bindValue(':ultimo_pedido', $this->ultimo_pedido, PDO::PARAM_STR);
        $consulta->bindValue(':cantidad_pedido', $this->cantidad_pedido, PDO::PARAM_INT);
        $consulta->bindValue(':estado', $this->estado, PDO::PARAM_STR);
        $consulta->bindValue(':stock', $this->stock, PDO::PARAM_INT);

        $consulta->execute();

        return $objAccesoDatos->obtenerUltimoId();
    }

    public static function obtenerTodos()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT * FROM criptomonedas");
        $consulta->execute();

        return $consulta->fetchAll(PDO::FETCH_CLASS, 'Producto');
    }

    public static function obtenerProducto($id_producto)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT * FROM productos WHERE id_producto = :id_producto");
        $consulta->bindValue(':id_producto', $id_producto, PDO::PARAM_INT);
        $consulta->execute();

        return $consulta->fetchObject('Producto');
    }

    public function modificarProducto()
    {
        $resultado = false;

        $objAccesoDato = AccesoDatos::obtenerInstancia();
        //realizar validacion
        $consulta = $objAccesoDato->prepararConsulta("UPDATE productos SET nombre = :nuevoNombre, sector = :nuevoSector, ultimo_pedido = :nuevoUltimoPedido, cantidad_pedido = :nuevaCantidadPedido, estado = :nuevoEstado, stock = :nuevoStock WHERE id_producto = :id_producto");
        $consulta->bindValue(':nuevoNombre', $this->nombre, PDO::PARAM_STR);
        $consulta->bindValue(':nuevoSector', $this->sector, PDO::PARAM_STR);
        $consulta->bindValue(':nuevoUltimoPedido', $this->ultimo_pedido, PDO::PARAM_STR);
        $consulta->bindValue(':nuevaCantidadPedido', $this->cantidad_pedido, PDO::PARAM_INT);
        $consulta->bindValue(':nuevoEstado', $this->estado, PDO::PARAM_STR);
        $consulta->bindValue(':nuevoStock', $this->stock, PDO::PARAM_STR);
        $consulta->bindValue(':id_producto', $this->id_producto, PDO::PARAM_INT);
        $consulta->execute();

        if ($consulta->rowCount() > 0) {
            $resultado = true;
        }

        return $resultado;
    }

    public function borrarProducto()
    {
        $resultado = false;

        $objAccesoDato = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDato->prepararConsulta("UPDATE productos SET estado = 'no disponible' WHERE id_producto = :id_producto");
        $consulta->bindValue(':id_producto', $this->id_producto, PDO::PARAM_INT);
        $consulta->execute();

        if ($consulta->rowCount() > 0) {
            $resultado = true;
        }

        return $resultado;
    }

    public static function ValidarSector($valor){
        $resultado = false;
        $sectores = array('tragos', 'cervezas', 'cocina', 'candy');

        foreach ($sectores as $sector) {
            if ($valor == $sector) {
                $resultado = true;
                break;
            }
        }
        
        return $resultado;
    }

    public static function ValidarEstado($valor){
        $resultado = false;
        $estados = array('activo', 'sin stock', 'no disponible');

        foreach ($estados as $estado) {
            if ($valor == $estado) {
                $resultado = true;
                break;
            }
        }
        
        return $resultado;
    }

    public static function Validaciones($sector, $estado){
        $resultado = "Error!<ul>El sector debe ser alguno de estos:<li>tragos</li><li>cervezas</li><li>cocina</li><li>candy</li></ul>";

        if (Producto::ValidarSector($sector)) {
            if (Producto::ValidarEstado($estado)) {
                $resultado = "validado";
            } else{
                $resultado = "Error!<ul>El estado debe ser alguno de estos:<li>activo</li><li>sin stock</li><li>no disponible</li></ul>";
            }
        } 

        return $resultado;
    }

    public static function VerificarExistencia($nombre, $cantidad, &$pedido){
        $resultado = json_encode(array("Failed" => "No existe ningun producto por ese nombre, es importante tener en cuenta las mayusculas y minusculas"));

        $objAccesoDato = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDato->prepararConsulta("SELECT * FROM productos WHERE nombre = :nombre");
        $consulta->bindValue(':nombre', $nombre, PDO::PARAM_STR);
        $consulta->execute();
        $producto = $consulta->fetchObject("Producto");

        if ($producto != false) {
            $pedido->sector = $producto->sector;
            $resultado = $producto->PedidoDeProducto($cantidad);
        }

        return $resultado;
    }

    public function PedidoDeProducto($cantidad){
        $resultado = json_encode(array("Failed" => "El producto no se encuentra activo en estos momentos, intente otro dia"));
        //cambia el valor del tipo
        $cantidad = intval($cantidad);

        if ($this->estado == "activo") {
            if ($this->stock >= $cantidad) {
                $this->stock -= $cantidad;

                if ($this->stock == 0) {
                    $this->estado = "sin stock";
                }
                $this->ultimo_pedido = date('Y-m-d H:i:s');
                $this->cantidad_pedido += $cantidad;
                if ($this->modificarProducto()) {
                    $resultado = "ok";
                } else{
                    $resultado = json_encode(array("Failed" => "Fallo la carga a la base de datos, intente mas tarde"));
                }
            } else{
                $resultado = json_encode(array("Failed" => "No hay stock suficiente para el pedido"));
            }
        }

        return $resultado;
    }

    public static function GuardarProductoEnCsv($csv, &$mensaje)
    {
        $resultado = false;

        if (Producto::ValidarCsv($csv, $mensaje)) {
            $archivotmp = $csv['tmp_name'];

            //cargamos el archivo
            $filas = file($archivotmp);

            //inicializamos variable a 0, esto nos ayudará a indicarle que no lea la primera línea
            $i = 0;

            //Recorremos el bucle para leer línea por línea
            foreach ($filas as $producto) {
                //abrimos bucle
                /*si es diferente a 0 significa que no se encuentra en la primera línea 
   (con los títulos de las columnas) y por lo tanto puede leerla*/
                if ($i != 0) {
                    //abrimos condición, solo entrará en la condición a partir de la segunda pasada del bucle.
                    /* La funcion explode nos ayuda a delimitar los campos, por lo tanto irá 
       leyendo hasta que encuentre un ; */
                    $datos = explode(",", $producto);
                    $producto = new Producto();
                    //usamos la función utf8_encode para leer correctamente los caracteres especiales
                    $producto->SetearValores(null, utf8_encode($datos[0]), null, utf8_encode($datos[1]), null, null, utf8_encode($datos[2]), $datos[3]);

                    if($producto->crearProducto() == false){
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

?>