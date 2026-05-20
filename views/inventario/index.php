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

$productos  = $productoModel->obtenerTodos();
$categorias = $categoriaModel->obtenerTodas();

// ── Estadísticas ──────────────────────────────────────────────────────────────
$totalProductos  = count($productos);
$totalUnidades   = array_sum(array_column($productos, 'stock'));
$criticos        = array_filter($productos, fn($p) => $p['stock'] > 0 && $p['stock'] <= $p['stock_minimo']);
$agotados        = array_filter($productos, fn($p) => $p['stock'] <= 0);
$valorInventario = array_sum(array_map(fn($p) => $p['stock'] * $p['precio_compra'], $productos));

$titulo = "Inventario";
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
    .inv-row { transition: background .15s; }
    .inv-row:hover { background: #f8fafc; }
    .badge-ok   { background:#dcfce7; color:#166534; }
    .badge-bajo { background:#fef9c3; color:#854d0e; }
    .badge-cero { background:#fee2e2; color:#991b1b; }
    .stock-bar-bg { background:#e5e7eb; border-radius:99px; height:8px; overflow:hidden; }
    .stock-bar    { border-radius:99px; height:8px; transition: width .5s ease; }
    .modal-overlay { background:rgba(15,23,42,.55); backdrop-filter:blur(4px); }
    .tab-btn { transition: all .2s; }
    .tab-btn.active { background:#1e3a8a; color:#fff; }
</style>

<!-- ── Encabezado ─────────────────────────────────────────────────────────── -->
<div class="mb-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
    <div>
        <h2 class="text-2xl font-bold text-gray-800">Control de Inventario</h2>
        <p class="text-sm text-gray-500 mt-1">Monitorea el stock, registra entradas y salidas de productos.</p>
    </div>
    <?php if ($puedeEditar): ?>
    <button onclick="abrirModalAjuste()"
            class="flex items-center gap-2 px-5 py-2.5 rounded-xl bg-brand-900 text-white font-semibold text-sm hover:bg-brand-800 transition shadow-md">
        <i class="fas fa-right-left"></i> Registrar Movimiento
    </button>
    <?php endif; ?>
</div>

<!-- ── Stats ─────────────────────────────────────────────────────────────── -->
<div class="grid grid-cols-2 lg:grid-cols-5 gap-4 mb-7">

    <div class="card p-5 flex items-center gap-3">
        <div class="w-11 h-11 rounded-xl bg-blue-100 flex items-center justify-center text-blue-600 text-lg flex-shrink-0">
            <i class="fas fa-box-open"></i>
        </div>
        <div>
            <div class="text-2xl font-bold text-gray-800"><?= $totalProductos ?></div>
            <div class="text-xs text-gray-500">Productos</div>
        </div>
    </div>

    <div class="card p-5 flex items-center gap-3">
        <div class="w-11 h-11 rounded-xl bg-indigo-100 flex items-center justify-center text-indigo-600 text-lg flex-shrink-0">
            <i class="fas fa-cubes"></i>
        </div>
        <div>
            <div class="text-2xl font-bold text-gray-800"><?= number_format($totalUnidades) ?></div>
            <div class="text-xs text-gray-500">Unidades totales</div>
        </div>
    </div>

    <div class="card p-5 flex items-center gap-3">
        <div class="w-11 h-11 rounded-xl bg-emerald-100 flex items-center justify-center text-emerald-600 text-lg flex-shrink-0">
            <i class="fas fa-dollar-sign"></i>
        </div>
        <div>
            <div class="text-xl font-bold text-gray-800">$<?= number_format($valorInventario, 0, ',', '.') ?></div>
            <div class="text-xs text-gray-500">Valor en stock</div>
        </div>
    </div>

    <div class="card p-5 flex items-center gap-3">
        <div class="w-11 h-11 rounded-xl bg-yellow-100 flex items-center justify-center text-yellow-600 text-lg flex-shrink-0">
            <i class="fas fa-triangle-exclamation"></i>
        </div>
        <div>
            <div class="text-2xl font-bold text-gray-800"><?= count($criticos) ?></div>
            <div class="text-xs text-gray-500">Stock crítico</div>
        </div>
    </div>

    <div class="card p-5 flex items-center gap-3">
        <div class="w-11 h-11 rounded-xl bg-red-100 flex items-center justify-center text-red-600 text-lg flex-shrink-0">
            <i class="fas fa-ban"></i>
        </div>
        <div>
            <div class="text-2xl font-bold text-gray-800"><?= count($agotados) ?></div>
            <div class="text-xs text-gray-500">Agotados</div>
        </div>
    </div>

</div>

<!-- ── Alertas de stock crítico ───────────────────────────────────────────── -->
<?php if (count($agotados) > 0 || count($criticos) > 0): ?>
<div class="mb-6 space-y-2">
    <?php if (count($agotados) > 0): ?>
    <div class="flex items-start gap-3 bg-red-50 border border-red-200 rounded-xl px-5 py-3.5">
        <i class="fas fa-circle-exclamation text-red-500 mt-0.5 flex-shrink-0"></i>
        <div class="text-sm text-red-700">
            <strong><?= count($agotados) ?> producto(s) agotado(s):</strong>
            <?= implode(', ', array_map(fn($p) => htmlspecialchars($p['nombre']), array_slice($agotados, 0, 5))) ?>
            <?= count($agotados) > 5 ? ' y ' . (count($agotados) - 5) . ' más...' : '' ?>
        </div>
    </div>
    <?php endif; ?>
    <?php if (count($criticos) > 0): ?>
    <div class="flex items-start gap-3 bg-yellow-50 border border-yellow-200 rounded-xl px-5 py-3.5">
        <i class="fas fa-triangle-exclamation text-yellow-500 mt-0.5 flex-shrink-0"></i>
        <div class="text-sm text-yellow-700">
            <strong><?= count($criticos) ?> producto(s) con stock bajo:</strong>
            <?= implode(', ', array_map(fn($p) => htmlspecialchars($p['nombre']), array_slice($criticos, 0, 5))) ?>
            <?= count($criticos) > 5 ? ' y ' . (count($criticos) - 5) . ' más...' : '' ?>
        </div>
    </div>
    <?php endif; ?>
</div>
<?php endif; ?>

<!-- ── Filtros ────────────────────────────────────────────────────────────── -->
<div class="card p-4 mb-5 flex flex-col sm:flex-row gap-3 items-start sm:items-center">
    <div class="relative flex-1 w-full">
        <i class="fas fa-search absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
        <input id="buscador" type="text" placeholder="Buscar producto..."
               class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500 transition">
    </div>
    <select id="filtroCategoria"
            class="px-4 py-2.5 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500 bg-white transition">
        <option value="">Todas las categorías</option>
        <?php foreach ($categorias as $cat): ?>
        <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['nombre']) ?></option>
        <?php endforeach; ?>
    </select>
    <!-- Tabs de estado -->
    <div class="flex items-center gap-1 bg-gray-100 rounded-xl p-1 flex-shrink-0">
        <button class="tab-btn active px-3 py-1.5 rounded-lg text-xs font-semibold" data-filtro="">Todos</button>
        <button class="tab-btn px-3 py-1.5 rounded-lg text-xs font-semibold text-gray-500" data-filtro="ok">OK</button>
        <button class="tab-btn px-3 py-1.5 rounded-lg text-xs font-semibold text-gray-500" data-filtro="bajo">Bajo</button>
        <button class="tab-btn px-3 py-1.5 rounded-lg text-xs font-semibold text-gray-500" data-filtro="cero">Agotado</button>
    </div>
</div>

<!-- ── Tabla de inventario ────────────────────────────────────────────────── -->
<div class="card overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-100">
                <tr>
                    <th class="px-5 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Producto</th>
                    <th class="px-5 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider hidden md:table-cell">Categoría</th>
                    <th class="px-5 py-3.5 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Estado</th>
                    <th class="px-5 py-3.5 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Stock actual</th>
                    <th class="px-5 py-3.5 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider hidden lg:table-cell">Stock mínimo</th>

                    <?php if ($puedeEditar): ?>
                    <th class="px-5 py-3.5 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Acciones</th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody id="tablaBody" class="divide-y divide-gray-50">
            <?php if (empty($productos)): ?>
                <tr>
                    <td colspan="8" class="py-16 text-center text-gray-400">
                        <i class="fas fa-warehouse text-5xl mb-3 block opacity-20"></i>
                        <p class="font-medium">No hay productos en el inventario.</p>
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach ($productos as $p):
                    $imgSrc = !empty($p['imagen'])
                        ? IMG_PRODUCTOS . htmlspecialchars($p['imagen'])
                        : 'https://placehold.co/48x48/e2e8f0/94a3b8?text=?';

                    $stockMax = max($p['stock_minimo'] * 3, $p['stock'], 1);
                    $pct      = min(100, round(($p['stock'] / $stockMax) * 100));

                    if ($p['stock'] <= 0) {
                        $bc = 'badge-cero'; $bt = 'Agotado';    $color = '#ef4444'; $estado = 'cero';
                    } elseif ($p['stock'] <= $p['stock_minimo']) {
                        $bc = 'badge-bajo'; $bt = 'Stock bajo';  $color = '#f59e0b'; $estado = 'bajo';
                    } else {
                        $bc = 'badge-ok';   $bt = 'Disponible';  $color = '#22c55e'; $estado = 'ok';
                    }

                    $valorStock = $p['stock'] * $p['precio_compra'];
                ?>
                <tr class="inv-row"
                    data-nombre="<?= strtolower(htmlspecialchars($p['nombre'])) ?>"
                    data-categoria="<?= $p['categoria_id'] ?>"
                    data-estado="<?= $estado ?>">

                    <!-- Producto -->
                    <td class="px-5 py-3.5">
                        <div class="flex items-center gap-3">
                            <img src="<?= $imgSrc ?>" alt=""
                                 class="w-11 h-11 rounded-xl object-cover border border-gray-100 flex-shrink-0"
                                 onerror="this.src='https://placehold.co/48x48/e2e8f0/94a3b8?text=?'">
                            <div>
                                <div class="font-semibold text-gray-800 leading-tight"><?= htmlspecialchars($p['nombre']) ?></div>
                                <?php if (!empty($p['codigo_barras'])): ?>
                                <div class="text-xs text-gray-400 font-mono mt-0.5"><?= htmlspecialchars($p['codigo_barras']) ?></div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </td>

                    <!-- Categoría -->
                    <td class="px-5 py-3.5 hidden md:table-cell">
                        <span class="px-2.5 py-1 rounded-full bg-gray-100 text-gray-600 text-xs font-medium">
                            <?= htmlspecialchars($p['categoria_nombre'] ?? '—') ?>
                        </span>
                    </td>

                    <!-- Estado -->
                    <td class="px-5 py-3.5 text-center">
                        <span class="text-xs font-semibold px-2.5 py-1 rounded-full <?= $bc ?>">
                            <?= $bt ?>
                        </span>
                    </td>

                    <!-- Stock actual -->
                    <td class="px-5 py-3.5 text-center">
                        <span class="text-xl font-bold <?= $p['stock'] <= 0 ? 'text-red-500' : ($p['stock'] <= $p['stock_minimo'] ? 'text-yellow-500' : 'text-gray-800') ?>">
                            <?= $p['stock'] ?>
                        </span>
                        <span class="text-xs text-gray-400 ml-1">uds</span>
                    </td>

                    <!-- Stock mínimo -->
                    <td class="px-5 py-3.5 text-center hidden lg:table-cell text-gray-500 text-sm">
                        <?= $p['stock_minimo'] ?> uds
                        <?php if ($puedeEditar): ?>
                        <button onclick='abrirModalMinimo(<?= $p['id'] ?>, "<?= addslashes(htmlspecialchars($p['nombre'])) ?>", <?= $p['stock_minimo'] ?>)'
                                class="ml-1 text-gray-300 hover:text-brand-500 transition text-xs" title="Editar mínimo">
                            <i class="fas fa-pen text-xs"></i>
                        </button>
                        <?php endif; ?>
                    </td>

                    <!-- Acciones -->
                    <?php if ($puedeEditar): ?>
                    <td class="px-5 py-3.5 text-center">
                        <div class="flex items-center justify-center gap-1.5">
                            <button onclick='abrirModalEntrada(<?= $p['id'] ?>, "<?= addslashes(htmlspecialchars($p['nombre'])) ?>", <?= $p['stock'] ?>)'
                                    title="Registrar entrada"
                                    class="p-2 rounded-lg bg-green-50 text-green-700 hover:bg-green-100 transition text-xs">
                                <i class="fas fa-arrow-down"></i>
                            </button>
                            <button onclick='abrirModalSalida(<?= $p['id'] ?>, "<?= addslashes(htmlspecialchars($p['nombre'])) ?>", <?= $p['stock'] ?>)'
                                    title="Registrar salida"
                                    class="p-2 rounded-lg bg-orange-50 text-orange-600 hover:bg-orange-100 transition text-xs">
                                <i class="fas fa-arrow-up"></i>
                            </button>
                            <button onclick='abrirModalCorreccion(<?= $p['id'] ?>, "<?= addslashes(htmlspecialchars($p['nombre'])) ?>", <?= $p['stock'] ?>)'
                                    title="Corrección de stock"
                                    class="p-2 rounded-lg bg-brand-50 text-brand-700 hover:bg-brand-100 transition text-xs">
                                <i class="fas fa-pen-to-square"></i>
                            </button>
                        </div>
                    </td>
                    <?php endif; ?>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
    <div class="px-5 py-3 border-t border-gray-50">
        <span id="contador" class="text-xs text-gray-400"></span>
    </div>
</div>

<!-- ══════════════════════════════════════════════════════
     MODAL: REGISTRAR MOVIMIENTO (Entrada / Salida / Corrección)
══════════════════════════════════════════════════════ -->
<div id="modalMovimiento" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4">
    <div class="modal-overlay absolute inset-0" onclick="cerrarModal('modalMovimiento')"></div>
    <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-md z-10">

        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
            <h3 class="font-bold text-gray-800 text-lg" id="modalMovTitulo">Registrar Movimiento</h3>
            <button onclick="cerrarModal('modalMovimiento')"
                    class="w-8 h-8 rounded-full bg-gray-100 flex items-center justify-center text-gray-500 hover:bg-gray-200 transition">
                <i class="fas fa-times text-sm"></i>
            </button>
        </div>

        <form method="POST" action="../../controllers/InventarioController.php?accion=ajustar" class="p-6 space-y-4">
            <input type="hidden" name="producto_id" id="mov_producto_id">
            <input type="hidden" name="tipo"        id="mov_tipo">

            <!-- Selector de producto (visible solo en modal general) -->
            <div id="selectorProductoWrap">
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">
                    Producto <span class="text-red-500">*</span>
                </label>
                <select id="mov_selector_producto" onchange="seleccionarProducto(this)"
                        class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500 bg-white transition">
                    <option value="">— Selecciona un producto —</option>
                    <?php foreach ($productos as $p): ?>
                    <option value="<?= $p['id'] ?>"
                            data-stock="<?= $p['stock'] ?>"
                            data-nombre="<?= htmlspecialchars($p['nombre']) ?>">
                        <?= htmlspecialchars($p['nombre']) ?> (Stock: <?= $p['stock'] ?>)
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Info producto -->
            <div class="bg-gray-50 rounded-xl p-4">
                <div class="text-xs text-gray-500 mb-1">Producto seleccionado</div>
                <div class="font-semibold text-gray-800" id="mov_nombre">—</div>
                <div class="flex items-center gap-4 mt-2 text-sm">
                    <span class="text-gray-500">Stock actual:</span>
                    <strong id="mov_stock_actual" class="text-brand-900 text-lg">—</strong>
                    <span class="text-gray-400">unidades</span>
                </div>
            </div>

            <!-- Tipo de movimiento (visible solo en modal general) -->
            <div id="tipoMovWrap">
                <label class="block text-xs font-semibold text-gray-600 mb-2">Tipo de movimiento</label>
                <div class="grid grid-cols-3 gap-2">
                    <label class="cursor-pointer">
                        <input type="radio" name="tipo_radio" value="entrada" class="sr-only peer" onchange="setTipo('entrada')">
                        <div class="peer-checked:bg-green-600 peer-checked:text-white peer-checked:border-green-600 border-2 border-gray-200 rounded-xl p-3 text-center text-xs font-semibold text-gray-600 hover:border-green-400 transition">
                            <i class="fas fa-arrow-down block text-lg mb-1"></i> Entrada
                        </div>
                    </label>
                    <label class="cursor-pointer">
                        <input type="radio" name="tipo_radio" value="salida" class="sr-only peer" onchange="setTipo('salida')">
                        <div class="peer-checked:bg-orange-500 peer-checked:text-white peer-checked:border-orange-500 border-2 border-gray-200 rounded-xl p-3 text-center text-xs font-semibold text-gray-600 hover:border-orange-400 transition">
                            <i class="fas fa-arrow-up block text-lg mb-1"></i> Salida
                        </div>
                    </label>
                    <label class="cursor-pointer">
                        <input type="radio" name="tipo_radio" value="correccion" class="sr-only peer" onchange="setTipo('correccion')">
                        <div class="peer-checked:bg-brand-700 peer-checked:text-white peer-checked:border-brand-700 border-2 border-gray-200 rounded-xl p-3 text-center text-xs font-semibold text-gray-600 hover:border-brand-400 transition">
                            <i class="fas fa-pen-to-square block text-lg mb-1"></i> Corrección
                        </div>
                    </label>
                </div>
            </div>

            <!-- Cantidad -->
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5" id="cantidadLabel">
                    Cantidad <span class="text-red-500">*</span>
                </label>
                <input type="number" name="cantidad" id="mov_cantidad" min="1" required
                       class="w-full px-4 py-3 rounded-xl border border-gray-200 text-center text-2xl font-bold focus:outline-none focus:ring-2 focus:ring-brand-500 transition"
                       placeholder="0">
                <p id="mov_preview" class="text-xs text-gray-400 mt-1.5 text-center"></p>
            </div>

            <!-- Motivo -->
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Motivo / Observación</label>
                <input type="text" name="motivo"
                       class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500 transition"
                       placeholder="Ej: Compra a proveedor, ajuste de inventario...">
            </div>

            <div class="flex justify-end gap-3 pt-2 border-t border-gray-100">
                <button type="button" onclick="cerrarModal('modalMovimiento')"
                        class="px-5 py-2.5 rounded-xl border border-gray-200 text-sm font-medium text-gray-600 hover:bg-gray-50 transition">
                    Cancelar
                </button>
                <button type="submit" id="btnGuardarMov"
                        class="px-6 py-2.5 rounded-xl bg-brand-900 text-white text-sm font-semibold hover:bg-brand-800 transition shadow-md">
                    <i class="fas fa-save mr-1.5"></i> Guardar
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ══════════════════════════════════════════════════════
     MODAL: EDITAR STOCK MÍNIMO
══════════════════════════════════════════════════════ -->
<div id="modalMinimo" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4">
    <div class="modal-overlay absolute inset-0" onclick="cerrarModal('modalMinimo')"></div>
    <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-sm z-10">

        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
            <h3 class="font-bold text-gray-800 text-lg">Stock Mínimo de Alerta</h3>
            <button onclick="cerrarModal('modalMinimo')"
                    class="w-8 h-8 rounded-full bg-gray-100 flex items-center justify-center text-gray-500 hover:bg-gray-200 transition">
                <i class="fas fa-times text-sm"></i>
            </button>
        </div>

        <form method="POST" action="../../controllers/InventarioController.php?accion=stock_minimo" class="p-6 space-y-4">
            <input type="hidden" name="producto_id" id="min_producto_id">

            <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-4 text-center">
                <i class="fas fa-triangle-exclamation text-yellow-500 text-2xl mb-2 block"></i>
                <div class="font-semibold text-gray-800" id="min_nombre">—</div>
                <p class="text-xs text-gray-500 mt-1">
                    Cuando el stock baje de este valor, el producto aparecerá como <strong>Stock bajo</strong>.
                </p>
            </div>

            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">
                    Nuevo stock mínimo <span class="text-red-500">*</span>
                </label>
                <input type="number" name="stock_minimo" id="min_valor" min="0" required
                       class="w-full px-4 py-3 rounded-xl border border-gray-200 text-center text-2xl font-bold focus:outline-none focus:ring-2 focus:ring-brand-500 transition">
            </div>

            <div class="flex justify-end gap-3 pt-2 border-t border-gray-100">
                <button type="button" onclick="cerrarModal('modalMinimo')"
                        class="px-5 py-2.5 rounded-xl border border-gray-200 text-sm font-medium text-gray-600 hover:bg-gray-50 transition">
                    Cancelar
                </button>
                <button type="submit"
                        class="px-6 py-2.5 rounded-xl bg-yellow-500 text-white text-sm font-semibold hover:bg-yellow-600 transition shadow-md">
                    <i class="fas fa-save mr-1.5"></i> Actualizar
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ══════════════════════════════════════════════════════
     SCRIPTS
══════════════════════════════════════════════════════ -->
<script>
// ── Variables globales del modal ───────────────────────────────────────────
let _stockActual = 0;
let _tipoActual  = '';

// ── Filtros ────────────────────────────────────────────────────────────────
const filas    = document.querySelectorAll('.inv-row');
const contador = document.getElementById('contador');
let filtroEstado = '';

function aplicarFiltros() {
    const q   = document.getElementById('buscador').value.toLowerCase().trim();
    const cat = document.getElementById('filtroCategoria').value;
    let visible = 0;

    filas.forEach(function (row) {
        const nombre  = row.dataset.nombre   || '';
        const rowCat  = row.dataset.categoria || '';
        const rowEst  = row.dataset.estado   || '';

        const matchQ   = !q            || nombre.includes(q);
        const matchCat = !cat          || rowCat === cat;
        const matchEst = !filtroEstado || rowEst === filtroEstado;

        const show = matchQ && matchCat && matchEst;
        row.style.display = show ? '' : 'none';
        if (show) visible++;
    });
    contador.textContent = visible + ' de ' + filas.length + ' productos';
}

document.getElementById('buscador').addEventListener('input', aplicarFiltros);
document.getElementById('filtroCategoria').addEventListener('change', aplicarFiltros);

document.querySelectorAll('.tab-btn').forEach(function (btn) {
    btn.addEventListener('click', function () {
        document.querySelectorAll('.tab-btn').forEach(b => {
            b.classList.remove('active');
            b.classList.add('text-gray-500');
        });
        this.classList.add('active');
        this.classList.remove('text-gray-500');
        filtroEstado = this.dataset.filtro;
        aplicarFiltros();
    });
});

aplicarFiltros();

// ── Cerrar modales ─────────────────────────────────────────────────────────
function cerrarModal(id) {
    document.getElementById(id).classList.add('hidden');
}
document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') {
        cerrarModal('modalMovimiento');
        cerrarModal('modalMinimo');
    }
});

// ── Abrir modal general ────────────────────────────────────────────────────
function abrirModalAjuste() {
    document.getElementById('modalMovTitulo').textContent = 'Registrar Movimiento';
    document.getElementById('tipoMovWrap').style.display = '';
    document.getElementById('selectorProductoWrap').style.display = '';
    document.getElementById('mov_selector_producto').value = '';
    document.getElementById('mov_producto_id').value = '';
    document.getElementById('mov_nombre').textContent = '— Selecciona un producto —';
    document.getElementById('mov_stock_actual').textContent = '—';
    document.getElementById('mov_cantidad').value = '';
    document.getElementById('mov_preview').textContent = '';
    _stockActual = 0;
    _tipoActual  = '';
    document.getElementById('mov_tipo').value = '';
    document.getElementById('modalMovimiento').classList.remove('hidden');
}

// ── Seleccionar producto desde el select ───────────────────────────────────
function seleccionarProducto(sel) {
    const opt = sel.options[sel.selectedIndex];
    if (!opt.value) {
        document.getElementById('mov_producto_id').value = '';
        document.getElementById('mov_nombre').textContent = '— Selecciona un producto —';
        document.getElementById('mov_stock_actual').textContent = '—';
        _stockActual = 0;
        return;
    }
    document.getElementById('mov_producto_id').value = opt.value;
    document.getElementById('mov_nombre').textContent = opt.dataset.nombre;
    document.getElementById('mov_stock_actual').textContent = opt.dataset.stock;
    _stockActual = parseInt(opt.dataset.stock);
    document.getElementById('mov_cantidad').value = '';
    document.getElementById('mov_preview').textContent = '';
}

// ── Abrir modal entrada ────────────────────────────────────────────────────
function abrirModalEntrada(id, nombre, stock) {
    _stockActual = parseInt(stock);
    _tipoActual  = 'entrada';
    document.getElementById('modalMovTitulo').textContent = '📥 Entrada de Stock';
    document.getElementById('tipoMovWrap').style.display = 'none';
    document.getElementById('selectorProductoWrap').style.display = 'none';
    document.getElementById('mov_producto_id').value = id;
    document.getElementById('mov_tipo').value = 'entrada';
    document.getElementById('mov_nombre').textContent = nombre;
    document.getElementById('mov_stock_actual').textContent = stock;
    document.getElementById('cantidadLabel').textContent = 'Cantidad a ingresar *';
    document.getElementById('mov_cantidad').value = '';
    document.getElementById('mov_preview').textContent = '';
    document.getElementById('btnGuardarMov').className = 'px-6 py-2.5 rounded-xl bg-green-700 text-white text-sm font-semibold hover:bg-green-800 transition shadow-md';
    document.getElementById('modalMovimiento').classList.remove('hidden');
    setTimeout(() => document.getElementById('mov_cantidad').focus(), 100);
}

// ── Abrir modal salida ─────────────────────────────────────────────────────
function abrirModalSalida(id, nombre, stock) {
    _stockActual = parseInt(stock);
    _tipoActual  = 'salida';
    document.getElementById('modalMovTitulo').textContent = '📤 Salida de Stock';
    document.getElementById('tipoMovWrap').style.display = 'none';
    document.getElementById('selectorProductoWrap').style.display = 'none';
    document.getElementById('mov_producto_id').value = id;
    document.getElementById('mov_tipo').value = 'salida';
    document.getElementById('mov_nombre').textContent = nombre;
    document.getElementById('mov_stock_actual').textContent = stock;
    document.getElementById('cantidadLabel').textContent = 'Cantidad a retirar *';
    document.getElementById('mov_cantidad').value = '';
    document.getElementById('mov_preview').textContent = '';
    document.getElementById('btnGuardarMov').className = 'px-6 py-2.5 rounded-xl bg-orange-500 text-white text-sm font-semibold hover:bg-orange-600 transition shadow-md';
    document.getElementById('modalMovimiento').classList.remove('hidden');
    setTimeout(() => document.getElementById('mov_cantidad').focus(), 100);
}

// ── Abrir modal corrección ─────────────────────────────────────────────────
function abrirModalCorreccion(id, nombre, stock) {
    _stockActual = parseInt(stock);
    _tipoActual  = 'correccion';
    document.getElementById('modalMovTitulo').textContent = '✏️ Corrección de Stock';
    document.getElementById('tipoMovWrap').style.display = 'none';
    document.getElementById('selectorProductoWrap').style.display = 'none';
    document.getElementById('mov_producto_id').value = id;
    document.getElementById('mov_tipo').value = 'correccion';
    document.getElementById('mov_nombre').textContent = nombre;
    document.getElementById('mov_stock_actual').textContent = stock;
    document.getElementById('cantidadLabel').textContent = 'Nuevo stock total *';
    document.getElementById('mov_cantidad').value = stock;
    document.getElementById('mov_preview').textContent = '';
    document.getElementById('btnGuardarMov').className = 'px-6 py-2.5 rounded-xl bg-brand-900 text-white text-sm font-semibold hover:bg-brand-800 transition shadow-md';
    document.getElementById('modalMovimiento').classList.remove('hidden');
    setTimeout(() => { document.getElementById('mov_cantidad').focus(); document.getElementById('mov_cantidad').select(); }, 100);
}

// ── Preview en tiempo real ─────────────────────────────────────────────────
document.getElementById('mov_cantidad').addEventListener('input', function () {
    const val = parseInt(this.value) || 0;
    const preview = document.getElementById('mov_preview');
    const tipo = document.getElementById('mov_tipo').value || _tipoActual;

    if (!tipo || val <= 0) { preview.textContent = ''; return; }

    let resultado;
    if (tipo === 'entrada')    resultado = _stockActual + val;
    else if (tipo === 'salida') resultado = Math.max(0, _stockActual - val);
    else                        resultado = val;

    preview.textContent = `Stock resultante: ${resultado} unidades`;
    preview.className = resultado <= 0
        ? 'text-xs text-red-500 mt-1.5 text-center font-semibold'
        : 'text-xs text-green-600 mt-1.5 text-center font-semibold';
});

// ── Selección de tipo en modal general ────────────────────────────────────
function setTipo(tipo) {
    _tipoActual = tipo;
    document.getElementById('mov_tipo').value = tipo;
    const labels = { entrada: 'Cantidad a ingresar *', salida: 'Cantidad a retirar *', correccion: 'Nuevo stock total *' };
    document.getElementById('cantidadLabel').textContent = labels[tipo] || 'Cantidad *';
    document.getElementById('mov_cantidad').value = '';
    document.getElementById('mov_preview').textContent = '';
}

// ── Modal stock mínimo ─────────────────────────────────────────────────────
function abrirModalMinimo(id, nombre, minActual) {
    document.getElementById('min_producto_id').value = id;
    document.getElementById('min_nombre').textContent = nombre;
    document.getElementById('min_valor').value = minActual;
    document.getElementById('modalMinimo').classList.remove('hidden');
    setTimeout(() => { document.getElementById('min_valor').focus(); document.getElementById('min_valor').select(); }, 100);
}
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
