
<?php
session_start();

if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['rol'] !== 'Administrador') {
    header("Location: ../usuarios/login.php");
    exit;
}

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../models/usuario.php';

$database = new Database();
$db = $database->conectar();
$usuarioModel = new Usuario($db);
$usuarios= $usuarioModel->obtenerTodos();

$titulo = "Dashboard Administrador";
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/sidebar.php';
?>

<div class="mb-6 flex justify-between items-center">
    <div>
        <h2 class="text-2xl font-bold text-gray-800">Gestión de Usuarios</h2>
        <p class="text-gray-500 text-sm mt-1">Administra los usuarios y roles del sistema.</p>
    </div>
    <button onclick="openModal('modalCrear')" class="btn-primary bg-brand-600 hover:bg-brand-800 text-white px-5 py-2.5 rounded-xl shadow-sm flex items-center gap-2 transition-colors">
        <i class="fas fa-user-plus"></i> Agregar Usuario
    </button>
</div>

<div class="card p-6 border-0 shadow-sm rounded-2xl bg-white">

    <?php if (isset($_SESSION['alert'])): ?>
    <script>
    Swal.fire({
        icon: '<?= $_SESSION['alert']['icon'] ?>',
        title: '<?= $_SESSION['alert']['title'] ?>',
        text: '<?= $_SESSION['alert']['text'] ?>',
        confirmButtonColor: '#1e3a8a'
    });
    </script>
    <?php unset($_SESSION['alert']); endif; ?>

    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-gray-50 border-b border-gray-100 text-gray-500 text-sm uppercase tracking-wider">
                    <th class="p-4 font-semibold rounded-tl-xl">Nombre Completo</th>
                    <th class="p-4 font-semibold">Correo</th>
                    <th class="p-4 font-semibold">Rol</th>
                    <th class="p-4 font-semibold rounded-tr-xl text-center">Acciones</th>
                </tr>
            </thead>
            <tbody class="text-sm">
                <?php foreach ($usuarios as $u): ?>
                <tr class="border-b border-gray-50 hover:bg-gray-50/50 transition-colors">
                    
                    <td class="p-4">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full bg-brand-100 text-brand-600 flex items-center justify-center font-bold">
                                <?= strtoupper(substr($u['nombres'], 0, 1)) ?>
                            </div>
                            <div class="font-bold text-gray-800"><?= htmlspecialchars($u['nombres'] . ' ' . $u['apellidos']) ?></div>
                        </div>
                    </td>
                    
                    <td class="p-4 text-gray-600"><?= htmlspecialchars($u['email']) ?></td>
                    
                    <td class="p-4">
                        <?php if(strtolower($u['rol']) == 'administrador'): ?>
                            <span class="px-3 py-1 bg-brand-100 text-brand-700 rounded-full text-xs font-bold border border-brand-200">Administrador</span>
                        <?php elseif(strtolower($u['rol']) == 'vendedor'): ?>
                            <span class="px-3 py-1 bg-amber-100 text-amber-700 rounded-full text-xs font-bold border border-amber-200">Vendedor</span>
                        <?php elseif(strtolower($u['rol']) == 'bodeguero'): ?>
                            <span class="px-3 py-1 bg-indigo-100 text-indigo-700 rounded-full text-xs font-bold border border-indigo-200">Bodeguero</span>
                        <?php else: ?>
                            <span class="px-3 py-1 bg-gray-100 text-gray-700 rounded-full text-xs font-bold border border-gray-200"><?= htmlspecialchars($u['rol']) ?></span>
                        <?php endif; ?>
                    </td>

                    <td class="p-4 text-center space-x-1">
                        <button onclick="openEditModal(<?= htmlspecialchars(json_encode($u)) ?>)" class="w-8 h-8 rounded-lg bg-gray-100 text-gray-600 hover:bg-brand-100 hover:text-brand-600 transition-colors inline-flex items-center justify-center" title="Editar">
                            <i class="fas fa-pen"></i>
                        </button>
                        <button onclick="openDeleteModal(<?= $u['id'] ?>,'<?= htmlspecialchars(addslashes($u['nombres'])) ?>')" class="w-8 h-8 rounded-lg bg-gray-100 text-gray-600 hover:bg-red-100 hover:text-red-600 transition-colors inline-flex items-center justify-center" title="Eliminar">
                            <i class="fas fa-trash-alt"></i>
                        </button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- MODAL CREAR -->
