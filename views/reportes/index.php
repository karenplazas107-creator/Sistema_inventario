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
require_once __DIR__ . '/../../models/Producto.php';

$database     = new Database();
$db           = $database->conectar();
$ventaModel   = new Venta($db);
$productoModel = new Producto($db);

// ── Datos para reportes ────────────────────────────────────────────────────
$resumen        = $ventaModel->resumenGeneral();
$ventasPorMes   = $ventaModel->ventasPorMes(12);
$ventasPorDia   = $ventaModel->ventasPorDia(30);
$topProductos   = $ventaModel->productosMasVendidos(8);
$porVendedor    = $ventaModel->ventasPorVendedor();
$productos      = $productoModel->obtenerTodos();

// Inventario stats
$totalProductos  = count($productos);
$totalUnidades   = array_sum(array_column($productos, 'stock'));
$agotados        = count(array_filter($productos, fn($p) => $p['stock'] <= 0));
$stockBajo       = count(array_filter($productos, fn($p) => $p['stock'] > 0 && $p['stock'] <= $p['stock_minimo']));
$valorInventario = array_sum(array_map(fn($p) => $p['stock'] * $p['precio_compra'], $productos));

$titulo = "Reportes e Informes";
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/sidebar.php';
?>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

<style>
    .stat-card { transition: transform .2s, box-shadow .2s; }
    .stat-card:hover { transform: translateY(-3px); box-shadow: 0 12px 30px rgba(30,58,138,.1); }
    .section-title {
        font-size: .7rem;
        font-weight: 700;
        letter-spacing: .1em;
        text-transform: uppercase;
        color: #94a3b8;
        margin-bottom: 1rem;
    }
</style>

<!-- ── Encabezado ─────────────────────────────────────────────────────────── -->
<div class="mb-7 flex flex-col md:flex-row md:items-center md:justify-between gap-3">
    <div>
        <h2 class="text-2xl font-bold text-gray-800">Reportes e Informes</h2>
        <p class="text-sm text-gray-500 mt-1">Resumen general del negocio en tiempo real.</p>
    </div>
    <div class="text-xs text-gray-400 flex items-center gap-1.5">
        <i class="fas fa-circle text-green-400 text-xs"></i>
        Datos actualizados al <?= date('d/m/Y H:i') ?>
    </div>
</div>

<!-- ══════════════════════════════════════════════════════
     SECCIÓN 1 — KPIs PRINCIPALES
══════════════════════════════════════════════════════ -->
<p class="section-title">Resumen de ventas</p>
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">

    <div class="stat-card card p-5 border-l-4 border-blue-500">
        <div class="flex items-start justify-between">
            <div>
                <div class="text-xs text-gray-500 mb-1">Ingresos totales</div>
                <div class="text-2xl font-bold text-gray-800">
                    $<?= number_format($resumen['ingresos_totales'], 0, ',', '.') ?>
                </div>
            </div>
            <div class="w-10 h-10 rounded-xl bg-blue-100 flex items-center justify-center text-blue-600 flex-shrink-0">
                <i class="fas fa-dollar-sign"></i>
            </div>
        </div>
        <div class="text-xs text-gray-400 mt-2"><?= $resumen['total_ventas'] ?> ventas registradas</div>
    </div>

    <div class="stat-card card p-5 border-l-4 border-green-500">
        <div class="flex items-start justify-between">
            <div>
                <div class="text-xs text-gray-500 mb-1">Ingresos hoy</div>
                <div class="text-2xl font-bold text-gray-800">
                    $<?= number_format($resumen['ingresos_hoy'], 0, ',', '.') ?>
                </div>
            </div>
            <div class="w-10 h-10 rounded-xl bg-green-100 flex items-center justify-center text-green-600 flex-shrink-0">
                <i class="fas fa-calendar-day"></i>
            </div>
        </div>
        <div class="text-xs text-gray-400 mt-2"><?= $resumen['ventas_hoy'] ?> ventas hoy</div>
    </div>

    <div class="stat-card card p-5 border-l-4 border-purple-500">
        <div class="flex items-start justify-between">
            <div>
                <div class="text-xs text-gray-500 mb-1">Ingresos este mes</div>
                <div class="text-2xl font-bold text-gray-800">
                    $<?= number_format($resumen['ingresos_mes'], 0, ',', '.') ?>
                </div>
            </div>
            <div class="w-10 h-10 rounded-xl bg-purple-100 flex items-center justify-center text-purple-600 flex-shrink-0">
                <i class="fas fa-calendar-month"></i>
            </div>
        </div>
        <div class="text-xs text-gray-400 mt-2"><?= date('F Y') ?></div>
    </div>

    <div class="stat-card card p-5 border-l-4 border-amber-500">
        <div class="flex items-start justify-between">
            <div>
                <div class="text-xs text-gray-500 mb-1">Ticket promedio</div>
                <div class="text-2xl font-bold text-gray-800">
                    $<?= number_format($resumen['ticket_promedio'], 0, ',', '.') ?>
                </div>
            </div>
            <div class="w-10 h-10 rounded-xl bg-amber-100 flex items-center justify-center text-amber-600 flex-shrink-0">
                <i class="fas fa-receipt"></i>
            </div>
        </div>
        <div class="text-xs text-gray-400 mt-2">Máx: $<?= number_format($resumen['venta_maxima'], 0, ',', '.') ?></div>
    </div>

