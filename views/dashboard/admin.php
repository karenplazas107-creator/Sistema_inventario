
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
$usuarios = $usuarioModel->obtenerTodos();

$titulo = "Dashboard Administrador";
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/sidebar.php';
?>

<style>
:root{
    --primary:#1E2A3A;
    --secondary:#2C3E50;
    --light:#F4F6F8;
    --gray:#E9EDF2;
    --border:#D6DCE3;
    --text:#1E2A3A;
}

/* BOTÓN */
.btn-agregar{
    background: var(--primary);
    transition: .3s;
}
.btn-agregar:hover{
    background:#16202B;
    transform: translateY(-2px);
}

/* TABLA */
.table-custom{
    background: white;
}
.table-custom thead{
    background: #2C3E50;
    color:white;
}
.table-custom tbody tr:hover{
    background:#F4F6F8;
}

/* BADGES */
.badge-admin{
    background:#dbeafe;
    color:#1e3a8a;
    border:1px solid #93c5fd;
}
.badge-vendedor{
    background:#fef3c7;
    color:#92400e;
    border:1px solid #fcf64d;
}
</style>

<div class="flex items-center justify-between mb-6">
    <div>
        <h2 style="color:var(--primary); font-size:2.5rem; font-weight:bold;">
            Gestión de Usuarios
        </h2>
        <p style="color:#6C7A89;">Administra los usuarios del sistema</p>
    </div>

    <button onclick="openModal('modalCrear')"
        class="btn-agregar text-white px-5 py-3 rounded-xl shadow flex items-center gap-2">
        <i class="fas fa-plus"></i> Agregar Usuario
    </button>
</div>

<div class="bg-white rounded-2xl shadow p-6 border">

<?php if (isset($_SESSION['alert'])): ?>
<script>
Swal.fire({
    icon: '<?= $_SESSION['alert']['icon'] ?>',
    title: '<?= $_SESSION['alert']['title'] ?>',
    text: '<?= $_SESSION['alert']['text'] ?>',
    confirmButtonColor: '#1E2A3A'
});
</script>
<?php unset($_SESSION['alert']); endif; ?>

<div class="overflow-x-auto">
<table class="w-full table-custom rounded-xl overflow-hidden">
<thead>
<tr>
<th class="p-4">Nombre</th>
<th class="p-4">Correo</th>
<th class="p-4">Rol</th>
<th class="p-4 text-center">Acciones</th>
</tr>
</thead>

<tbody>
<?php foreach ($usuarios as $u): ?>
<tr>
<td class="p-4"><?= $u['nombre'] ?></td>
<td class="p-4"><?= $u['correo'] ?></td>
<td class="p-4">
<?php if(strtolower($u['nombre_rol']) == 'administrador'): ?>
<span class="badge-admin px-2 py-1 rounded text-xs">Administrador</span>
<?php else: ?>
<span class="badge-vendedor px-2 py-1 rounded text-xs">Vendedor</span>
<?php endif; ?>
</td>

<td class="p-4 text-center space-x-2">
<button onclick="openEditModal(<?= htmlspecialchars(json_encode($u)) ?>)"
class="px-3 py-1 border rounded hover:bg-gray-100">
<i class="fas fa-pen"></i>
</button>

<button onclick="openDeleteModal(<?= $u['id_usuario'] ?>,'<?= $u['nombre'] ?>')"
class="px-3 py-1 bg-red-500 text-white rounded hover:bg-red-600">
<i class="fas fa-trash"></i>
</button>
</td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
</div>
</div>

<!-- MODAL CREAR -->
<div id="modalCrear" class="fixed inset-0 bg-black bg-opacity-50 hidden flex items-center justify-center">
<div class="bg-white rounded-xl p-6 w-full max-w-lg">

<h3 class="text-xl font-bold mb-4" style="color:var(--primary)">Agregar Usuario</h3>

<form action="../../controllers/UsuarioController.php" method="POST">

<input type="text" name="nombre" placeholder="Nombre" class="w-full mb-3 p-2 border rounded">
<input type="email" name="correo" placeholder="Correo" class="w-full mb-3 p-2 border rounded">
<input type="password" name="password" placeholder="Contraseña" class="w-full mb-3 p-2 border rounded">

<button class="w-full text-white p-2 rounded" style="background:var(--primary)">
Guardar
</button>

</form>

</div>
</div>

<script>
function openModal(id){ document.getElementById(id).classList.remove('hidden'); }
function closeModal(id){ document.getElementById(id).classList.add('hidden'); }

function openEditModal(u){
console.log(u);
}

function openDeleteModal(id,nombre){
if(confirm("Eliminar a "+nombre+"?")){
window.location.href='../../controllers/AdminUsuarioController.php?accion=eliminar&id='+id;
}
}
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>

