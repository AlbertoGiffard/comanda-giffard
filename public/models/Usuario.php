<?php

class Usuario
{
    public $id_usuario;
    public $mail;
    public $clave;
    public $nombre;
    public $fecha_creacion;
    public $sector;
    public $puesto;
    public $ultimo_movimiento;
    public $cantidad_operaciones;
    public $estado;
    public $nivel_acceso;

    public function __construct()
    {
    }

    public function SetearValores($id_usuario, $mail, $clave, $nombre, $fecha_creacion, $sector, $puesto, $ultimo_movimiento, $cantidad_operaciones, $estado, $nivel_acceso)
    {
        $this->id_usuario = $id_usuario;
        $this->mail = $mail;
        $this->clave = $clave;
        $this->nombre = $nombre;
        $this->fecha_creacion = $fecha_creacion;
        $this->sector = $sector;
        $this->puesto = $puesto;
        $this->ultimo_movimiento = $ultimo_movimiento;
        $this->cantidad_operaciones = $cantidad_operaciones;
        $this->estado = $estado;
        $this->nivel_acceso = $nivel_acceso;
    }
    public function crearUsuario()
    {
        $resultado = false;
        //genera un codigo hash para la contraseña
        $claveHash = password_hash($this->clave, PASSWORD_DEFAULT);
        //setea la fecha actual como fecha de creacion
        $this->fecha_creacion = date('Y-m-d');
        $this->cantidad_operaciones = 0;

        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("INSERT INTO usuarios (mail, clave, nombre, fecha_creacion, sector, puesto, cantidad_operaciones, estado, nivel_acceso) VALUES (:mail, :clave, :nombre, :fecha_creacion, :sector, :puesto, :cantidad_operaciones, :estado, :nivel_acceso)");

        $consulta->bindValue(':mail', $this->mail, PDO::PARAM_STR);
        $consulta->bindValue(':clave', $claveHash);
        $consulta->bindValue(':nombre', $this->nombre, PDO::PARAM_STR);
        $consulta->bindValue(':fecha_creacion', $this->fecha_creacion, PDO::PARAM_STR);
        $consulta->bindValue(':sector', $this->sector, PDO::PARAM_STR);
        $consulta->bindValue(':puesto', $this->puesto, PDO::PARAM_STR);
        $consulta->bindValue(':cantidad_operaciones', $this->cantidad_operaciones, PDO::PARAM_INT);
        $consulta->bindValue(':estado', $this->estado, PDO::PARAM_STR);
        $consulta->bindValue(':nivel_acceso', $this->nivel_acceso, PDO::PARAM_STR);

        $consulta->execute();

        if ($consulta->rowCount() > 0) {
            $resultado = true;
        }

        return $resultado;
    }

    public static function obtenerTodos()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT * FROM usuarios");
        $consulta->execute();