<div id="modalCrear" class="fixed inset-0 bg-black bg-opacity-50 hidden flex items-center justify-center z-50">
<div class="bg-white rounded-xl p-6 w-full max-w-lg">

<h3 class="text-xl font-bold mb-4" style="color:var(--primary)">Agregar Usuario</h3>

<form action="../../controllers/UsuarioController.php" method="POST">
<input type="hidden" name="desde_admin" value="1">
<input type="text" name="nombres" placeholder="Nombres" class="w-full mb-3 p-2 border rounded" required>
<input type="text" name="apellidos" placeholder="Apellidos" class="w-full mb-3 p-2 border rounded" required>
<input type="text" name="movil" placeholder="Móvil" class="w-full mb-3 p-2 border rounded" required>
<input type="email" name="email" placeholder="Correo" class="w-full mb-3 p-2 border rounded" required>
<input type="password" name="password" placeholder="Contraseña" class="w-full mb-3 p-2 border rounded" required>
<input type="password" name="confirmar_password" placeholder="Confirmar Contraseña" class="w-full mb-3 p-2 border rounded" required>
<select name="rol" class="w-full mb-3 p-2 border rounded" required>
    <option value="Administrador">Administrador</option>
    <option value="Vendedor">Vendedor</option>
    <option value="Bodeguero">Bodeguero</option>
</select>

<div class="flex justify-end gap-2 mt-4">
    <button type="button" onclick="closeModal('modalCrear')" class="px-4 py-2 bg-gray-300 text-gray-700 rounded hover:bg-gray-400">Cancelar</button>
    <button type="submit" class="text-white px-4 py-2 rounded" style="background:var(--primary)">Guardar</button>
</div>
</form>

</div>
</div>

<!-- MODAL EDITAR -->
<div id="modalEditar" class="fixed inset-0 bg-black bg-opacity-50 hidden flex items-center justify-center z-50">
    <div class="bg-white rounded-xl p-6 w-full max-w-lg">

        <h3 class="text-xl font-bold mb-4" style="color:var(--primary)">Editar Usuario</h3>

        <form action="../../controllers/AdminUsuarioController.php?accion=editar" method="POST">
            <input type="hidden" name="id_usuario" id="edit_id_usuario">

            <label class="block text-sm text-gray-700 mb-1">Nombres</label>
            <input type="text" name="nombres" id="edit_nombres" placeholder="Nombres" class="w-full mb-3 p-2 border rounded" required>

            <label class="block text-sm text-gray-700 mb-1">Apellidos</label>
            <input type="text" name="apellidos" id="edit_apellidos" placeholder="Apellidos" class="w-full mb-3 p-2 border rounded" required>

            <label class="block text-sm text-gray-700 mb-1">Móvil</label>
            <input type="text" name="movil" id="edit_movil" placeholder="Móvil" class="w-full mb-3 p-2 border rounded" required>

            <label class="block text-sm text-gray-700 mb-1">Rol</label>
            <select name="rol" id="edit_rol" class="w-full mb-4 p-2 border rounded" required>
                <option value="Administrador">Administrador</option>
                <option value="Vendedor">Vendedor</option>
                <option value="Bodeguero">Bodeguero</option>
            </select>

            <div class="flex justify-end gap-2 mt-4">
                <button type="button" onclick="closeModal('modalEditar')" class="px-4 py-2 bg-gray-300 text-gray-700 rounded hover:bg-gray-400">Cancelar</button>
                <button type="submit" class="text-white px-4 py-2 rounded" style="background:var(--primary)">Guardar Cambios</button>
            </div>

        </form>

    </div>
</div>

<script>
function openModal(id){ document.getElementById(id).classList.remove('hidden'); }
function closeModal(id){ document.getElementById(id).classList.add('hidden'); }

function openEditModal(u){
    document.getElementById('edit_id_usuario').value = u.id;
    document.getElementById('edit_nombres').value = u.nombres;
    document.getElementById('edit_apellidos').value = u.apellidos;
    document.getElementById('edit_movil').value = u.movil;
    document.getElementById('edit_rol').value = u.rol;
    openModal('modalEditar');
}

function openDeleteModal(id, nombre){
    Swal.fire({
        title: '¿Estás seguro?',
        text: "¡No podrás revertir esto! Se eliminará a " + nombre,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#1E2A3A',
        cancelButtonColor: '#d33',
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

