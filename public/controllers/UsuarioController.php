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
    $payload = $lista;

    $response->getBody()->write(json_encode($payload));
    return $response
      ->withHeader('Content-Type', 'application/json');
  }

  public function TraerUno($request, $response, $args)
  {
    // Buscamos usuario por id
    $id_usuario = $args['id_usuario'];

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

  public function TraerMovimientos($request, $response, $args)
  {
    $movimientos = Movimiento::obtenerTodos();
    
    if ($movimientos == false) {
      $payload = json_encode(array("mensaje" => "Error! no se encontró a ningun movimiento"));
    } else {
      $payload = json_encode($movimientos);
    }

    $response->getBody()->write($payload);
    return $response
      ->withHeader('Content-Type', 'application/json');
  }

  public function TraerMovimientosEntreFechas($request, $response, $args)
  {
    $desde = $args['desde'];
    $hasta = $args['hasta'];

    $movimientos = Movimiento::obtenerMovimientoEntreFechas($desde, $hasta);

    if ($movimientos == false) {
      $payload = json_encode(array("mensaje" => "Error! no se encontró a ningun movimiento"));
    } else {
      $payload = json_encode($movimientos);
    }

    $response->getBody()->write($payload);
    return $response
      ->withHeader('Content-Type', 'application/json');
  }

  public function TraerTodosPdf($request, $response, $args)
  {
    $type = 'application/json';
    $lista = Usuario::obtenerTodos();
    $fpdf = new FPDF();
    $fpdf->addPage();
    $fpdf->SetFont('Arial', '', 12);

    // Title row
    $fpdf->SetFont('', 'B');
    $fpdf->Cell(190, 20, "Todos los usuarios", 1, 0, 'C', false);
    $fpdf->Ln();
    $fpdf->Cell(10, 20, "ID", 1, 0, 'L', false);
    $fpdf->SetFont('', '');
    $fpdf->Cell(60, 20, "Mail", 1, 0, 'C', false);
    $fpdf->Cell(20, 20, "Nombre", 1, 0, 'C', false);
    $fpdf->Cell(20, 20, "Sector", 1, 0, 'C', false);
    $fpdf->Cell(30, 20, "Operaciones", 1, 0, 'C', false);
    $fpdf->Cell(30, 20, "Nivel Acceso", 1, 0, 'C', false);
    $fpdf->Cell(20, 20, "Estado", 1, 0, 'C', false);
    $fpdf->Ln();

    if ($lista != false) {
      foreach ($lista as $usuario) {
        $mailCorto = explode("@", $usuario->mail);

        $fpdf->SetFont('', 'B');
        $fpdf->Cell(10, 20, $usuario->id_usuario, "LTR", 0, 'L');
        $fpdf->SetFont('', '');
        $fpdf->Cell(60, 20, $usuario->mail, "LTR");
        $fpdf->Cell(20, 20, $usuario->nombre, "LTR");
        $fpdf->Cell(20, 20, $usuario->sector, "LTR");
        $fpdf->Cell(30, 20, $usuario->cantidad_operaciones, "LTR");
        $fpdf->Cell(30, 20, $usuario->nivel_acceso, "LTR");
        $fpdf->Cell(20, 20, $usuario->estado, "LTR");
        $fpdf->Ln();
      }
      $fpdf->Cell(190,0,'','T');
      $type = 'application/pdf';
      $response->getBody()->write($fpdf->OutPut());
    } else {
      $payload = json_encode(array("Mensaje" => "Error! intente nuevamente"));
      $response->getBody()->write($payload);
    }

    return $response
      ->withHeader('Content-Type', $type);
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
          $payload = json_encode(array("mensaje" => 'Bienvenido/a ' . $usuario->nombre, 'ID_usuario' => $usuario->id_usuario, 'JWT' => AutentificadorJWT::CrearToken($usuario)));
        } else {
          $payload = json_encode(array("mensaje" => "Error! no se pudo cargar el movimiento/login, intente mas tarde"));
        }
      } else {
        $payload = json_encode(array("mensaje" => "Contraseña equivocada"));
      }
    } else {
      $payload = json_encode(array("mensaje" => "Usuario y contraseña equivocado"));
    }

    $response->getBody()->write($payload);
    return $response
      ->withHeader('Content-Type', 'application/json');
  }

  public function ModificarUno($request, $response, $args)
  {
    $usuario = $request->getAttribute('usuario');

    if($usuario->modificarUsuarioPorMail()){
      $payload = json_encode(array("mensaje" => "Usuario modificado con exito"));
    } else{
      $payload = json_encode(array("mensaje" => "No se logro modificar al usuario intente mas tarde"));
    }

    $response->getBody()->write($payload);
    return $response
      ->withHeader('Content-Type', 'application/json');
  }

  public function BorrarUno($request, $response, $args)
  {
    $id_usuario = $args['id'];
    
    $usuario = Usuario::obtenerUsuario($id_usuario);
    //valida que el usuario exista
    if ($usuario == false) {
      $payload = json_encode(array("mensaje" => "Error! no se encontró a ningun usuario por ese ID"));
    } else {
      if($usuario->borrarUsuario()){
        $payload = json_encode(array("mensaje" => "Usuario eliminado con exito"));
      } else{
        $payload = json_encode(array("mensaje" => "No se logro eliminar al usuario intente mas tarde"));
      }
    }
    

    $response->getBody()->write($payload);
    return $response
      ->withHeader('Content-Type', 'application/json');
  }
}
