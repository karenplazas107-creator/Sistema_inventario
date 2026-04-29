<?php
session_start();

require_once __DIR__ . '/../config/database.php';

class AuthController {

    public function login() {

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: ../views/usuarios/login.php");
            exit;
        }

        $email    = trim($_POST['email']    ?? '');
        $password = trim($_POST['password'] ?? '');

        // Validar campos vacíos
        if (empty($email) || empty($password)) {
            $_SESSION['alert'] = [
                'icon'  => 'warning',
                'title' => 'Campos incompletos',
                'text'  => 'Debe ingresar correo y contraseña'
            ];
            header("Location: ../views/usuarios/login.php");
            exit;
        }

        $database = new Database();
        $db = $database->conectar();

        // CONSULTA  ← columna con ñ entre backticks
        $sql = "SELECT 
                    u.id_usuario,
                    u.`contraseña`,
                    p.nombre,
                    p.correo,
                    r.id_rol,
                    r.nombre_rol
                FROM Usuario u
                INNER JOIN Persona p ON u.id_persona = p.id_persona
                INNER JOIN Rol r     ON u.id_rol     = r.id_rol
                WHERE p.correo = :email
                LIMIT 1";

        $stmt = $db->prepare($sql);
        $stmt->bindParam(":email", $email);
        $stmt->execute();

        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

        // Usuario no existe
        if (!$usuario) {
            $_SESSION['alert'] = [
                'icon'  => 'error',
                'title' => 'Usuario no encontrado',
                'text'  => 'El correo no está registrado'
            ];
            header("Location: ../views/usuarios/login.php");
            exit;
        }

        // Verificar contraseña  ← usa la clave del array con ñ
        if (!password_verify($password, $usuario['contraseña'])) {
            $_SESSION['alert'] = [
                'icon'  => 'error',
                'title' => 'Contraseña incorrecta',
                'text'  => 'Verifique sus credenciales'
            ];
            header("Location: ../views/usuarios/login.php");
            exit;
        }

        // Regenerar sesión por seguridad
        session_regenerate_id(true);

        // Guardar datos en sesión
        $_SESSION['usuario'] = [
            'id_usuario' => $usuario['id_usuario'],
            'nombre'     => $usuario['nombre'],
            'correo'     => $usuario['correo'],
            'rol_id'     => $usuario['id_rol'],
            'rol'        => $usuario['nombre_rol']
        ];

        // Redirección por rol
        if ($usuario['id_rol'] == 1) {
            header("Location: ../views/dashboard/admin.php");
        } else if ($usuario['id_rol'] == 2) {
            header("Location: ../views/dashboard/index.php");
        } else {
            header("Location: ../views/dashboard/index.php");
        }

        exit;
    }

    public function logout() {
        session_unset();
        session_destroy();
        header("Location: ../views/usuarios/login.php");
        exit;
    }
}

// Instanciar controlador
$controller = new AuthController();

$accion = $_GET['accion'] ?? 'login';

if ($accion === 'logout') {
    $controller->logout();
} else {
    $controller->login();
}
?>