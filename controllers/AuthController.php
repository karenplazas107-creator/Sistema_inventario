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

        // CONSULTA
        $sql = "SELECT * FROM usuarios WHERE email = :email LIMIT 1";

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

        // Verificar contraseña
        if (!password_verify($password, $usuario['password'])) {
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
            'id_usuario' => $usuario['id'],
            'nombres'    => $usuario['nombres'],
            'apellidos'  => $usuario['apellidos'],
            'email'      => $usuario['email'],
            'rol'        => $usuario['rol']
        ];

        // Redirección por rol
        $rol_str = strtolower($usuario['rol']);
        if ($rol_str === 'administrador') {
            header("Location: ../views/dashboard/admin.php");
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