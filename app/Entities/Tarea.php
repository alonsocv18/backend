<?php

namespace App\Entities;

class Tarea
{
    public $tarea_id;
    public $tarea_titulo;
    public $tarea_descripcion;
    public $fecha_limite;
    public $prioridad_id;
    public $estado_id;
    public $proyecto_id;
    public $categoria_id;
    public $usuario_asignado;
    public $usuario_creador;
    public $fecha_creacion;
    public $fecha_eliminacion;
    public $nombre_proyecto;
    public $nombre_estado;
    public $nombre_prioridad;
    public $nombre_asignado; 
    public $color_prioridad; 

    public function __construct($data = [])
    {
        if (!empty($data)) {
            foreach ($data as $key => $value) {
                if (property_exists($this, $key)) {
                    $this->$key = $value;
                }
            }
        }
    }

    // Verifica si la tarea está eliminada (Soft Delete)
    public function estaEliminada()
    {
        return !empty($this->fecha_eliminacion);
    }

    public function toArray()
    {
        return [
            'id'            => $this->tarea_id,
            'titulo'        => $this->tarea_titulo,
            'descripcion'   => $this->tarea_descripcion,
            'fecha_limite'  => $this->fecha_limite,
            'proyecto'      => $this->nombre_proyecto,
            'proyecto_id'   => $this->proyecto_id,
            'estado'        => $this->nombre_estado,
            'estado_id'     => $this->estado_id,
            'prioridad'     => $this->nombre_prioridad,
            'prioridad_color' => $this->color_prioridad,
            'usuario_asignado_nombre' => $this->nombre_asignado,
            'fecha_creacion'=> $this->fecha_creacion,
            // Si está eliminada, podemos decidir no enviarla o enviarla con flag
            'eliminada'     => $this->estaEliminada()
        ];
    }
}