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
$db = $database->conectar();
$productoModel = new Producto($db);
$categoriaModel = new Categoria($db);

$accion = $_GET['accion'] ?? '';
$rol = $_SESSION['usuario']['rol'];
$puedeEditar = in_array($rol, ['Administrador', 'Bodeguero']);

// ─── PRODUCTOS ────────────────────────────────────────────────────────────────

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if ($accion === 'crear_producto') {
        if (!$puedeEditar) {
            $_SESSION['alert'] = ['icon' => 'error', 'title' => 'Acceso Denegado', 'text' => 'No tienes permisos para crear productos.'];
            header("Location: ../views/Catalogo/index.php");
            exit;
        }
        $nombre        = trim($_POST['nombre'] ?? '');
        $descripcion   = trim($_POST['descripcion'] ?? '');
        $precio_compra = floatval($_POST['precio_compra'] ?? 0);
        $precio_venta  = floatval($_POST['precio_venta'] ?? 0);
        $categoria_id  = intval($_POST['categoria_id'] ?? 0);
        $codigo_barras = trim($_POST['codigo_barras'] ?? '') ?: null;

        if (empty($nombre) || $precio_venta <= 0 || $categoria_id <= 0) {
            $_SESSION['alert'] = ['icon' => 'warning', 'title' => 'Datos incompletos', 'text' => 'Completa todos los campos obligatorios.'];
            header("Location: ../views/Catalogo/index.php");
            exit;
        }

        // Manejo de imagen
        $imagen = null;
        if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
            $ext = strtolower(pathinfo($_FILES['imagen']['name'], PATHINFO_EXTENSION));
            $permitidos = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
            if (!in_array($ext, $permitidos)) {
                $_SESSION['alert'] = ['icon' => 'warning', 'title' => 'Formato inválido', 'text' => 'Solo se permiten imágenes JPG, PNG, WEBP o GIF.'];
                header("Location: ../views/Catalogo/index.php");
                exit;
            }
            $uploadDir = __DIR__ . '/../img/productos/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            $nombreArchivo = 'prod_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
            if (move_uploaded_file($_FILES['imagen']['tmp_name'], $uploadDir . $nombreArchivo)) {
                $imagen = $nombreArchivo;
            }
        }

        if ($productoModel->crear($nombre, $descripcion, $precio_compra, $precio_venta, $categoria_id, $codigo_barras, $imagen)) {
            $_SESSION['alert'] = ['icon' => 'success', 'title' => 'Producto creado', 'text' => "\"$nombre\" fue agregado al catálogo."];
        } else {
            $_SESSION['alert'] = ['icon' => 'error', 'title' => 'Error', 'text' => 'No se pudo crear el producto.'];
        }
        header("Location: ../views/Catalogo/index.php");
        exit;
    }

    if ($accion === 'editar_producto') {
        if (!$puedeEditar) {
            $_SESSION['alert'] = ['icon' => 'error', 'title' => 'Acceso Denegado', 'text' => 'No tienes permisos para editar productos.'];
            header("Location: ../views/Catalogo/index.php");
            exit;
        }
        $id            = intval($_POST['id'] ?? 0);
        $nombre        = trim($_POST['nombre'] ?? '');
        $descripcion   = trim($_POST['descripcion'] ?? '');
        $precio_compra = floatval($_POST['precio_compra'] ?? 0);
        $precio_venta  = floatval($_POST['precio_venta'] ?? 0);
        $categoria_id  = intval($_POST['categoria_id'] ?? 0);
        $codigo_barras = trim($_POST['codigo_barras'] ?? '') ?: null;

        // Manejo de imagen en edición
        $imagen = null;
        if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
            $ext = strtolower(pathinfo($_FILES['imagen']['name'], PATHINFO_EXTENSION));
            $permitidos = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
            if (in_array($ext, $permitidos)) {
                $uploadDir = __DIR__ . '/../img/productos/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }
                $nombreArchivo = 'prod_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
                if (move_uploaded_file($_FILES['imagen']['tmp_name'], $uploadDir . $nombreArchivo)) {
                    $imagen = $nombreArchivo;
                    // Eliminar imagen anterior si existe
                    $imagenAnterior = trim($_POST['imagen_actual'] ?? '');
                    if ($imagenAnterior && file_exists($uploadDir . $imagenAnterior)) {
                        unlink($uploadDir . $imagenAnterior);
                    }
                }
            }
        }

        if ($productoModel->editar($id, $nombre, $descripcion, $precio_compra, $precio_venta, $categoria_id, $codigo_barras, $imagen)) {
            $_SESSION['alert'] = ['icon' => 'success', 'title' => 'Actualizado', 'text' => 'Producto actualizado correctamente.'];
        } else {
            $_SESSION['alert'] = ['icon' => 'error', 'title' => 'Error', 'text' => 'No se pudo actualizar el producto.'];
        }
        header("Location: ../views/Catalogo/index.php");
        exit;
    }

    // ─── CATEGORÍAS ───────────────────────────────────────────────────────────

    if ($accion === 'crear_categoria') {
        if (!$puedeEditar) {
            $_SESSION['alert'] = ['icon' => 'error', 'title' => 'Acceso Denegado', 'text' => 'No tienes permisos para crear categorías.'];
            header("Location: ../views/Catalogo/index.php");
            exit;
        }
        $nombre      = trim($_POST['nombre'] ?? '');
        $descripcion = trim($_POST['descripcion'] ?? '');

        if (empty($nombre)) {
            $_SESSION['alert'] = ['icon' => 'warning', 'title' => 'Nombre requerido', 'text' => 'El nombre de la categoría es obligatorio.'];
            header("Location: ../views/Catalogo/index.php");
            exit;
        }

        if ($categoriaModel->crear($nombre, $descripcion)) {
            $_SESSION['alert'] = ['icon' => 'success', 'title' => 'Categoría creada', 'text' => "\"$nombre\" fue agregada."];
        } else {
            $_SESSION['alert'] = ['icon' => 'error', 'title' => 'Error', 'text' => 'No se pudo crear la categoría.'];
        }
        header("Location: ../views/Catalogo/index.php");
        exit;
    }

    if ($accion === 'editar_categoria') {
        if (!$puedeEditar) {
            $_SESSION['alert'] = ['icon' => 'error', 'title' => 'Acceso Denegado', 'text' => 'No tienes permisos para editar categorías.'];
            header("Location: ../views/Catalogo/index.php");
            exit;
        }
        $id          = intval($_POST['id'] ?? 0);
        $nombre      = trim($_POST['nombre'] ?? '');
        $descripcion = trim($_POST['descripcion'] ?? '');

        if ($categoriaModel->editar($id, $nombre, $descripcion)) {
            $_SESSION['alert'] = ['icon' => 'success', 'title' => 'Actualizado', 'text' => 'Categoría actualizada correctamente.'];
        } else {
            $_SESSION['alert'] = ['icon' => 'error', 'title' => 'Error', 'text' => 'No se pudo actualizar la categoría.'];
        }
        header("Location: ../views/Catalogo/index.php");
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {

    if ($accion === 'eliminar_producto') {
        if (!$puedeEditar) {
            $_SESSION['alert'] = ['icon' => 'error', 'title' => 'Acceso Denegado', 'text' => 'No tienes permisos para eliminar productos.'];
            header("Location: ../views/Catalogo/index.php");
            exit;
        }
        $id = intval($_GET['id'] ?? 0);
        if ($productoModel->eliminar($id)) {
            $_SESSION['alert'] = ['icon' => 'success', 'title' => 'Eliminado', 'text' => 'Producto eliminado del catálogo.'];
        } else {
            $_SESSION['alert'] = ['icon' => 'error', 'title' => 'Error', 'text' => 'No se pudo eliminar. Puede tener ventas o compras asociadas.'];
        }
        header("Location: ../views/Catalogo/index.php");
        exit;
    }

    if ($accion === 'eliminar_categoria') {
        if (!$puedeEditar) {
            $_SESSION['alert'] = ['icon' => 'error', 'title' => 'Acceso Denegado', 'text' => 'No tienes permisos para eliminar categorías.'];
            header("Location: ../views/Catalogo/index.php");
            exit;
        }
        $id = intval($_GET['id'] ?? 0);
        $resultado = $categoriaModel->eliminar($id);
        if ($resultado === true) {
            $_SESSION['alert'] = ['icon' => 'success', 'title' => 'Eliminada', 'text' => 'Categoría eliminada correctamente.'];
        } elseif ($resultado === 'tiene_productos') {
            $_SESSION['alert'] = ['icon' => 'warning', 'title' => 'No se puede eliminar', 'text' => 'Esta categoría tiene productos asociados. Reasígnalos primero.'];
        } else {
            $_SESSION['alert'] = ['icon' => 'error', 'title' => 'Error', 'text' => 'No se pudo eliminar la categoría.'];
        }
        header("Location: ../views/Catalogo/index.php");
        exit;
    }
}

// Redirigir si no hay acción válida
header("Location: ../views/Catalogo/index.php");
exit;
?>
