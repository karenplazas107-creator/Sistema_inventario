<?php
class Usuario {

    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    // LISTAR EMPLEADOS (Excluir Compradores)
    public function obtenerTodos() {
        $sql = "SELECT id, rol, nombres, apellidos, movil, email, created_at FROM usuarios WHERE rol != 'Comprador'";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // LISTAR CLIENTES (Solo Compradores)
    public function obtenerClientes() {
        $sql = "SELECT id, rol, nombres, apellidos, movil, email, created_at FROM usuarios WHERE rol = 'Comprador' ORDER BY nombres ASC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // VALIDAR SI EL CORREO EXISTE
    public function existeCorreo($email) {
        $sql = "SELECT id FROM usuarios WHERE email = :email LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(":email", $email);
        $stmt->execute();
        return $stmt->rowCount() > 0;
    }

    // OBTENER USUARIO PARA LOGIN
    public function obtenerPorCorreo($email) {
        $sql = "SELECT * FROM usuarios WHERE email = :email LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(":email", $email);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // REGISTRAR USUARIO
    public function registrar($datos) {
        try {
            $sql = "INSERT INTO usuarios (rol, nombres, apellidos, movil, email, password)
                    VALUES (:rol, :nombres, :apellidos, :movil, :email, :password)";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(":rol",       $datos['rol']);
            $stmt->bindParam(":nombres",   $datos['nombres']);
            $stmt->bindParam(":apellidos", $datos['apellidos']);
            $stmt->bindParam(":movil",     $datos['movil']);
            $stmt->bindParam(":email",     $datos['email']);
            $stmt->bindParam(":password",  $datos['password']);
            $stmt->execute();
            return true;
        } catch (Exception $e) {
            return "Error al registrar: " . $e->getMessage();
        }
    }

    // ELIMINAR USUARIO
    public function eliminarCompleto($id) {
        try {
            $stmt = $this->conn->prepare("DELETE FROM usuarios WHERE id = ?");
            $stmt->execute([$id]);
            return true;
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    // EDITAR USUARIO
    public function editarCompleto($id, $nombres, $apellidos, $movil, $rol) {
        try {
            $stmt = $this->conn->prepare("UPDATE usuarios SET nombres = ?, apellidos = ?, movil = ?, rol = ? WHERE id = ?");
            $stmt->execute([$nombres, $apellidos, $movil, $rol, $id]);
            return true;
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }
}
?>