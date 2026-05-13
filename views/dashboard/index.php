<?php
session_start();

if (!isset($_SESSION['usuario'])) {
    header("Location: ../usuarios/login.php");
    exit;
}

$titulo = "Inicio - Panel de Control";
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/sidebar.php';
?>

<!-- Importar Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<div class="mb-8">
    <h2 class="text-3xl font-bold text-gray-800">
        ¡Bienvenido, <?= htmlspecialchars($_SESSION['usuario']['nombres']) ?>! 👋
    </h2>
    <p class="text-gray-500 mt-2">Este es el resumen general de tu gestión como <strong class="text-blue-600"><?= htmlspecialchars($_SESSION['usuario']['rol']) ?></strong>.</p>
</div>

<!-- TARJETAS DE ESTADÍSTICAS RÁPIDAS -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <!-- Ventas -->
    <div class="card p-6 border-l-4 border-green-500 hover:shadow-lg transition-shadow">
        <div class="flex justify-between items-start">
            <div>
                <p class="text-sm text-gray-500 font-medium mb-1">Ventas del Mes</p>
                <h3 class="text-3xl font-bold text-gray-800">$24,500</h3>
            </div>
            <div class="w-12 h-12 rounded-full bg-green-100 flex items-center justify-center text-green-500 text-xl">
                <i class="fas fa-chart-line"></i>
            </div>
        </div>
        <div class="mt-4 flex items-center text-sm">
            <span class="text-green-500 font-bold flex items-center"><i class="fas fa-arrow-up mr-1"></i> 12.5%</span>
            <span class="text-gray-400 ml-2">vs mes anterior</span>
        </div>
    </div>

    <!-- Inventario -->
    <div class="card p-6 border-l-4 border-blue-500 hover:shadow-lg transition-shadow">
        <div class="flex justify-between items-start">
            <div>
                <p class="text-sm text-gray-500 font-medium mb-1">Total Inventario</p>
                <h3 class="text-3xl font-bold text-gray-800">1,248</h3>
            </div>
            <div class="w-12 h-12 rounded-full bg-blue-100 flex items-center justify-center text-blue-500 text-xl">
                <i class="fas fa-boxes-stacked"></i>
            </div>
        </div>
        <div class="mt-4 flex items-center text-sm">
            <span class="text-blue-500 font-bold flex items-center"><i class="fas fa-plus mr-1"></i> 45 nuevos</span>
            <span class="text-gray-400 ml-2">esta semana</span>
        </div>
    </div>

    <!-- Clientes -->
    <div class="card p-6 border-l-4 border-purple-500 hover:shadow-lg transition-shadow">
        <div class="flex justify-between items-start">
            <div>
                <p class="text-sm text-gray-500 font-medium mb-1">Nuevos Clientes</p>
                <h3 class="text-3xl font-bold text-gray-800">124</h3>
            </div>
            <div class="w-12 h-12 rounded-full bg-purple-100 flex items-center justify-center text-purple-500 text-xl">
                <i class="fas fa-users"></i>
            </div>
        </div>
        <div class="mt-4 flex items-center text-sm">
            <span class="text-purple-500 font-bold flex items-center"><i class="fas fa-arrow-up mr-1"></i> 8.2%</span>
            <span class="text-gray-400 ml-2">vs mes anterior</span>
        </div>
    </div>

    <!-- Alertas -->
    <div class="card p-6 border-l-4 border-red-500 hover:shadow-lg transition-shadow">
        <div class="flex justify-between items-start">
            <div>
                <p class="text-sm text-gray-500 font-medium mb-1">Stock Crítico</p>
                <h3 class="text-3xl font-bold text-gray-800">12</h3>
            </div>
            <div class="w-12 h-12 rounded-full bg-red-100 flex items-center justify-center text-red-500 text-xl">
                <i class="fas fa-triangle-exclamation"></i>
            </div>
        </div>
        <div class="mt-4 flex items-center text-sm">
            <span class="text-red-500 font-bold">Requieren atención</span>
            <span class="text-gray-400 ml-2">inmediata</span>
        </div>
    </div>
</div>

<!-- SECCIÓN DE GRÁFICAS -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
    
    <!-- Gráfica de Crecimiento de Ventas (Bar/Line) -->
    <div class="card p-6 bg-white border border-gray-100 rounded-2xl shadow-sm">
        <h3 class="text-lg font-bold text-gray-800 mb-4 border-b pb-2">Crecimiento de Ventas (Últimos 6 meses)</h3>
        <div class="relative h-72 w-full">
            <canvas id="ventasChart"></canvas>
        </div>
    </div>

    <!-- Gráfica de Ruleta de Inventario (Doughnut) -->
    <div class="card p-6 bg-white border border-gray-100 rounded-2xl shadow-sm flex flex-col">
        <h3 class="text-lg font-bold text-gray-800 mb-4 border-b pb-2">Estado del Inventario (Ruleta)</h3>
        <div class="relative flex-1 w-full flex items-center justify-center min-h-[250px]">
            <canvas id="inventarioChart"></canvas>
        </div>
    </div>

</div>

<!-- SCRIPT PARA RENDERIZAR LAS GRÁFICAS -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    
    // 1. Gráfica de Ventas (Barras)
    const ctxVentas = document.getElementById('ventasChart').getContext('2d');
    new Chart(ctxVentas, {
        type: 'line',
        data: {
            labels: ['Nov', 'Dic', 'Ene', 'Feb', 'Mar', 'Abr'],
            datasets: [{
                label: 'Ventas ($)',
                data: [12000, 19000, 15000, 18000, 22000, 24500],
                backgroundColor: 'rgba(59, 130, 246, 0.2)', // Azul claro
                borderColor: '#3b82f6', // Azul fuerte
                borderWidth: 3,
                tension: 0.4, // Curva suave
                fill: true,
                pointBackgroundColor: '#ffffff',
                pointBorderColor: '#3b82f6',
                pointBorderWidth: 2,
                pointRadius: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: { borderDash: [5, 5] }
                },
                x: {
                    grid: { display: false }
                }
            }
        }
    });

    // 2. Gráfica de Inventario (Ruleta / Doughnut)
    const ctxInventario = document.getElementById('inventarioChart').getContext('2d');
    new Chart(ctxInventario, {
        type: 'doughnut',
        data: {
            labels: ['Stock Disponible', 'Stock Bajo', 'Agotados'],
            datasets: [{
                data: [75, 15, 10], // Porcentajes simulados
                backgroundColor: [
                    '#10b981', // Verde
                    '#f59e0b', // Amarillo/Naranja
                    '#ef4444'  // Rojo
                ],
                borderWidth: 0,
                hoverOffset: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '70%', // Grosor de la ruleta
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        padding: 20,
                        usePointStyle: true,
                        font: { family: "'Inter', sans-serif" }
                    }
                }
            }
        }
    });
});
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