</div>

<!-- ══════════════════════════════════════════════════════
     SECCIÓN 2 — GRÁFICAS
══════════════════════════════════════════════════════ -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">

    <!-- Gráfica ventas por mes (ocupa 2 columnas) -->
    <div class="lg:col-span-2 card p-6">
        <div class="flex items-center justify-between mb-5">
            <div>
                <div class="font-semibold text-gray-800">Ingresos por mes</div>
                <div class="text-xs text-gray-400 mt-0.5">Últimos 12 meses</div>
            </div>
            <div class="w-9 h-9 rounded-xl bg-brand-50 flex items-center justify-center text-brand-700">
                <i class="fas fa-chart-line text-sm"></i>
            </div>
        </div>
        <div style="height:240px;">
            <canvas id="chartMeses"></canvas>
        </div>
    </div>

    <!-- Gráfica ventas por día (últimos 30 días) -->
    <div class="card p-6">
        <div class="flex items-center justify-between mb-5">
            <div>
                <div class="font-semibold text-gray-800">Ventas diarias</div>
                <div class="text-xs text-gray-400 mt-0.5">Últimos 30 días</div>
            </div>
            <div class="w-9 h-9 rounded-xl bg-green-50 flex items-center justify-center text-green-700">
                <i class="fas fa-chart-bar text-sm"></i>
            </div>
        </div>
        <div style="height:240px;">
            <canvas id="chartDias"></canvas>
        </div>
    </div>

</div>

<!-- ══════════════════════════════════════════════════════
     SECCIÓN 3 — PRODUCTOS + VENDEDORES
