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

$database      = new Database();
$db            = $database->conectar();
$ventaModel    = new Venta($db);
$productoModel = new Producto($db);

$tipo = $_GET['tipo'] ?? 'inventario';
$fecha = date('d/m/Y H:i');
$fechaArchivo = date('Y-m-d');

// ── Datos ────────────────────────────────────────────────────────────────────
$resumen       = $ventaModel->resumenGeneral();
$ventasPorMes  = $ventaModel->ventasPorMes(12);
$topProductos  = $ventaModel->productosMasVendidos(10);
$porVendedor   = $ventaModel->ventasPorVendedor();
$productos     = $productoModel->obtenerTodos();

$totalProductos  = count($productos);
$totalUnidades   = array_sum(array_column($productos, 'stock'));
$agotados        = count(array_filter($productos, fn($p) => $p['stock'] <= 0));
$stockBajo       = count(array_filter($productos, fn($p) => $p['stock'] > 0 && $p['stock'] <= $p['stock_minimo']));
$valorInventario = array_sum(array_map(fn($p) => $p['stock'] * $p['precio_compra'], $productos));

$titulosTipo = [
    'inventario'    => 'Reporte de Inventario',
    'ventas_mes'    => 'Reporte de Ventas por Mes',
    'top_productos' => 'Productos Más Vendidos',
    'vendedores'    => 'Rendimiento por Vendedor',
    'stock_bajo'    => 'Productos con Stock Bajo / Agotado',
    'resumen'       => 'Resumen General',
];
$tituloPDF = $titulosTipo[$tipo] ?? 'Reporte';

function fmt($n) {
    return '$' . number_format((float)$n, 0, ',', '.');
}

