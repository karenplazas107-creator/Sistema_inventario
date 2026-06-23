
<?php
session_start();

if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['rol'] !== 'Administrador') {
    header("Location: ../usuarios/login.php");
    exit;
}

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../models/Usuario.php';

$database = new Database();
$db = $database->conectar();
$usuarioModel = new Usuario($db);
$usuarios= $usuarioModel->obtenerTodos();

$titulo = "Dashboard Administrador";
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/sidebar.php';
?>

<div class="mb-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
    <div>
        <h2 class="text-2xl font-bold text-gray-800">Gestión de Usuarios</h2>
        <p class="text-gray-500 text-sm mt-1">Administra los usuarios, roles y colaboradores de tu tienda.</p>
    </div>
    <button onclick="openModal('modalCrear')" class="btn-primary bg-brand-600 hover:bg-brand-800 text-white px-5 py-2.5 rounded-xl shadow-md flex items-center gap-2 transition-all hover:scale-[1.02]">
        <i class="fas fa-user-plus"></i> Agregar Usuario
    </button>
</div>

<?php if (isset($_SESSION['alert'])): ?>
<script>
document.addEventListener('DOMContentLoaded', function () {
    Swal.fire({
        icon: '<?= $_SESSION['alert']['icon'] ?>',
        title: '<?= $_SESSION['alert']['title'] ?>',
        text: '<?= $_SESSION['alert']['text'] ?>',
        confirmButtonColor: '#1e3a8a',
        timer: 3000,
        timerProgressBar: true
    });
});
</script>
<?php unset($_SESSION['alert']); endif; ?>

<!-- ── Stats ─────────────────────────────────────────────────────────────── -->
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-7">
    <!-- Card 1: Total -->
    <div class="card p-5 flex items-center gap-4 bg-white rounded-2xl border border-gray-100 shadow-sm">
        <div class="w-12 h-12 rounded-xl bg-blue-50 flex items-center justify-center text-blue-600 text-xl flex-shrink-0">
            <i class="fas fa-users"></i>
        </div>
        <div>
            <div class="text-2xl font-bold text-gray-800"><?= count($usuarios) ?></div>
            <div class="text-xs text-gray-500">Total usuarios</div>
        </div>
    </div>
    <!-- Card 2: Administradores -->
    <div class="card p-5 flex items-center gap-4 bg-white rounded-2xl border border-gray-100 shadow-sm">
        <div class="w-12 h-12 rounded-xl bg-green-50 flex items-center justify-center text-green-600 text-xl flex-shrink-0">
            <i class="fas fa-user-shield"></i>
        </div>
        <div>
            <div class="text-2xl font-bold text-gray-800"><?= count(array_filter($usuarios, fn($u) => strtolower($u['rol']) === 'administrador')) ?></div>
            <div class="text-xs text-gray-500">Administradores</div>
        </div>
    </div>
    <!-- Card 3: Vendedores -->
    <div class="card p-5 flex items-center gap-4 bg-white rounded-2xl border border-gray-100 shadow-sm">
        <div class="w-12 h-12 rounded-xl bg-amber-50 flex items-center justify-center text-amber-600 text-xl flex-shrink-0">
            <i class="fas fa-user-tag"></i>
        </div>
        <div>
            <div class="text-2xl font-bold text-gray-800"><?= count(array_filter($usuarios, fn($u) => strtolower($u['rol']) === 'vendedor')) ?></div>
            <div class="text-xs text-gray-500">Vendedores</div>
        </div>
    </div>
    <!-- Card 4: Bodegueros -->
    <div class="card p-5 flex items-center gap-4 bg-white rounded-2xl border border-gray-100 shadow-sm">
        <div class="w-12 h-12 rounded-xl bg-purple-50 flex items-center justify-center text-purple-600 text-xl flex-shrink-0">
            <i class="fas fa-user-gear"></i>
        </div>
        <div>
            <div class="text-2xl font-bold text-gray-800"><?= count(array_filter($usuarios, fn($u) => strtolower($u['rol']) === 'bodeguero')) ?></div>
            <div class="text-xs text-gray-500">Bodegueros</div>
        </div>
    </div>
</div>

