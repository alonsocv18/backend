<?php

namespace App\Services;

use App\Repositories\ProyectoRepository;
use App\Entities\Proyecto;
use App\Utils\Crypto;
use Exception;

class ProyectoService
{
    private $proyectoRepository;

    public function __construct()
    {
        $this->proyectoRepository = new ProyectoRepository();
    }

    public function listarProyectos()
    {
        // Todos los roles pueden ver proyectos (para poder asignarse tareas)
        $proyectos = $this->proyectoRepository->listar();

        // Desencriptar nombres de creadores para mostrarlos bonitos
        foreach ($proyectos as $p) {
            if (!empty($p->nombre_creador)) {
                $p->nombre_creador = Crypto::desencriptar($p->nombre_creador);
            }
        }

        return $proyectos;
    }

    public function crearProyecto($datos, $usuarioActual)
    {
        // Solo Rol 1 (Admin) y 2 (PM) pueden crear
        if ($usuarioActual->rol_id == 3) {
            throw new Exception("No tienes permisos para crear proyectos.");
        }

        if (empty($datos['nombre'])) {
            throw new Exception("El nombre del proyecto es obligatorio.");
        }

        $proyecto = new Proyecto();
        $proyecto->proyecto_nombre = $datos['nombre'];
        $proyecto->proyecto_descripcion = isset($datos['descripcion']) ? $datos['descripcion'] : '';
        $proyecto->usuario_creador = $usuarioActual->usuario_id;
        $proyecto->fecha_inicio = isset($datos['fecha_inicio']) ? $datos['fecha_inicio'] : date('Y-m-d');

        return $this->proyectoRepository->crear($proyecto);
    }

    public function editarProyecto($id, $datos, $usuarioActual)
    {
        // Solo Rol 1 (Admin) y 2 (PM) pueden editar
        if ($usuarioActual->rol_id == 3) {
            throw new Exception("No tienes permisos para editar proyectos.");
        }

        $proyecto = $this->proyectoRepository->obtenerPorId($id);
        if (!$proyecto) {
            throw new Exception("El proyecto no existe.");
        }

        // Actualizar campos
        $proyecto->proyecto_nombre = isset($datos['nombre']) ? $datos['nombre'] : $proyecto->proyecto_nombre;
        $proyecto->proyecto_descripcion = isset($datos['descripcion']) ? $datos['descripcion'] : $proyecto->proyecto_descripcion;
        $proyecto->fecha_inicio = isset($datos['fecha_inicio']) ? $datos['fecha_inicio'] : $proyecto->fecha_inicio;

        return $this->proyectoRepository->actualizar($proyecto);
    }

    public function eliminarProyecto($id, $usuarioActual)
    {
        // Solo Rol 1 (Admin) y 2 (PM) pueden eliminar
        if ($usuarioActual->rol_id == 3) {
            throw new Exception("No tienes permisos para eliminar proyectos.");
        }

        return $this->proyectoRepository->eliminar($id);
    }
}