function estadoBadge($stock, $minimo) {
    if ($stock <= 0)         return '<span style="color:#dc2626;font-weight:700;">Agotado</span>';
    if ($stock <= $minimo)   return '<span style="color:#d97706;font-weight:700;">Stock Bajo</span>';
    return '<span style="color:#059669;font-weight:700;">Normal</span>';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title><?= htmlspecialchars($tituloPDF) ?> | Almacén Europa</title>
<style>
    * { margin: 0; padding: 0; box-sizing: border-box; }

    body {
        font-family: 'Segoe UI', Arial, sans-serif;
        font-size: 12px;
        color: #1e293b;
        background: #fff;
    }

    /* ── CABECERA ── */
    .header {
        background: #1e293b;
        color: white;
        padding: 24px 32px 20px;
        display: flex;
        justify-content: space-between;
        align-items: flex-end;
        border-bottom: 4px solid #2563eb;
    }
    .header-brand { font-size: 22px; font-weight: 800; letter-spacing: 1px; }
    .header-sub   { font-size: 11px; color: #94a3b8; margin-top: 3px; }
    .header-right { text-align: right; font-size: 11px; color: #94a3b8; }
    .header-title { font-size: 15px; font-weight: 700; color: #fff; margin-bottom: 2px; }

    /* ── CUERPO ── */
    .content { padding: 28px 32px; }

    /* ── SECCIÓN ── */
    .section-label {
        font-size: 9px;
        font-weight: 800;
        letter-spacing: 2px;
        text-transform: uppercase;
        color: #64748b;
        margin: 24px 0 10px;
        border-bottom: 1px solid #e2e8f0;
        padding-bottom: 5px;
    }

    /* ── TABLA ── */
    table { width: 100%; border-collapse: collapse; margin-bottom: 8px; }
    thead th {
        background: #1e293b;
        color: #fff;
        padding: 8px 10px;
        text-align: left;
        font-size: 10px;
        font-weight: 700;
        letter-spacing: .5px;
        text-transform: uppercase;
    }
    thead th.center { text-align: center; }
    thead th.right  { text-align: right; }
    tbody tr:nth-child(even) { background: #f8fafc; }
    tbody tr:hover { background: #eff6ff; }
    tbody td { padding: 7px 10px; border-bottom: 1px solid #e2e8f0; }
    tbody td.center { text-align: center; }
    tbody td.right  { text-align: right; }
    tfoot td {
        background: #1e293b;
        color: #fff;
        font-weight: 700;
        padding: 8px 10px;
    }
    tfoot td.right { text-align: right; }

    /* ── KPI CARDS ── */
    .kpi-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 12px;
        margin-bottom: 20px;
    }
    .kpi-card {
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        padding: 14px;
        background: #f8fafc;
    }
    .kpi-label { font-size: 10px; color: #64748b; margin-bottom: 4px; }
    .kpi-value { font-size: 20px; font-weight: 800; color: #1e293b; }
    .kpi-sub   { font-size: 10px; color: #94a3b8; margin-top: 3px; }
    .kpi-card.blue   { border-left: 4px solid #3b82f6; }
    .kpi-card.green  { border-left: 4px solid #10b981; }
    .kpi-card.purple { border-left: 4px solid #8b5cf6; }
    .kpi-card.amber  { border-left: 4px solid #f59e0b; }
    .kpi-card.red    { border-left: 4px solid #ef4444; }

    /* ── PIE ── */
    .footer-bar {
        margin-top: 40px;
        border-top: 1px solid #e2e8f0;
        padding-top: 12px;
        display: flex;
        justify-content: space-between;
        font-size: 10px;
        color: #94a3b8;
    }

    /* ── BOTONES (solo pantalla) ── */
    .no-print {
        position: fixed;
        top: 20px;
        right: 20px;
        display: flex;
        gap: 10px;
        z-index: 999;
    }
    .btn {
        padding: 10px 22px;
        border-radius: 10px;
        font-size: 13px;
        font-weight: 700;
        cursor: pointer;
        border: none;
        display: flex;
        align-items: center;
        gap: 8px;
        text-decoration: none;
        transition: all .2s;
    }
    .btn-print  { background: #1e293b; color: #fff; }
    .btn-print:hover  { background: #0f172a; }
    .btn-back   { background: #f1f5f9; color: #1e293b; border: 1px solid #e2e8f0; }
    .btn-back:hover   { background: #e2e8f0; }

    /* ── PRINT ── */
    @media print {
        .no-print { display: none !important; }
        body { font-size: 11px; }
        .header { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        thead th { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        tfoot td  { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        .kpi-card { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        table { page-break-inside: auto; }
        tr    { page-break-inside: avoid; }
    }
</style>
</head>
<body>

<!-- ── Botones flotantes (no se imprimen) ─────────────────────────────────── -->
<div class="no-print">
    <a href="index.php" class="btn btn-back">
        ← Volver
    </a>
    <button onclick="window.print()" class="btn btn-print">
        ⬇ Descargar / Imprimir PDF
    </button>
</div>

<!-- ── CABECERA ───────────────────────────────────────────────────────────── -->
<div class="header">
    <div>
        <div class="header-brand">ALMACÉN EUROPA</div>
        <div class="header-sub">Sistema de Gestión · <?= htmlspecialchars($tituloPDF) ?></div>
    </div>
    <div class="header-right">
        <div class="header-title"><?= htmlspecialchars($tituloPDF) ?></div>
        <div>Generado: <?= $fecha ?></div>
        <div>Usuario: <?= htmlspecialchars($_SESSION['usuario']['nombres'] ?? '') ?> · <?= htmlspecialchars($rol) ?></div>
    </div>
</div>

<div class="content">

<?php /* ════════════════════════════════════════════════════════════════════
         TIPO: RESUMEN GENERAL
═══════════════════════════════════════════════════════════════════════════ */ ?>
<?php if ($tipo === 'resumen'): ?>

    <div class="section-label">Indicadores Clave de Ventas</div>
    <div class="kpi-grid">
        <div class="kpi-card blue">
            <div class="kpi-label">Ingresos Totales</div>
            <div class="kpi-value"><?= fmt($resumen['ingresos_totales']) ?></div>
            <div class="kpi-sub"><?= $resumen['total_ventas'] ?> ventas</div>
        </div>
        <div class="kpi-card green">
            <div class="kpi-label">Ingresos Hoy</div>
            <div class="kpi-value"><?= fmt($resumen['ingresos_hoy']) ?></div>
            <div class="kpi-sub"><?= $resumen['ventas_hoy'] ?> ventas hoy</div>
        </div>
        <div class="kpi-card purple">
            <div class="kpi-label">Ingresos Este Mes</div>
            <div class="kpi-value"><?= fmt($resumen['ingresos_mes']) ?></div>
            <div class="kpi-sub"><?= date('F Y') ?></div>
        </div>
        <div class="kpi-card amber">
            <div class="kpi-label">Ticket Promedio</div>
            <div class="kpi-value"><?= fmt($resumen['ticket_promedio']) ?></div>
            <div class="kpi-sub">Máx: <?= fmt($resumen['venta_maxima']) ?></div>
        </div>
    </div>

    <div class="section-label">Estado del Inventario</div>
    <div class="kpi-grid">
        <div class="kpi-card blue">
            <div class="kpi-label">Total Productos</div>
            <div class="kpi-value"><?= $totalProductos ?></div>
        </div>
        <div class="kpi-card green">
            <div class="kpi-label">Unidades en Stock</div>
            <div class="kpi-value"><?= number_format($totalUnidades) ?></div>
        </div>
        <div class="kpi-card amber">
            <div class="kpi-label">Stock Bajo</div>
            <div class="kpi-value"><?= $stockBajo ?></div>
        </div>
        <div class="kpi-card red">
            <div class="kpi-label">Agotados</div>
            <div class="kpi-value"><?= $agotados ?></div>
        </div>
    </div>

    <div class="section-label">Valor del Inventario</div>
    <div class="kpi-grid" style="grid-template-columns: repeat(2, 1fr);">
        <div class="kpi-card green">
            <div class="kpi-label">Valor Total en Stock (precio compra)</div>
            <div class="kpi-value" style="font-size:24px;"><?= fmt($valorInventario) ?></div>
        </div>
        <div class="kpi-card purple">
            <div class="kpi-label">Valor Potencial de Venta</div>
            <div class="kpi-value" style="font-size:24px;">
                <?= fmt(array_sum(array_map(fn($p) => $p['stock'] * $p['precio_venta'], $productos))) ?>
            </div>
        </div>
    </div>

<?php /* ════════════════════════════════════════════════════════════════════
         TIPO: INVENTARIO COMPLETO
═══════════════════════════════════════════════════════════════════════════ */ ?>
<?php elseif ($tipo === 'inventario'): ?>

    <div class="section-label">Listado Completo de Inventario (<?= $totalProductos ?> productos)</div>
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Producto</th>
                <th>Categoría</th>
                <th class="right">P. Compra</th>
                <th class="right">P. Venta</th>
                <th class="center">Stock</th>
                <th class="center">Mín.</th>
                <th class="center">Estado</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($productos as $i => $p): ?>
            <tr>
                <td class="center"><?= $i + 1 ?></td>
                <td><strong><?= htmlspecialchars($p['nombre']) ?></strong></td>
                <td><?= htmlspecialchars($p['categoria_nombre'] ?? '—') ?></td>
                <td class="right"><?= fmt($p['precio_compra']) ?></td>
                <td class="right"><?= fmt($p['precio_venta']) ?></td>
                <td class="center"><?= $p['stock'] ?></td>
                <td class="center"><?= $p['stock_minimo'] ?></td>
                <td class="center"><?= estadoBadge($p['stock'], $p['stock_minimo']) ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
        <tfoot>
            <tr>
                <td colspan="5"><strong>TOTALES</strong></td>
                <td class="center"><strong><?= number_format($totalUnidades) ?></strong></td>
                <td></td>
                <td class="center"><strong><?= fmt($valorInventario) ?></strong></td>
            </tr>
        </tfoot>
    </table>

<?php /* ════════════════════════════════════════════════════════════════════
         TIPO: VENTAS POR MES
═══════════════════════════════════════════════════════════════════════════ */ ?>
<?php elseif ($tipo === 'ventas_mes'): ?>

    <?php
    $totalVentasMes = array_sum(array_column($ventasPorMes, 'total_ventas'));
    $totalIngresosMes = array_sum(array_column($ventasPorMes, 'ingresos'));
    ?>

    <div class="section-label">Ventas Agrupadas por Mes — Últimos 12 meses</div>
    <div class="kpi-grid" style="grid-template-columns: repeat(3,1fr); margin-bottom:20px;">
        <div class="kpi-card blue">
            <div class="kpi-label">Total Ventas (12 meses)</div>
            <div class="kpi-value"><?= number_format($totalVentasMes) ?></div>
        </div>
        <div class="kpi-card green">
            <div class="kpi-label">Ingresos Acumulados</div>
            <div class="kpi-value"><?= fmt($totalIngresosMes) ?></div>
        </div>
        <div class="kpi-card purple">
            <div class="kpi-label">Promedio Mensual</div>
            <div class="kpi-value"><?= count($ventasPorMes) > 0 ? fmt($totalIngresosMes / count($ventasPorMes)) : '$0' ?></div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Mes</th>
                <th class="center">N° Ventas</th>
                <th class="right">Ingresos</th>
                <th class="right">Promedio por Venta</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($ventasPorMes as $i => $d): ?>
            <tr>
                <td class="center"><?= $i + 1 ?></td>
                <td><strong><?= htmlspecialchars($d['mes_label']) ?></strong></td>
                <td class="center"><?= $d['total_ventas'] ?></td>
                <td class="right"><?= fmt($d['ingresos']) ?></td>
                <td class="right"><?= $d['total_ventas'] > 0 ? fmt($d['ingresos'] / $d['total_ventas']) : '$0' ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
        <tfoot>
            <tr>
                <td colspan="2"><strong>TOTAL</strong></td>
                <td class="center"><strong><?= number_format($totalVentasMes) ?></strong></td>
                <td class="right"><strong><?= fmt($totalIngresosMes) ?></strong></td>
                <td></td>
            </tr>
        </tfoot>
    </table>

<?php /* ════════════════════════════════════════════════════════════════════
         TIPO: TOP PRODUCTOS
═══════════════════════════════════════════════════════════════════════════ */ ?>
<?php elseif ($tipo === 'top_productos'): ?>

    <div class="section-label">Productos Más Vendidos — por unidades</div>
    <?php if (empty($topProductos)): ?>
        <p style="text-align:center;padding:40px;color:#94a3b8;">Sin datos de ventas registrados aún.</p>
    <?php else: ?>
    <table>
        <thead>
            <tr>
                <th class="center">#</th>
                <th>Producto</th>
                <th>Categoría</th>
                <th class="center">Unidades Vendidas</th>
                <th class="right">Ingresos Generados</th>
                <th class="right">Precio Unitario</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($topProductos as $i => $p): ?>
            <tr>
                <td class="center" style="font-weight:800;color:#2563eb;"><?= $i + 1 ?></td>
                <td><strong><?= htmlspecialchars($p['nombre']) ?></strong></td>
                <td><?= htmlspecialchars($p['categoria'] ?? '—') ?></td>
                <td class="center"><?= number_format($p['unidades_vendidas']) ?></td>
                <td class="right"><?= fmt($p['ingresos_generados']) ?></td>
                <td class="right"><?= fmt($p['precio_venta']) ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
        <tfoot>
            <tr>
                <td colspan="3"><strong>TOTAL</strong></td>
                <td class="center"><strong><?= number_format(array_sum(array_column($topProductos, 'unidades_vendidas'))) ?></strong></td>
                <td class="right"><strong><?= fmt(array_sum(array_column($topProductos, 'ingresos_generados'))) ?></strong></td>
                <td></td>
            </tr>
        </tfoot>
    </table>
    <?php endif; ?>

<?php /* ════════════════════════════════════════════════════════════════════
         TIPO: RENDIMIENTO POR VENDEDOR
═══════════════════════════════════════════════════════════════════════════ */ ?>
<?php elseif ($tipo === 'vendedores'): ?>

    <div class="section-label">Rendimiento por Vendedor — Histórico</div>
    <?php if (empty($porVendedor)): ?>
        <p style="text-align:center;padding:40px;color:#94a3b8;">Sin datos de vendedores registrados.</p>
    <?php else: ?>
    <?php
    $totalV = array_sum(array_column($porVendedor, 'total_ventas'));
    $totalI = array_sum(array_column($porVendedor, 'ingresos'));
    ?>
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Vendedor</th>
                <th>Rol</th>
                <th class="center">N° Ventas</th>
                <th class="right">Ingresos</th>
                <th class="right">Promedio por Venta</th>
                <th class="center">% del Total</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($porVendedor as $i => $v): ?>
            <tr>
                <td class="center"><?= $i + 1 ?></td>
                <td><strong><?= htmlspecialchars($v['nombres'] . ' ' . $v['apellidos']) ?></strong></td>
                <td><?= htmlspecialchars($v['rol'] ?? '—') ?></td>
                <td class="center"><?= $v['total_ventas'] ?></td>
                <td class="right"><?= fmt($v['ingresos']) ?></td>
                <td class="right"><?= $v['total_ventas'] > 0 ? fmt($v['ingresos'] / $v['total_ventas']) : '$0' ?></td>
                <td class="center"><?= $totalI > 0 ? round(($v['ingresos'] / $totalI) * 100, 1) . '%' : '0%' ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
        <tfoot>
            <tr>
                <td colspan="3"><strong>TOTAL</strong></td>
                <td class="center"><strong><?= number_format($totalV) ?></strong></td>
                <td class="right"><strong><?= fmt($totalI) ?></strong></td>
                <td></td>
                <td class="center"><strong>100%</strong></td>
            </tr>
        </tfoot>
    </table>
    <?php endif; ?>

<?php /* ════════════════════════════════════════════════════════════════════
         TIPO: STOCK BAJO / AGOTADO
═══════════════════════════════════════════════════════════════════════════ */ ?>
<?php elseif ($tipo === 'stock_bajo'): ?>

    <?php $criticos = array_filter($productos, fn($p) => $p['stock'] <= $p['stock_minimo']); ?>

    <div class="kpi-grid" style="grid-template-columns:repeat(3,1fr);margin-bottom:20px;">
        <div class="kpi-card red">
            <div class="kpi-label">Productos Críticos</div>
            <div class="kpi-value"><?= count($criticos) ?></div>
        </div>
        <div class="kpi-card red">
            <div class="kpi-label">Agotados</div>
            <div class="kpi-value"><?= $agotados ?></div>
        </div>
        <div class="kpi-card amber">
            <div class="kpi-label">Stock Bajo</div>
            <div class="kpi-value"><?= $stockBajo ?></div>
        </div>
    </div>

    <div class="section-label">Productos con Stock Bajo o Agotado (<?= count($criticos) ?>)</div>

    <?php if (empty($criticos)): ?>
        <p style="text-align:center;padding:40px;color:#059669;font-weight:700;">
            ✓ No hay productos con stock crítico. ¡Inventario en buen estado!
        </p>
    <?php else: ?>
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Producto</th>
                <th>Categoría</th>
                <th class="right">P. Compra</th>
                <th class="right">P. Venta</th>
                <th class="center">Stock Actual</th>
                <th class="center">Stock Mínimo</th>
                <th class="center">Faltante</th>
                <th class="center">Estado</th>
            </tr>
        </thead>
        <tbody>
            <?php $idx = 1; foreach ($criticos as $p): ?>
            <tr>
                <td class="center"><?= $idx++ ?></td>
                <td><strong><?= htmlspecialchars($p['nombre']) ?></strong></td>
                <td><?= htmlspecialchars($p['categoria_nombre'] ?? '—') ?></td>
                <td class="right"><?= fmt($p['precio_compra']) ?></td>
                <td class="right"><?= fmt($p['precio_venta']) ?></td>
                <td class="center"><?= $p['stock'] ?></td>
                <td class="center"><?= $p['stock_minimo'] ?></td>
                <td class="center" style="color:#dc2626;font-weight:700;">
                    <?= max(0, $p['stock_minimo'] - $p['stock']) ?>
                </td>
                <td class="center"><?= estadoBadge($p['stock'], $p['stock_minimo']) ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>

<?php endif; ?>

    <!-- ── PIE DEL DOCUMENTO ──────────────────────────────────────────────── -->
    <div class="footer-bar">
        <span>Almacén Europa &mdash; Sistema de Gestión</span>
        <span><?= htmlspecialchars($tituloPDF) ?> &mdash; Generado el <?= $fecha ?></span>
        <span>Usuario: <?= htmlspecialchars($_SESSION['usuario']['nombres'] ?? '') ?></span>
    </div>

</div>

<script>
    // Auto-print si viene el parámetro ?print=1
    <?php if (isset($_GET['print']) && $_GET['print'] == '1'): ?>
    window.onload = function() { window.print(); };
    <?php endif; ?>
</script>

</body>
</html>
