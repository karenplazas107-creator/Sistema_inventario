<?php
session_start();

require_once __DIR__ . '/../config/database.php'; 
require_once __DIR__ . '/../models/usuario.php';

class AdminUsuarioController {

    private $usuario;

    public function __construct() {
        $db = (new Database())->conectar();
        $this->usuario = new Usuario($db);
    }

    // ELIMINAR USUARIO
    public function eliminar() {
        $id = $_GET['id'] ?? null;

        if (!$id) {
            $_SESSION['alert'] = ['icon'=>'error','title'=>'Error','text'=>'ID inválido'];
            header("Location: ../views/dashboard/admin.php");
            exit;
        }

        $resultado = $this->usuario->eliminarCompleto($id);

        $_SESSION['alert'] = $resultado === true
            ? ['icon'=>'success','title'=>'Eliminado','text'=>'Usuario eliminado']
            : ['icon'=>'error','title'=>'Error','text'=>$resultado];

        header("Location: ../views/dashboard/admin.php");
        exit;
    }

    // EDITAR
    public function editar() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: ../views/dashboard/admin.php");
            exit;
        }

        $id        = $_POST['id_usuario'];
        $nombres   = $_POST['nombres'];
        $apellidos = $_POST['apellidos'];
        $movil     = $_POST['movil'];
        $rol       = $_POST['rol'];

        $resultado = $this->usuario->editarCompleto($id, $nombres, $apellidos, $movil, $rol);

        $_SESSION['alert'] = $resultado === true
            ? ['icon'=>'success','title'=>'Actualizado','text'=>'Usuario actualizado']
            : ['icon'=>'error','title'=>'Error','text'=>$resultado];

        header("Location: ../views/dashboard/admin.php");
        exit;
    }

    // ACTIVAR / DESACTIVAR
    public function toggleEstado() {
        $id     = $_GET['id']     ?? null;
        $estado = $_GET['estado'] ?? null;

        if (!$id || $estado === null) {
            $_SESSION['alert'] = ['icon'=>'error','title'=>'Error','text'=>'Parámetros inválidos'];
            header("Location: ../views/dashboard/admin.php");
            exit;
        }

        $nuevo_estado = $estado == 1 ? 0 : 1;
        $resultado = $this->usuario->cambiarEstado($id, $nuevo_estado);

        $_SESSION['alert'] = $resultado === true
            ? ['icon'=>'success','title'=>'Éxito','text'=>$nuevo_estado == 1 ? 'Usuario activado' : 'Usuario desactivado']
            : ['icon'=>'error','title'=>'Error','text'=>$resultado];

        header("Location: ../views/dashboard/admin.php");
        exit;
    }
}

// RUTAS
$controller = new AdminUsuarioController();
$accion = $_GET['accion'] ?? '';

switch ($accion) {
    case 'eliminar':
        $controller->eliminar();
        break;

    case 'editar':
        $controller->editar();
        break;

    case 'toggleEstado':
        $controller->toggleEstado();
        break;

    default:
        header("Location: ../views/dashboard/admin.php");
        exit;
}