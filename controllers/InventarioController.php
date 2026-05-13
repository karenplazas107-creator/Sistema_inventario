<?php
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Producto.php';

if (!isset($_SESSION['usuario'])) {
    header("Location: ../views/usuarios/login.php");
    exit;
}

$database = new Database();
$db       = $database->conectar();
$productoModel = new Producto($db);

$accion      = $_GET['accion'] ?? '';
$rol         = $_SESSION['usuario']['rol'];
$puedeEditar = in_array($rol, ['Administrador', 'Bodeguero']);

// ── POST ──────────────────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // ── Ajuste de stock (entrada / salida / corrección) ───────────────────────
    if ($accion === 'ajustar') {
        if (!$puedeEditar) {
            $_SESSION['alert'] = ['icon'=>'error','title'=>'Sin permiso','text'=>'No tienes permisos para ajustar el inventario.'];
            header("Location: ../views/inventario/index.php"); exit;
        }

        $producto_id = intval($_POST['producto_id'] ?? 0);
        $tipo        = $_POST['tipo'] ?? '';          // entrada | salida | correccion
        $cantidad    = intval($_POST['cantidad'] ?? 0);
        $motivo      = trim($_POST['motivo'] ?? '');

        if ($producto_id <= 0 || $cantidad <= 0 || !in_array($tipo, ['entrada','salida','correccion'])) {
            $_SESSION['alert'] = ['icon'=>'warning','title'=>'Datos inválidos','text'=>'Verifica los campos del ajuste.'];
            header("Location: ../views/inventario/index.php"); exit;
        }

        // Obtener stock actual
        $prod = $productoModel->obtenerPorId($producto_id);
        if (!$prod) {
            $_SESSION['alert'] = ['icon'=>'error','title'=>'Error','text'=>'Producto no encontrado.'];
            header("Location: ../views/inventario/index.php"); exit;
        }

        $stockActual = intval($prod['stock']);

        if ($tipo === 'entrada') {
            $nuevoStock = $stockActual + $cantidad;
        } elseif ($tipo === 'salida') {
            $nuevoStock = max(0, $stockActual - $cantidad);
        } else {
            // corrección directa
            $nuevoStock = $cantidad;
        }

        if ($productoModel->actualizarStock($producto_id, $nuevoStock)) {
            $tipoTexto = ['entrada'=>'Entrada','salida'=>'Salida','correccion'=>'Corrección'][$tipo];
            $_SESSION['alert'] = ['icon'=>'success','title'=>'Stock actualizado','text'=>"$tipoTexto registrada. Nuevo stock: $nuevoStock unidades."];
        } else {
            $_SESSION['alert'] = ['icon'=>'error','title'=>'Error','text'=>'No se pudo actualizar el stock.'];
        }
        header("Location: ../views/inventario/index.php"); exit;
    }

    // ── Actualizar stock mínimo ───────────────────────────────────────────────
    if ($accion === 'stock_minimo') {
        if (!$puedeEditar) {
            $_SESSION['alert'] = ['icon'=>'error','title'=>'Sin permiso','text'=>'No tienes permisos para modificar el stock mínimo.'];
            header("Location: ../views/inventario/index.php"); exit;
        }

        $producto_id  = intval($_POST['producto_id']  ?? 0);
        $stock_minimo = intval($_POST['stock_minimo'] ?? 0);

        $stmt = $db->prepare("UPDATE inventario SET stock_minimo = :smin WHERE producto_id = :pid");
        $stmt->bindParam(':smin', $stock_minimo, PDO::PARAM_INT);
        $stmt->bindParam(':pid',  $producto_id,  PDO::PARAM_INT);

        if ($stmt->execute()) {
            $_SESSION['alert'] = ['icon'=>'success','title'=>'Actualizado','text'=>'Stock mínimo actualizado correctamente.'];
        } else {
            $_SESSION['alert'] = ['icon'=>'error','title'=>'Error','text'=>'No se pudo actualizar el stock mínimo.'];
        }
        header("Location: ../views/inventario/index.php"); exit;
    }
}

header("Location: ../views/inventario/index.php");
exit;
?>
