<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header("Location: ../usuarios/login.php");
    exit;
}

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../models/Venta.php';

$database   = new Database();
$db         = $database->conectar();
$ventaModel = new Venta($db);

$rol        = $_SESSION['usuario']['rol'];
$usuario_id = $_SESSION['usuario']['id'];
$nombre     = htmlspecialchars($_SESSION['usuario']['nombres']);

// Obtener ventas del usuario actual
$todasVentas = $ventaModel->obtenerTodas();
$misVentas   = array_filter($todasVentas, fn($v) => $v['usuario_id'] == $usuario_id);
$misVentas   = array_values($misVentas);

$totalGastado = array_sum(array_column($misVentas, 'total'));
$totalPedidos = count($misVentas);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis Facturas | Almacén Europa</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>tailwind.config={theme:{extend:{colors:{brand:{50:'#eff6ff',100:'#dbeafe',500:'#3b82f6',600:'#2563eb',800:'#1e40af',900:'#1e3a8a',950:'#172554'},accent:'#0ea5e9'},fontFamily:{sans:['Inter','sans-serif'],heading:['Outfit','sans-serif']}}}}</script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Outfit:wght@700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body { background:#f1f5f9; font-family:'Inter',sans-serif; }
        .factura-card { transition: transform .2s, box-shadow .2s; }
        .factura-card:hover { transform: translateY(-3px); box-shadow: 0 12px 30px rgba(30,58,138,.1); }
        .modal-overlay { background:rgba(15,23,42,.55); backdrop-filter:blur(4px); }
    </style>
</head>
<body>

<!-- NAVBAR -->
<nav class="sticky top-0 z-40 bg-white/90 backdrop-blur-md border-b border-gray-200 shadow-sm">
    <div class="max-w-4xl mx-auto px-4 h-16 flex items-center justify-between">
        <a href="../dashboard/comprador.php" class="flex items-center gap-2.5 hover:opacity-80 transition">
            <div class="w-9 h-9 rounded-xl bg-brand-900 flex items-center justify-center">
                <i class="fas fa-store text-white text-sm"></i>
            </div>
            <span class="font-heading text-xl font-bold text-gray-900">Almacén<span class="text-brand-600">Europa</span></span>
        </a>
        <div class="flex items-center gap-3">
            <a href="../dashboard/comprador.php"
               class="flex items-center gap-2 px-4 py-2 rounded-xl bg-brand-900 text-white text-sm font-semibold hover:bg-brand-800 transition shadow-md">
                <i class="fas fa-shopping-cart"></i> Seguir comprando
            </a>
            <a href="../../controllers/AuthController.php?accion=logout"
               class="w-10 h-10 rounded-xl bg-gray-100 text-gray-500 flex items-center justify-center hover:bg-red-50 hover:text-red-500 transition">
                <i class="fas fa-right-from-bracket text-sm"></i>
            </a>
        </div>
    </div>
</nav>

<div class="max-w-4xl mx-auto px-4 py-10">

    <!-- Encabezado -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-800 font-heading">Mis Facturas</h1>
        <p class="text-gray-500 mt-1">Historial completo de tus compras en Almacén Europa.</p>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-2 sm:grid-cols-3 gap-4 mb-8">
        <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100 flex items-center gap-4">
            <div class="w-11 h-11 rounded-xl bg-blue-100 flex items-center justify-center text-blue-600 text-lg flex-shrink-0">
                <i class="fas fa-receipt"></i>
            </div>
            <div>
                <div class="text-2xl font-bold text-gray-800"><?= $totalPedidos ?></div>
                <div class="text-xs text-gray-500">Pedidos realizados</div>
            </div>
        </div>
        <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100 flex items-center gap-4">
            <div class="w-11 h-11 rounded-xl bg-green-100 flex items-center justify-center text-green-600 text-lg flex-shrink-0">
                <i class="fas fa-dollar-sign"></i>
            </div>
            <div>
                <div class="text-xl font-bold text-gray-800">$<?= number_format($totalGastado, 0, ',', '.') ?></div>
                <div class="text-xs text-gray-500">Total gastado</div>
            </div>
        </div>
        <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100 flex items-center gap-4 col-span-2 sm:col-span-1">
            <div class="w-11 h-11 rounded-xl bg-purple-100 flex items-center justify-center text-purple-600 text-lg flex-shrink-0">
                <i class="fas fa-star"></i>
            </div>
            <div>
                <div class="text-xl font-bold text-gray-800">
                    $<?= $totalPedidos > 0 ? number_format($totalGastado / $totalPedidos, 0, ',', '.') : '0' ?>
                </div>
                <div class="text-xs text-gray-500">Promedio por pedido</div>
            </div>
        </div>
    </div>

    <!-- Lista de facturas -->
    <?php if (empty($misVentas)): ?>
    <div class="bg-white rounded-2xl p-16 text-center shadow-sm border border-gray-100">
        <i class="fas fa-file-invoice text-6xl text-gray-200 mb-4 block"></i>
        <h3 class="text-xl font-bold text-gray-600 mb-2">Aún no tienes facturas</h3>
        <p class="text-gray-400 mb-6">Realiza tu primera compra para ver tus comprobantes aquí.</p>
        <a href="../dashboard/comprador.php"
           class="inline-flex items-center gap-2 px-6 py-3 rounded-xl bg-brand-900 text-white font-semibold hover:bg-brand-800 transition shadow-md">
            <i class="fas fa-shopping-cart"></i> Ir a la tienda
        </a>
    </div>
    <?php else: ?>

    <!-- Buscador -->
    <div class="bg-white rounded-2xl p-4 shadow-sm border border-gray-100 mb-5">
        <div class="relative">
            <i class="fas fa-search absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
            <input id="buscador" type="text" placeholder="Buscar por número de factura o fecha..."
                   class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500 transition">
        </div>
    </div>

    <div class="space-y-4" id="listaFacturas">
        <?php
        // Pre-cargar detalles
        $detallesMap = [];
        foreach ($misVentas as $v) {
            $detallesMap[$v['id']] = $ventaModel->obtenerDetalles($v['id']);
        }
        ?>
        <?php foreach ($misVentas as $v):
            $numFac  = 'POS-' . str_pad($v['id'], 6, '0', STR_PAD_LEFT);
            $fecha   = date('d/m/Y', strtotime($v['fecha']));
            $hora    = date('h:i A', strtotime($v['fecha']));
            $dets    = $detallesMap[$v['id']] ?? [];
            $nItems  = array_sum(array_column($dets, 'cantidad'));
        ?>
        <div class="factura-card bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden"
             data-buscar="<?= strtolower($numFac . ' ' . $fecha) ?>">

            <div class="flex items-center justify-between p-5">
                <!-- Icono + info -->
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl bg-brand-50 flex items-center justify-center text-brand-700 flex-shrink-0">
                        <i class="fas fa-file-invoice text-xl"></i>
                    </div>
                    <div>
                        <div class="font-bold text-gray-800 font-mono"><?= $numFac ?></div>
                        <div class="text-sm text-gray-500 mt-0.5">
                            <i class="fas fa-calendar-day mr-1 text-gray-400"></i><?= $fecha ?>
                            <span class="mx-1.5 text-gray-300">·</span>
                            <i class="fas fa-clock mr-1 text-gray-400"></i><?= $hora ?>
                        </div>
                        <div class="text-xs text-gray-400 mt-0.5"><?= $nItems ?> artículo(s)</div>
                    </div>
                </div>

                <!-- Total + acciones -->
                <div class="flex items-center gap-4">
                    <div class="text-right">
                        <div class="text-xl font-bold text-brand-900">
                            $<?= number_format($v['total'], 2, ',', '.') ?>
                        </div>
                        <span class="inline-flex items-center gap-1 text-xs font-semibold text-green-700 bg-green-100 px-2 py-0.5 rounded-full mt-1">
                            <i class="fas fa-circle-check text-xs"></i> Pagado
                        </span>
                    </div>
                    <div class="flex flex-col gap-2">
                        <button onclick="verDetalle(<?= $v['id'] ?>)"
                                class="w-9 h-9 rounded-xl bg-blue-50 text-blue-600 hover:bg-blue-100 flex items-center justify-center transition"
                                title="Ver detalle">
                            <i class="fas fa-eye text-sm"></i>
                        </button>
                        <a href="factura_pos.php?id=<?= $v['id'] ?>"
                           class="w-9 h-9 rounded-xl bg-brand-50 text-brand-700 hover:bg-brand-100 flex items-center justify-center transition"
                           title="Ver/Imprimir factura">
                            <i class="fas fa-print text-sm"></i>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Productos (colapsado) -->
            <?php if (!empty($dets)): ?>
            <div class="border-t border-gray-50 px-5 py-3 bg-gray-50/50">
                <div class="flex flex-wrap gap-2">
                    <?php foreach (array_slice($dets, 0, 3) as $d): ?>
                    <span class="text-xs bg-white border border-gray-200 text-gray-600 px-2.5 py-1 rounded-full">
                        <?= htmlspecialchars($d['producto_nombre'] ?? '—') ?> ×<?= $d['cantidad'] ?>
                    </span>
                    <?php endforeach; ?>
                    <?php if (count($dets) > 3): ?>
                    <span class="text-xs text-gray-400 px-2 py-1">+<?= count($dets) - 3 ?> más</span>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
    </div>

    <div class="mt-4 text-center">
        <span id="contador" class="text-xs text-gray-400"></span>
    </div>

    <?php endif; ?>
</div>

<!-- MODAL DETALLE -->
<div id="modalDetalle" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4">
    <div class="modal-overlay absolute inset-0" onclick="cerrarDetalle()"></div>
    <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-md z-10 max-h-[90vh] flex flex-col">
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
            <h3 class="font-bold text-gray-800 text-lg" id="detalleTitulo">Detalle</h3>
            <button onclick="cerrarDetalle()" class="w-8 h-8 rounded-full bg-gray-100 flex items-center justify-center text-gray-500 hover:bg-gray-200 transition">
                <i class="fas fa-times text-sm"></i>
            </button>
        </div>
        <div id="detalleContenido" class="p-6 overflow-y-auto flex-1"></div>
        <div class="px-6 pb-5">
            <a id="btnImprimirDetalle" href="#" target="_blank"
               class="w-full flex items-center justify-center gap-2 py-3 rounded-xl bg-brand-900 text-white font-bold hover:bg-brand-800 transition shadow-md">
                <i class="fas fa-print"></i> Imprimir Factura
            </a>
        </div>
    </div>
</div>

<script>
const detallesData = <?= json_encode($detallesMap) ?>;
const ventasData   = <?= json_encode(array_column($misVentas, null, 'id')) ?>;

// ── Buscador ───────────────────────────────────────────────────────────────
const cards   = document.querySelectorAll('.factura-card');
const contador = document.getElementById('contador');

function actualizarContador(n) {
    if (contador) contador.textContent = n + ' de ' + cards.length + ' facturas';
}

document.getElementById('buscador')?.addEventListener('input', function () {
    const q = this.value.toLowerCase().trim();
    let visible = 0;
    cards.forEach(function (card) {
        const show = !q || card.dataset.buscar.includes(q);
        card.style.display = show ? '' : 'none';
        if (show) visible++;
    });
    actualizarContador(visible);
});

actualizarContador(cards.length);

// ── Modal detalle ──────────────────────────────────────────────────────────
function verDetalle(id) {
    const venta    = ventasData[id];
    const detalles = detallesData[id] || [];
    const numFac   = 'POS-' + String(id).padStart(6, '0');

    document.getElementById('detalleTitulo').textContent = numFac;
    document.getElementById('btnImprimirDetalle').href = 'factura_pos.php?id=' + id;

    let itemsHtml = '';
    let subtotal  = 0;

    detalles.forEach(function (d) {
        const linea = d.cantidad * d.precio;
        subtotal += linea;
        itemsHtml += `
        <div class="flex items-center justify-between py-2.5 border-b border-gray-50 last:border-0">
            <div class="flex-1">
                <div class="font-medium text-gray-800 text-sm">${d.producto_nombre || '—'}</div>
                <div class="text-xs text-gray-400 mt-0.5">${d.cantidad} × $${parseFloat(d.precio).toLocaleString('es-CO',{minimumFractionDigits:2})}</div>
            </div>
            <div class="font-bold text-gray-800 text-sm ml-4">$${linea.toLocaleString('es-CO',{minimumFractionDigits:2})}</div>
        </div>`;
    });

    const fecha = venta ? new Date(venta.fecha).toLocaleString('es-CO') : '—';

    document.getElementById('detalleContenido').innerHTML = `
        <div class="bg-gray-50 rounded-xl p-4 mb-4 text-sm">
            <div class="flex justify-between mb-1">
                <span class="text-gray-500">Fecha</span>
                <span class="font-semibold text-gray-800">${fecha}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-500">Estado</span>
                <span class="text-green-600 font-semibold">✓ Pagado</span>
            </div>
        </div>
        <div class="mb-4">${itemsHtml}</div>
        <div class="bg-brand-50 rounded-xl p-4 flex items-center justify-between">
            <span class="font-semibold text-brand-900">Total</span>
            <span class="text-2xl font-bold text-brand-900">$${parseFloat(venta ? venta.total : subtotal).toLocaleString('es-CO',{minimumFractionDigits:2})}</span>
        </div>`;

    document.getElementById('modalDetalle').classList.remove('hidden');
}

function cerrarDetalle() {
    document.getElementById('modalDetalle').classList.add('hidden');
}

document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') cerrarDetalle();
});
</script>
</body>
</html>
