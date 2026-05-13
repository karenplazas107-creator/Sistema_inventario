<?php
class Proveedor {
    private $conn;
    private $table = "proveedores";

    public function __construct($db) {
        $this->conn = $db;
    }

    public function obtenerTodos() {
        $stmt = $this->conn->prepare(
            "SELECT * FROM {$this->table} ORDER BY nombre ASC"
        );
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerPorId($id) {
        $stmt = $this->conn->prepare(
            "SELECT * FROM {$this->table} WHERE id = :id"
        );
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function crear($nombre, $telefono, $email, $direccion) {
        $stmt = $this->conn->prepare(
            "INSERT INTO {$this->table} (nombre, telefono, email, direccion)
             VALUES (:nombre, :telefono, :email, :direccion)"
        );
        $stmt->bindParam(':nombre',    $nombre);
        $stmt->bindParam(':telefono',  $telefono);
        $stmt->bindParam(':email',     $email);
        $stmt->bindParam(':direccion', $direccion);
        return $stmt->execute();
    }

    public function editar($id, $nombre, $telefono, $email, $direccion) {
        $stmt = $this->conn->prepare(
            "UPDATE {$this->table}
             SET nombre = :nombre, telefono = :telefono,
                 email = :email, direccion = :direccion
             WHERE id = :id"
        );
        $stmt->bindParam(':nombre',    $nombre);
        $stmt->bindParam(':telefono',  $telefono);
        $stmt->bindParam(':email',     $email);
        $stmt->bindParam(':direccion', $direccion);
        $stmt->bindParam(':id',        $id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function eliminar($id) {
        try {
            $stmt = $this->conn->prepare(
                "DELETE FROM {$this->table} WHERE id = :id"
            );
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            return false; // tiene compras asociadas
        }
    }

    public function existeEmail($email, $excludeId = null) {
        $sql = "SELECT id FROM {$this->table} WHERE email = :email";
        if ($excludeId) $sql .= " AND id != :eid";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':email', $email);
        if ($excludeId) $stmt->bindParam(':eid', $excludeId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->rowCount() > 0;
    }
}
?>
