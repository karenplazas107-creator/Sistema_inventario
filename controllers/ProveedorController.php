<?php
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Proveedor.php';

if (!isset($_SESSION['usuario'])) {
    header("Location: ../views/usuarios/login.php");
    exit;
}

$rol         = $_SESSION['usuario']['rol'];
$puedeEditar = in_array($rol, ['Administrador', 'Bodeguero']);

if (!$puedeEditar) {
    $_SESSION['alert'] = ['icon'=>'error','title'=>'Sin permiso','text'=>'No tienes permisos para gestionar proveedores.'];
    header("Location: ../views/proveedores/index.php");
    exit;
}

$database = new Database();
$db       = $database->conectar();
$model    = new Proveedor($db);
$accion   = $_GET['accion'] ?? '';

// ── POST ──────────────────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nombre    = trim($_POST['nombre']    ?? '');
    $telefono  = trim($_POST['telefono']  ?? '');
    $email     = trim($_POST['email']     ?? '');
    $direccion = trim($_POST['direccion'] ?? '');

    if (empty($nombre)) {
        $_SESSION['alert'] = ['icon'=>'warning','title'=>'Campo requerido','text'=>'El nombre del proveedor es obligatorio.'];
        header("Location: ../views/proveedores/index.php"); exit;
    }

    if ($accion === 'crear') {
        if (!empty($email) && $model->existeEmail($email)) {
            $_SESSION['alert'] = ['icon'=>'warning','title'=>'Email duplicado','text'=>'Ya existe un proveedor con ese correo.'];
            header("Location: ../views/proveedores/index.php"); exit;
        }
        if ($model->crear($nombre, $telefono, $email, $direccion)) {
            $_SESSION['alert'] = ['icon'=>'success','title'=>'Proveedor creado','text'=>"\"$nombre\" fue agregado correctamente."];
        } else {
            $_SESSION['alert'] = ['icon'=>'error','title'=>'Error','text'=>'No se pudo crear el proveedor.'];
        }
        header("Location: ../views/proveedores/index.php"); exit;
    }

    if ($accion === 'editar') {
        $id = intval($_POST['id'] ?? 0);
        if (!empty($email) && $model->existeEmail($email, $id)) {
            $_SESSION['alert'] = ['icon'=>'warning','title'=>'Email duplicado','text'=>'Ya existe otro proveedor con ese correo.'];
            header("Location: ../views/proveedores/index.php"); exit;
        }
        if ($model->editar($id, $nombre, $telefono, $email, $direccion)) {
            $_SESSION['alert'] = ['icon'=>'success','title'=>'Actualizado','text'=>'Proveedor actualizado correctamente.'];
        } else {
            $_SESSION['alert'] = ['icon'=>'error','title'=>'Error','text'=>'No se pudo actualizar el proveedor.'];
        }
        header("Location: ../views/proveedores/index.php"); exit;
    }
}

// ── GET ───────────────────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'GET' && $accion === 'eliminar') {
    $id = intval($_GET['id'] ?? 0);
    if ($model->eliminar($id)) {
        $_SESSION['alert'] = ['icon'=>'success','title'=>'Eliminado','text'=>'Proveedor eliminado correctamente.'];
    } else {
        $_SESSION['alert'] = ['icon'=>'error','title'=>'No se puede eliminar','text'=>'Este proveedor tiene compras asociadas.'];
    }
    header("Location: ../views/proveedores/index.php"); exit;
}

header("Location: ../views/proveedores/index.php");
exit;
?>
