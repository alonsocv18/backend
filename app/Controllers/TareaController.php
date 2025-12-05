<?php

namespace App\Controllers;

use App\Services\TareaService;
use App\Utils\ApiResponse;
use App\Validators\TareaValidator;
use \Slim\Slim;

class TareaController
{
    private $tareaService;

    public function __construct()
    {
        $this->tareaService = new TareaService();
    }

    // GET /tareas
    public function listar()
    {
        try {
            $app = Slim::getInstance();

            // 1. Obtenemos el usuario que el Middleware validó previamente.
            // Esto es genial: ¡Ya sabemos su ID y su Rol sin consultar la BD de nuevo!
            $usuarioLogueado = $app->usuario;

            // 2. Llamamos al servicio pasando al usuario entero
            // El servicio decidirá qué tareas mostrarle según su rol.
            $tareas = $this->tareaService->listarTareas($usuarioLogueado);

            // 3. Convertimos los objetos a Array para la respuesta JSON
            $data = array_map(function ($t) {
                return $t->toArray();
            }, $tareas);

            ApiResponse::exito("Tareas recuperadas correctamente.", $data);

        } catch (\Exception $e) {
            ApiResponse::error("Error al listar tareas: " . $e->getMessage());
        }
    }

    // POST /tareas
    public function crear()
    {
        try {
            $app = Slim::getInstance();
            $datos = json_decode($app->request->getBody(), true);
            $usuarioLogueado = $app->usuario; // Objeto Usuario completo

            TareaValidator::validarCreacion($datos);

            // CAMBIO: Pasamos $usuarioLogueado (Objeto) en vez de solo el ID
            $nuevoId = $this->tareaService->crearTarea($datos, $usuarioLogueado);

            ApiResponse::exito("Tarea creada exitosamente.", ['id' => $nuevoId]);

        } catch (\Exception $e) {
            ApiResponse::alerta($e->getMessage());
        }
    }

    // PUT /tareas/:id
    public function editar($id)
    {
        try {
            $app = Slim::getInstance();
            $datos = json_decode($app->request->getBody(), true);

            // Necesitamos saber quién está editando para validar permisos
            $usuarioLogueado = $app->usuario;

            TareaValidator::validarEdicion($datos);

            $this->tareaService->editarTarea($id, $datos, $usuarioLogueado);

            ApiResponse::exito("Tarea actualizada correctamente.");

        } catch (\Exception $e) {
            ApiResponse::alerta($e->getMessage());
        }
    }

    // SOFT DELETE
    public function eliminar($id)
    {
        try {
            $app = Slim::getInstance();

            // 1. Obtenemos quién quiere borrar
            $usuarioLogueado = $app->usuario;

            // 2. Llamamos al servicio PASANDO EL USUARIO
            $this->tareaService->eliminarTarea($id, $usuarioLogueado);

            ApiResponse::exito("Tarea eliminada correctamente.");

        } catch (\Exception $e) {
            ApiResponse::alerta("No se pudo eliminar la tarea: " . $e->getMessage());
        }
    }
}