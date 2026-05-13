<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header("Location: ../usuarios/login.php");
    exit;
}

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../models/Producto.php';
require_once __DIR__ . '/../../models/Categoria.php';

$database = new Database();
$db = $database->conectar();
$productoModel = new Producto($db);
$categoriaModel = new Categoria($db);

$rol = $_SESSION['usuario']['rol'];
$puedeEditar = in_array($rol, ['Administrador', 'Bodeguero']);

// Obtener datos
$categorias  = $categoriaModel->obtenerTodas();
$productos   = $productoModel->obtenerTodos();
$totalProductos = count($productos);
$totalCategorias = count($categorias);
$sinStock = array_filter($productos, fn($p) => $p['stock'] <= 0);
$stockBajo = array_filter($productos, fn($p) => $p['stock'] > 0 && $p['stock'] <= $p['stock_minimo']);

$titulo = "Catálogo de Productos";
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/sidebar.php';
?>

<!-- ══════════════════════════════════════════════════════
     ALERTAS SWEETALERT2
══════════════════════════════════════════════════════ -->
<?php if (isset($_SESSION['alert'])): ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    Swal.fire({
        icon:  '<?= $_SESSION['alert']['icon'] ?>',
        title: '<?= addslashes($_SESSION['alert']['title']) ?>',
        text:  '<?= addslashes($_SESSION['alert']['text']) ?>',
        confirmButtonColor: '#1e3a8a',
        timer: 3500,
        timerProgressBar: true
    });
});
</script>
<?php unset($_SESSION['alert']); endif; ?>

<!-- ══════════════════════════════════════════════════════
     ESTILOS ESPECÍFICOS DEL CATÁLOGO
