<?php

namespace App\Entities;

class Proyecto
{
    public $proyecto_id;
    public $proyecto_nombre;
    public $proyecto_descripcion;
    public $usuario_creador;
    public $proyecto_estado;
    public $fecha_inicio;
    public $fecha_creacion;
    public $nombre_creador;

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

    public function toArray()
    {
        return [
            'id' => $this->proyecto_id,
            'nombre' => $this->proyecto_nombre,
            'descripcion' => $this->proyecto_descripcion,
            'creador_id' => $this->usuario_creador,
            'creador_nombre' => $this->nombre_creador, //Desencriptado
            'estado' => $this->proyecto_estado == 1 ? 'Activo' : 'Eliminado',
            'fecha_inicio' => $this->fecha_inicio,
            'fecha_creacion' => $this->fecha_creacion
        ];
    }
}