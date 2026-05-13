<?php
session_start();

if (!isset($_SESSION['usuario'])) {
    header("Location: ../usuarios/login.php");
    exit;
}

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../models/Usuario.php';

$database = new Database();
$db = $database->conectar();
$usuarioModel = new Usuario($db);

// Obtener solo usuarios con rol 'Comprador'
$clientes = $usuarioModel->obtenerClientes();

$titulo = "Gestión de Clientes";
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/sidebar.php';
?>

<!-- ── Encabezado ──────────────────────────────────────────────────────────── -->
<div class="mb-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
    <div>
        <h2 class="text-2xl font-bold text-gray-800">Directorio de Clientes</h2>
        <p class="text-gray-500 text-sm mt-1">Personas registradas como compradores en la plataforma.</p>
    </div>
    <div class="flex items-center gap-3">
        <span class="text-sm text-gray-500">
            <i class="fas fa-users mr-1 text-brand-600"></i>
            <strong class="text-gray-800"><?= count($clientes) ?></strong> clientes registrados
        </span>
    </div>
</div>

<!-- ── Buscador ───────────────────────────────────────────────────────────── -->
<div class="card p-4 mb-5">
    <div class="relative">
        <i class="fas fa-search absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
        <input id="buscador" type="text" placeholder="Buscar por nombre, apellido, email o móvil..."
               class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500 transition">
    </div>
</div>

<!-- ── Tabla ──────────────────────────────────────────────────────────────── -->
<div class="card overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-50 border-b border-gray-100">
                    <th class="px-5 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Rol</th>
                    <th class="px-5 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Nombres</th>
                    <th class="px-5 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Apellidos</th>
                    <th class="px-5 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Móvil</th>
                    <th class="px-5 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Email</th>
                    <th class="px-5 py-3.5 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">
                        <i class="fas fa-key text-yellow-500"></i>
                    </th>
                </tr>
            </thead>
            <tbody id="tablaBody" class="divide-y divide-gray-50">
                <?php if (empty($clientes)): ?>
                <tr>
                    <td colspan="6" class="py-16 text-center text-gray-400">
                        <i class="fas fa-users-slash text-4xl mb-3 block opacity-20"></i>
                        <p class="font-medium">No hay clientes registrados aún.</p>
                    </td>
                </tr>
                <?php else: ?>
                    <?php foreach ($clientes as $c): ?>
                    <tr class="hover:bg-gray-50 transition-colors cliente-row"
                        data-buscar="<?= strtolower(htmlspecialchars($c['nombres'] . ' ' . $c['apellidos'] . ' ' . $c['email'] . ' ' . $c['movil'])) ?>">

                        <!-- Rol -->
                        <td class="px-5 py-3.5">
                            <span class="px-3 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-700 border border-green-200">
                                Comprador
                            </span>
                        </td>

                        <!-- Nombres -->
                        <td class="px-5 py-3.5 font-medium text-gray-800">
                            <?= htmlspecialchars($c['nombres']) ?>
                        </td>

                        <!-- Apellidos -->
                        <td class="px-5 py-3.5 text-gray-700">
                            <?= htmlspecialchars($c['apellidos']) ?>
                        </td>

                        <!-- Móvil -->
                        <td class="px-5 py-3.5 text-gray-600 font-mono text-xs">
                            <?= htmlspecialchars($c['movil'] ?: '—') ?>
                        </td>

                        <!-- Email -->
                        <td class="px-5 py-3.5 text-gray-600">
                            <?= htmlspecialchars($c['email']) ?>
                        </td>

                        <!-- Acciones -->
                        <td class="px-5 py-3.5 text-center">
                            <button onclick="openDeleteModal(<?= $c['id'] ?>, '<?= addslashes(htmlspecialchars($c['nombres'] . ' ' . $c['apellidos'])) ?>')"
                                    title="Eliminar cliente"
                                    class="w-8 h-8 rounded-lg bg-red-50 text-red-500 hover:bg-red-100 hover:text-red-700 transition inline-flex items-center justify-center">
                                <i class="fas fa-trash-alt text-xs"></i>
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Contador -->
    <div class="px-5 py-3 border-t border-gray-50">
        <span id="contador" class="text-xs text-gray-400"></span>
    </div>
</div>

<script>
// ── Buscador en tiempo real ────────────────────────────────────────────────
const filas    = document.querySelectorAll('.cliente-row');
const contador = document.getElementById('contador');

function actualizarContador(visible) {
    contador.textContent = visible + ' de ' + filas.length + ' clientes';
}

document.getElementById('buscador').addEventListener('input', function () {
    const q = this.value.toLowerCase().trim();
    let visible = 0;
    filas.forEach(function (row) {
        const match = !q || row.dataset.buscar.includes(q);
        row.style.display = match ? '' : 'none';
        if (match) visible++;
    });
    actualizarContador(visible);
});

actualizarContador(filas.length);

// ── Eliminar ───────────────────────────────────────────────────────────────
function openDeleteModal(id, nombre) {
    Swal.fire({
        title: '¿Eliminar cliente?',
        html: `<span class="text-gray-600">Se eliminará la cuenta de <strong>"${nombre}"</strong>. Esta acción no se puede deshacer.</span>`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    }).then(function (result) {
        if (result.isConfirmed) {
            window.location.href = '../../controllers/AdminUsuarioController.php?accion=eliminar&id=' + id;
        }
    });
}
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