<!-- ── Buscador + Filtros ─────────────────────────────────────────────────── -->
<div class="card p-4 mb-5 flex flex-col sm:flex-row gap-3 items-center bg-white rounded-2xl border border-gray-100 shadow-sm">
    <div class="relative flex-1 w-full">
        <i class="fas fa-search absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
        <input id="buscador" type="text" placeholder="Buscar usuario..."
               class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500 transition">
    </div>
    <select id="filtroRol"
            class="px-4 py-2.5 rounded-xl border border-gray-200 text-sm font-semibold text-gray-600 focus:outline-none focus:ring-2 focus:ring-brand-500 bg-white transition">
        <option value="">Todos los roles</option>
        <option value="administrador">Administradores</option>
        <option value="vendedor">Vendedores</option>
        <option value="bodeguero">Bodegueros</option>
    </select>
    <div class="flex items-center gap-1.5 bg-gray-100 p-1 rounded-xl">
        <button id="btnGridView" onclick="setView('grid')" class="w-9 h-9 rounded-lg flex items-center justify-center transition-colors text-gray-400 hover:text-gray-600" title="Vista cuadrícula">
            <i class="fas fa-th-large"></i>
        </button>
        <button id="btnListView" onclick="setView('list')" class="w-9 h-9 rounded-lg flex items-center justify-center transition-colors text-brand-600 bg-white shadow-sm" title="Vista lista">
            <i class="fas fa-list"></i>
        </button>
    </div>
</div>

<!-- ── Vista Cuadrícula (Cards) ───────────────────────────────────────────── -->
<div id="gridContainer" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 hidden">
    <?php if (empty($usuarios)): ?>
        <div class="col-span-full card p-16 text-center text-gray-400 border border-gray-100 bg-white rounded-2xl shadow-sm">
            <i class="fas fa-users-slash text-5xl mb-3 block opacity-20"></i>
            <p class="font-medium">No hay usuarios registrados.</p>
        </div>
    <?php else: ?>
        <?php foreach ($usuarios as $u):
            $inicial = strtoupper(substr($u['nombres'], 0, 1));
            $rolLower = strtolower($u['rol']);
            
            if ($rolLower === 'administrador') {
                $badgeClass = 'bg-blue-50 text-blue-600 border border-blue-100';
                $avatarClass = 'bg-blue-100 text-blue-700';
            } elseif ($rolLower === 'vendedor') {
                $badgeClass = 'bg-amber-50 text-amber-600 border border-amber-100';
                $avatarClass = 'bg-amber-100 text-amber-700';
            } elseif ($rolLower === 'bodeguero') {
                $badgeClass = 'bg-indigo-50 text-indigo-600 border border-indigo-100';
                $avatarClass = 'bg-indigo-100 text-indigo-700';
            } else {
                $badgeClass = 'bg-gray-50 text-gray-600 border border-gray-100';
                $avatarClass = 'bg-gray-100 text-gray-700';
            }
        ?>
        <div class="usuario-card bg-white rounded-2xl border border-gray-100 p-6 shadow-sm flex flex-col relative transition-all duration-300 hover:shadow-md hover:-translate-y-1"
             data-nombre="<?= strtolower(htmlspecialchars($u['nombres'] . ' ' . $u['apellidos'])) ?>"
             data-rol="<?= $rolLower ?>">
            
            <!-- Badge de Rol -->
            <span class="absolute top-5 right-5 px-3 py-1 rounded-full text-xs font-semibold <?= $badgeClass ?>">
                <?= htmlspecialchars($u['rol']) ?>
            </span>
            
            <!-- Avatar Inicial -->
            <div class="w-16 h-16 rounded-full flex items-center justify-center font-bold text-xl mb-4 <?= $avatarClass ?>">
                <?= $inicial ?>
            </div>
            
            <!-- Info principal -->
            <h3 class="font-heading text-lg font-bold text-gray-800 leading-snug mb-0.5">
                <?= htmlspecialchars($u['nombres'] . ' ' . $u['apellidos']) ?>
            </h3>
            <span class="text-xs text-gray-400 font-semibold uppercase tracking-wider mb-4">Colaborador</span>
            
            <!-- Detalles de contacto -->
            <div class="space-y-2 mb-6">
                <div class="flex items-center gap-2.5 text-sm text-gray-600 font-mono">
                    <i class="fas fa-phone text-brand-500 text-xs w-4 text-center"></i>
                    <span><?= htmlspecialchars($u['movil'] ?: 'Sin número') ?></span>
                </div>
                <div class="flex items-center gap-2.5 text-sm text-gray-600">
                    <i class="fas fa-envelope text-brand-500 text-xs w-4 text-center"></i>
                    <span class="truncate"><?= htmlspecialchars($u['email']) ?></span>
                </div>
            </div>
            
            <!-- Botones de Acción -->
            <div class="flex gap-3 mt-auto">
                <button onclick="openEditModal(<?= htmlspecialchars(json_encode($u)) ?>)" 
                        class="flex-1 py-2.5 rounded-xl border border-gray-200 text-sm font-semibold text-gray-600 hover:bg-gray-50 hover:text-brand-600 transition flex items-center justify-center gap-2">
                    <i class="fas fa-pen text-xs"></i> Editar
                </button>
                <button onclick="openDeleteModal(<?= $u['id'] ?>,'<?= htmlspecialchars(addslashes($u['nombres'])) ?>')" 
                        class="py-2.5 px-4 rounded-xl border border-red-100 text-sm font-semibold text-red-500 hover:bg-red-50 transition flex items-center justify-center gap-2">
                    <i class="fas fa-trash-alt text-xs"></i> Eliminar
                </button>
            </div>
        </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<!-- ── Vista Lista (Tabla) ────────────────────────────────────────────────── -->
