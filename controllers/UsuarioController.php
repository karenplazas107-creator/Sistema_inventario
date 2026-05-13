<?php
session_start();

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Usuario.php';

class UsuarioController {

    public function registrar() {

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: ../views/usuarios/registre.php");
            exit;
        }

        $nombres            = trim($_POST['nombres']            ?? '');
        $apellidos          = trim($_POST['apellidos']          ?? '');
        $movil              = trim($_POST['movil']              ?? '');
        $email              = trim($_POST['email']              ?? '');
        $password           = trim($_POST['password']           ?? '');
        $confirmar_password = trim($_POST['confirmar_password'] ?? '');
        $desde_admin        = isset($_POST['desde_admin']) && $_POST['desde_admin'] == '1';
        $rol                = $desde_admin ? trim($_POST['rol'] ?? 'Vendedor') : 'Comprador';

        // VALIDACIONES
        if (empty($nombres) || empty($apellidos) || empty($email) || empty($password) || empty($confirmar_password)) {
            $_SESSION['alert'] = [
                'icon'  => 'warning',
                'title' => 'Campos incompletos',
                'text'  => 'Debe completar todos los campos'
            ];
            header($desde_admin
                ? "Location: ../views/dashboard/admin.php"
                : "Location: ../views/usuarios/registre.php"
            );
            exit;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['alert'] = [
                'icon'  => 'error',
                'title' => 'Correo inválido',
                'text'  => 'Ingrese un correo válido'
            ];
            header($desde_admin
                ? "Location: ../views/dashboard/admin.php"
                : "Location: ../views/usuarios/registre.php"
            );
            exit;
        }

        if ($password !== $confirmar_password) {
            $_SESSION['alert'] = [
                'icon'  => 'error',
                'title' => 'Error',
                'text'  => 'Las contraseñas no coinciden'
            ];
            header($desde_admin
                ? "Location: ../views/dashboard/admin.php"
                : "Location: ../views/usuarios/registre.php"
            );
            exit;
        }

        if (strlen($password) < 6) {
            $_SESSION['alert'] = [
                'icon'  => 'warning',
                'title' => 'Contraseña inválida',
                'text'  => 'Mínimo 6 caracteres'
            ];
            header($desde_admin
                ? "Location: ../views/dashboard/admin.php"
                : "Location: ../views/usuarios/registre.php"
            );
            exit;
        }

        $database = new Database();
        $db = $database->conectar();
        $usuario = new Usuario($db);

        if ($usuario->existeCorreo($email)) {
            $_SESSION['alert'] = [
                'icon'  => 'error',
                'title' => 'Correo existente',
                'text'  => 'Este correo ya está registrado'
            ];
            header($desde_admin
                ? "Location: ../views/dashboard/admin.php"
                : "Location: ../views/usuarios/registre.php"
            );
            exit;
        }

        $passwordHash = password_hash($password, PASSWORD_DEFAULT);

        $datos = [
            'nombres'    => $nombres,
            'apellidos'  => $apellidos,
            'movil'      => $movil,
            'email'      => $email,
            'password'   => $passwordHash,
            'rol'        => $rol
        ];

        $resultado = $usuario->registrar($datos);

        if ($resultado === true) {
            $_SESSION['alert'] = [
                'icon'  => 'success',
                'title' => 'Registro exitoso',
                'text'  => 'Usuario creado correctamente'
            ];
        } else {
            $_SESSION['alert'] = [
                'icon'  => 'error',
                'title' => 'Error',
                'text'  => $resultado
            ];
        }

        header($desde_admin
            ? "Location: ../views/dashboard/admin.php"
            : "Location: ../views/usuarios/registre.php"
        );
        exit;
    }
}

$controller = new UsuarioController();
$controller->registrar();
?>