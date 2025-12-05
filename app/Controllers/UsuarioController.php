<?php

namespace App\Controllers;

use App\Services\UsuarioService;
use App\Utils\ApiResponse;
use App\Utils\Auth;
use \Slim\Slim;

class UsuarioController
{
    private $usuarioService;

    public function __construct()
    {
        $this->usuarioService = new UsuarioService();
    }

    // MÉTODOS PÚBLICOS (Registro y Login)
    public function registrar()
    {
        try {
            $app = Slim::getInstance();
            $datos = json_decode($app->request->getBody(), true);

            // Validar datos mínimos
            if (empty($datos) || empty($datos['usuario_nombre']) || empty($datos['usuario_correo']) || empty($datos['usuario_password'])) {
                ApiResponse::alerta("Faltan datos obligatorios.");
                return;
            }

            // Registrar (Por defecto Rol 3)
            $nuevoId = $this->usuarioService->registrarUsuario($datos);

            ApiResponse::exito("Usuario registrado correctamente.", ['id' => $nuevoId]);

        } catch (\Exception $e) {
            ApiResponse::alerta($e->getMessage());
        }
    }

    public function login()
    {
        try {
            $app = Slim::getInstance();
            $datos = json_decode($app->request->getBody(), true);

            if (empty($datos['usuario_correo']) || empty($datos['usuario_password'])) {
                ApiResponse::alerta("Correo y contraseña requeridos.");
                return;
            }

            // Validar credenciales (incluye lógica de bloqueo)
            $usuario = $this->usuarioService->loginUsuario(
                $datos['usuario_correo'],
                $datos['usuario_password']
            );

            // Generar Token JWT
            $tokenJwt = Auth::generarToken($usuario);

            // Guardar sesión en BD
            $this->usuarioService->guardarTokenSesion($usuario->usuario_id, $tokenJwt);

            $respuesta = [
                'usuario' => $usuario->toArray(),
                'token' => $tokenJwt
            ];

            ApiResponse::exito("Inicio de sesión exitoso.", $respuesta);

        } catch (\Exception $e) {
            ApiResponse::alerta($e->getMessage());
        }
    }

    // MÉTODOS DE ADMINISTRADOR (Gestión)
    public function listarTodo()
    {
        try {
            $app = Slim::getInstance();
            $usuarioLogueado = $app->usuario; // Inyectado por Middleware

            // Filtro opcional por URL (ej: ?rol_id=2)
            $filtroRol = $app->request->get('rol_id');

            $lista = $this->usuarioService->listarUsuariosAdmin($usuarioLogueado, $filtroRol);

            // Mapear a array
            $data = array_map(function ($u) {
                return $u->toArray();
            }, $lista);

            ApiResponse::exito("Lista de usuarios.", $data);

        } catch (\Exception $e) {
            ApiResponse::alerta($e->getMessage());
        }
    }

    public function crearAdmin()
    {
        try {
            $app = Slim::getInstance();
            $datos = json_decode($app->request->getBody(), true);
            $usuarioLogueado = $app->usuario;

            if (empty($datos)) {
                ApiResponse::alerta("Sin datos.");
                return;
            }

            $id = $this->usuarioService->crearUsuarioAdmin($datos, $usuarioLogueado);

            ApiResponse::exito("Usuario creado por admin.", ['id' => $id]);

        } catch (\Exception $e) {
            ApiResponse::alerta($e->getMessage());
        }
    }

    public function editarAdmin($id)
    {
        try {
            $app = Slim::getInstance();
            $datos = json_decode($app->request->getBody(), true);
            $usuarioLogueado = $app->usuario;

            if (empty($datos)) {
                ApiResponse::alerta("Sin datos para editar.");
                return;
            }

            $this->usuarioService->editarUsuarioAdmin($id, $datos, $usuarioLogueado);

            ApiResponse::exito("Usuario actualizado.");

        } catch (\Exception $e) {
            ApiResponse::alerta($e->getMessage());
        }
    }

    public function eliminarAdmin($id)
    {
        try {
            $app = Slim::getInstance();
            $usuarioLogueado = $app->usuario;

            $this->usuarioService->eliminarUsuarioAdmin($id, $usuarioLogueado);

            ApiResponse::exito("Usuario eliminado (Soft Delete).");

        } catch (\Exception $e) {
            ApiResponse::alerta($e->getMessage());
        }
    }
}