<div id="listContainer" class="card overflow-hidden bg-white border border-gray-100 shadow-sm rounded-2xl">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-100">
                <tr>
                    <th class="px-5 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Usuario</th>
                    <th class="px-5 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Correo</th>
                    <th class="px-5 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Móvil</th>
                    <th class="px-5 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Rol</th>
                    <th class="px-5 py-3.5 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Acciones</th>
                </tr>
            </thead>
            <tbody id="tablaBody" class="divide-y divide-gray-50">
                <?php foreach ($usuarios as $u):
                    $inicial = strtoupper(substr($u['nombres'], 0, 1));
                    $rolLower = strtolower($u['rol']);
                    
                    if ($rolLower === 'administrador') {
                        $badgeClass = 'bg-blue-50 text-blue-600 border border-blue-100';
                        $avatarClass = 'bg-blue-100 text-blue-600';
                    } elseif ($rolLower === 'vendedor') {
                        $badgeClass = 'bg-amber-50 text-amber-600 border border-amber-100';
                        $avatarClass = 'bg-amber-100 text-amber-600';
                    } elseif ($rolLower === 'bodeguero') {
                        $badgeClass = 'bg-indigo-50 text-indigo-600 border border-indigo-100';
                        $avatarClass = 'bg-indigo-100 text-indigo-600';
                    } else {
                        $badgeClass = 'bg-gray-50 text-gray-600 border border-gray-100';
                        $avatarClass = 'bg-gray-100 text-gray-600';
                    }
                ?>
                <tr class="usuario-row hover:bg-gray-50 transition-colors"
                    data-nombre="<?= strtolower(htmlspecialchars($u['nombres'] . ' ' . $u['apellidos'])) ?>"
                    data-rol="<?= $rolLower ?>">
                    <td class="px-5 py-3.5">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full flex items-center justify-center font-bold text-sm flex-shrink-0 <?= $avatarClass ?>">
                                <?= $inicial ?>
                            </div>
                            <span class="font-semibold text-gray-800"><?= htmlspecialchars($u['nombres'] . ' ' . $u['apellidos']) ?></span>
                        </div>
                    </td>
                    <td class="px-5 py-3.5 text-gray-600"><?= htmlspecialchars($u['email']) ?></td>
                    <td class="px-5 py-3.5 text-gray-500 font-mono text-xs"><?= htmlspecialchars($u['movil'] ?: '—') ?></td>
                    <td class="px-5 py-3.5">
                        <span class="px-2.5 py-1 rounded-full text-xs font-semibold <?= $badgeClass ?>">
                            <?= htmlspecialchars($u['rol']) ?>
                        </span>
                    </td>
                    <td class="px-5 py-3.5 text-center">
                        <div class="flex items-center justify-center gap-1.5">
                            <button onclick="openEditModal(<?= htmlspecialchars(json_encode($u)) ?>)" 
                                    class="p-2 rounded-lg bg-brand-50 text-brand-700 hover:bg-brand-100 transition text-xs" title="Editar">
                                <i class="fas fa-pen"></i>
                            </button>
                            <button onclick="openDeleteModal(<?= $u['id'] ?>,'<?= htmlspecialchars(addslashes($u['nombres'])) ?>')" 
                                    class="p-2 rounded-lg bg-red-50 text-red-500 hover:bg-red-100 transition text-xs" title="Eliminar">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- ── Contador / Paginador ──────────────────────────────────────────────── -->
