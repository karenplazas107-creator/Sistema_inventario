<?php
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Venta.php';

if (!isset($_SESSION['usuario'])) {
    header("Location: ../views/usuarios/login.php");
    exit;
}

$database = new Database();
$db = $database->conectar();
$ventaModel = new Venta($db);

$accion = $_GET['accion'] ?? '';
$rol = $_SESSION['usuario']['rol'];

// Permisos para editar/eliminar (solo Admin y Vendedor)
$puedeEditar = in_array($rol, ['Administrador', 'Vendedor']);
// Permisos para registrar (Admin, Vendedor, Bodeguero)
$puedeRegistrar = in_array($rol, ['Administrador', 'Vendedor', 'Bodeguero']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($accion === 'crear') {
        if (!$puedeRegistrar) {
            $_SESSION['alert'] = ['icon' => 'error', 'title' => 'Acceso Denegado', 'text' => 'No tienes permisos para registrar ventas.'];
            header("Location: ../views/ventas/index.php");
            exit;
        }

        $usuario_id = $_SESSION['usuario']['id']; // El empleado que registra la venta
        $productos = $_POST['productos'] ?? [];
        $cantidades = $_POST['cantidades'] ?? [];
        $precios = $_POST['precios'] ?? [];
        
        $total = 0;
        $detalles = [];

        for ($i = 0; $i < count($productos); $i++) {
            if ($cantidades[$i] > 0) {
                $subtotal = $cantidades[$i] * $precios[$i];
                $total += $subtotal;
                $detalles[] = [
                    'producto_id' => $productos[$i],
                    'cantidad' => $cantidades[$i],
                    'precio' => $precios[$i]
                ];
            }
        }

        if (empty($detalles)) {
            $_SESSION['alert'] = ['icon' => 'warning', 'title' => 'Carrito vacío', 'text' => 'Debe agregar al menos un producto a la venta.'];
            header("Location: ../views/ventas/crear.php");
            exit;
        }

        if ($ventaModel->crear($usuario_id, $total, $detalles)) {
            $_SESSION['alert'] = ['icon' => 'success', 'title' => 'Éxito', 'text' => 'Venta registrada correctamente.'];
            header("Location: ../views/ventas/index.php");
            exit;
        } else {
            $_SESSION['alert'] = ['icon' => 'error', 'title' => 'Error', 'text' => 'No se pudo registrar la venta.'];
            header("Location: ../views/ventas/crear.php");
            exit;
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if ($accion === 'eliminar') {
        if (!$puedeEditar) {
            $_SESSION['alert'] = ['icon' => 'error', 'title' => 'Acceso Denegado', 'text' => 'Solo los administradores y vendedores pueden eliminar ventas.'];
            header("Location: ../views/ventas/index.php");
            exit;
        }

        $id = $_GET['id'] ?? null;
        if ($id && $ventaModel->eliminar($id)) {
            $_SESSION['alert'] = ['icon' => 'success', 'title' => 'Éxito', 'text' => 'Venta eliminada correctamente.'];
        } else {
            $_SESSION['alert'] = ['icon' => 'error', 'title' => 'Error', 'text' => 'No se pudo eliminar la venta.'];
        }
        header("Location: ../views/ventas/index.php");
        exit;
    }
}
?>
