<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header("Location: ../usuarios/login.php");
    exit;
}

$rol = $_SESSION['usuario']['rol'];
if ($rol === 'Comprador') {
    header("Location: ../dashboard/index.php");
    exit;
}

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../models/Venta.php';

$database = new Database();
$db       = $database->conectar();
$ventaModel = new Venta($db);

$ventas       = $ventaModel->obtenerTodas();
$puedeEliminar = in_array($rol, ['Administrador', 'Vendedor']);

// ── Stats ──────────────────────────────────────────────────────────────────
$totalVentas   = count($ventas);
$totalIngresos = array_sum(array_column($ventas, 'total'));

// Ventas de hoy
$hoy = date('Y-m-d');
$ventasHoy = array_filter($ventas, fn($v) => str_starts_with($v['fecha'], $hoy));
$ingresosHoy = array_sum(array_column($ventasHoy, 'total'));

// Venta más alta
$maxVenta = $totalVentas > 0 ? max(array_column($ventas, 'total')) : 0;

$titulo = "Ventas";
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/sidebar.php';
?>

<style>
    .venta-row { transition: background .15s; }
    .venta-row:hover { background: #f8fafc; }
    .modal-overlay { background: rgba(15,23,42,.55); backdrop-filter: blur(4px); }
</style>

<!-- ── Encabezado ─────────────────────────────────────────────────────────── -->
<div class="mb-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
    <div>
        <h2 class="text-2xl font-bold text-gray-800">Historial de Ventas</h2>
        <p class="text-sm text-gray-500 mt-1">Consulta y administra todos los registros de ventas.</p>
    </div>
    <a href="crear.php"
       class="flex items-center gap-2 px-5 py-2.5 rounded-xl bg-brand-900 text-white font-semibold text-sm hover:bg-brand-800 transition shadow-md">
        <i class="fas fa-plus"></i> Nueva Venta
    </a>
</div>

<!-- ── Stats ─────────────────────────────────────────────────────────────── -->
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-7">

    <div class="card p-5 flex items-center gap-4">
        <div class="w-11 h-11 rounded-xl bg-blue-100 flex items-center justify-center text-blue-600 text-lg flex-shrink-0">
            <i class="fas fa-receipt"></i>
        </div>
        <div>
            <div class="text-2xl font-bold text-gray-800"><?= $totalVentas ?></div>
            <div class="text-xs text-gray-500">Total ventas</div>
        </div>
    </div>

    <div class="card p-5 flex items-center gap-4">
        <div class="w-11 h-11 rounded-xl bg-green-100 flex items-center justify-center text-green-600 text-lg flex-shrink-0">
            <i class="fas fa-dollar-sign"></i>
        </div>
        <div>
            <div class="text-xl font-bold text-gray-800">$<?= number_format($totalIngresos, 0, ',', '.') ?></div>
            <div class="text-xs text-gray-500">Ingresos totales</div>
        </div>
    </div>

    <div class="card p-5 flex items-center gap-4">
        <div class="w-11 h-11 rounded-xl bg-purple-100 flex items-center justify-center text-purple-600 text-lg flex-shrink-0">
            <i class="fas fa-calendar-day"></i>
        </div>
        <div>
            <div class="text-xl font-bold text-gray-800">$<?= number_format($ingresosHoy, 0, ',', '.') ?></div>
            <div class="text-xs text-gray-500">Ingresos hoy (<?= count($ventasHoy) ?> ventas)</div>
        </div>
    </div>

    <div class="card p-5 flex items-center gap-4">
        <div class="w-11 h-11 rounded-xl bg-amber-100 flex items-center justify-center text-amber-600 text-lg flex-shrink-0">
            <i class="fas fa-arrow-trend-up"></i>
        </div>
        <div>
            <div class="text-xl font-bold text-gray-800">$<?= number_format($maxVenta, 0, ',', '.') ?></div>
            <div class="text-xs text-gray-500">Venta más alta</div>
        </div>
    </div>

</div>

<!-- ── Buscador ───────────────────────────────────────────────────────────── -->
<div class="card p-4 mb-5 flex flex-col sm:flex-row gap-3 items-center">
    <div class="relative flex-1 w-full">
        <i class="fas fa-search absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
        <input id="buscador" type="text" placeholder="Buscar por # venta o responsable..."
               class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500 transition">
    </div>
    <input type="date" id="filtroDia"
           class="px-4 py-2.5 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500 bg-white transition"
           title="Filtrar por fecha">
    <button onclick="document.getElementById('filtroDia').value=''; aplicarFiltros();"
            class="px-4 py-2.5 rounded-xl border border-gray-200 text-sm text-gray-500 hover:bg-gray-50 transition">
        <i class="fas fa-filter-circle-xmark mr-1"></i> Limpiar
    </button>
</div>

<!-- ── Tabla ──────────────────────────────────────────────────────────────── -->
<div class="card overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-100">
                <tr>
                    <th class="px-5 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider"># Venta</th>
                    <th class="px-5 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Responsable</th>
                    <th class="px-5 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider hidden md:table-cell">Fecha</th>
                    <th class="px-5 py-3.5 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Total</th>
                    <th class="px-5 py-3.5 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Acciones</th>
                </tr>
            </thead>
            <tbody id="tablaBody" class="divide-y divide-gray-50">
            <?php if (empty($ventas)): ?>
                <tr>
                    <td colspan="5" class="py-16 text-center text-gray-400">
                        <i class="fas fa-receipt text-5xl mb-3 block opacity-20"></i>
                        <p class="font-medium">No hay ventas registradas.</p>
                        <a href="crear.php" class="mt-3 inline-flex items-center gap-2 px-5 py-2 rounded-xl bg-brand-900 text-white text-sm font-semibold hover:bg-brand-800 transition">
                            Registrar primera venta
                        </a>
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach ($ventas as $v): ?>
                <tr class="venta-row"
                    data-buscar="<?= strtolower('#' . str_pad($v['id'],6,'0',STR_PAD_LEFT) . ' ' . $v['nombres'] . ' ' . $v['apellidos']) ?>"
                    data-fecha="<?= substr($v['fecha'], 0, 10) ?>">

                    <!-- # Venta -->
                    <td class="px-5 py-3.5">
                        <span class="font-bold text-brand-900 font-mono">
                            #<?= str_pad($v['id'], 6, '0', STR_PAD_LEFT) ?>
                        </span>
                    </td>

                    <!-- Responsable -->
                    <td class="px-5 py-3.5">
                        <div class="flex items-center gap-2.5">
                            <div class="w-8 h-8 rounded-full bg-brand-100 text-brand-700 flex items-center justify-center font-bold text-xs flex-shrink-0">
                                <?= strtoupper(substr($v['nombres'] ?? '?', 0, 1)) ?>
                            </div>
                            <span class="font-medium text-gray-800">
                                <?= htmlspecialchars(($v['nombres'] ?? '') . ' ' . ($v['apellidos'] ?? '')) ?>
                            </span>
                        </div>
                    </td>

                    <!-- Fecha -->
                    <td class="px-5 py-3.5 hidden md:table-cell text-gray-500">
                        <div><?= date('d/m/Y', strtotime($v['fecha'])) ?></div>
                        <div class="text-xs text-gray-400"><?= date('h:i A', strtotime($v['fecha'])) ?></div>
                    </td>

                    <!-- Total -->
                    <td class="px-5 py-3.5 text-right">
                        <span class="font-bold text-lg text-gray-800">
                            $<?= number_format($v['total'], 2, ',', '.') ?>
                        </span>
                    </td>

                    <!-- Acciones -->
                    <td class="px-5 py-3.5 text-center">
                        <div class="flex items-center justify-center gap-1.5">
                            <button onclick="verDetalle(<?= $v['id'] ?>)"
                                    title="Ver detalle"
                                    class="p-2 rounded-lg bg-blue-50 text-blue-600 hover:bg-blue-100 transition text-xs">
                                <i class="fas fa-eye"></i>
                            </button>
                            <a href="factura.php?id=<?= $v['id'] ?>" target="_blank"
                               title="Imprimir Factura"
                               class="p-2 rounded-lg bg-emerald-50 text-emerald-600 hover:bg-emerald-100 transition text-xs">
                                <i class="fas fa-print"></i>
                            </a>
                            <?php if ($puedeEliminar): ?>
                            <button onclick="confirmarEliminar(<?= $v['id'] ?>)"
                                    title="Eliminar venta"
                                    class="p-2 rounded-lg bg-red-50 text-red-500 hover:bg-red-100 transition text-xs">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                            <?php endif; ?>
                        </div>
                    </td>
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
     MODAL: DETALLE DE VENTA
══════════════════════════════════════════════════════ -->
<div id="modalDetalle" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4">
    <div class="modal-overlay absolute inset-0" onclick="cerrarDetalle()"></div>
    <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-lg z-10 max-h-[90vh] flex flex-col">

        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
            <h3 class="font-bold text-gray-800 text-lg" id="detalleTitulo">Detalle de Venta</h3>
            <button onclick="cerrarDetalle()"
                    class="w-8 h-8 rounded-full bg-gray-100 flex items-center justify-center text-gray-500 hover:bg-gray-200 transition">
                <i class="fas fa-times text-sm"></i>
            </button>
        </div>

        <div id="detalleContenido" class="p-6 overflow-y-auto flex-1">
            <div class="flex items-center justify-center py-8">
                <i class="fas fa-spinner fa-spin text-brand-600 text-2xl"></i>
            </div>
        </div>
    </div>
</div>

<!-- Datos de ventas para el modal (JSON embebido) -->
<script>
const ventasData = <?= json_encode(array_column($ventas, null, 'id')) ?>;
</script>

<?php
// Pre-cargar detalles de todas las ventas para el modal
$detallesMap = [];
foreach ($ventas as $v) {
    $detallesMap[$v['id']] = $ventaModel->obtenerDetalles($v['id']);
}
?>
<script>
const detallesData = <?= json_encode($detallesMap) ?>;

// ── Filtros ────────────────────────────────────────────────────────────────
const filas    = document.querySelectorAll('.venta-row');
const contador = document.getElementById('contador');

function aplicarFiltros() {
    const q    = document.getElementById('buscador').value.toLowerCase().trim();
    const dia  = document.getElementById('filtroDia').value;
    let visible = 0;

    filas.forEach(function (row) {
        const buscar = row.dataset.buscar || '';
        const fecha  = row.dataset.fecha  || '';
        const matchQ   = !q   || buscar.includes(q);
        const matchDia = !dia || fecha === dia;
        const show = matchQ && matchDia;
        row.style.display = show ? '' : 'none';
        if (show) visible++;
    });
    contador.textContent = visible + ' de ' + filas.length + ' ventas';
}

document.getElementById('buscador').addEventListener('input', aplicarFiltros);
document.getElementById('filtroDia').addEventListener('change', aplicarFiltros);
aplicarFiltros();

// ── Modal detalle ──────────────────────────────────────────────────────────
function verDetalle(id) {
    const venta    = ventasData[id];
    const detalles = detallesData[id] || [];

    document.getElementById('detalleTitulo').textContent =
        'Venta #' + String(id).padStart(6, '0');

    let itemsHtml = '';
    let subtotal  = 0;

    if (detalles.length === 0) {
        itemsHtml = '<p class="text-gray-400 text-sm text-center py-4">Sin productos registrados.</p>';
    } else {
        detalles.forEach(function (d) {
            const linea = d.cantidad * d.precio;
            subtotal += linea;
            itemsHtml += `
            <div class="flex items-center justify-between py-3 border-b border-gray-50 last:border-0">
                <div class="flex-1">
                    <div class="font-medium text-gray-800 text-sm">${d.producto_nombre || '—'}</div>
                    <div class="text-xs text-gray-400 mt-0.5">
                        ${d.cantidad} × $${parseFloat(d.precio).toLocaleString('es-CO', {minimumFractionDigits:2})}
                    </div>
                </div>
                <div class="font-bold text-gray-800 text-sm ml-4">
                    $${linea.toLocaleString('es-CO', {minimumFractionDigits:2})}
                </div>
            </div>`;
        });
    }

    const fecha = venta ? new Date(venta.fecha).toLocaleString('es-CO') : '—';
    const responsable = venta ? (venta.nombres + ' ' + venta.apellidos) : '—';

    document.getElementById('detalleContenido').innerHTML = `
        <div class="bg-gray-50 rounded-xl p-4 mb-5 grid grid-cols-2 gap-3 text-sm">
            <div>
                <div class="text-xs text-gray-400 mb-0.5">Responsable</div>
                <div class="font-semibold text-gray-800">${responsable}</div>
            </div>
            <div>
                <div class="text-xs text-gray-400 mb-0.5">Fecha</div>
                <div class="font-semibold text-gray-800">${fecha}</div>
            </div>
        </div>

        <div class="mb-4">
            <div class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Productos</div>
            ${itemsHtml}
        </div>

        <div class="bg-brand-50 rounded-xl p-4 flex items-center justify-between">
            <span class="font-semibold text-brand-900">Total</span>
            <span class="text-2xl font-bold text-brand-900">
                $${parseFloat(venta ? venta.total : subtotal).toLocaleString('es-CO', {minimumFractionDigits:2})}
            </span>
        </div>
        
        <div class="mt-6">
            <a href="factura.php?id=${id}" target="_blank" 
               class="w-full flex items-center justify-center gap-2 py-3 bg-emerald-600 text-white rounded-xl font-bold hover:bg-emerald-700 transition shadow-lg shadow-emerald-200">
                <i class="fas fa-print"></i> IMPRIMIR FACTURA
            </a>
        </div>`;

    document.getElementById('modalDetalle').classList.remove('hidden');
}

function cerrarDetalle() {
    document.getElementById('modalDetalle').classList.add('hidden');
}

document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') cerrarDetalle();
});

// ── Eliminar ───────────────────────────────────────────────────────────────
function confirmarEliminar(id) {
    Swal.fire({
        title: '¿Eliminar venta?',
        html: `<span class="text-gray-600">Se eliminará la venta <strong>#${String(id).padStart(6,'0')}</strong> y todos sus productos. Esta acción no se puede revertir.</span>`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc2626',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    }).then(function (result) {
        if (result.isConfirmed) {
            window.location.href = '../../controllers/VentaController.php?accion=eliminar&id=' + id;
        }
    });
}
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