<div class="mt-6 flex flex-col sm:flex-row justify-between items-center gap-4 bg-white border border-gray-100 p-4 rounded-2xl shadow-sm">
    <span id="contadorDisplay" class="text-xs text-gray-400 font-medium"></span>
    <div class="flex items-center gap-1">
        <button class="px-3 py-1.5 rounded-lg border border-gray-200 text-gray-400 hover:bg-gray-50 transition text-xs font-semibold"><i class="fas fa-chevron-left"></i></button>
        <button class="px-3 py-1.5 rounded-lg bg-brand-900 text-white text-xs font-bold shadow-sm">1</button>
        <button class="px-3 py-1.5 rounded-lg border border-gray-200 text-gray-400 hover:bg-gray-50 transition text-xs font-semibold"><i class="fas fa-chevron-right"></i></button>
    </div>
</div>

<!-- ══════════════════════════════════════════════════════
     MODALES
     ══════════════════════════════════════════════════════ -->

<!-- MODAL CREAR -->
<div id="modalCrear" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4">
    <div class="modal-overlay absolute inset-0 bg-slate-900/60 backdrop-blur-sm" onclick="closeModal('modalCrear')"></div>
    <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-lg overflow-hidden z-10 transform scale-95 transition-all duration-300">
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 bg-white">
            <h3 class="font-bold text-gray-800 text-lg">Agregar Usuario</h3>
            <button onclick="closeModal('modalCrear')" class="w-8 h-8 rounded-full bg-gray-100 flex items-center justify-center text-gray-500 hover:bg-gray-200 transition">
                <i class="fas fa-times text-sm"></i>
            </button>
        </div>
        <form action="../../controllers/UsuarioController.php" method="POST" class="p-6 space-y-4">
            <input type="hidden" name="desde_admin" value="1">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">Nombres *</label>
                    <input type="text" name="nombres" required class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500 transition" placeholder="Ej: Karen">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">Apellidos *</label>
                    <input type="text" name="apellidos" required class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500 transition" placeholder="Ej: Plazas">
                </div>
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Móvil *</label>
                <input type="text" name="movil" required class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500 transition" placeholder="Ej: 3456789876">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Correo Electrónico *</label>
                <input type="email" name="email" required class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500 transition" placeholder="correo@almaceneuropa.com">
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">Contraseña *</label>
                    <input type="password" name="password" required class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500 transition" placeholder="••••••••">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">Confirmar Contraseña *</label>
                    <input type="password" name="confirmar_password" required class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500 transition" placeholder="••••••••">
                </div>
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Rol *</label>
                <select name="rol" required class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500 bg-white transition">
                    <option value="Administrador">Administrador</option>
                    <option value="Vendedor">Vendedor</option>
                    <option value="Bodeguero">Bodeguero</option>
                </select>
            </div>
            <div class="flex justify-end gap-3 pt-3 border-t border-gray-100">
                <button type="button" onclick="closeModal('modalCrear')" class="px-5 py-2.5 rounded-xl border border-gray-200 text-sm font-medium text-gray-600 hover:bg-gray-50 transition">Cancelar</button>
                <button type="submit" class="px-6 py-2.5 rounded-xl bg-brand-900 hover:bg-brand-800 text-white text-sm font-semibold transition shadow-md">Guardar Usuario</button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL EDITAR -->
<div id="modalEditar" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4">
    <div class="modal-overlay absolute inset-0 bg-slate-900/60 backdrop-blur-sm" onclick="closeModal('modalEditar')"></div>
    <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-lg overflow-hidden z-10 transform scale-95 transition-all duration-300">
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 bg-white">
            <h3 class="font-bold text-gray-800 text-lg">Editar Usuario</h3>
            <button onclick="closeModal('modalEditar')" class="w-8 h-8 rounded-full bg-gray-100 flex items-center justify-center text-gray-500 hover:bg-gray-200 transition">
                <i class="fas fa-times text-sm"></i>
            </button>
        </div>
        <form action="../../controllers/AdminUsuarioController.php?accion=editar" method="POST" class="p-6 space-y-4">
            <input type="hidden" name="id_usuario" id="edit_id_usuario">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">Nombres *</label>
                    <input type="text" name="nombres" id="edit_nombres" required class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500 transition" placeholder="Nombres">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">Apellidos *</label>
                    <input type="text" name="apellidos" id="edit_apellidos" required class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500 transition" placeholder="Apellidos">
                </div>
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Móvil *</label>
                <input type="text" name="movil" id="edit_movil" required class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500 transition" placeholder="Móvil">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Rol *</label>
                <select name="rol" id="edit_rol" required class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500 bg-white transition">
                    <option value="Administrador">Administrador</option>
                    <option value="Vendedor">Vendedor</option>
                    <option value="Bodeguero">Bodeguero</option>
                </select>
            </div>
            <div class="flex justify-end gap-3 pt-3 border-t border-gray-100">
                <button type="button" onclick="closeModal('modalEditar')" class="px-5 py-2.5 rounded-xl border border-gray-200 text-sm font-medium text-gray-600 hover:bg-gray-50 transition">Cancelar</button>
                <button type="submit" class="px-6 py-2.5 rounded-xl bg-brand-900 hover:bg-brand-800 text-white text-sm font-semibold transition shadow-md">Guardar Cambios</button>
            </div>
        </form>
    </div>
