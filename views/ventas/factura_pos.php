<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header("Location: ../usuarios/login.php");
    exit;
}

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../models/Venta.php';

$id = intval($_GET['id'] ?? 0);
if ($id <= 0) {
    header("Location: index.php");
    exit;
}

$database   = new Database();
$db         = $database->conectar();
$ventaModel = new Venta($db);

$venta    = $ventaModel->obtenerPorId($id);
$detalles = $ventaModel->obtenerDetalles($id);

if (!$venta) {
    header("Location: index.php");
    exit;
}

$rol            = $_SESSION['usuario']['rol'];
$esComprador    = $rol === 'Comprador';
$numeroFactura  = 'POS-' . str_pad($venta['id'], 6, '0', STR_PAD_LEFT);
$fecha          = date('d/m/Y', strtotime($venta['fecha']));
$hora           = date('h:i A', strtotime($venta['fecha']));
$vendedor       = trim(($venta['nombres'] ?? '') . ' ' . ($venta['apellidos'] ?? ''));

// Calcular subtotal
$subtotal = array_sum(array_map(fn($d) => $d['cantidad'] * $d['precio'], $detalles));
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Factura <?= $numeroFactura ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Outfit:wght@700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        * { font-family: 'Inter', sans-serif; }
        body { background: #f1f5f9; }

        /* ── Ticket POS ── */
        .ticket {
            width: 320px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 20px 60px rgba(0,0,0,.15);
            overflow: hidden;
            position: relative;
        }

        /* Borde dentado inferior del ticket */
        .ticket-edge {
            height: 20px;
            background: repeating-linear-gradient(
                90deg,
                #f1f5f9 0px, #f1f5f9 10px,
                white 10px, white 20px
            );
        }

        .divider-dashed {
            border-top: 2px dashed #e2e8f0;
            margin: 12px 0;
        }

        .item-row {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            padding: 6px 0;
            font-size: 13px;
            border-bottom: 1px solid #f8fafc;
        }

        @media print {
            .no-print { display: none !important; }
            body { background: white !important; }
            .ticket {
                box-shadow: none !important;
                border-radius: 0 !important;
                width: 100% !important;
                max-width: 320px;
                margin: 0 auto;
            }
            .page-wrapper { padding: 0 !important; }
        }
    </style>
</head>
<body>

<!-- ── Barra de acciones ───────────────────────────────────────────────────── -->
<div class="no-print sticky top-0 z-10 bg-white border-b border-gray-200 shadow-sm">
    <div class="max-w-lg mx-auto px-4 py-3 flex items-center justify-between">
        <a href="<?= $esComprador ? '../dashboard/comprador.php' : 'index.php' ?>"
           class="flex items-center gap-2 text-sm text-gray-600 hover:text-gray-900 font-medium transition">
            <i class="fas fa-arrow-left"></i>
            <?= $esComprador ? 'Seguir comprando' : 'Volver a Ventas' ?>
        </a>
        <div class="flex items-center gap-2">
            <?php if ($esComprador): ?>
            <a href="mis_facturas.php"
               class="flex items-center gap-2 px-4 py-2 rounded-xl border border-gray-200 text-gray-600 text-sm font-medium hover:bg-gray-50 transition">
                <i class="fas fa-file-invoice"></i> Mis Facturas
            </a>
            <?php endif; ?>
            <button onclick="window.print()"
                    class="flex items-center gap-2 px-5 py-2 rounded-xl bg-brand-900 text-white text-sm font-semibold hover:bg-brand-800 transition shadow-md">
                <i class="fas fa-print"></i> Imprimir
            </button>
        </div>
    </div>
</div>

<!-- ── Wrapper ─────────────────────────────────────────────────────────────── -->
<div class="page-wrapper min-h-screen flex flex-col items-center justify-start py-10 px-4">

    <!-- Mensaje de éxito (no se imprime) -->
    <div class="no-print mb-6 text-center">
        <div class="inline-flex items-center gap-3 bg-green-50 border border-green-200 rounded-2xl px-6 py-4 shadow-sm">
            <div class="w-10 h-10 rounded-full bg-green-100 flex items-center justify-center text-green-600 text-xl flex-shrink-0">
                <i class="fas fa-circle-check"></i>
            </div>
            <div class="text-left">
                <div class="font-bold text-green-800">¡Venta registrada con éxito!</div>
                <div class="text-sm text-green-600">Comprobante <?= $numeroFactura ?></div>
            </div>
        </div>
    </div>

    <!-- ── TICKET POS ──────────────────────────────────────────────────────── -->
    <div class="ticket">

        <!-- Cabecera -->
        <div class="bg-brand-950 text-white px-6 pt-7 pb-5 text-center">
            <div class="w-12 h-12 rounded-xl bg-white/15 flex items-center justify-center mx-auto mb-3">
                <i class="fas fa-store text-white text-xl"></i>
            </div>
            <div class="font-extrabold text-xl tracking-tight" style="font-family:'Outfit',sans-serif;">
                Almacén Europa
            </div>
            <div class="text-blue-200 text-xs mt-1">Parque Principal Fundadores</div>
            <div class="text-blue-200 text-xs">+57 3243785321</div>
        </div>

        <!-- Borde dentado superior del cuerpo -->
        <div class="ticket-edge" style="background:repeating-linear-gradient(90deg,#172554 0px,#172554 10px,white 10px,white 20px);"></div>

        <!-- Cuerpo del ticket -->
        <div class="px-5 py-4">

            <!-- Número y fecha -->
            <div class="text-center mb-4">
                <div class="text-xs text-gray-400 uppercase tracking-widest">Comprobante de Venta</div>
                <div class="text-2xl font-bold text-gray-900 mt-1"><?= $numeroFactura ?></div>
                <div class="text-xs text-gray-500 mt-1"><?= $fecha ?> — <?= $hora ?></div>
            </div>

            <div class="divider-dashed"></div>

            <!-- Vendedor -->
            <div class="flex justify-between text-xs text-gray-500 mb-1">
                <span>Atendido por:</span>
                <span class="font-semibold text-gray-700"><?= htmlspecialchars($vendedor) ?></span>
            </div>

            <div class="divider-dashed"></div>

            <!-- Productos -->
            <div class="mb-2">
                <div class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Productos</div>
                <?php foreach ($detalles as $d):
                    $linea = $d['cantidad'] * $d['precio'];
                ?>
                <div class="item-row">
                    <div class="flex-1 pr-2">
                        <div class="font-medium text-gray-800 leading-tight"><?= htmlspecialchars($d['producto_nombre'] ?? '—') ?></div>
                        <div class="text-gray-400 text-xs mt-0.5">
                            <?= $d['cantidad'] ?> × $<?= number_format($d['precio'], 2, ',', '.') ?>
                        </div>
                    </div>
                    <div class="font-bold text-gray-800 text-right flex-shrink-0">
                        $<?= number_format($linea, 2, ',', '.') ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <div class="divider-dashed"></div>

            <!-- Totales -->
            <div class="space-y-1.5 mb-4">
                <div class="flex justify-between text-sm text-gray-500">
                    <span>Subtotal</span>
                    <span>$<?= number_format($subtotal, 2, ',', '.') ?></span>
                </div>
                <div class="flex justify-between text-sm text-gray-500">
                    <span>Descuento</span>
                    <span>$0,00</span>
                </div>
                <div class="flex justify-between text-lg font-extrabold text-gray-900 pt-2 border-t border-gray-200">
                    <span>TOTAL</span>
                    <span class="text-brand-900">$<?= number_format($venta['total'], 2, ',', '.') ?></span>
                </div>
            </div>

            <!-- Estado pago -->
            <div class="bg-green-50 border border-green-200 rounded-xl py-2.5 text-center mb-4">
                <span class="text-green-700 font-bold text-sm">
                    <i class="fas fa-circle-check mr-1.5"></i> PAGADO
                </span>
            </div>

            <!-- Código de barras visual (líneas decorativas) -->
            <div class="flex justify-center gap-px mb-4">
                <?php
                $widths = [2,1,3,1,2,1,1,3,2,1,3,2,1,2,1,3,1,2,1,3,2,1,2,3,1,2,1,1,3,2];
                foreach ($widths as $w): ?>
                <div style="width:<?= $w ?>px;height:40px;background:#1e293b;border-radius:1px;"></div>
                <?php endforeach; ?>
            </div>
            <div class="text-center text-xs text-gray-400 font-mono tracking-widest mb-4">
                <?= $numeroFactura ?>
            </div>

            <div class="divider-dashed"></div>

            <!-- Pie -->
            <div class="text-center text-xs text-gray-400 py-2 space-y-1">
                <div class="font-semibold text-gray-600">¡Gracias por su compra!</div>
                <div>Conserve este comprobante</div>
                <div>EuropaVi@gmail.com</div>
            </div>
        </div>

        <!-- Borde dentado inferior -->
        <div class="ticket-edge"></div>

    </div>
    <!-- fin ticket -->

    <!-- Botones de acción (no se imprimen) -->
    <div class="no-print mt-6 flex flex-col sm:flex-row gap-3 w-full max-w-xs">
        <button onclick="window.print()"
                class="flex-1 flex items-center justify-center gap-2 py-3 rounded-xl bg-brand-900 text-white font-semibold text-sm hover:bg-brand-800 transition shadow-md">
            <i class="fas fa-print"></i> Imprimir Ticket
        </button>
        <a href="<?= $esComprador ? '../dashboard/comprador.php' : 'crear.php' ?>"
           class="flex-1 flex items-center justify-center gap-2 py-3 rounded-xl border-2 border-brand-900 text-brand-900 font-semibold text-sm hover:bg-brand-50 transition">
            <i class="fas fa-<?= $esComprador ? 'shopping-cart' : 'plus' ?>"></i>
            <?= $esComprador ? 'Nueva compra' : 'Nueva venta' ?>
        </a>
    </div>

    <?php if ($esComprador): ?>
    <a href="mis_facturas.php"
       class="no-print mt-3 flex items-center gap-2 text-sm text-brand-600 hover:text-brand-800 font-medium transition">
        <i class="fas fa-file-invoice"></i> Ver todas mis facturas
    </a>
    <?php endif; ?>

</div>

<script>
// Configurar Tailwind brand colors
tailwind.config = {
    theme: { extend: { colors: { brand: { 50:'#eff6ff',100:'#dbeafe',600:'#2563eb',800:'#1e40af',900:'#1e3a8a',950:'#172554' } } } }
}
</script>
</body>
</html>
