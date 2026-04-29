
<?php
$rol = $_SESSION['usuario']['rol'];
$nombre = $_SESSION['usuario']['nombre'];
$rolDisplay = htmlspecialchars(ucfirst($rol));
?>

<aside style="width:240px;min-width:240px;background-color:#1E2A3A;" class="text-white shadow-2xl flex flex-col">

    <!-- Brand -->
    <div class="flex items-center justify-center border-b px-4 py-6" style="border-color:rgba(255,255,255,0.08)">
        <div class="text-center">
            <div class="font-extrabold tracking-wide" style="font-size:2rem">
                Europa
            </div>
        </div>
    </div>

    <!-- NAV -->
    <nav class="mt-4 px-3 flex flex-col gap-1 flex-1">

        <style>
            .nav-item {
                color: rgba(255,255,255,.70);
                font-size: .875rem;
                transition: 0.3s;
            }

            .nav-item:hover {
                color: #FFFFFF;
                background-color: #2C3E50;
                transform: translateX(4px);
            }

            .sidebar-active {
                background-color: #2C3E50;
                color: #FFFFFF !important;
                font-weight: 600;
            }
        </style>

        <a href="../dashboard/admin.php"
           class="sidebar-active nav-item flex items-center gap-3 px-4 py-3 rounded-lg">
            <i class="fas fa-users w-4 text-center"></i>
            <span>Usuarios</span>
        </a>

        <a href="../clientes/index.php"
           class="nav-item flex items-center gap-3 px-4 py-3 rounded-lg">
           <i class="fas fa-user-tie w-4 text-center"></i>
           <span>Catalogo</span>
        </a>

        <a href="../proveedores/index.php"
           class="nav-item flex items-center gap-3 px-4 py-3 rounded-lg">
           <i class="fas fa-truck w-4 text-center"></i>
           <span>Proveedores</span>
        </a>

        <a href="../productos/index.php"
           class="nav-item flex items-center gap-3 px-4 py-3 rounded-lg">
          <i class="fas fa-box-open w-4 text-center"></i>
           <span>Productos</span>
        </a>

        <a href="../inventario/index.php"
           class="nav-item flex items-center gap-3 px-4 py-3 rounded-lg">
           <i class="fas fa-warehouse w-4 text-center"></i>
           <span>Inventario</span>
        </a>


        <a href="../ventas/index.php"
           class="nav-item flex items-center gap-3 px-4 py-3 rounded-lg">
           <i class="fas fa-cash-register w-4 text-center"></i>
           <span>Ventas</span>
        </a>

        <a href="../reportes/index.php"
           class="nav-item flex items-center gap-3 px-4 py-3 rounded-lg">
           <i class="fas fa-chart-line w-4 text-center"></i>
           <span>Reportes</span>
        </a>

        <!-- LOGOUT -->
        <style>
            .logout:hover {
                color: #FF6B6B;
                background-color: rgba(255, 107, 107, 0.1);
            }
        </style>

        <a href="../../controllers/AuthController.php?accion=logout"
           class="nav-item logout flex items-center gap-3 px-4 py-3 rounded-lg mb-3">
           <i class="fas fa-right-from-bracket w-4 text-center"></i>
           <span>Cerrar Sesión</span>
        </a>

    </nav>
</aside>

<!-- MAIN -->
<main class="flex-1 flex flex-col">

    <!-- HEADER SUPERIOR -->
    <header class="flex items-center justify-between px-8 shadow-sm"
            style="height:64px;background-color:#FFFFFF;border-bottom:1px solid #E9EDF2">

        <h1 class="font-semibold" style="font-size:1.5rem;color:#1E2A3A">
            <?= htmlspecialchars($titulo) ?>
        </h1>

        <div class="flex items-center gap-4">

            <div class="w-10 h-10 rounded-full flex items-center justify-center"
                 style="background:#2C3E50;">
                <i class="fas fa-user text-white"></i>
            </div>

            <div class="text-right">
                <div style="color:#1E2A3A;font-size:14px;font-weight:600;">
                    <?= $rolDisplay ?>
                </div>
                <div style="color:#7A869A;font-size:12px;">
                    <?= htmlspecialchars($nombre) ?>
                </div>
            </div>

        </div>

    </header>

    <!-- CONTENIDO -->
    <section class="p-8 flex-1">
