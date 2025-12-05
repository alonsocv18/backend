<?php

namespace App\Services;

use App\Repositories\TareaRepository;
use App\Entities\Tarea;
use App\Utils\Crypto;
use Exception;

class TareaService
{
    private $tareaRepository;

    public function __construct()
    {
        // Inicializa el repositorio de tareas
        $this->tareaRepository = new TareaRepository();
    }

    // Lista las tareas aplicando filtro por rol y desencripta los nombres asignados
    public function listarTareas($usuario)
    {
        $tareas = $this->tareaRepository->listar($usuario->usuario_id, $usuario->rol_id);

        // Ajusta los nombres visibles de usuarios asignados
        foreach ($tareas as $tarea) {
            if (!empty($tarea->nombre_asignado)) {
                $tarea->nombre_asignado = Crypto::desencriptar($tarea->nombre_asignado);
            } else {
                $tarea->nombre_asignado = "Sin asignar";
            }
        }

        return $tareas;
    }

    public function crearTarea($datos, $usuarioActual)
    {
        if (empty($datos['titulo']) || empty($datos['proyecto_id'])) {
            throw new Exception("El título y el proyecto son obligatorios.");
        }

        $asignadoFinal = null;

        if ($usuarioActual->rol_id == 3) {
            // El usuario se auto-asigna la tarea
            $asignadoFinal = $usuarioActual->usuario_id;
        } else {
            // Admin y PM pueden asignar libremente
            $asignadoFinal = isset($datos['usuario_asignado']) ? $datos['usuario_asignado'] : null;
        }

        // Crea la entidad tarea
        $tarea = new Tarea();
        $tarea->tarea_titulo = $datos['titulo'];
        $tarea->tarea_descripcion = isset($datos['descripcion']) ? $datos['descripcion'] : '';
        $tarea->fecha_limite = isset($datos['fecha_limite']) ? $datos['fecha_limite'] : null;
        $tarea->prioridad_id = isset($datos['prioridad_id']) ? $datos['prioridad_id'] : 2;
        $tarea->estado_id = 1;
        $tarea->proyecto_id = $datos['proyecto_id'];
        $tarea->categoria_id = isset($datos['categoria_id']) ? $datos['categoria_id'] : null;
        $tarea->usuario_asignado = $asignadoFinal;
        $tarea->usuario_creador = $usuarioActual->usuario_id;

        return $this->tareaRepository->crear($tarea);
    }

    public function editarTarea($id, $datos, $usuarioActual)
    {
        $tareaActual = $this->tareaRepository->obtenerPorId($id);
        if (!$tareaActual) {
            throw new Exception("La tarea no existe.");
        }

        // El usuario normal solo puede modificar tareas propias
        if ($usuarioActual->rol_id == 3) {
            $soyElAsignado = $tareaActual->usuario_asignado == $usuarioActual->usuario_id;
            $soyElCreador = $tareaActual->usuario_creador == $usuarioActual->usuario_id;

            if (!$soyElAsignado && !$soyElCreador) {
                throw new Exception("No tienes permiso para editar esta tarea.");
            }
        }

        // Solo Admin o PM pueden reasignar tareas
        if ($usuarioActual->rol_id != 3) {
            if (array_key_exists('usuario_asignado', $datos)) {
                $tareaActual->usuario_asignado = $datos['usuario_asignado'];
            }
        }

        // Actualiza solo los campos enviados
        $tareaActual->tarea_titulo = isset($datos['titulo']) ? $datos['titulo'] : $tareaActual->tarea_titulo;
        $tareaActual->tarea_descripcion = isset($datos['descripcion']) ? $datos['descripcion'] : $tareaActual->tarea_descripcion;
        $tareaActual->fecha_limite = isset($datos['fecha_limite']) ? $datos['fecha_limite'] : $tareaActual->fecha_limite;
        $tareaActual->prioridad_id = isset($datos['prioridad_id']) ? $datos['prioridad_id'] : $tareaActual->prioridad_id;
        $tareaActual->estado_id = isset($datos['estado_id']) ? $datos['estado_id'] : $tareaActual->estado_id;
        $tareaActual->usuario_asignado = isset($datos['usuario_asignado']) ? $datos['usuario_asignado'] : $tareaActual->usuario_asignado;

        return $this->tareaRepository->actualizar($tareaActual);
    }

    public function eliminarTarea($id, $usuarioActual)
    {
        $tarea = $this->tareaRepository->obtenerPorId($id);

        if (!$tarea) {
            throw new Exception("La tarea no existe o ya fue eliminada.");
        }

        // El usuario normal solo puede borrar tareas propias o asignadas
        if ($usuarioActual->rol_id == 3) {
            if (
                $tarea->usuario_creador != $usuarioActual->usuario_id &&
                $tarea->usuario_asignado != $usuarioActual->usuario_id
            ) {
                throw new Exception("No tienes permiso para eliminar esta tarea.");
            }
        }

        return $this->tareaRepository->eliminar($id);
    }
}
