<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header("Location: ../usuarios/login.php");
    exit;
}

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../models/Proveedor.php';

$database  = new Database();
$db        = $database->conectar();
$model     = new Proveedor($db);

$rol         = $_SESSION['usuario']['rol'];
$puedeEditar = in_array($rol, ['Administrador', 'Bodeguero']);

$proveedores = $model->obtenerTodos();
$total       = count($proveedores);

$titulo = "Proveedores";
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
    .prov-row { transition: background .15s; }
    .prov-row:hover { background: #f8fafc; }
    .modal-overlay { background: rgba(15,23,42,.55); backdrop-filter: blur(4px); }
</style>

<!-- ── Encabezado ─────────────────────────────────────────────────────────── -->
<div class="mb-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
    <div>
        <h2 class="text-2xl font-bold text-gray-800">Proveedores</h2>
        <p class="text-sm text-gray-500 mt-1">Gestiona los proveedores del almacén.</p>
    </div>
    <?php if ($puedeEditar): ?>
    <button onclick="abrirModalCrear()"
            class="flex items-center gap-2 px-5 py-2.5 rounded-xl bg-brand-900 text-white font-semibold text-sm hover:bg-brand-800 transition shadow-md">
        <i class="fas fa-plus"></i> Nuevo Proveedor
    </button>
    <?php endif; ?>
</div>

<!-- ── Stats ─────────────────────────────────────────────────────────────── -->
<div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-7">
    <div class="card p-5 flex items-center gap-4">
        <div class="w-11 h-11 rounded-xl bg-blue-100 flex items-center justify-center text-blue-600 text-lg flex-shrink-0">
            <i class="fas fa-truck"></i>
        </div>
        <div>
            <div class="text-2xl font-bold text-gray-800"><?= $total ?></div>
            <div class="text-xs text-gray-500">Proveedores registrados</div>
        </div>
    </div>
    <div class="card p-5 flex items-center gap-4">
        <div class="w-11 h-11 rounded-xl bg-green-100 flex items-center justify-center text-green-600 text-lg flex-shrink-0">
            <i class="fas fa-envelope"></i>
        </div>
        <div>
            <div class="text-2xl font-bold text-gray-800">
                <?= count(array_filter($proveedores, fn($p) => !empty($p['email']))) ?>
            </div>
            <div class="text-xs text-gray-500">Con email registrado</div>
        </div>
    </div>
    <div class="card p-5 flex items-center gap-4">
        <div class="w-11 h-11 rounded-xl bg-purple-100 flex items-center justify-center text-purple-600 text-lg flex-shrink-0">
            <i class="fas fa-phone"></i>
        </div>
        <div>
            <div class="text-2xl font-bold text-gray-800">
                <?= count(array_filter($proveedores, fn($p) => !empty($p['telefono']))) ?>
            </div>
            <div class="text-xs text-gray-500">Con teléfono registrado</div>
        </div>
    </div>
</div>

<!-- ── Buscador ───────────────────────────────────────────────────────────── -->
<div class="card p-4 mb-5">
    <div class="relative">
        <i class="fas fa-search absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
        <input id="buscador" type="text" placeholder="Buscar por nombre, email, teléfono o dirección..."
               class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500 transition">
    </div>
</div>

<!-- ── Tabla ──────────────────────────────────────────────────────────────── -->
<div class="card overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-100">
                <tr>
                    <th class="px-5 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Proveedor</th>
                    <th class="px-5 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider hidden md:table-cell">Teléfono</th>
                    <th class="px-5 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider hidden lg:table-cell">Email</th>
                    <th class="px-5 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider hidden xl:table-cell">Dirección</th>
                    <?php if ($puedeEditar): ?>
                    <th class="px-5 py-3.5 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Acciones</th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody id="tablaBody" class="divide-y divide-gray-50">
            <?php if (empty($proveedores)): ?>
                <tr>
                    <td colspan="5" class="py-16 text-center text-gray-400">
                        <i class="fas fa-truck text-5xl mb-3 block opacity-20"></i>
                        <p class="font-medium">No hay proveedores registrados.</p>
                        <?php if ($puedeEditar): ?>
                        <button onclick="abrirModalCrear()"
                                class="mt-3 px-5 py-2 rounded-xl bg-brand-900 text-white text-sm font-semibold hover:bg-brand-800 transition">
                            Agregar el primero
                        </button>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach ($proveedores as $p): ?>
                <tr class="prov-row"
                    data-buscar="<?= strtolower(htmlspecialchars($p['nombre'] . ' ' . $p['email'] . ' ' . $p['telefono'] . ' ' . $p['direccion'])) ?>">

                    <!-- Nombre -->
                    <td class="px-5 py-3.5">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-brand-100 text-brand-700 flex items-center justify-center font-bold text-sm flex-shrink-0">
                                <?= strtoupper(substr($p['nombre'], 0, 2)) ?>
                            </div>
                            <div>
                                <div class="font-semibold text-gray-800"><?= htmlspecialchars($p['nombre']) ?></div>
                                <div class="text-xs text-gray-400 mt-0.5">ID #<?= str_pad($p['id'], 4, '0', STR_PAD_LEFT) ?></div>
                            </div>
                        </div>
                    </td>

                    <!-- Teléfono -->
                    <td class="px-5 py-3.5 hidden md:table-cell">
                        <?php if (!empty($p['telefono'])): ?>
                        <a href="tel:<?= htmlspecialchars($p['telefono']) ?>"
                           class="flex items-center gap-1.5 text-gray-600 hover:text-brand-700 transition text-sm">
                            <i class="fas fa-phone text-gray-400 text-xs"></i>
                            <?= htmlspecialchars($p['telefono']) ?>
                        </a>
                        <?php else: ?>
                        <span class="text-gray-300 text-xs">—</span>
                        <?php endif; ?>
                    </td>

                    <!-- Email -->
                    <td class="px-5 py-3.5 hidden lg:table-cell">
                        <?php if (!empty($p['email'])): ?>
                        <a href="mailto:<?= htmlspecialchars($p['email']) ?>"
                           class="flex items-center gap-1.5 text-gray-600 hover:text-brand-700 transition text-sm">
                            <i class="fas fa-envelope text-gray-400 text-xs"></i>
                            <?= htmlspecialchars($p['email']) ?>
                        </a>
                        <?php else: ?>
                        <span class="text-gray-300 text-xs">—</span>
                        <?php endif; ?>
                    </td>

                    <!-- Dirección -->
                    <td class="px-5 py-3.5 hidden xl:table-cell text-gray-500 text-sm">
                        <?php if (!empty($p['direccion'])): ?>
                        <div class="flex items-start gap-1.5">
                            <i class="fas fa-location-dot text-gray-400 text-xs mt-0.5 flex-shrink-0"></i>
                            <span class="line-clamp-2"><?= htmlspecialchars($p['direccion']) ?></span>
                        </div>
                        <?php else: ?>
                        <span class="text-gray-300 text-xs">—</span>
                        <?php endif; ?>
                    </td>

                    <!-- Acciones -->
                    <?php if ($puedeEditar): ?>
                    <td class="px-5 py-3.5 text-center">
                        <div class="flex items-center justify-center gap-1.5">
                            <button onclick='abrirModalEditar(<?= json_encode($p) ?>)'
                                    title="Editar"
                                    class="p-2 rounded-lg bg-brand-50 text-brand-700 hover:bg-brand-100 transition text-xs">
                                <i class="fas fa-pen-to-square"></i>
                            </button>
                            <button onclick="confirmarEliminar(<?= $p['id'] ?>, '<?= addslashes(htmlspecialchars($p['nombre'])) ?>')"
                                    title="Eliminar"
                                    class="p-2 rounded-lg bg-red-50 text-red-500 hover:bg-red-100 transition text-xs">
                                <i class="fas fa-trash-alt"></i>
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
     MODAL: CREAR / EDITAR PROVEEDOR
══════════════════════════════════════════════════════ -->
<div id="modalProveedor" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4">
    <div class="modal-overlay absolute inset-0" onclick="cerrarModal()"></div>
    <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-lg z-10">

        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
            <h3 class="font-bold text-gray-800 text-lg" id="modalTitulo">Nuevo Proveedor</h3>
            <button onclick="cerrarModal()"
                    class="w-8 h-8 rounded-full bg-gray-100 flex items-center justify-center text-gray-500 hover:bg-gray-200 transition">
                <i class="fas fa-times text-sm"></i>
            </button>
        </div>

        <form id="formProveedor" method="POST"
              action="../../controllers/ProveedorController.php?accion=crear"
              class="p-6 space-y-4">
            <input type="hidden" name="id" id="prov_id">

            <!-- Nombre -->
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">
                    Nombre <span class="text-red-500">*</span>
                </label>
                <input type="text" name="nombre" id="prov_nombre" required
                       class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500 transition"
                       placeholder="Ej: Distribuidora Nacional S.A.">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <!-- Teléfono -->
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">
                        <i class="fas fa-phone mr-1 text-gray-400"></i> Teléfono
                    </label>
                    <input type="text" name="telefono" id="prov_telefono"
                           class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500 transition"
                           placeholder="Ej: 3001234567">
                </div>

                <!-- Email -->
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">
                        <i class="fas fa-envelope mr-1 text-gray-400"></i> Email
                    </label>
                    <input type="email" name="email" id="prov_email"
                           class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500 transition"
                           placeholder="proveedor@email.com">
                </div>
            </div>

            <!-- Dirección -->
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">
                    <i class="fas fa-location-dot mr-1 text-gray-400"></i> Dirección
                </label>
                <textarea name="direccion" id="prov_direccion" rows="2"
                          class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500 transition resize-none"
                          placeholder="Calle, ciudad, departamento..."></textarea>
            </div>

            <div class="flex justify-end gap-3 pt-2 border-t border-gray-100">
                <button type="button" onclick="cerrarModal()"
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
     SCRIPTS
══════════════════════════════════════════════════════ -->
<script>
// ── Buscador ───────────────────────────────────────────────────────────────
const filas    = document.querySelectorAll('.prov-row');
const contador = document.getElementById('contador');

function actualizarContador(n) {
    contador.textContent = n + ' de ' + filas.length + ' proveedores';
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

// ── Modal ──────────────────────────────────────────────────────────────────
function abrirModalCrear() {
    document.getElementById('modalTitulo').textContent = 'Nuevo Proveedor';
    document.getElementById('formProveedor').action = '../../controllers/ProveedorController.php?accion=crear';
    document.getElementById('formProveedor').reset();
    document.getElementById('prov_id').value = '';
    document.getElementById('modalProveedor').classList.remove('hidden');
}

function abrirModalEditar(p) {
    document.getElementById('modalTitulo').textContent = 'Editar Proveedor';
    document.getElementById('formProveedor').action = '../../controllers/ProveedorController.php?accion=editar';
    document.getElementById('prov_id').value        = p.id;
    document.getElementById('prov_nombre').value    = p.nombre    || '';
    document.getElementById('prov_telefono').value  = p.telefono  || '';
    document.getElementById('prov_email').value     = p.email     || '';
    document.getElementById('prov_direccion').value = p.direccion || '';
    document.getElementById('modalProveedor').classList.remove('hidden');
}

function cerrarModal() {
    document.getElementById('modalProveedor').classList.add('hidden');
}

document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') cerrarModal();
});

// ── Eliminar ───────────────────────────────────────────────────────────────
function confirmarEliminar(id, nombre) {
    Swal.fire({
        title: '¿Eliminar proveedor?',
        html: `<span class="text-gray-600">Se eliminará <strong>"${nombre}"</strong>. Si tiene compras asociadas no se podrá eliminar.</span>`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc2626',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    }).then(function (result) {
        if (result.isConfirmed) {
            window.location.href = '../../controllers/ProveedorController.php?accion=eliminar&id=' + id;
        }
    });
}
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
