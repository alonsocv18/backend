<?php

namespace App\Repositories;

use App\Config\Database;
use App\Entities\Proyecto;
use PDO;

class ProyectoRepository
{
    private $conn;

    public function __construct()
    {
        $this->conn = Database::getInstance()->getConnection();
    }

    public function listar()
    {
        // Se trae el nombre encriptado del creador 
        $sql = "SELECT p.*, u.usuario_nombre as nombre_creador 
                FROM proyectos p
                LEFT JOIN usuarios u ON p.usuario_creador = u.usuario_id
                WHERE p.proyecto_estado = 1 
                ORDER BY p.fecha_creacion DESC";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute();

        $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $proyectos = [];
        foreach ($resultados as $fila) {
            $proyectos[] = new Proyecto($fila);
        }

        return $proyectos;
    }

    /**
     * Listar solo proyectos donde el usuario tiene tareas asignadas
     * Usado para usuarios con rol 3 (Usuario Normal)
     */
    public function listarPorUsuario($usuarioId)
    {
        $sql = "SELECT DISTINCT p.*, u.usuario_nombre as nombre_creador 
                FROM proyectos p
                LEFT JOIN usuarios u ON p.usuario_creador = u.usuario_id
                INNER JOIN tareas t ON t.proyecto_id = p.proyecto_id
                WHERE p.proyecto_estado = 1 
                AND t.usuario_asignado = :usuario_id
                AND t.fecha_eliminacion IS NULL
                ORDER BY p.fecha_creacion DESC";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':usuario_id', $usuarioId);
        $stmt->execute();

        $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $proyectos = [];
        foreach ($resultados as $fila) {
            $proyectos[] = new Proyecto($fila);
        }

        return $proyectos;
    }

    public function obtenerPorId($id)
    {
        $sql = "SELECT * FROM proyectos WHERE proyecto_id = :id AND proyecto_estado = 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id', $id);
        $stmt->execute();

        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        return $data ? new Proyecto($data) : null;
    }

    public function crear(Proyecto $proyecto)
    {
        $sql = "INSERT INTO proyectos 
                (proyecto_nombre, proyecto_descripcion, usuario_creador, proyecto_estado, fecha_inicio, fecha_creacion)
                VALUES 
                (:nombre, :desc, :creador, 1, :inicio, NOW())";

        $stmt = $this->conn->prepare($sql);

        $stmt->bindParam(':nombre', $proyecto->proyecto_nombre);
        $stmt->bindParam(':desc', $proyecto->proyecto_descripcion);
        $stmt->bindParam(':creador', $proyecto->usuario_creador);
        $stmt->bindParam(':inicio', $proyecto->fecha_inicio);

        if ($stmt->execute()) {
            return $this->conn->lastInsertId();
        }
        return false;
    }

    public function actualizar(Proyecto $proyecto)
    {
        $sql = "UPDATE proyectos SET 
                    proyecto_nombre = :nombre, 
                    proyecto_descripcion = :desc,
                    fecha_inicio = :inicio
                WHERE proyecto_id = :id";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':nombre', $proyecto->proyecto_nombre);
        $stmt->bindParam(':desc', $proyecto->proyecto_descripcion);
        $stmt->bindParam(':inicio', $proyecto->fecha_inicio);
        $stmt->bindParam(':id', $proyecto->proyecto_id);

        return $stmt->execute();
    }

    public function eliminar($id)
    {
        // Soft Delete: Cambiamos estado a 0
        $sql = "UPDATE proyectos SET proyecto_estado = 0 WHERE proyecto_id = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }
}