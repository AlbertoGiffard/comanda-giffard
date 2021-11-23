<?php


class TokenController
{
    public static function ValidacionTokenCompleta($header)
    {
        $token = trim(explode("Bearer", $header)[1]);
        try {
            $validacion = AutentificadorJWT::VerificarToken($token);
            $datos = TokenController::TraerDatosUsuario($token);
        } catch (Exception $e) {
            $datos = false;
        }

        return $datos;
    }

    public static function TraerDatosUsuario($token)
    {
        try {
            $usuario = AutentificadorJWT::ObtenerData($token);
        } catch (Exception $e) {
            $usuario = false;
        }

        return $usuario;
    }
    
}