        return $consulta->fetchAll(PDO::FETCH_CLASS, 'Usuario');
    }

    public static function obtenerUsuario($id_usuario)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT * FROM usuarios WHERE id_usuario = :id_usuario");
        $consulta->bindValue(':id_usuario', $id_usuario, PDO::PARAM_INT);
        $consulta->execute();

        return $consulta->fetchObject('Usuario');
    }
    public static function RealizarLogin(&$usuario)
    {
        $resultado = -1;
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT * FROM usuarios WHERE mail = :mail limit 1");
        $consulta->bindValue(':mail', $usuario->mail, PDO::PARAM_STR);
        $consulta->execute();
        $usuarioDB = $consulta->fetchObject("Usuario");

        if ($usuarioDB) {
            //coincide solo el usuario
            $resultado = 0;

            if (password_verify($usuario->clave, $usuarioDB->clave)) {
                $usuario = $usuarioDB;
                $resultado = 1;
            }
        }

        return $resultado;
    }

    public function modificarUsuario()
    {
        $resultado = false;

        try {
            $objAccesoDato = AccesoDatos::obtenerInstancia();
            //realizar validacion
            $consulta = $objAccesoDato->prepararConsulta("UPDATE usuarios SET mail = :NuevoMail, nombre = :nuevoNombre, sector = :nuevoSector, puesto = :nuevoPuesto, ultimo_movimiento = :nuevoUltimoMovimiento, cantidad_operaciones = :nuevasOperaciones, estado = :nuevoEstado, nivel_acceso = :nuevoNivelAcceso WHERE id_usuario = :id_usuario");
            $consulta->bindValue(':NuevoMail', $this->mail, PDO::PARAM_STR);
            $consulta->bindValue(':nuevoNombre', $this->nombre, PDO::PARAM_STR);
            $consulta->bindValue(':nuevoSector', $this->sector, PDO::PARAM_STR);
            $consulta->bindValue(':nuevoPuesto', $this->puesto, PDO::PARAM_STR);
            $consulta->bindValue(':nuevoUltimoMovimiento', $this->ultimo_movimiento, PDO::PARAM_STR);
            $consulta->bindValue(':nuevasOperaciones', $this->cantidad_operaciones, PDO::PARAM_INT);
            $consulta->bindValue(':nuevoEstado', $this->estado, PDO::PARAM_STR);
            $consulta->bindValue(':nuevoNivelAcceso', $this->nivel_acceso, PDO::PARAM_STR);
            $consulta->bindValue(':id_usuario', $this->id_usuario, PDO::PARAM_INT);
            $consulta->execute();
            $resultado = true;
        } catch (\Throwable $th) {
            $resultado = false;
        }


        return $resultado;
    }

    public function modificarUsuarioPorMail()
    {
        $resultado = false;

        try {
            $objAccesoDato = AccesoDatos::obtenerInstancia();
            //realizar validacion
            $consulta = $objAccesoDato->prepararConsulta("UPDATE usuarios SET sector = :nuevoSector, puesto = :nuevoPuesto, estado = :nuevoEstado, nivel_acceso = :nuevoNivelAcceso WHERE mail = :mail");
            $consulta->bindValue(':nuevoSector', $this->sector, PDO::PARAM_STR);
            $consulta->bindValue(':nuevoPuesto', $this->puesto, PDO::PARAM_STR);
            $consulta->bindValue(':nuevoEstado', $this->estado, PDO::PARAM_STR);
            $consulta->bindValue(':nuevoNivelAcceso', $this->nivel_acceso, PDO::PARAM_STR);
            $consulta->bindValue(':mail', $this->mail, PDO::PARAM_STR);
            $consulta->execute();
            $resultado = true;
        } catch (\Throwable $th) {
            $resultado = false;
        }


        return $resultado;
    }

    /* 
        Cuando Sumar:
        0 = socios
        1 = sectores
        2 = atencion
    */
    public static function SumarOperacion($pedido, &$payload, $idUsuarioToken)
    {
        $usuarioActualizar = Usuario::obtenerUsuario($pedido->id_responsable);
        $usuarioToken = Usuario::obtenerUsuario($idUsuarioToken);
        $resultado = false;
        $cuandoSumar = 0;
        $cuandoSumarToken = 0;

        if ($usuarioActualizar != false) {
            if ($usuarioActualizar->sector == "cocina" || $usuarioActualizar->sector == "tragos" || $usuarioActualizar->sector == "cervezas" || $usuarioActualizar->sector == "candy") {
                $cuandoSumar = 1;
            }
            if ($usuarioToken->sector == "cocina" || $usuarioToken->sector == "tragos" || $usuarioToken->sector == "cervezas" || $usuarioToken->sector == "candy") {
                $cuandoSumarToken = 1;
            }

            if ($usuarioActualizar->sector == "atencion") {
                $cuandoSumar = 2;
            }
            if ($usuarioToken->sector == "atencion") {
                $cuandoSumarToken = 2;
            }

            switch ($pedido->estado) {
                case 'listo para servir':
                    if ($cuandoSumar == 1) {
                        $usuarioActualizar->cantidad_operaciones++;
                    }
                    if ($cuandoSumarToken == 1) {
                        $usuarioToken->cantidad_operaciones++;
                    }
                    break;

                case 'cobrado':
                    if ($cuandoSumar == 2) {
                        $usuarioActualizar->cantidad_operaciones++;
                    }
                    if ($cuandoSumarToken == 2) {
                        $usuarioToken->cantidad_operaciones++;
                    }
                    break;

                case 'cerrado':
                    if ($cuandoSumar == 0) {
                        $usuarioActualizar->cantidad_operaciones++;
                    }
                    if ($cuandoSumarToken == 0) {
                        $usuarioToken->cantidad_operaciones++;
                    }
                    break;
            }

            if ($usuarioActualizar->modificarUsuario() && $usuarioToken->modificarUsuario()) {
                $resultado = true;
            } else {
                $resultado = false;
                $payload = array("mensaje" => "no se logro agregar la nueva operacion al empleado, intente mas tarde");
            }
        } else {
            $resultado = false;
            $payload = array("mensaje" => "no se logro encontrar al empleado");
        }

        return $resultado;
    }

    public function borrarUsuario()
    {
        try {
            $objAccesoDato = AccesoDatos::obtenerInstancia();
            $consulta = $objAccesoDato->prepararConsulta("UPDATE usuarios SET estado = 'borrado' WHERE id_usuario = :id_usuario");
            $consulta->bindValue(':id_usuario', $this->id_usuario, PDO::PARAM_INT);
            $consulta->execute();
            $resultado = true;
        } catch (\Throwable $th) {
            $resultado = false;
        }

        return $resultado;
    }

    public static function ValidarSector($valor)
    {
        $resultado = false;
        $sectores = array('tragos', 'cervezas', 'cocina', 'candy', 'atencion', 'socios');

        foreach ($sectores as $sector) {
            if ($valor == $sector) {
                $resultado = true;
                break;
            }
        }

        return $resultado;
    }

    public static function ValidarEstado($valor)
    {
        $resultado = false;
        $estados = array('activo', 'suspendido', 'borrado');

        foreach ($estados as $estado) {
            if ($valor == $estado) {
                $resultado = true;
                break;
            }
        }

        return $resultado;
    }

    public static function ValidarAcceso($valor)
    {
        $resultado = false;
        $niveles = array('admin', 'supervisor', 'empleado');

        foreach ($niveles as $nivel) {
            if ($valor == $nivel) {
                $resultado = true;
                break;
            }
        }

        return $resultado;
    }

    public static function ValidarMail($email)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT mail FROM usuarios WHERE mail = :email");
        $consulta->bindValue(':email', $email, PDO::PARAM_STR);
        $consulta->execute();

        return $consulta->fetchColumn();
    }

    public static function Validaciones($sector, $estado, $acceso, $email)
    {
        $resultado = "El sector debe ser alguno de estos: tragos, cervezas, cocina, candy, atencion o socios";

        if (Usuario::ValidarSector($sector)) {
            if (Usuario::ValidarEstado($estado)) {
                if (Usuario::ValidarAcceso($acceso)) {
                    //quiere decir que el mail no existe en la db
                    if (Usuario::ValidarMail($email) == false) {
                        $resultado = "validado";
                    } else {
                        $resultado = "Ya existe un usuario con este correo, intente con otro";
                    }
                } else {
                    $resultado = "El acceso debe ser alguno de estos: admin, supervisor o empleado";
                }
            } else {
                $resultado = "El estado debe ser alguno de estos: activo, suspendido o borrado";
            }
        }

        return $resultado;
    }

    public static function ValidacionesModificar($sector, $estado, $acceso, $email)
    {
        $resultado = "El sector debe ser alguno de estos: tragos, cervezas, cocina, candy, atencion o socios";

        if (Usuario::ValidarSector($sector)) {
            if (Usuario::ValidarEstado($estado)) {
                if (Usuario::ValidarAcceso($acceso)) {
                    //quiere decir que el mail no existe en la db
                    if (Usuario::ValidarMail($email) != false) {
                        $resultado = "validado";
                    } else {
                        $resultado = "Ya existe un usuario con este correo, intente con otro";
                    }
                } else {
                    $resultado = "El acceso debe ser alguno de estos: admin, supervisor o empleado";
                }
            } else {
                $resultado = "El estado debe ser alguno de estos: activo, suspendido o borrado";
            }
        }

        return $resultado;
    }

    public static function GuardarUsuarioEnCsv($csv, &$mensaje)
    {
        $resultado = false;

        if (Usuario::ValidarCsv($csv, $mensaje)) {
            $archivotmp = $csv['tmp_name'];

            //cargamos el archivo
            $filas = file($archivotmp);

            //inicializamos variable a 0, esto nos ayudará a indicarle que no lea la primera línea
            $i = 0;

            //Recorremos el bucle para leer línea por línea
            foreach ($filas as $usuario) {
                //abrimos bucle
                /*si es diferente a 0 significa que no se encuentra en la primera línea 
   (con los títulos de las columnas) y por lo tanto puede leerla*/
                if ($i != 0) {
                    //abrimos condición, solo entrará en la condición a partir de la segunda pasada del bucle.
                    /* La funcion explode nos ayuda a delimitar los campos, por lo tanto irá 
       leyendo hasta que encuentre un ; */
                    $datos = explode(",", $usuario);
                    $usuario = new Usuario();
                    //usamos la función utf8_encode para leer correctamente los caracteres especiales
                    $usuario->SetearValores(null, utf8_encode($datos[0]), utf8_encode($datos[1]), utf8_encode($datos[2]), null, utf8_encode($datos[3]), utf8_encode($datos[4]), null, null, utf8_encode($datos[5]), utf8_encode($datos[6]));

                    if ($usuario->crearUsuario() == false) {
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
