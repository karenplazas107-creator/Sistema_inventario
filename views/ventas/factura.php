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

$numeroFactura = 'FAC-' . str_pad($venta['id'], 6, '0', STR_PAD_LEFT);
$fecha         = date('d/m/Y', strtotime($venta['fecha']));
$hora          = date('h:i A', strtotime($venta['fecha']));
$vendedor      = ($venta['nombres'] ?? '') . ' ' . ($venta['apellidos'] ?? '');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Factura <?= $numeroFactura ?> | Almacén Europa</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Outfit:wght@700;800&display=swap');

        * { font-family: 'Inter', sans-serif; }

        @media print {
            .no-print { display: none !important; }
            body { background: white !important; }
            .factura-wrapper { box-shadow: none !important; margin: 0 !important; }
        }

        body { background: #f1f5f9; }

        .factura-wrapper {
            max-width: 720px;
            margin: 2rem auto;
            background: white;
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(0,0,0,.12);
            overflow: hidden;
        }

        .header-factura {
            background: linear-gradient(135deg, #172554 0%, #1e3a8a 60%, #0ea5e9 100%);
            padding: 2rem 2.5rem;
            color: white;
        }

        .linea-item:nth-child(even) { background: #f8fafc; }

        .total-row {
            background: #1e3a8a;
            color: white;
        }

        .sello {
            border: 3px solid #1e3a8a;
            border-radius: 50%;
            width: 80px;
            height: 80px;
            display: flex;
            align-items: center;
            justify-content: center;
            transform: rotate(-15deg);
            opacity: .15;
            position: absolute;
            right: 2.5rem;
            bottom: 1.5rem;
        }
    </style>
</head>
<body>

<!-- ── Barra de acciones (no se imprime) ──────────────────────────────────── -->
<div class="no-print sticky top-0 z-10 bg-white border-b border-gray-200 shadow-sm">
    <div class="max-w-3xl mx-auto px-6 py-3 flex items-center justify-between">
        <a href="index.php"
           class="flex items-center gap-2 text-sm text-gray-600 hover:text-gray-900 transition font-medium">
            <i class="fas fa-arrow-left"></i> Volver a Ventas
        </a>
        <div class="flex items-center gap-3">
            <span class="text-sm text-gray-500">Factura <strong class="text-gray-800"><?= $numeroFactura ?></strong></span>
            <button onclick="window.print()"
                    class="flex items-center gap-2 px-5 py-2 rounded-xl bg-blue-900 text-white text-sm font-semibold hover:bg-blue-800 transition shadow-md">
                <i class="fas fa-print"></i> Imprimir
            </button>
        </div>
    </div>
</div>

<!-- ── Factura ─────────────────────────────────────────────────────────────── -->
<div class="factura-wrapper">

    <!-- Encabezado -->
    <div class="header-factura">
        <div class="flex items-start justify-between">
            <div>
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-12 h-12 rounded-xl bg-white/20 flex items-center justify-center">
                        <i class="fas fa-store text-white text-xl"></i>
                    </div>
                    <div>
                        <div class="font-extrabold text-2xl tracking-tight" style="font-family:'Outfit',sans-serif;">
                            Almacén Europa
                        </div>
                        <div class="text-blue-200 text-xs tracking-widest uppercase">Sistema de Gestión</div>
                    </div>
                </div>
                <div class="text-blue-200 text-xs space-y-0.5 mt-2">
                    <div><i class="fas fa-map-marker-alt mr-1.5"></i> Parque Principal Fundadores — Sede Principal</div>
                    <div><i class="fas fa-envelope mr-1.5"></i> EuropaVi@gmail.com</div>
                    <div><i class="fas fa-phone mr-1.5"></i> +57 3243785321</div>
                </div>
            </div>
            <div class="text-right">
                <div class="text-blue-200 text-xs uppercase tracking-widest mb-1">Factura de Venta</div>
                <div class="text-3xl font-bold tracking-tight"><?= $numeroFactura ?></div>
                <div class="mt-3 space-y-1 text-sm text-blue-100">
                    <div><span class="text-blue-300 text-xs">Fecha:</span> <?= $fecha ?></div>
                    <div><span class="text-blue-300 text-xs">Hora:</span> <?= $hora ?></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Info vendedor -->
    <div class="px-10 py-5 bg-gray-50 border-b border-gray-100 flex flex-wrap gap-6">
        <div>
            <div class="text-xs text-gray-400 uppercase tracking-wider mb-0.5">Atendido por</div>
            <div class="font-semibold text-gray-800"><?= htmlspecialchars($vendedor) ?></div>
        </div>
        <div>
            <div class="text-xs text-gray-400 uppercase tracking-wider mb-0.5">Rol</div>
            <div class="font-semibold text-gray-800"><?= htmlspecialchars($venta['rol'] ?? 'Vendedor') ?></div>
        </div>
        <div class="ml-auto text-right">
            <div class="text-xs text-gray-400 uppercase tracking-wider mb-0.5">Estado</div>
            <span class="px-3 py-1 rounded-full bg-green-100 text-green-700 text-xs font-bold border border-green-200">
                <i class="fas fa-circle-check mr-1"></i> PAGADO
            </span>
        </div>
    </div>

    <!-- Tabla de productos -->
    <div class="px-10 py-6">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b-2 border-gray-200">
                    <th class="text-left py-2.5 text-xs font-semibold text-gray-500 uppercase tracking-wider">Producto</th>
                    <th class="text-center py-2.5 text-xs font-semibold text-gray-500 uppercase tracking-wider w-20">Cant.</th>
                    <th class="text-right py-2.5 text-xs font-semibold text-gray-500 uppercase tracking-wider w-28">P. Unitario</th>
                    <th class="text-right py-2.5 text-xs font-semibold text-gray-500 uppercase tracking-wider w-28">Subtotal</th>
                </tr>
            </thead>
            <tbody>
            <?php
            $subtotal = 0;
            foreach ($detalles as $i => $d):
                $linea = $d['cantidad'] * $d['precio'];
                $subtotal += $linea;
            ?>
            <tr class="linea-item border-b border-gray-50">
                <td class="py-3 pr-4">
                    <div class="font-medium text-gray-800"><?= htmlspecialchars($d['producto_nombre'] ?? '—') ?></div>
                </td>
                <td class="py-3 text-center text-gray-600"><?= $d['cantidad'] ?></td>
                <td class="py-3 text-right text-gray-600">$<?= number_format($d['precio'], 2, ',', '.') ?></td>
                <td class="py-3 text-right font-semibold text-gray-800">$<?= number_format($linea, 2, ',', '.') ?></td>
            </tr>
            <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr class="border-t border-gray-200">
                    <td colspan="3" class="py-3 text-right text-sm text-gray-500 font-medium">Subtotal</td>
                    <td class="py-3 text-right font-semibold text-gray-700">$<?= number_format($subtotal, 2, ',', '.') ?></td>
                </tr>
                <tr>
                    <td colspan="3" class="py-2 text-right text-sm text-gray-500 font-medium">Descuento</td>
                    <td class="py-2 text-right text-gray-500">$0,00</td>
                </tr>
                <tr class="total-row rounded-xl">
                    <td colspan="3" class="py-4 px-4 text-right font-bold text-lg rounded-l-xl">TOTAL</td>
                    <td class="py-4 px-4 text-right font-extrabold text-2xl rounded-r-xl">
                        $<?= number_format($venta['total'], 2, ',', '.') ?>
                    </td>
                </tr>
            </tfoot>
        </table>
    </div>

    <!-- Pie de factura -->
    <div class="px-10 pb-8 relative">
        <div class="border-t border-dashed border-gray-200 pt-5 flex flex-wrap items-end justify-between gap-4">
            <div class="text-xs text-gray-400 space-y-1">
                <div class="font-semibold text-gray-600 mb-2">Notas</div>
                <div>• Esta factura es el comprobante oficial de su compra.</div>
                <div>• Conserve este documento para cualquier reclamación.</div>
                <div>• Gracias por su preferencia — Almacén Europa.</div>
            </div>
            <div class="text-right">
                <div class="text-xs text-gray-400 mb-1">Firma del vendedor</div>
                <div class="w-40 border-b border-gray-300 mb-1"></div>
                <div class="text-xs text-gray-500"><?= htmlspecialchars($vendedor) ?></div>
            </div>
        </div>

        <!-- Sello decorativo -->
        <div class="sello">
            <i class="fas fa-check-double text-blue-900 text-2xl"></i>
        </div>

        <!-- Código de factura -->
        <div class="mt-6 text-center text-xs text-gray-300">
            <?= $numeroFactura ?> · <?= $fecha ?> · <?= $hora ?> · Almacén Europa
        </div>
    </div>

</div>

<div class="no-print h-10"></div>

</body>
</html>