══════════════════════════════════════════════════════ -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">

    <!-- Top productos más vendidos -->
    <div class="card p-6">
        <div class="flex items-center justify-between mb-5">
            <div>
                <div class="font-semibold text-gray-800">Productos más vendidos</div>
                <div class="text-xs text-gray-400 mt-0.5">Por unidades vendidas</div>
            </div>
            <i class="fas fa-trophy text-amber-400"></i>
        </div>

        <?php if (empty($topProductos)): ?>
        <div class="text-center py-8 text-gray-400 text-sm">
            <i class="fas fa-box-open text-3xl mb-2 block opacity-20"></i>
            Sin datos de ventas aún.
        </div>
        <?php else:
            $maxUnidades = max(array_column($topProductos, 'unidades_vendidas'));
        ?>
        <div class="space-y-3">
            <?php foreach ($topProductos as $i => $prod):
                $pct = $maxUnidades > 0 ? round(($prod['unidades_vendidas'] / $maxUnidades) * 100) : 0;
                $colores = ['#3b82f6','#8b5cf6','#10b981','#f59e0b','#ef4444','#06b6d4','#ec4899','#84cc16'];
                $color   = $colores[$i % count($colores)];
            ?>
            <div>
                <div class="flex items-center justify-between text-sm mb-1">
                    <div class="flex items-center gap-2 flex-1 min-w-0">
                        <span class="w-5 h-5 rounded-full flex items-center justify-center text-white text-xs font-bold flex-shrink-0"
                              style="background:<?= $color ?>">
                            <?= $i + 1 ?>
                        </span>
                        <span class="font-medium text-gray-800 truncate"><?= htmlspecialchars($prod['nombre']) ?></span>
                    </div>
                    <div class="text-right flex-shrink-0 ml-3">
                        <span class="font-bold text-gray-800"><?= number_format($prod['unidades_vendidas']) ?> uds</span>
                        <div class="text-xs text-gray-400">$<?= number_format($prod['ingresos_generados'], 0, ',', '.') ?></div>
                    </div>
                </div>
                <div class="h-1.5 bg-gray-100 rounded-full overflow-hidden">
                    <div class="h-full rounded-full transition-all duration-700"
                         style="width:<?= $pct ?>%;background:<?= $color ?>"></div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>

    <!-- Rendimiento por vendedor -->
    <div class="card p-6">
        <div class="flex items-center justify-between mb-5">
            <div>
                <div class="font-semibold text-gray-800">Rendimiento por vendedor</div>
                <div class="text-xs text-gray-400 mt-0.5">Total histórico</div>
            </div>
            <i class="fas fa-users text-brand-500"></i>
        </div>

        <?php if (empty($porVendedor)): ?>
        <div class="text-center py-8 text-gray-400 text-sm">
            <i class="fas fa-users text-3xl mb-2 block opacity-20"></i>
            Sin datos de vendedores aún.
        </div>
        <?php else: ?>
        <div class="space-y-3">
            <?php
            $maxIngresos = max(array_column($porVendedor, 'ingresos'));
            foreach ($porVendedor as $v):
                $pct = $maxIngresos > 0 ? round(($v['ingresos'] / $maxIngresos) * 100) : 0;
            ?>
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-full bg-brand-100 text-brand-700 flex items-center justify-center font-bold text-sm flex-shrink-0">
                    <?= strtoupper(substr($v['nombres'], 0, 1)) ?>
                </div>
                <div class="flex-1 min-w-0">
                    <div class="flex items-center justify-between mb-1">
                        <span class="text-sm font-medium text-gray-800 truncate">
                            <?= htmlspecialchars($v['nombres'] . ' ' . $v['apellidos']) ?>
                        </span>
                        <span class="text-xs font-bold text-gray-700 ml-2 flex-shrink-0">
                            $<?= number_format($v['ingresos'], 0, ',', '.') ?>
                        </span>
                    </div>
                    <div class="flex items-center gap-2">
                        <div class="flex-1 h-1.5 bg-gray-100 rounded-full overflow-hidden">
                            <div class="h-full bg-brand-600 rounded-full" style="width:<?= $pct ?>%"></div>
                        </div>
                        <span class="text-xs text-gray-400 flex-shrink-0"><?= $v['total_ventas'] ?> ventas</span>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>

</div>

<!-- ══════════════════════════════════════════════════════
     SECCIÓN 4 — ESTADO DEL INVENTARIO
══════════════════════════════════════════════════════ -->
<p class="section-title">Estado del inventario</p>
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">

    <div class="stat-card card p-5 flex items-center gap-4">
        <div class="w-11 h-11 rounded-xl bg-blue-100 flex items-center justify-center text-blue-600 text-lg flex-shrink-0">
            <i class="fas fa-box-open"></i>
        </div>
        <div>
            <div class="text-2xl font-bold text-gray-800"><?= $totalProductos ?></div>
            <div class="text-xs text-gray-500">Productos</div>
        </div>
    </div>

    <div class="stat-card card p-5 flex items-center gap-4">
        <div class="w-11 h-11 rounded-xl bg-indigo-100 flex items-center justify-center text-indigo-600 text-lg flex-shrink-0">
            <i class="fas fa-cubes"></i>
        </div>
        <div>
            <div class="text-2xl font-bold text-gray-800"><?= number_format($totalUnidades) ?></div>
            <div class="text-xs text-gray-500">Unidades en stock</div>
        </div>
    </div>

    <div class="stat-card card p-5 flex items-center gap-4">
        <div class="w-11 h-11 rounded-xl bg-yellow-100 flex items-center justify-center text-yellow-600 text-lg flex-shrink-0">
            <i class="fas fa-triangle-exclamation"></i>
        </div>
        <div>
            <div class="text-2xl font-bold text-gray-800"><?= $stockBajo ?></div>
            <div class="text-xs text-gray-500">Stock bajo</div>
        </div>
    </div>

    <div class="stat-card card p-5 flex items-center gap-4">
        <div class="w-11 h-11 rounded-xl bg-emerald-100 flex items-center justify-center text-emerald-600 text-lg flex-shrink-0">
            <i class="fas fa-dollar-sign"></i>
        </div>
        <div>
            <div class="text-xl font-bold text-gray-800">$<?= number_format($valorInventario, 0, ',', '.') ?></div>
            <div class="text-xs text-gray-500">Valor en stock</div>
        </div>
    </div>