</div>

<script>
// ── Estado de Vista (Grid vs List) ─────────────────────────────────────────
let currentView = 'list';

function setView(view) {
    currentView = view;
    
    const gridBtn = document.getElementById('btnGridView');
    const listBtn = document.getElementById('btnListView');
    const gridCont = document.getElementById('gridContainer');
    const listCont = document.getElementById('listContainer');
    
    if (view === 'grid') {
        gridBtn.className = 'w-9 h-9 rounded-lg flex items-center justify-center transition-colors text-brand-600 bg-white shadow-sm';
        listBtn.className = 'w-9 h-9 rounded-lg flex items-center justify-center transition-colors text-gray-400 hover:text-gray-600';
        gridCont.classList.remove('hidden');
        listCont.classList.add('hidden');
    } else {
        listBtn.className = 'w-9 h-9 rounded-lg flex items-center justify-center transition-colors text-brand-600 bg-white shadow-sm';
        gridBtn.className = 'w-9 h-9 rounded-lg flex items-center justify-center transition-colors text-gray-400 hover:text-gray-600';
        listCont.classList.remove('hidden');
        gridCont.classList.add('hidden');
    }
}

// ── Buscador + Filtros en Tiempo Real ────────────────────────────────────────
const buscador  = document.getElementById('buscador');
const filtroRol = document.getElementById('filtroRol');
const contadorDisplay = document.getElementById('contadorDisplay');

const cards = document.querySelectorAll('.usuario-card');
const rows  = document.querySelectorAll('.usuario-row');

function aplicarFiltros() {
    const q   = buscador.value.toLowerCase().trim();
    const rol = filtroRol.value;
    let visibleCards = 0;
    let visibleRows  = 0;
    
    // Filtrar Cards
    cards.forEach(card => {
        const matchQ   = !q   || card.dataset.nombre.includes(q);
        const matchRol = !rol || card.dataset.rol === rol;
        const show = matchQ && matchRol;
        card.style.display = show ? 'flex' : 'none';
        if (show) visibleCards++;
    });
    
    // Filtrar Rows
    rows.forEach(row => {
        const matchQ   = !q   || row.dataset.nombre.includes(q);
        const matchRol = !rol || row.dataset.rol === rol;
        const show = matchQ && matchRol;
        row.style.display = show ? '' : 'none';
        if (show) visibleRows++;
    });
    
    const total   = currentView === 'grid' ? cards.length : rows.length;
    const visible = currentView === 'grid' ? visibleCards  : visibleRows;
    contadorDisplay.textContent = `Mostrando ${visible} de ${total} usuarios`;
}

buscador.addEventListener('input', aplicarFiltros);
filtroRol.addEventListener('change', aplicarFiltros);
aplicarFiltros(); // Inicializar contador

// ── Modales ────────────────────────────────────────────────────────────────
function openModal(id) {
    const modal = document.getElementById(id);
    modal.classList.remove('hidden');
    // Forzar reflow para animación
    setTimeout(() => {
        modal.querySelector('.relative').classList.remove('scale-95');
    }, 10);
}

function closeModal(id) {
    const modal = document.getElementById(id);
    modal.querySelector('.relative').classList.add('scale-95');
    setTimeout(() => {
        modal.classList.add('hidden');
    }, 150);
}

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeModal('modalCrear');
        closeModal('modalEditar');
    }
});

function openEditModal(u) {
    document.getElementById('edit_id_usuario').value = u.id;
    document.getElementById('edit_nombres').value = u.nombres;
    document.getElementById('edit_apellidos').value = u.apellidos;
    document.getElementById('edit_movil').value = u.movil;
    document.getElementById('edit_rol').value = u.rol;
    openModal('modalEditar');
}

function openDeleteModal(id, nombre) {
    Swal.fire({
        title: '¿Eliminar usuario?',
        html: `<span class="text-gray-600">Se eliminará la cuenta del colaborador <strong>"${nombre}"</strong>. Esta acción no se puede deshacer.</span>`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc2626',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = '../../controllers/AdminUsuarioController.php?accion=eliminar&id=' + id;
        }
    });
}
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
