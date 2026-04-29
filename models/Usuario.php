<?php
class Usuario {

    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    // LISTAR USUARIOS
    public function obtenerTodos() {
        $sql = "SELECT 
                    u.id_usuario,
                    p.nombre,
                    p.correo,
                    r.nombre_rol
                FROM Usuario u
                INNER JOIN Persona p ON u.id_persona = p.id_persona
                INNER JOIN Rol r ON u.id_rol = r.id_rol";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // VALIDAR SI EL CORREO EXISTE
    public function existeCorreo($correo) {
        $sql = "SELECT id_persona FROM Persona WHERE correo = :correo LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(":correo", $correo);
        $stmt->execute();

        return $stmt->rowCount() > 0;
    }

    // OBTENER USUARIO PARA LOGIN
    public function obtenerPorCorreo($correo) {
        $sql = "SELECT u.*, p.nombre, p.correo, r.nombre_rol
                FROM Usuario u
                INNER JOIN Persona p ON u.id_persona = p.id_persona
                INNER JOIN Rol r ON u.id_rol = r.id_rol
                WHERE p.correo = :correo
                LIMIT 1";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(":correo", $correo);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // REGISTRAR USUARIO
    public function registrar($datos) {
        try {
            $this->conn->beginTransaction();

            // 1) INSERTAR PERSONA
            $sqlPersona = "INSERT INTO Persona (nombre, telefono, correo)
                           VALUES (:nombre, :telefono, :correo)";
            $stmtPersona = $this->conn->prepare($sqlPersona);
            $stmtPersona->bindParam(":nombre",    $datos['nombre']);
            $stmtPersona->bindParam(":telefono",  $datos['telefono']);
            $stmtPersona->bindParam(":correo",    $datos['correo']);
            $stmtPersona->execute();

            $id_persona = $this->conn->lastInsertId();

            // 2) INSERTAR USUARIO  ← columna con ñ entre backticks
            $sqlUsuario = "INSERT INTO Usuario (`contraseña`, id_persona, id_rol)
                           VALUES (:contrasena, :id_persona, :id_rol)";
            $stmtUsuario = $this->conn->prepare($sqlUsuario);
            $stmtUsuario->bindParam(":contrasena", $datos['contrasena']);
            $stmtUsuario->bindParam(":id_persona", $id_persona);
            $stmtUsuario->bindParam(":id_rol",     $datos['rol']);
            $stmtUsuario->execute();

            $this->conn->commit();
            return true;

        } catch (Exception $e) {
            if ($this->conn->inTransaction()) {
                $this->conn->rollBack();
            }
            return "Error al registrar: " . $e->getMessage();
        }
    }

    // ELIMINAR USUARIO Y PERSONA
    public function eliminarCompleto($id_usuario) {
        try {
            $this->conn->beginTransaction();

            $stmt = $this->conn->prepare("SELECT id_persona FROM Usuario WHERE id_usuario = ?");
            $stmt->execute([$id_usuario]);
            $data = $stmt->fetch();

            if (!$data) return "Usuario no encontrado";

            $id_persona = $data['id_persona'];

            $this->conn->prepare("DELETE FROM Usuario WHERE id_usuario = ?")->execute([$id_usuario]);
            $this->conn->prepare("DELETE FROM Persona WHERE id_persona = ?")->execute([$id_persona]);

            $this->conn->commit();
            return true;

        } catch (Exception $e) {
            $this->conn->rollBack();
            return $e->getMessage();
        }
    }

    // EDITAR USUARIO
    public function editarCompleto($id, $nombre, $telefono, $rol) {
        try {
            $this->conn->beginTransaction();

            $stmt = $this->conn->prepare("SELECT id_persona FROM Usuario WHERE id_usuario = ?");
            $stmt->execute([$id]);
            $data = $stmt->fetch();

            $id_persona = $data['id_persona'];

            $this->conn->prepare("
                UPDATE Persona SET nombre = ?, telefono = ? WHERE id_persona = ?
            ")->execute([$nombre, $telefono, $id_persona]);

            $this->conn->prepare("
                UPDATE Usuario SET id_rol = ? WHERE id_usuario = ?
            ")->execute([$rol, $id]);

            $this->conn->commit();
            return true;

        } catch (Exception $e) {
            $this->conn->rollBack();
            return $e->getMessage();
        }
    }
}
?>