══════════════════════════════════════════════════════ -->
<style>
    /* Librería JsBarcode se carga abajo */
    .product-card {
        transition: transform 0.25s ease, box-shadow 0.25s ease;
    }
    .product-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 16px 40px rgba(30,58,138,0.12);
    }
    .badge-stock-ok   { background:#dcfce7; color:#166534; }
    .badge-stock-bajo { background:#fef9c3; color:#854d0e; }
    .badge-sin-stock  { background:#fee2e2; color:#991b1b; }

    /* Filtro de categorías */
    .cat-pill {
        cursor: pointer;
        transition: all 0.2s;
        border: 2px solid transparent;
    }
    .cat-pill:hover  { border-color: #2563eb; color: #2563eb; }
    .cat-pill.active { background: #1e3a8a; color: #fff; border-color: #1e3a8a; }

    /* Modal overlay */
    .modal-overlay {
        background: rgba(15,23,42,0.6);
        backdrop-filter: blur(4px);
    }

    /* Preview imagen */
    #imgPreview { object-fit: cover; }

    /* Barcode canvas */
    .barcode-wrap svg, .barcode-wrap canvas { max-width: 100%; height: auto; }
</style>

<!-- ══════════════════════════════════════════════════════
     ENCABEZADO + STATS
══════════════════════════════════════════════════════ -->
<div class="mb-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
    <div>
        <h2 class="text-2xl font-bold text-gray-800">Catálogo de Productos</h2>
        <p class="text-sm text-gray-500 mt-1">Gestiona productos, imágenes, precios y códigos de barras.</p>
    </div>
    <?php if ($puedeEditar): ?>
    <div class="flex gap-3">
        <button onclick="abrirModalCategoria()"
                class="flex items-center gap-2 px-4 py-2.5 rounded-xl border-2 border-brand-900 text-brand-900 font-semibold text-sm hover:bg-brand-900 hover:text-white transition-all">
            <i class="fas fa-folder-plus"></i> Nueva Categoría
        </button>
        <button onclick="abrirModalProducto()"
                class="flex items-center gap-2 px-5 py-2.5 rounded-xl bg-brand-900 text-white font-semibold text-sm hover:bg-brand-800 transition-all shadow-md">
            <i class="fas fa-plus"></i> Nuevo Producto
        </button>
    </div>
    <?php endif; ?>
</div>

<!-- Stats rápidas -->
<div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
    <div class="card p-5 flex items-center gap-4">
        <div class="w-11 h-11 rounded-xl bg-blue-100 flex items-center justify-center text-blue-600 text-lg flex-shrink-0">
            <i class="fas fa-box-open"></i>
        </div>
        <div>
            <div class="text-2xl font-bold text-gray-800"><?= $totalProductos ?></div>
            <div class="text-xs text-gray-500">Productos</div>
        </div>
    </div>
    <div class="card p-5 flex items-center gap-4">
        <div class="w-11 h-11 rounded-xl bg-purple-100 flex items-center justify-center text-purple-600 text-lg flex-shrink-0">
            <i class="fas fa-tags"></i>
        </div>
        <div>
            <div class="text-2xl font-bold text-gray-800"><?= $totalCategorias ?></div>
            <div class="text-xs text-gray-500">Categorías</div>
        </div>
    </div>
    <div class="card p-5 flex items-center gap-4">
        <div class="w-11 h-11 rounded-xl bg-yellow-100 flex items-center justify-center text-yellow-600 text-lg flex-shrink-0">
            <i class="fas fa-triangle-exclamation"></i>
        </div>
        <div>
            <div class="text-2xl font-bold text-gray-800"><?= count($stockBajo) ?></div>
            <div class="text-xs text-gray-500">Stock Bajo</div>
        </div>
    </div>
    <div class="card p-5 flex items-center gap-4">
        <div class="w-11 h-11 rounded-xl bg-red-100 flex items-center justify-center text-red-600 text-lg flex-shrink-0">
            <i class="fas fa-ban"></i>
        </div>
        <div>
            <div class="text-2xl font-bold text-gray-800"><?= count($sinStock) ?></div>
            <div class="text-xs text-gray-500">Sin Stock</div>
        </div>
    </div>
</div>

<!-- ══════════════════════════════════════════════════════
     FILTROS: BÚSQUEDA + CATEGORÍAS
══════════════════════════════════════════════════════ -->
<div class="card p-5 mb-6">
    <div class="flex flex-col md:flex-row gap-4 items-start md:items-center">
        <!-- Buscador -->
        <div class="relative flex-1 w-full">
            <i class="fas fa-search absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
            <input type="text" id="buscador" placeholder="Buscar por nombre, categoría o código de barras..."
                   class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-transparent transition">
        </div>
        <!-- Toggle vista -->
        <div class="flex items-center gap-2 bg-gray-100 rounded-xl p-1">
            <button id="btnGrid" onclick="setVista('grid')"
                    class="px-3 py-1.5 rounded-lg text-sm font-medium transition bg-white shadow text-brand-900">
                <i class="fas fa-grip"></i>
            </button>
            <button id="btnList" onclick="setVista('list')"
                    class="px-3 py-1.5 rounded-lg text-sm font-medium transition text-gray-500 hover:text-gray-700">
                <i class="fas fa-list"></i>
            </button>
        </div>
    </div>

    <!-- Pills de categorías -->
    <div class="flex flex-wrap gap-2 mt-4">
        <span class="cat-pill active px-4 py-1.5 rounded-full text-sm font-medium bg-brand-900 text-white"
              data-cat="todos" onclick="filtrarCategoria('todos', this)">
            Todos
        </span>
        <?php foreach ($categorias as $cat): ?>
        <span class="cat-pill px-4 py-1.5 rounded-full text-sm font-medium bg-gray-100 text-gray-600"
              data-cat="<?= $cat['id'] ?>" onclick="filtrarCategoria('<?= $cat['id'] ?>', this)">
            <?= htmlspecialchars($cat['nombre']) ?>
        </span>
        <?php endforeach; ?>
    </div>
</div>

<!-- ══════════════════════════════════════════════════════
     GRID DE PRODUCTOS
══════════════════════════════════════════════════════ -->
<div id="vistaGrid" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-5">
<?php if (empty($productos)): ?>
    <div class="col-span-4 text-center py-20 text-gray-400">
        <i class="fas fa-box-open text-5xl mb-4 block opacity-30"></i>
        <p class="text-lg font-medium">No hay productos en el catálogo.</p>
        <?php if ($puedeEditar): ?>
        <button onclick="abrirModalProducto()" class="mt-4 px-5 py-2 rounded-xl bg-brand-900 text-white text-sm font-semibold hover:bg-brand-800 transition">
            Agregar el primero
        </button>
        <?php endif; ?>
    </div>
<?php else: ?>
    <?php foreach ($productos as $p):
        $imgSrc = !empty($p['imagen'])
            ? '../../img/productos/' . htmlspecialchars($p['imagen'])
            : 'https://placehold.co/400x300/e2e8f0/94a3b8?text=Sin+Imagen';

        if ($p['stock'] <= 0) {
            $badgeClass = 'badge-sin-stock';
            $badgeText  = 'Sin stock';
        } elseif ($p['stock'] <= $p['stock_minimo']) {
            $badgeClass = 'badge-stock-bajo';
            $badgeText  = 'Stock bajo';
        } else {
            $badgeClass = 'badge-stock-ok';
            $badgeText  = 'Disponible';
        }
    ?>
    <div class="product-card card overflow-hidden flex flex-col"
         data-nombre="<?= strtolower(htmlspecialchars($p['nombre'])) ?>"
         data-categoria="<?= $p['categoria_id'] ?>"
         data-barcode="<?= strtolower(htmlspecialchars($p['codigo_barras'] ?? '')) ?>">

        <!-- Imagen -->
        <div class="relative h-44 bg-gray-50 overflow-hidden">
            <img src="<?= $imgSrc ?>" alt="<?= htmlspecialchars($p['nombre']) ?>"
                 class="w-full h-full object-cover transition-transform duration-500 hover:scale-105"
                 onerror="this.src='https://placehold.co/400x300/e2e8f0/94a3b8?text=Sin+Imagen'">
            <!-- Badge stock -->
            <span class="absolute top-2 right-2 text-xs font-semibold px-2.5 py-1 rounded-full <?= $badgeClass ?>">
                <?= $badgeText ?>
            </span>
            <!-- Badge categoría -->
            <span class="absolute top-2 left-2 text-xs font-medium px-2.5 py-1 rounded-full bg-white/90 text-gray-700 shadow-sm">
                <?= htmlspecialchars($p['categoria_nombre'] ?? '—') ?>
            </span>
        </div>

        <!-- Info -->
        <div class="p-4 flex flex-col flex-1">
            <h3 class="font-semibold text-gray-800 text-sm leading-tight mb-1 line-clamp-2">
                <?= htmlspecialchars($p['nombre']) ?>
            </h3>
            <?php if (!empty($p['descripcion'])): ?>
            <p class="text-xs text-gray-400 mb-3 line-clamp-2"><?= htmlspecialchars($p['descripcion']) ?></p>
            <?php endif; ?>

            <!-- Precio -->
            <div class="mt-auto">
                <div class="flex items-end justify-between mb-3">
                    <div>
                        <div class="text-xs text-gray-400">Precio venta</div>
                        <div class="text-xl font-bold text-brand-900">
                            $<?= number_format($p['precio_venta'], 2, ',', '.') ?>
                        </div>
                    </div>
                    <div class="text-right">
                        <div class="text-xs text-gray-400">Stock</div>
                        <div class="text-lg font-bold <?= $p['stock'] <= 0 ? 'text-red-500' : ($p['stock'] <= $p['stock_minimo'] ? 'text-yellow-500' : 'text-green-600') ?>">
                            <?= $p['stock'] ?> uds
                        </div>
                    </div>
                </div>

                <!-- Código de barras -->
                <?php if (!empty($p['codigo_barras'])): ?>
                <div class="border-t pt-3 mt-1">
                    <div class="text-xs text-gray-400 mb-1 flex items-center gap-1">
                        <i class="fas fa-barcode"></i> Código de barras
                    </div>
                    <div class="barcode-wrap flex justify-center bg-white rounded-lg p-2 border border-gray-100">
                        <svg class="barcode-svg" data-value="<?= htmlspecialchars($p['codigo_barras']) ?>"></svg>
                    </div>
                    <div class="text-center text-xs text-gray-500 mt-1 font-mono tracking-widest">
                        <?= htmlspecialchars($p['codigo_barras']) ?>
                    </div>
                </div>
                <?php else: ?>
                <div class="border-t pt-3 mt-1 text-center text-xs text-gray-300 italic">
                    <i class="fas fa-barcode mr-1"></i> Sin código de barras
                </div>
                <?php endif; ?>

                <!-- Acciones (solo si puede editar) -->
                <?php if ($puedeEditar): ?>
                <div class="flex gap-2 mt-3">
                    <button onclick='abrirModalEditar(<?= json_encode($p) ?>)'
                            class="flex-1 flex items-center justify-center gap-1.5 py-2 rounded-lg bg-brand-50 text-brand-800 text-xs font-semibold hover:bg-brand-100 transition">
                        <i class="fas fa-pen-to-square"></i> Editar
                    </button>
                    <a href="../../controllers/CatalogoController.php?accion=eliminar_producto&id=<?= $p['id'] ?>"
                       onclick="return confirmarEliminar(event, '<?= addslashes(htmlspecialchars($p['nombre'])) ?>')"
                       class="flex items-center justify-center gap-1.5 px-3 py-2 rounded-lg bg-red-50 text-red-600 text-xs font-semibold hover:bg-red-100 transition">
                        <i class="fas fa-trash"></i>
                    </a>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
<?php endif; ?>
</div>

<!-- ══════════════════════════════════════════════════════
     VISTA LISTA (oculta por defecto)
══════════════════════════════════════════════════════ -->
<div id="vistaList" class="hidden card overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-gray-50 border-b border-gray-100">
            <tr>
                <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Producto</th>
                <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider hidden md:table-cell">Categoría</th>
                <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider hidden lg:table-cell">Cód. Barras</th>
                <th class="text-right px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Precio</th>
                <th class="text-center px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Stock</th>
                <?php if ($puedeEditar): ?>
                <th class="text-center px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Acciones</th>
                <?php endif; ?>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-50" id="tablaBody">
        <?php foreach ($productos as $p):
            $imgSrc = !empty($p['imagen'])
                ? '../../img/productos/' . htmlspecialchars($p['imagen'])
                : 'https://placehold.co/80x80/e2e8f0/94a3b8?text=?';
            if ($p['stock'] <= 0) { $bc='badge-sin-stock'; $bt='Sin stock'; }
            elseif ($p['stock'] <= $p['stock_minimo']) { $bc='badge-stock-bajo'; $bt='Stock bajo'; }
            else { $bc='badge-stock-ok'; $bt='Disponible'; }
        ?>
        <tr class="hover:bg-gray-50 transition product-row"
            data-nombre="<?= strtolower(htmlspecialchars($p['nombre'])) ?>"
            data-categoria="<?= $p['categoria_id'] ?>"
            data-barcode="<?= strtolower(htmlspecialchars($p['codigo_barras'] ?? '')) ?>">
            <td class="px-4 py-3">
                <div class="flex items-center gap-3">
                    <img src="<?= $imgSrc ?>" alt="" class="w-10 h-10 rounded-lg object-cover border border-gray-100 flex-shrink-0"
                         onerror="this.src='https://placehold.co/80x80/e2e8f0/94a3b8?text=?'">
                    <div>
                        <div class="font-medium text-gray-800"><?= htmlspecialchars($p['nombre']) ?></div>
                        <div class="text-xs text-gray-400 line-clamp-1"><?= htmlspecialchars($p['descripcion'] ?? '') ?></div>
                    </div>
                </div>
            </td>
            <td class="px-4 py-3 hidden md:table-cell">
                <span class="px-2.5 py-1 rounded-full bg-gray-100 text-gray-600 text-xs font-medium">
                    <?= htmlspecialchars($p['categoria_nombre'] ?? '—') ?>
                </span>
            </td>
            <td class="px-4 py-3 hidden lg:table-cell font-mono text-xs text-gray-500">
                <?= !empty($p['codigo_barras']) ? htmlspecialchars($p['codigo_barras']) : '<span class="text-gray-300">—</span>' ?>
            </td>
            <td class="px-4 py-3 text-right font-bold text-brand-900">
                $<?= number_format($p['precio_venta'], 2, ',', '.') ?>
            </td>
            <td class="px-4 py-3 text-center">
                <span class="text-xs font-semibold px-2.5 py-1 rounded-full <?= $bc ?>">
                    <?= $p['stock'] ?> — <?= $bt ?>
                </span>
            </td>
            <?php if ($puedeEditar): ?>
            <td class="px-4 py-3 text-center">
                <div class="flex items-center justify-center gap-2">
                    <button onclick='abrirModalEditar(<?= json_encode($p) ?>)'
                            class="p-1.5 rounded-lg bg-brand-50 text-brand-700 hover:bg-brand-100 transition text-xs">
                        <i class="fas fa-pen-to-square"></i>
                    </button>
                    <a href="../../controllers/CatalogoController.php?accion=eliminar_producto&id=<?= $p['id'] ?>"
                       onclick="return confirmarEliminar(event, '<?= addslashes(htmlspecialchars($p['nombre'])) ?>')"
                       class="p-1.5 rounded-lg bg-red-50 text-red-600 hover:bg-red-100 transition text-xs">
                        <i class="fas fa-trash"></i>
                    </a>
                </div>
            </td>
            <?php endif; ?>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>


<!-- ══════════════════════════════════════════════════════
     MODAL: NUEVO / EDITAR PRODUCTO
══════════════════════════════════════════════════════ -->
<div id="modalProducto" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4">
    <div class="modal-overlay absolute inset-0" onclick="cerrarModalProducto()"></div>
    <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-2xl max-h-[90vh] overflow-y-auto z-10">

        <!-- Header modal -->
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 sticky top-0 bg-white z-10">
            <h3 class="font-bold text-gray-800 text-lg" id="modalProductoTitulo">Nuevo Producto</h3>
            <button onclick="cerrarModalProducto()" class="w-8 h-8 rounded-full bg-gray-100 flex items-center justify-center text-gray-500 hover:bg-gray-200 transition">
                <i class="fas fa-times text-sm"></i>
            </button>
        </div>

        <form id="formProducto" method="POST" enctype="multipart/form-data"
              action="../../controllers/CatalogoController.php?accion=crear_producto" class="p-6 space-y-5">
            <input type="hidden" name="id" id="prod_id">
            <input type="hidden" name="imagen_actual" id="prod_imagen_actual">

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

                <!-- Nombre -->
                <div class="md:col-span-2">
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">Nombre del producto <span class="text-red-500">*</span></label>
                    <input type="text" name="nombre" id="prod_nombre" required
                           class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500 transition"
                           placeholder="Ej: Arroz Diana 500g">
                </div>

                <!-- Descripción -->
                <div class="md:col-span-2">
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">Descripción</label>
                    <textarea name="descripcion" id="prod_descripcion" rows="2"
                              class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500 transition resize-none"
                              placeholder="Descripción breve del producto..."></textarea>
                </div>

                <!-- Precio compra -->
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">Precio de compra</label>
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm font-medium">$</span>
                        <input type="number" name="precio_compra" id="prod_precio_compra" step="0.01" min="0"
                               class="w-full pl-7 pr-4 py-2.5 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500 transition"
                               placeholder="0.00">
                    </div>
                </div>

                <!-- Precio venta -->
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">Precio de venta <span class="text-red-500">*</span></label>
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm font-medium">$</span>
                        <input type="number" name="precio_venta" id="prod_precio_venta" step="0.01" min="0.01" required
                               class="w-full pl-7 pr-4 py-2.5 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500 transition"
                               placeholder="0.00">
                    </div>
                </div>

                <!-- Categoría -->
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">Categoría <span class="text-red-500">*</span></label>
                    <select name="categoria_id" id="prod_categoria_id" required
                            class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500 transition bg-white">
                        <option value="">— Seleccionar —</option>
                        <?php foreach ($categorias as $cat): ?>
                        <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['nombre']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Código de barras -->
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">
                        <i class="fas fa-barcode mr-1"></i> Código de barras
                    </label>
                    <input type="text" name="codigo_barras" id="prod_codigo_barras"
                           class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500 transition font-mono"
                           placeholder="Ej: 7702001234567">
                </div>

                <!-- Imagen -->
                <div class="md:col-span-2">
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">
                        <i class="fas fa-image mr-1"></i> Imagen del producto
                    </label>
                    <div class="flex items-start gap-4">
                        <div class="w-24 h-24 rounded-xl border-2 border-dashed border-gray-200 overflow-hidden flex-shrink-0 bg-gray-50 flex items-center justify-center">
                            <img id="imgPreview" src="" alt="" class="w-full h-full object-cover hidden">
                            <i id="imgIcon" class="fas fa-image text-3xl text-gray-300"></i>
                        </div>
                        <div class="flex-1">
                            <label for="prod_imagen"
                                   class="flex items-center gap-2 px-4 py-2.5 rounded-xl border-2 border-dashed border-gray-200 text-sm text-gray-500 cursor-pointer hover:border-brand-400 hover:text-brand-600 transition">
                                <i class="fas fa-upload"></i>
                                <span id="imgLabel">Seleccionar imagen (JPG, PNG, WEBP)</span>
                            </label>
                            <input type="file" name="imagen" id="prod_imagen" accept="image/*" class="hidden" onchange="previewImagen(this)">
                            <p class="text-xs text-gray-400 mt-1.5">Tamaño recomendado: 400×400px. Máx. 2MB.</p>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Footer modal -->
            <div class="flex justify-end gap-3 pt-2 border-t border-gray-100">
                <button type="button" onclick="cerrarModalProducto()"
                        class="px-5 py-2.5 rounded-xl border border-gray-200 text-sm font-medium text-gray-600 hover:bg-gray-50 transition">
                    Cancelar
                </button>
                <button type="submit"
                        class="px-6 py-2.5 rounded-xl bg-brand-900 text-white text-sm font-semibold hover:bg-brand-800 transition shadow-md">
                    <i class="fas fa-save mr-1.5"></i> Guardar Producto
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ══════════════════════════════════════════════════════
     MODAL: NUEVA / EDITAR CATEGORÍA
══════════════════════════════════════════════════════ -->
<div id="modalCategoria" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4">
    <div class="modal-overlay absolute inset-0" onclick="cerrarModalCategoria()"></div>
    <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-md z-10">

        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
            <h3 class="font-bold text-gray-800 text-lg" id="modalCategoriaTitulo">Nueva Categoría</h3>
            <button onclick="cerrarModalCategoria()" class="w-8 h-8 rounded-full bg-gray-100 flex items-center justify-center text-gray-500 hover:bg-gray-200 transition">
                <i class="fas fa-times text-sm"></i>
            </button>
        </div>

        <form id="formCategoria" method="POST"
              action="../../controllers/CatalogoController.php?accion=crear_categoria" class="p-6 space-y-4">
            <input type="hidden" name="id" id="cat_id">

            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Nombre <span class="text-red-500">*</span></label>
                <input type="text" name="nombre" id="cat_nombre" required
                       class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500 transition"
                       placeholder="Ej: Lácteos">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Descripción</label>
                <textarea name="descripcion" id="cat_descripcion" rows="2"
                          class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500 transition resize-none"
                          placeholder="Descripción opcional..."></textarea>
            </div>

            <div class="flex justify-end gap-3 pt-2 border-t border-gray-100">
                <button type="button" onclick="cerrarModalCategoria()"
                        class="px-5 py-2.5 rounded-xl border border-gray-200 text-sm font-medium text-gray-600 hover:bg-gray-50 transition">
                    Cancelar
                </button>
                <button type="submit"
                        class="px-6 py-2.5 rounded-xl bg-brand-900 text-white text-sm font-semibold hover:bg-brand-800 transition shadow-md">
                    <i class="fas fa-save mr-1.5"></i> Guardar
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ══════════════════════════════════════════════════════
     SCRIPTS
══════════════════════════════════════════════════════ -->
<!-- JsBarcode para generar códigos de barras en el navegador -->
<script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.6/dist/JsBarcode.all.min.js"></script>

<script>
// ── Generar códigos de barras ──────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.barcode-svg[data-value]').forEach(function (el) {
        const val = el.getAttribute('data-value');
        if (val && val.trim() !== '') {
            try {
                JsBarcode(el, val, {
                    format: 'CODE128',
                    width: 1.5,
                    height: 40,
                    displayValue: false,
                    margin: 4,
                    background: '#ffffff',
                    lineColor: '#1e293b'
                });
            } catch (e) {
                el.closest('.barcode-wrap').innerHTML =
                    '<span class="text-xs text-red-400">Código inválido</span>';
            }
        }
    });
});

// ── Vista grid / lista ─────────────────────────────────────────────────────
function setVista(tipo) {
    const grid = document.getElementById('vistaGrid');
    const list = document.getElementById('vistaList');
    const btnG = document.getElementById('btnGrid');
    const btnL = document.getElementById('btnList');
    if (tipo === 'grid') {
        grid.classList.remove('hidden');
        list.classList.add('hidden');
        btnG.classList.add('bg-white', 'shadow', 'text-brand-900');
        btnG.classList.remove('text-gray-500');
        btnL.classList.remove('bg-white', 'shadow', 'text-brand-900');
        btnL.classList.add('text-gray-500');
    } else {
        list.classList.remove('hidden');
        grid.classList.add('hidden');
        btnL.classList.add('bg-white', 'shadow', 'text-brand-900');
        btnL.classList.remove('text-gray-500');
        btnG.classList.remove('bg-white', 'shadow', 'text-brand-900');
        btnG.classList.add('text-gray-500');
    }
}

// ── Filtro búsqueda + categoría ────────────────────────────────────────────
let filtroCategoria = 'todos';

document.getElementById('buscador').addEventListener('input', aplicarFiltros);

function filtrarCategoria(catId, el) {
    filtroCategoria = catId;
    document.querySelectorAll('.cat-pill').forEach(p => p.classList.remove('active'));
    el.classList.add('active');
    aplicarFiltros();
}

function aplicarFiltros() {
    const termino = document.getElementById('buscador').value.toLowerCase().trim();
    // Grid
    document.querySelectorAll('#vistaGrid .product-card').forEach(card => {
        const nombre   = card.dataset.nombre   || '';
        const cat      = card.dataset.categoria || '';
        const barcode  = card.dataset.barcode   || '';
        const matchCat = filtroCategoria === 'todos' || cat === filtroCategoria;
        const matchQ   = !termino || nombre.includes(termino) || barcode.includes(termino);
        card.style.display = (matchCat && matchQ) ? '' : 'none';
    });
    // Lista
    document.querySelectorAll('#tablaBody .product-row').forEach(row => {
        const nombre   = row.dataset.nombre   || '';
        const cat      = row.dataset.categoria || '';
        const barcode  = row.dataset.barcode   || '';
        const matchCat = filtroCategoria === 'todos' || cat === filtroCategoria;
        const matchQ   = !termino || nombre.includes(termino) || barcode.includes(termino);
        row.style.display = (matchCat && matchQ) ? '' : 'none';
    });
}

// ── Modal Producto ─────────────────────────────────────────────────────────
function abrirModalProducto() {
    document.getElementById('modalProductoTitulo').textContent = 'Nuevo Producto';
    document.getElementById('formProducto').action = '../../controllers/CatalogoController.php?accion=crear_producto';
    document.getElementById('formProducto').reset();
    document.getElementById('prod_id').value = '';
    document.getElementById('prod_imagen_actual').value = '';
    resetImgPreview();
    document.getElementById('modalProducto').classList.remove('hidden');
}

function abrirModalEditar(p) {
    document.getElementById('modalProductoTitulo').textContent = 'Editar Producto';
    document.getElementById('formProducto').action = '../../controllers/CatalogoController.php?accion=editar_producto';
    document.getElementById('prod_id').value           = p.id;
    document.getElementById('prod_nombre').value       = p.nombre || '';
    document.getElementById('prod_descripcion').value  = p.descripcion || '';
    document.getElementById('prod_precio_compra').value= p.precio_compra || '';
    document.getElementById('prod_precio_venta').value = p.precio_venta || '';
    document.getElementById('prod_categoria_id').value = p.categoria_id || '';
    document.getElementById('prod_codigo_barras').value= p.codigo_barras || '';
    document.getElementById('prod_imagen_actual').value= p.imagen || '';

    // Preview imagen actual
    if (p.imagen) {
        const preview = document.getElementById('imgPreview');
        preview.src = '../../img/productos/' + p.imagen;
        preview.classList.remove('hidden');
        document.getElementById('imgIcon').classList.add('hidden');
        document.getElementById('imgLabel').textContent = 'Cambiar imagen';
    } else {
        resetImgPreview();
    }
    document.getElementById('modalProducto').classList.remove('hidden');
}

function cerrarModalProducto() {
    document.getElementById('modalProducto').classList.add('hidden');
}

// ── Modal Categoría ────────────────────────────────────────────────────────
function abrirModalCategoria(id, nombre, descripcion) {
    if (id) {
        document.getElementById('modalCategoriaTitulo').textContent = 'Editar Categoría';
        document.getElementById('formCategoria').action = '../../controllers/CatalogoController.php?accion=editar_categoria';
        document.getElementById('cat_id').value          = id;
        document.getElementById('cat_nombre').value      = nombre || '';
        document.getElementById('cat_descripcion').value = descripcion || '';
    } else {
        document.getElementById('modalCategoriaTitulo').textContent = 'Nueva Categoría';
        document.getElementById('formCategoria').action = '../../controllers/CatalogoController.php?accion=crear_categoria';
        document.getElementById('formCategoria').reset();
        document.getElementById('cat_id').value = '';
    }
    document.getElementById('modalCategoria').classList.remove('hidden');
}

function cerrarModalCategoria() {
    document.getElementById('modalCategoria').classList.add('hidden');
}

// ── Preview imagen ─────────────────────────────────────────────────────────
function previewImagen(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function (e) {
            const preview = document.getElementById('imgPreview');
            preview.src = e.target.result;
            preview.classList.remove('hidden');
            document.getElementById('imgIcon').classList.add('hidden');
            document.getElementById('imgLabel').textContent = input.files[0].name;
        };
        reader.readAsDataURL(input.files[0]);
    }
}

function resetImgPreview() {
    const preview = document.getElementById('imgPreview');
    preview.src = '';
    preview.classList.add('hidden');
    document.getElementById('imgIcon').classList.remove('hidden');
    document.getElementById('imgLabel').textContent = 'Seleccionar imagen (JPG, PNG, WEBP)';
}

// ── Confirmar eliminar ─────────────────────────────────────────────────────
function confirmarEliminar(e, nombre) {
    e.preventDefault();
    const url = e.currentTarget.href;
    Swal.fire({
        title: '¿Eliminar producto?',
        html: `<span class="text-gray-600">Se eliminará <strong>"${nombre}"</strong> del catálogo. Esta acción no se puede deshacer.</span>`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc2626',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    }).then(result => {
        if (result.isConfirmed) window.location.href = url;
    });
    return false;
}

// ── Cerrar modales con ESC ─────────────────────────────────────────────────
document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') {
        cerrarModalProducto();
        cerrarModalCategoria();
    }
});
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
