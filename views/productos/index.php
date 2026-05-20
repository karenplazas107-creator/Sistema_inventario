<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header("Location: ../usuarios/login.php");
    exit;
}

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../models/Producto.php';
require_once __DIR__ . '/../../models/Categoria.php';
require_once __DIR__ . '/../../config/rutas.php';

$database = new Database();
$db       = $database->conectar();
$productoModel  = new Producto($db);
$categoriaModel = new Categoria($db);

$rol         = $_SESSION['usuario']['rol'];
$puedeEditar = in_array($rol, ['Administrador', 'Bodeguero']);

$productos   = $productoModel->obtenerTodos();
$categorias  = $categoriaModel->obtenerTodas();

// Estadísticas rápidas
$total       = count($productos);
$disponibles = count(array_filter($productos, fn($p) => $p['stock'] > $p['stock_minimo']));
$stockBajo   = count(array_filter($productos, fn($p) => $p['stock'] > 0 && $p['stock'] <= $p['stock_minimo']));
$sinStock    = count(array_filter($productos, fn($p) => $p['stock'] <= 0));

$titulo = "Gestión de Productos";
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/sidebar.php';
?>

<?php if (isset($_SESSION['alert'])): ?>
<script>
document.addEventListener('DOMContentLoaded', function () {
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

<style>
    .prod-row { transition: background .15s; }
    .prod-row:hover { background: #f8fafc; }
    .badge-ok   { background:#dcfce7; color:#166534; }
    .badge-bajo { background:#fef9c3; color:#854d0e; }
    .badge-cero { background:#fee2e2; color:#991b1b; }
    .stock-bar-bg { background:#e5e7eb; border-radius:99px; height:6px; }
    .stock-bar    { border-radius:99px; height:6px; transition: width .4s; }
    .modal-overlay { background:rgba(15,23,42,.55); backdrop-filter:blur(4px); }
    input[type=number]::-webkit-inner-spin-button { opacity:1; }
</style>

<!-- ── Encabezado ─────────────────────────────────────────────────────────── -->
<div class="mb-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
    <div>
        <h2 class="text-2xl font-bold text-gray-800">Gestión de Productos</h2>
        <p class="text-sm text-gray-500 mt-1">Administra el catálogo, precios, stock y códigos de barras.</p>
    </div>
    <?php if ($puedeEditar): ?>
    <button onclick="abrirModalCrear()"
            class="flex items-center gap-2 px-5 py-2.5 rounded-xl bg-brand-900 text-white font-semibold text-sm hover:bg-brand-800 transition shadow-md">
        <i class="fas fa-plus"></i> Nuevo Producto
    </button>
    <?php endif; ?>
</div>

<!-- ── Stats ─────────────────────────────────────────────────────────────── -->
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-7">
    <div class="card p-5 flex items-center gap-4">
        <div class="w-11 h-11 rounded-xl bg-blue-100 flex items-center justify-center text-blue-600 text-lg flex-shrink-0">
            <i class="fas fa-box-open"></i>
        </div>
        <div>
            <div class="text-2xl font-bold text-gray-800"><?= $total ?></div>
            <div class="text-xs text-gray-500">Total productos</div>
        </div>
    </div>
    <div class="card p-5 flex items-center gap-4">
        <div class="w-11 h-11 rounded-xl bg-green-100 flex items-center justify-center text-green-600 text-lg flex-shrink-0">
            <i class="fas fa-circle-check"></i>
        </div>
        <div>
            <div class="text-2xl font-bold text-gray-800"><?= $disponibles ?></div>
            <div class="text-xs text-gray-500">Disponibles</div>
        </div>
    </div>
    <div class="card p-5 flex items-center gap-4">
        <div class="w-11 h-11 rounded-xl bg-yellow-100 flex items-center justify-center text-yellow-600 text-lg flex-shrink-0">
            <i class="fas fa-triangle-exclamation"></i>
        </div>
        <div>
            <div class="text-2xl font-bold text-gray-800"><?= $stockBajo ?></div>
            <div class="text-xs text-gray-500">Stock bajo</div>
        </div>
    </div>
    <div class="card p-5 flex items-center gap-4">
        <div class="w-11 h-11 rounded-xl bg-red-100 flex items-center justify-center text-red-600 text-lg flex-shrink-0">
            <i class="fas fa-ban"></i>
        </div>
        <div>
            <div class="text-2xl font-bold text-gray-800"><?= $sinStock ?></div>
            <div class="text-xs text-gray-500">Sin stock</div>
        </div>
    </div>
</div>

<!-- ── Barra de búsqueda + filtro ────────────────────────────────────────── -->
<div class="card p-4 mb-5 flex flex-col sm:flex-row gap-3 items-start sm:items-center">
    <div class="relative flex-1 w-full">
        <i class="fas fa-search absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
        <input id="buscador" type="text" placeholder="Buscar por nombre, categoría o código de barras..."
               class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500 transition">
    </div>
    <select id="filtroCategoria"
            class="px-4 py-2.5 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500 bg-white transition">
        <option value="">Todas las categorías</option>
        <?php foreach ($categorias as $cat): ?>
        <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['nombre']) ?></option>
        <?php endforeach; ?>
    </select>
    <select id="filtroStock"
            class="px-4 py-2.5 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500 bg-white transition">
        <option value="">Todo el stock</option>
        <option value="ok">Disponible</option>
        <option value="bajo">Stock bajo</option>
        <option value="cero">Sin stock</option>
    </select>
</div>

<!-- ── Tabla de productos ─────────────────────────────────────────────────── -->
<div class="card overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-100">
                <tr>
                    <th class="text-left px-5 py-3.5 text-xs font-semibold text-gray-500 uppercase tracking-wider">Producto</th>
                    <th class="text-left px-5 py-3.5 text-xs font-semibold text-gray-500 uppercase tracking-wider hidden md:table-cell">Categoría</th>
                    <th class="text-left px-5 py-3.5 text-xs font-semibold text-gray-500 uppercase tracking-wider hidden lg:table-cell">Cód. Barras</th>
                    <th class="text-right px-5 py-3.5 text-xs font-semibold text-gray-500 uppercase tracking-wider">P. Compra</th>
                    <th class="text-right px-5 py-3.5 text-xs font-semibold text-gray-500 uppercase tracking-wider">P. Venta</th>
                    <th class="text-center px-5 py-3.5 text-xs font-semibold text-gray-500 uppercase tracking-wider">Stock</th>
                    <?php if ($puedeEditar): ?>
                    <th class="text-center px-5 py-3.5 text-xs font-semibold text-gray-500 uppercase tracking-wider">Acciones</th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody id="tablaBody" class="divide-y divide-gray-50">
            <?php if (empty($productos)): ?>
                <tr>
                    <td colspan="7" class="text-center py-16 text-gray-400">
                        <i class="fas fa-box-open text-4xl mb-3 block opacity-30"></i>
                        <p class="font-medium">No hay productos registrados.</p>
                        <?php if ($puedeEditar): ?>
                        <button onclick="abrirModalCrear()" class="mt-3 px-5 py-2 rounded-xl bg-brand-900 text-white text-sm font-semibold hover:bg-brand-800 transition">
                            Crear el primero
                        </button>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach ($productos as $p):
                    $imgSrc = !empty($p['imagen'])
                        ? IMG_PRODUCTOS . htmlspecialchars($p['imagen'])
                        : 'https://placehold.co/56x56/e2e8f0/94a3b8?text=?';

                    $stockPct = $p['stock_minimo'] > 0
                        ? min(100, round(($p['stock'] / ($p['stock_minimo'] * 3)) * 100))
                        : ($p['stock'] > 0 ? 100 : 0);

                    if ($p['stock'] <= 0) {
                        $badgeClass = 'badge-cero'; $badgeText = 'Sin stock'; $barColor = '#ef4444'; $stockStatus = 'cero';
                    } elseif ($p['stock'] <= $p['stock_minimo']) {
                        $badgeClass = 'badge-bajo'; $badgeText = 'Stock bajo'; $barColor = '#f59e0b'; $stockStatus = 'bajo';
                    } else {
                        $badgeClass = 'badge-ok';   $badgeText = 'Disponible'; $barColor = '#22c55e'; $stockStatus = 'ok';
                    }
                ?>
                <tr class="prod-row"
                    data-nombre="<?= strtolower(htmlspecialchars($p['nombre'])) ?>"
                    data-categoria="<?= $p['categoria_id'] ?>"
                    data-barcode="<?= strtolower(htmlspecialchars($p['codigo_barras'] ?? '')) ?>"
                    data-stock="<?= $stockStatus ?>">

                    <!-- Producto -->
                    <td class="px-5 py-3.5">
                        <div class="flex items-center gap-3">
                            <img src="<?= $imgSrc ?>" alt=""
                                 class="w-12 h-12 rounded-xl object-cover border border-gray-100 flex-shrink-0"
                                 onerror="this.src='https://placehold.co/56x56/e2e8f0/94a3b8?text=?'">
                            <div>
                                <div class="font-semibold text-gray-800 leading-tight"><?= htmlspecialchars($p['nombre']) ?></div>
                                <div class="text-xs text-gray-400 mt-0.5 line-clamp-1 max-w-[200px]">
                                    <?= htmlspecialchars($p['descripcion'] ?? '—') ?>
                                </div>
                            </div>
                        </div>
                    </td>

                    <!-- Categoría -->
                    <td class="px-5 py-3.5 hidden md:table-cell">
                        <span class="px-2.5 py-1 rounded-full bg-gray-100 text-gray-600 text-xs font-medium">
                            <?= htmlspecialchars($p['categoria_nombre'] ?? '—') ?>
                        </span>
                    </td>

                    <!-- Código de barras -->
                    <td class="px-5 py-3.5 hidden lg:table-cell font-mono text-xs text-gray-500">
                        <?= !empty($p['codigo_barras']) ? htmlspecialchars($p['codigo_barras']) : '<span class="text-gray-300">—</span>' ?>
                    </td>

                    <!-- Precio compra -->
                    <td class="px-5 py-3.5 text-right text-gray-600 font-medium">
                        $<?= number_format($p['precio_compra'], 2, ',', '.') ?>
                    </td>

                    <!-- Precio venta -->
                    <td class="px-5 py-3.5 text-right font-bold text-brand-900">
                        $<?= number_format($p['precio_venta'], 2, ',', '.') ?>
                    </td>

                    <!-- Stock -->
                    <td class="px-5 py-3.5">
                        <div class="flex flex-col items-center gap-1.5 min-w-[100px]">
                            <div class="flex items-center justify-between w-full">
                                <span class="text-xs font-semibold px-2 py-0.5 rounded-full <?= $badgeClass ?>">
                                    <?= $badgeText ?>
                                </span>
                                <span class="text-xs font-bold text-gray-700"><?= $p['stock'] ?> uds</span>
                            </div>
                            <div class="stock-bar-bg w-full">
                                <div class="stock-bar" style="width:<?= $stockPct ?>%;background:<?= $barColor ?>"></div>
                            </div>
                            <div class="text-xs text-gray-400 w-full text-right">
                                Mín: <?= $p['stock_minimo'] ?>
                            </div>
                        </div>
                    </td>

                    <!-- Acciones -->
                    <?php if ($puedeEditar): ?>
                    <td class="px-5 py-3.5">
                        <div class="flex items-center justify-center gap-1.5">
                            <!-- Ajustar stock -->
                            <button onclick='abrirModalStock(<?= $p['id'] ?>, "<?= addslashes(htmlspecialchars($p['nombre'])) ?>", <?= $p['stock'] ?>)'
                                    title="Ajustar stock"
                                    class="p-2 rounded-lg bg-green-50 text-green-700 hover:bg-green-100 transition text-xs">
                                <i class="fas fa-cubes-stacked"></i>
                            </button>
                            <!-- Editar -->
                            <button onclick='abrirModalEditar(<?= json_encode($p) ?>)'
                                    title="Editar producto"
                                    class="p-2 rounded-lg bg-brand-50 text-brand-700 hover:bg-brand-100 transition text-xs">
                                <i class="fas fa-pen-to-square"></i>
                            </button>
                            <!-- Eliminar -->
                            <a href="../../controllers/ProductoController.php?accion=eliminar&id=<?= $p['id'] ?>"
                               onclick="return confirmarEliminar(event, '<?= addslashes(htmlspecialchars($p['nombre'])) ?>')"
                               title="Eliminar"
                               class="p-2 rounded-lg bg-red-50 text-red-600 hover:bg-red-100 transition text-xs">
                                <i class="fas fa-trash"></i>
                            </a>
                        </div>
                    </td>
                    <?php endif; ?>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Contador de resultados -->
    <div class="px-5 py-3 border-t border-gray-50 flex items-center justify-between">
        <span id="contadorResultados" class="text-xs text-gray-400"></span>
    </div>
</div>


<!-- ══════════════════════════════════════════════════════
     MODAL: CREAR / EDITAR PRODUCTO
══════════════════════════════════════════════════════ -->
<div id="modalProducto" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4">
    <div class="modal-overlay absolute inset-0" onclick="cerrarModal('modalProducto')"></div>
    <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-2xl max-h-[92vh] overflow-y-auto z-10">

        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 sticky top-0 bg-white z-10">
            <h3 class="font-bold text-gray-800 text-lg" id="modalProdTitulo">Nuevo Producto</h3>
            <button onclick="cerrarModal('modalProducto')"
                    class="w-8 h-8 rounded-full bg-gray-100 flex items-center justify-center text-gray-500 hover:bg-gray-200 transition">
                <i class="fas fa-times text-sm"></i>
            </button>
        </div>

        <form id="formProducto" method="POST" enctype="multipart/form-data"
              action="../../controllers/ProductoController.php?accion=crear" class="p-6 space-y-5">
            <input type="hidden" name="id"            id="prod_id">
            <input type="hidden" name="imagen_actual" id="prod_imagen_actual">

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

                <!-- Nombre -->
                <div class="md:col-span-2">
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">
                        Nombre <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="nombre" id="prod_nombre" required
                           class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500 transition"
                           placeholder="Ej: Arroz Diana 500g">
                </div>

                <!-- Descripción -->
                <div class="md:col-span-2">
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">Descripción</label>
                    <textarea name="descripcion" id="prod_descripcion" rows="2"
                              class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500 transition resize-none"
                              placeholder="Descripción breve..."></textarea>
                </div>

                <!-- Precio compra -->
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">Precio de compra</label>
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm">$</span>
                        <input type="number" name="precio_compra" id="prod_precio_compra" step="0.01" min="0"
                               class="w-full pl-7 pr-4 py-2.5 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500 transition"
                               placeholder="0.00">
                    </div>
                </div>

                <!-- Precio venta -->
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">
                        Precio de venta <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm">$</span>
                        <input type="number" name="precio_venta" id="prod_precio_venta" step="0.01" min="0.01" required
                               class="w-full pl-7 pr-4 py-2.5 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500 transition"
                               placeholder="0.00">
                    </div>
                </div>

                <!-- Categoría -->
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">
                        Categoría <span class="text-red-500">*</span>
                    </label>
                    <select name="categoria_id" id="prod_categoria_id" required
                            class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500 bg-white transition">
                        <option value="">— Seleccionar —</option>
                        <?php foreach ($categorias as $cat): ?>
                        <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['nombre']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Stock mínimo -->
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">
                        Stock mínimo de alerta
                    </label>
                    <input type="number" name="stock_minimo" id="prod_stock_minimo" min="0" value="5"
                           class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500 transition">
                </div>

                <!-- Código de barras -->
                <div class="md:col-span-2">
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">
                        <i class="fas fa-barcode mr-1"></i> Código de barras
                    </label>
                    <input type="text" name="codigo_barras" id="prod_codigo_barras"
                           class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-sm font-mono focus:outline-none focus:ring-2 focus:ring-brand-500 transition"
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
                            <p class="text-xs text-gray-400 mt-1.5">Recomendado: 400×400px. Máx. 2MB.</p>
                        </div>
                    </div>
                </div>

            </div>

            <div class="flex justify-end gap-3 pt-2 border-t border-gray-100">
                <button type="button" onclick="cerrarModal('modalProducto')"
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
     MODAL: AJUSTAR STOCK
══════════════════════════════════════════════════════ -->
<div id="modalStock" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4">
    <div class="modal-overlay absolute inset-0" onclick="cerrarModal('modalStock')"></div>
    <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-sm z-10">

        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
            <h3 class="font-bold text-gray-800 text-lg">Ajustar Stock</h3>
            <button onclick="cerrarModal('modalStock')"
                    class="w-8 h-8 rounded-full bg-gray-100 flex items-center justify-center text-gray-500 hover:bg-gray-200 transition">
                <i class="fas fa-times text-sm"></i>
            </button>
        </div>

        <form method="POST" action="../../controllers/ProductoController.php?accion=ajustar_stock" class="p-6 space-y-4">
            <input type="hidden" name="id" id="stock_prod_id">

            <div class="bg-gray-50 rounded-xl p-4 text-center">
                <div class="text-xs text-gray-500 mb-1">Producto</div>
                <div class="font-semibold text-gray-800" id="stock_prod_nombre">—</div>
                <div class="text-sm text-gray-500 mt-1">
                    Stock actual: <strong id="stock_actual_display" class="text-brand-900">—</strong> unidades
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">
                    Nuevo stock <span class="text-red-500">*</span>
                </label>
                <input type="number" name="nuevo_stock" id="nuevo_stock" min="0" required
                       class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500 transition text-center text-lg font-bold"
                       placeholder="0">
            </div>

            <div class="flex justify-end gap-3 pt-2 border-t border-gray-100">
                <button type="button" onclick="cerrarModal('modalStock')"
                        class="px-5 py-2.5 rounded-xl border border-gray-200 text-sm font-medium text-gray-600 hover:bg-gray-50 transition">
                    Cancelar
                </button>
                <button type="submit"
                        class="px-6 py-2.5 rounded-xl bg-green-700 text-white text-sm font-semibold hover:bg-green-800 transition shadow-md">
                    <i class="fas fa-cubes-stacked mr-1.5"></i> Actualizar Stock
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ══════════════════════════════════════════════════════
     SCRIPTS
══════════════════════════════════════════════════════ -->
<script>
// ── Filtros ────────────────────────────────────────────────────────────────
const buscador        = document.getElementById('buscador');
const filtroCategoria = document.getElementById('filtroCategoria');
const filtroStock     = document.getElementById('filtroStock');
const contador        = document.getElementById('contadorResultados');
const filas           = document.querySelectorAll('#tablaBody .prod-row');

function aplicarFiltros() {
    const q    = buscador.value.toLowerCase().trim();
    const cat  = filtroCategoria.value;
    const stk  = filtroStock.value;
    let visible = 0;

    filas.forEach(function (row) {
        const nombre  = row.dataset.nombre   || '';
        const barcode = row.dataset.barcode  || '';
        const rowCat  = row.dataset.categoria || '';
        const rowStk  = row.dataset.stock    || '';

        const matchQ   = !q   || nombre.includes(q) || barcode.includes(q);
        const matchCat = !cat || rowCat === cat;
        const matchStk = !stk || rowStk === stk;

        const show = matchQ && matchCat && matchStk;
        row.style.display = show ? '' : 'none';
        if (show) visible++;
    });

    contador.textContent = visible + ' de ' + filas.length + ' productos';
}

buscador.addEventListener('input', aplicarFiltros);
filtroCategoria.addEventListener('change', aplicarFiltros);
filtroStock.addEventListener('change', aplicarFiltros);
aplicarFiltros(); // inicial

// ── Modales ────────────────────────────────────────────────────────────────
function cerrarModal(id) {
    document.getElementById(id).classList.add('hidden');
}

document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') {
        cerrarModal('modalProducto');
        cerrarModal('modalStock');
    }
});

// ── Modal Crear ────────────────────────────────────────────────────────────
function abrirModalCrear() {
    document.getElementById('modalProdTitulo').textContent = 'Nuevo Producto';
    document.getElementById('formProducto').action = '../../controllers/ProductoController.php?accion=crear';
    document.getElementById('formProducto').reset();
    document.getElementById('prod_id').value = '';
    document.getElementById('prod_imagen_actual').value = '';
    document.getElementById('prod_stock_minimo').value = '5';
    resetImgPreview();
    document.getElementById('modalProducto').classList.remove('hidden');
}

// ── Modal Editar ───────────────────────────────────────────────────────────
function abrirModalEditar(p) {
    document.getElementById('modalProdTitulo').textContent = 'Editar Producto';
    document.getElementById('formProducto').action = '../../controllers/ProductoController.php?accion=editar';
    document.getElementById('prod_id').value            = p.id;
    document.getElementById('prod_nombre').value        = p.nombre        || '';
    document.getElementById('prod_descripcion').value   = p.descripcion   || '';
    document.getElementById('prod_precio_compra').value = p.precio_compra || '';
    document.getElementById('prod_precio_venta').value  = p.precio_venta  || '';
    document.getElementById('prod_categoria_id').value  = p.categoria_id  || '';
    document.getElementById('prod_codigo_barras').value = p.codigo_barras || '';
    document.getElementById('prod_stock_minimo').value  = p.stock_minimo  || '5';
    document.getElementById('prod_imagen_actual').value = p.imagen        || '';

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

// ── Modal Stock ────────────────────────────────────────────────────────────
function abrirModalStock(id, nombre, stockActual) {
    document.getElementById('stock_prod_id').value       = id;
    document.getElementById('stock_prod_nombre').textContent = nombre;
    document.getElementById('stock_actual_display').textContent = stockActual;
    document.getElementById('nuevo_stock').value         = stockActual;
    document.getElementById('modalStock').classList.remove('hidden');
    setTimeout(() => document.getElementById('nuevo_stock').select(), 100);
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
        html: `<span class="text-gray-600">Se eliminará <strong>"${nombre}"</strong>. Esta acción no se puede deshacer.</span>`,
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
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
