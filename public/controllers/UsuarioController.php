<?php
require_once './models/Usuario.php';
require_once './models/Movimiento.php';
require_once './interfaces/IApiUsable.php';

class UsuarioController extends Usuario implements IApiUsable
{
  public function CargarUno($request, $response, $args)
  {
    $user = $request->getAttribute('usuario');

    // Creamos el usuario
    if ($user->crearUsuario()) {
      $payload = json_encode(array("mensaje" => "Usuario creado con exito"));
    } else {
      $payload = json_encode(array("mensaje" => "Error! no se pudo cargar el usuario, intente mas tarde"));
    }

    $response->getBody()->write($payload);
    return $response
      ->withHeader('Content-Type', 'application/json');
  }

  public function TraerTodos($request, $response, $args)
  {
    $lista = Usuario::obtenerTodos();
    //me lo trae como un json
    //$payload = json_encode(array("listaUsuario" => $lista));
    $payload = "<table> <th> ID </th> <th> Mail </th> <th> Nombre </th> <th> Fecha_creacion </th> <th> Sector </th> <th> Puesto </th> <th> Ultimo Movimiento </th> <th> N° de Operaciones </th> <th> Estado </th> <th> Nivel Acceso </th>";

    foreach ($lista as $usuario) {
      $payload = $payload . "<tr>" . "<td>" . $usuario->id_usuario . "</td>" . "<td>" . $usuario->mail . "</td>" . "<td>" . $usuario->nombre . "</td>" . "<td>" . $usuario->fecha_creacion . "</td>" . "<td>" . $usuario->sector . "</td>" . "<td>" . $usuario->puesto . "</td>" . "<td>" . $usuario->ultimo_movimiento . "</td>" . "<td>" . $usuario->cantidad_operaciones . "</td>" . "<td>" . $usuario->estado . "</td>" . "<td>" . $usuario->nivel_acceso . "</td>" . "</tr>";
    }
    $payload = $payload . "</table>";

    $response->getBody()->write($payload);
    return $response
      ->withHeader('Content-Type', 'application/json');
    /* return $response
      ->withHeader('Content-Type', 'text/csv; charset=utf-8'); */
  }

  public function TraerUno($request, $response, $args)
  {
    // Buscamos usuario por id
    $id_usuario = $request->getAttribute('id_usuario');

    $usuario = Usuario::obtenerUsuario($id_usuario);
    //valida que el usuario exista
    if ($usuario == false) {
      $payload = json_encode(array("mensaje" => "Error! no se encontró a ningun usuario por ese ID"));
    } else {
      $payload = json_encode($usuario);
    }

    $response->getBody()->write($payload);
    return $response
      ->withHeader('Content-Type', 'application/json');
  }

  public function CargarMovimiento($request, $response, $args)
  {
    $movimiento = $request->getAttribute('movimiento');

    // Creamos la entrada
    if ($movimiento->crearMovimiento()) {
      $payload = json_encode(array("mensaje" => "Movimiento guardado con exito"));
    } else {
      $payload = json_encode(array("mensaje" => "Error! no se pudo cargar el movimiento, intente mas tarde"));
    }

    $response->getBody()->write($payload);
    return $response
      ->withHeader('Content-Type', 'application/json');
  }

  public function CargarCsv($request, $response, $args)
  {
    $csv = $request->getAttribute('csv');
    $mensaje = "El fichero no es válido";

    // Creamos la entrada
    if (Usuario::GuardarUsuarioEnCsv($csv, $mensaje)) {
      $payload = json_encode(array("mensaje" => "Cargado csv con usuarios correctamente"));
    } else {
      $payload = json_encode(array("mensaje" => "Error!" . $mensaje));
    }

    $response->getBody()->write($payload);
    return $response
      ->withHeader('Content-Type', 'application/json');
  }

  public function TraerTodosCsv($request, $response, $args)
  {
    header('Content-Type: text/plain;charset=UTF-8');
    $stream = fopen('php://output', 'w');

    $lista = Usuario::obtenerTodos();

    fputcsv($stream, array("id_usuario", "mail", "nombre", "fecha_creacion", "sector", "puesto", "ultimo_movimiento", "cantidad_operaciones", "estado", "nivel_acceso"));

    foreach($lista as $usuario){
      fputcsv($stream, array($usuario->id_usuario, $usuario->mail, $usuario->nombre, $usuario->fecha_creacion, $usuario->sector, $usuario->puesto, $usuario->ultimo_movimiento, $usuario->cantidad_operaciones, $usuario->estado, $usuario->nivel_acceso));
    }
    // more complex codes would be here. It may cause error.

    header("Content-Type: application/text");
    header("Content-Disposition: attachment; filename=output.csv");

    $response->getBody()->write(mb_convert_encoding(ob_get_clean(), 'SJIS', 'UTF-8'));
    return $response
      ->withHeader('Content-Type', 'application/json');
  }
  public function LoginUsuario($request, $response, $args)
  {
    $usuario = $request->getAttribute('usuario');
    $movimiento = new Movimiento();
    $movimiento->tipo = "entrada";

    $resultadoLogin = Usuario::RealizarLogin($usuario);

    //return $resultadoLogin;
    if ($resultadoLogin != -1) {
      if ($resultadoLogin != 0) {
        //aca porque recien aqui ya tengo el id del usuario
        $movimiento->id_usuario = $usuario->id_usuario;
        //registro la entrada
        if ($movimiento->crearMovimiento()) {
          $mensaje = json_encode(array('mensaje' => 'Bienvenido/a ' . $usuario->nombre, 'ID_usuario' => $usuario->id_usuario, 'JWT' => AutentificadorJWT::CrearToken($usuario)));
        } else {
          $payload = json_encode(array("mensaje" => "Error! no se pudo cargar el movimiento, intente mas tarde"));
        }
      } else {
        $mensaje = "Contraseña equivocada";
      }
    } else {
      $mensaje = "Usuario y contraseña equivocado";
    }

    $response->getBody()->write($mensaje);
    return $response
      ->withHeader('Content-Type', 'application/json');
  }
  // PARTIR DE ACA TODO POR HACER
  public function ModificarUno($request, $response, $args)
  {
    $parametros = $request->getParsedBody();

    $id = $parametros['id'];
    $usuario = $parametros['usuario'];
    $clave = password_hash($parametros['clave'], PASSWORD_DEFAULT);
    // Creamos el usuario
    $usr = new Usuario();
    $usr->id = $id;
    $usr->usuario = $usuario;
    $usr->clave = $clave;

    Usuario::modificarUsuario($usr);

    $payload = json_encode(array("mensaje" => "Usuario modificado con exito"));

    $response->getBody()->write($payload);
    return $response
      ->withHeader('Content-Type', 'application/json');
  }

  public function BorrarUno($request, $response, $args)
  {
    $usuarioId = $args['usuarioId'];
    Usuario::borrarUsuario($usuarioId);

    $payload = json_encode(array("mensaje" => "Usuario borrado con exito"));

    $response->getBody()->write($payload);
    return $response->withHeader('Content-Type', 'application/json');
  }
}
