<?php
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Producto.php';
require_once __DIR__ . '/../models/Categoria.php';

if (!isset($_SESSION['usuario'])) {
    header("Location: ../views/usuarios/login.php");
    exit;
}

$database = new Database();
$db       = $database->conectar();
$productoModel  = new Producto($db);
$categoriaModel = new Categoria($db);

$accion = $_GET['accion'] ?? '';
$rol    = $_SESSION['usuario']['rol'];

// Solo Administrador y Bodeguero pueden modificar
$puedeEditar = in_array($rol, ['Administrador', 'Bodeguero']);

// ── POST ──────────────────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // ── Crear producto ────────────────────────────────────────────────────────
    if ($accion === 'crear') {
        if (!$puedeEditar) {
            $_SESSION['alert'] = ['icon'=>'error','title'=>'Sin permiso','text'=>'No tienes permisos para crear productos.'];
            header("Location: ../views/productos/index.php"); exit;
        }

        $nombre        = trim($_POST['nombre']        ?? '');
        $descripcion   = trim($_POST['descripcion']   ?? '');
        $precio_compra = floatval($_POST['precio_compra'] ?? 0);
        $precio_venta  = floatval($_POST['precio_venta']  ?? 0);
        $categoria_id  = intval($_POST['categoria_id']    ?? 0);
        $codigo_barras = trim($_POST['codigo_barras'] ?? '') ?: null;
        $stock_minimo  = intval($_POST['stock_minimo'] ?? 5);

        if (empty($nombre) || $precio_venta <= 0 || $categoria_id <= 0) {
            $_SESSION['alert'] = ['icon'=>'warning','title'=>'Datos incompletos','text'=>'Nombre, precio de venta y categoría son obligatorios.'];
            header("Location: ../views/productos/index.php"); exit;
        }

        // Imagen
        $imagen = null;
        if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
            $ext       = strtolower(pathinfo($_FILES['imagen']['name'], PATHINFO_EXTENSION));
            $permitidos = ['jpg','jpeg','png','webp','gif'];
            if (in_array($ext, $permitidos)) {
                $uploadDir = __DIR__ . '/../img/productos/';
                if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
                $nombreArchivo = 'prod_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
                if (move_uploaded_file($_FILES['imagen']['tmp_name'], $uploadDir . $nombreArchivo)) {
                    $imagen = $nombreArchivo;
                }
            }
        }

        if ($productoModel->crearConStock($nombre, $descripcion, $precio_compra, $precio_venta, $categoria_id, $codigo_barras, $imagen, $stock_minimo)) {
            $_SESSION['alert'] = ['icon'=>'success','title'=>'Producto creado','text'=>"\"$nombre\" fue agregado correctamente."];
        } else {
            $_SESSION['alert'] = ['icon'=>'error','title'=>'Error','text'=>'No se pudo crear el producto.'];
        }
        header("Location: ../views/productos/index.php"); exit;
    }

    // ── Editar producto ───────────────────────────────────────────────────────
    if ($accion === 'editar') {
        if (!$puedeEditar) {
            $_SESSION['alert'] = ['icon'=>'error','title'=>'Sin permiso','text'=>'No tienes permisos para editar productos.'];
            header("Location: ../views/productos/index.php"); exit;
        }

        $id            = intval($_POST['id']           ?? 0);
        $nombre        = trim($_POST['nombre']         ?? '');
        $descripcion   = trim($_POST['descripcion']    ?? '');
        $precio_compra = floatval($_POST['precio_compra'] ?? 0);
        $precio_venta  = floatval($_POST['precio_venta']  ?? 0);
        $categoria_id  = intval($_POST['categoria_id']    ?? 0);
        $codigo_barras = trim($_POST['codigo_barras']  ?? '') ?: null;
        $stock_minimo  = intval($_POST['stock_minimo'] ?? 5);

        // Imagen
        $imagen = null;
        if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
            $ext        = strtolower(pathinfo($_FILES['imagen']['name'], PATHINFO_EXTENSION));
            $permitidos = ['jpg','jpeg','png','webp','gif'];
            if (in_array($ext, $permitidos)) {
                $uploadDir = __DIR__ . '/../img/productos/';
                if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
                $nombreArchivo = 'prod_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
                if (move_uploaded_file($_FILES['imagen']['tmp_name'], $uploadDir . $nombreArchivo)) {
                    $imagen = $nombreArchivo;
                    // Borrar imagen anterior
                    $imgAnterior = trim($_POST['imagen_actual'] ?? '');
                    if ($imgAnterior && file_exists($uploadDir . $imgAnterior)) {
                        unlink($uploadDir . $imgAnterior);
                    }
                }
            }
        }

        if ($productoModel->editarConStock($id, $nombre, $descripcion, $precio_compra, $precio_venta, $categoria_id, $codigo_barras, $imagen, $stock_minimo)) {
            $_SESSION['alert'] = ['icon'=>'success','title'=>'Actualizado','text'=>'Producto actualizado correctamente.'];
        } else {
            $_SESSION['alert'] = ['icon'=>'error','title'=>'Error','text'=>'No se pudo actualizar el producto.'];
        }
        header("Location: ../views/productos/index.php"); exit;
    }

    // ── Ajustar stock ─────────────────────────────────────────────────────────
    if ($accion === 'ajustar_stock') {
        if (!$puedeEditar) {
            $_SESSION['alert'] = ['icon'=>'error','title'=>'Sin permiso','text'=>'No tienes permisos para ajustar stock.'];
            header("Location: ../views/productos/index.php"); exit;
        }

        $id        = intval($_POST['id']        ?? 0);
        $nuevo_stock = intval($_POST['nuevo_stock'] ?? 0);

        if ($productoModel->actualizarStock($id, $nuevo_stock)) {
            $_SESSION['alert'] = ['icon'=>'success','title'=>'Stock actualizado','text'=>'El stock fue ajustado correctamente.'];
        } else {
            $_SESSION['alert'] = ['icon'=>'error','title'=>'Error','text'=>'No se pudo ajustar el stock.'];
        }
        header("Location: ../views/productos/index.php"); exit;
    }
}

// ── GET ───────────────────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'GET') {

    // ── Eliminar producto ─────────────────────────────────────────────────────
    if ($accion === 'eliminar') {
        if (!$puedeEditar) {
            $_SESSION['alert'] = ['icon'=>'error','title'=>'Sin permiso','text'=>'No tienes permisos para eliminar productos.'];
            header("Location: ../views/productos/index.php"); exit;
        }

        $id = intval($_GET['id'] ?? 0);

        // Borrar imagen si existe
        $prod = $productoModel->obtenerPorId($id);
        if ($prod && !empty($prod['imagen'])) {
            $imgPath = __DIR__ . '/../img/productos/' . $prod['imagen'];
            if (file_exists($imgPath)) unlink($imgPath);
        }

        if ($productoModel->eliminar($id)) {
            $_SESSION['alert'] = ['icon'=>'success','title'=>'Eliminado','text'=>'Producto eliminado correctamente.'];
        } else {
            $_SESSION['alert'] = ['icon'=>'error','title'=>'Error','text'=>'No se pudo eliminar. Puede tener ventas asociadas.'];
        }
        header("Location: ../views/productos/index.php"); exit;
    }
}

header("Location: ../views/productos/index.php");
exit;
?>
