<?php
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Venta.php';

// Base URL del proyecto — se detecta automáticamente
$base = str_replace('\\', '/', dirname(dirname(str_replace($_SERVER['DOCUMENT_ROOT'], '', __FILE__))));

if (!isset($_SESSION['usuario'])) {
    header("Location: $base/views/usuarios/login.php");
    exit;
}

$database = new Database();
$db = $database->conectar();
$ventaModel = new Venta($db);

$accion = $_GET['accion'] ?? '';
$rol    = $_SESSION['usuario']['rol'];

$puedeEditar    = in_array($rol, ['Administrador', 'Vendedor']);
$puedeRegistrar = in_array($rol, ['Administrador', 'Vendedor', 'Bodeguero', 'Comprador']);

// ── POST: CREAR VENTA ─────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $accion === 'crear') {

    if (!$puedeRegistrar) {
        $_SESSION['alert'] = ['icon'=>'error','title'=>'Acceso Denegado','text'=>'No tienes permisos para registrar ventas.'];
        header("Location: $base/views/ventas/index.php");
        exit;
    }

    $usuario_id  = $_SESSION['usuario']['id'];
    $productos   = $_POST['productos']   ?? [];
    $cantidades  = $_POST['cantidades']  ?? [];
    $precios     = $_POST['precios']     ?? [];
    $metodo_pago = $_POST['metodo_pago'] ?? 'Efectivo';

    $total    = 0;
    $detalles = [];

    for ($i = 0; $i < count($productos); $i++) {
        $cant = intval($cantidades[$i] ?? 0);
        if ($cant > 0) {
            $precio   = floatval($precios[$i] ?? 0);
            $total   += $cant * $precio;
            $detalles[] = [
                'producto_id' => intval($productos[$i]),
                'cantidad'    => $cant,
                'precio'      => $precio
            ];
        }
    }

    if (empty($detalles)) {
        $_SESSION['alert'] = ['icon'=>'warning','title'=>'Carrito vacío','text'=>'Agrega al menos un producto.'];
        $back = ($rol === 'Comprador') ? "$base/views/dashboard/comprador.php" : "$base/views/ventas/crear.php";
        header("Location: $back");
        exit;
    }

    $ventaId = $ventaModel->crear($usuario_id, $total, $detalles, $metodo_pago);

    if ($ventaId) {
        header("Location: $base/views/ventas/factura_pos.php?id=$ventaId");
        exit;
    } else {
        $_SESSION['alert'] = ['icon'=>'error','title'=>'Error','text'=>'No se pudo registrar la venta. Intenta de nuevo.'];
        $back = ($rol === 'Comprador') ? "$base/views/dashboard/comprador.php" : "$base/views/ventas/crear.php";
        header("Location: $back");
        exit;
    }
}

// ── GET: ELIMINAR VENTA ───────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'GET' && $accion === 'eliminar') {

    if (!$puedeEditar) {
        $_SESSION['alert'] = ['icon'=>'error','title'=>'Acceso Denegado','text'=>'Solo administradores y vendedores pueden eliminar ventas.'];
        header("Location: $base/views/ventas/index.php");
        exit;
    }

    $id = intval($_GET['id'] ?? 0);
    if ($id && $ventaModel->eliminar($id)) {
        $_SESSION['alert'] = ['icon'=>'success','title'=>'Eliminada','text'=>'Venta eliminada correctamente.'];
    } else {
        $_SESSION['alert'] = ['icon'=>'error','title'=>'Error','text'=>'No se pudo eliminar la venta.'];
    }
    header("Location: $base/views/ventas/index.php");
    exit;
}

// Fallback
header("Location: $base/views/ventas/index.php");
exit;
?>
