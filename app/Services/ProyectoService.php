<?php

namespace App\Services;

use App\Repositories\ProyectoRepository;
use App\Entities\Proyecto;
use App\Utils\Crypto;
use App\Validators\ProyectoValidator;
use Exception;

class ProyectoService
{
    private $proyectoRepository;

    public function __construct()
    {
        $this->proyectoRepository = new ProyectoRepository();
    }

    /**
     * Listar proyectos según el rol del usuario
     */
    public function listarProyectos($usuarioActual = null)
    {
        // Si es usuario normal (rol 3), solo ver proyectos donde tenga tareas asignadas
        if ($usuarioActual && $usuarioActual->rol_id == 3) {
            $proyectos = $this->proyectoRepository->listarPorUsuario(
                $usuarioActual->usuario_id
            );
        } else {
            $proyectos = $this->proyectoRepository->listar();
        }

        foreach ($proyectos as $p) {
            if (!empty($p->nombre_creador)) {
                $p->nombre_creador = Crypto::desencriptar($p->nombre_creador);
            }
        }

        return $proyectos;
    }

    /**
     * Obtener un proyecto por su ID
     */
    public function obtenerProyectoPorId($id)
    {
        $proyecto = $this->proyectoRepository->obtenerPorId($id);

        if (!$proyecto) {
            throw new Exception("El proyecto no existe.");
        }
        return $proyecto;
    }

    /**
     * Crear un nuevo proyecto
     */
    public function crearProyecto($datos, $usuarioActual)
    {
        if ($usuarioActual->rol_id == 3) {
            throw new Exception("No tienes permisos para crear proyectos.");
        }

        ProyectoValidator::validarCreacion($datos);

        $proyecto = new Proyecto();
        $proyecto->proyecto_nombre = $datos["nombre"];
        $proyecto->proyecto_descripcion = isset($datos["descripcion"])
            ? $datos["descripcion"]
            : "";
        $proyecto->sucursal_id = $datos["sucursal_id"];
        $proyecto->estado_id = isset($datos["estado_id"])
            ? $datos["estado_id"]
            : 1; // Default: Planificación
        $proyecto->usuario_creador = $usuarioActual->usuario_id;
        $proyecto->fecha_inicio = isset($datos["fecha_inicio"])
            ? $datos["fecha_inicio"]
            : date("Y-m-d");
        $proyecto->fecha_fin = isset($datos["fecha_fin"])
            ? $datos["fecha_fin"]
            : null;

        return $this->proyectoRepository->crear($proyecto);
    }

    /**
     * Editar un proyecto existente
     */
    public function editarProyecto($id, $datos, $usuarioActual)
    {
        if ($usuarioActual->rol_id == 3) {
            throw new Exception("No tienes permisos para editar proyectos.");
        }

        ProyectoValidator::validarEdicion($datos);

        $proyecto = $this->proyectoRepository->obtenerPorId($id);
        if (!$proyecto) {
            throw new Exception("El proyecto no existe.");
        }

        $proyecto->proyecto_nombre = isset($datos["nombre"])
            ? $datos["nombre"]
            : $proyecto->proyecto_nombre;
        $proyecto->proyecto_descripcion = isset($datos["descripcion"])
            ? $datos["descripcion"]
            : $proyecto->proyecto_descripcion;
        $proyecto->sucursal_id = isset($datos["sucursal_id"])
            ? $datos["sucursal_id"]
            : $proyecto->sucursal_id;
        $proyecto->estado_id = isset($datos["estado_id"])
            ? $datos["estado_id"]
            : $proyecto->estado_id;
        $proyecto->fecha_inicio = isset($datos["fecha_inicio"])
            ? $datos["fecha_inicio"]
            : $proyecto->fecha_inicio;
        $proyecto->fecha_fin = isset($datos["fecha_fin"])
            ? $datos["fecha_fin"]
            : $proyecto->fecha_fin;

        return $this->proyectoRepository->actualizar($proyecto);
    }

    /**
     * Eliminar un proyecto (Soft Delete)
     */
    public function eliminarProyecto($id, $usuarioActual)
    {
        if ($usuarioActual->rol_id == 3) {
            throw new Exception("No tienes permisos para eliminar proyectos.");
        }

        return $this->proyectoRepository->eliminar($id);
    }
}
