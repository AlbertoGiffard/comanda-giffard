<?php

use Firebase\JWT\JWT;
use Slim\Psr7\Response as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;

class AutentificadorJWT
{
    private static $claveSecreta = 'T3sT$JWT';
    private static $tipoEncriptacion = ['HS256'];
    //Este es el MW
    public static function GenerarToken($request, $handler)
    {
        $mensaje = "Faltan datos";
        $parametros = $request->getParsedBody();
        $ingreso = false;

        switch ($_SERVER['REQUEST_METHOD']) {
            case 'POST':
                if (isset($_POST['mail']) && isset($_POST['clave'])) {
                    $user = new Usuario();
                    $user->SetearValores(null, $_POST['mail'], $_POST['clave'], null);
                    //envio el objeto usuario a usuarioController
                    $request = $request->withAttribute('usuario', $user);
                    //Todo lo que esta antes de esta linea se ejecuta previo de que continue la logica, una vez que se llama a la funcion de handler, lo deja seguir
                    $response = $handler->handle($request);
                    $ingreso = true;
                }
                break;
        }

        //valido que haya logrado ingresar
        //aqui no llamo al handler porque indica que no logro continuar
        if (!$ingreso) {
            $response = new Response();

            $response->getBody()->write($mensaje);
        }

        //Despues
        return $response;
    }
    public static function AutenticacionEmpleado($request, $handler)
    {
        $mensaje = "Falta la autorizacion";
        $parametros = $request->getParsedBody();
        $ingreso = false;
        $header = $request->getHeaderLine('Authorization');

        if (!empty($header)) {
            $usuarioValidado = TokenController::ValidacionTokenCompleta($header);
            if ($usuarioValidado != false) {
                    $response = $handler->handle($request);
                    $ingreso = true;
            } else {
                $mensaje = "Token incorrecto";
            }
        }

        if (!$ingreso) {
            $response = new Response();

            $response->getBody()->write(json_encode(array("Error" => $mensaje)));
        }

        //Despues
        return $response;
    }

    public static function AutenticacionSupervisor($request, $handler)
    {
        $mensaje = "Falta la autorizacion";
        $parametros = $request->getParsedBody();
        $ingreso = false;
        $header = $request->getHeaderLine('Authorization');

        if (!empty($header)) {
            $usuarioValidado = TokenController::ValidacionTokenCompleta($header);
            if ($usuarioValidado != false) {
                if ($usuarioValidado->{'estado'} == "activo") {
                    if ($usuarioValidado->{'nivel_acceso'} == "supervisor" || $usuarioValidado->{'nivel_acceso'} == "admin") {
                        $response = $handler->handle($request);
                        $ingreso = true;
                    } else {
                        $mensaje = "Tu nivel de acceso no te permite ejecutar esta accion";
                    }
                } else {
                    $mensaje = "Tu estado no es 'activo'";
                }
            } else {
                $mensaje = "Token incorrecto";
            }
        }

        if (!$ingreso) {
            $response = new Response();

            $response->getBody()->write(json_encode(array("Error" => $mensaje)));
        }

        //Despues
        return $response;
    }

    public static function AutenticacionAdmin($request, $handler)
    {
        $mensaje = "Falta la autorizacion";
        $parametros = $request->getParsedBody();
        $ingreso = false;
        $header = $request->getHeaderLine('Authorization');

        if (!empty($header)) {
            $usuarioValidado = TokenController::ValidacionTokenCompleta($header);
            if ($usuarioValidado != false) {
                    if ($usuarioValidado->{'nivel_acceso'} == "admin") {
                        $response = $handler->handle($request);
                        $ingreso = true;
                    } else {
                        $mensaje = "Tu nivel de acceso no te permite ejecutar esta accion";
                    }
            } else {
                $mensaje = "Token incorrecto";
            }
        }

        if (!$ingreso) {
            $response = new Response();

            $response->getBody()->write(json_encode(array("Error" => $mensaje)));
        }

        //Despues
        return $response;
    }
    //Se usa en UsuarioController
    public static function CrearToken($datos)
    {
        /* Datos:{
            -id_usuario
            -nombre
            -sector
            -cantidad_operaciones
            -estado
            -nivel_acceso
        } */
        $ahora = time();
        $payload = array(
            'iat' => $ahora,
            'exp' => $ahora + (60000),
            'aud' => self::Aud(),
            'id_usuario' => $datos->id_usuario,
            'nivel_acceso' => $datos->nivel_acceso,
            'app' => "Comanda"
        );
        return JWT::encode($payload, self::$claveSecreta);
    }
    //Usado en el controlador
    public static function VerificarToken($token)
    {
        if (empty($token)) {
            throw new Exception("El token esta vacio.");
        }
        try {
            $decodificado = JWT::decode(
                $token,
                self::$claveSecreta,
                self::$tipoEncriptacion
            );

            return $decodificado;
        } catch (Exception $e) {
            throw $e;
        }
        if ($decodificado->aud !== self::Aud()) {
            throw new Exception("No es el usuario valido");
        }
    }


    public static function ObtenerPayLoad($token)
    {
        if (empty($token)) {
            throw new Exception("El token esta vacio.");
        }
        return JWT::decode(
            $token,
            self::$claveSecreta,
            self::$tipoEncriptacion
        );
    }

    public static function ObtenerData($token)
    {
        $usuario = new Usuario();
        $usuario->id_usuario = JWT::decode(
            $token,
            self::$claveSecreta,
            self::$tipoEncriptacion
        )->id_usuario;
        $usuario->nivel_acceso = JWT::decode(
            $token,
            self::$claveSecreta,
            self::$tipoEncriptacion
        )->nivel_acceso;

        return $usuario;
    }

    private static function Aud()
    {
        $aud = '';

        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $aud = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $aud = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            $aud = $_SERVER['REMOTE_ADDR'];
        }

        $aud .= @$_SERVER['HTTP_USER_AGENT'];
        $aud .= gethostname();

        return sha1($aud);
    }
}