</div>

<!-- ══════════════════════════════════════════════════════
     SCRIPTS — Chart.js
══════════════════════════════════════════════════════ -->
<script>
// ── Datos desde PHP ────────────────────────────────────────────────────────
const datosMeses = <?= json_encode($ventasPorMes) ?>;
const datosDias  = <?= json_encode($ventasPorDia) ?>;

// ── Paleta ─────────────────────────────────────────────────────────────────
const colorPrimario = '#2563eb';
const colorSecundario = '#10b981';

// ── Opciones base ──────────────────────────────────────────────────────────
const opcionesBase = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: { display: false },
        tooltip: {
            backgroundColor: '#1e293b',
            titleColor: '#f1f5f9',
            bodyColor: '#cbd5e1',
            padding: 10,
            cornerRadius: 8,
            callbacks: {
                label: ctx => ' $' + parseFloat(ctx.raw).toLocaleString('es-CO', { minimumFractionDigits: 0 })
            }
        }
    },
    scales: {
        x: { grid: { display: false }, ticks: { color: '#94a3b8', font: { size: 11 } } },
        y: {
            grid: { color: '#f1f5f9', borderDash: [4,4] },
            ticks: {
                color: '#94a3b8',
                font: { size: 11 },
                callback: v => '$' + (v >= 1000 ? (v/1000).toFixed(0)+'k' : v)
            }
        }
    }
};

// ── Gráfica por mes ────────────────────────────────────────────────────────
(function () {
    const labels   = datosMeses.map(d => d.mes_label);
    const ingresos = datosMeses.map(d => parseFloat(d.ingresos));

    new Chart(document.getElementById('chartMeses'), {
        type: 'line',
        data: {
            labels,
            datasets: [{
                data: ingresos,
                borderColor: colorPrimario,
                backgroundColor: 'rgba(37,99,235,.08)',
                borderWidth: 2.5,
                tension: 0.4,
                fill: true,
                pointBackgroundColor: '#fff',
                pointBorderColor: colorPrimario,
                pointBorderWidth: 2,
                pointRadius: 4,
                pointHoverRadius: 6
            }]
        },
        options: {
            ...opcionesBase,
            plugins: {
                ...opcionesBase.plugins,
                tooltip: {
                    ...opcionesBase.plugins.tooltip,
                    callbacks: {
                        title: ctx => ctx[0].label,
                        label: ctx => ' Ingresos: $' + parseFloat(ctx.raw).toLocaleString('es-CO', {minimumFractionDigits:0}),
                        afterLabel: ctx => {
                            const d = datosMeses[ctx.dataIndex];
                            return ' Ventas: ' + d.total_ventas;
                        }
                    }
                }
            }
        }
    });
})();

// ── Gráfica por día ────────────────────────────────────────────────────────
(function () {
    const labels   = datosDias.map(d => {
        const f = new Date(d.dia + 'T00:00:00');
        return f.toLocaleDateString('es-CO', { day:'2-digit', month:'short' });
    });
    const ingresos = datosDias.map(d => parseFloat(d.ingresos));

    new Chart(document.getElementById('chartDias'), {
        type: 'bar',
        data: {
            labels,
            datasets: [{
                data: ingresos,
                backgroundColor: 'rgba(16,185,129,.75)',
                borderRadius: 6,
                borderSkipped: false,
                hoverBackgroundColor: colorSecundario
            }]
        },
        options: {
            ...opcionesBase,
            plugins: {
                ...opcionesBase.plugins,
                tooltip: {
                    ...opcionesBase.plugins.tooltip,
                    callbacks: {
                        title: ctx => ctx[0].label,
                        label: ctx => ' $' + parseFloat(ctx.raw).toLocaleString('es-CO', {minimumFractionDigits:0}),
                        afterLabel: ctx => {
                            const d = datosDias[ctx.dataIndex];
                            return ' ' + d.total_ventas + ' venta(s)';
                        }
                    }
                }
            }
        }
    });
})();
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
