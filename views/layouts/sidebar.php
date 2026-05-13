
<?php
$rol = $_SESSION['usuario']['rol'] ?? '';
$nombre = ($_SESSION['usuario']['nombres'] ?? '') . ' ' . ($_SESSION['usuario']['apellidos'] ?? '');
$rolDisplay = htmlspecialchars(ucfirst($rol));
?>

<aside style="width:240px;min-width:240px;" class="bg-brand-950 text-white shadow-2xl flex flex-col relative overflow-hidden">
    
    <!-- Efecto visual sutil de fondo -->
    <div class="absolute top-0 right-0 w-32 h-32 bg-brand-600 rounded-full blur-3xl opacity-20 -mr-10 -mt-10"></div>

    <!-- Brand -->
    <div class="flex items-center justify-center border-b px-4 py-8 border-brand-800/50 relative z-10">
        <div class="text-center">
            <div class="font-extrabold tracking-wider bg-clip-text text-transparent bg-gradient-to-r from-white to-brand-100" style="font-size:2.2rem; font-family: 'Outfit', sans-serif;">
                Europa
            </div>
            <div class="text-brand-100/50 text-xs tracking-widest mt-1 uppercase font-semibold">Sistema de Gestión</div>
        </div>
    </div>

    <!-- NAV -->
    <nav class="mt-6 px-3 flex flex-col gap-1.5 flex-1 relative z-10 overflow-y-auto">

        <style>
            .nav-item {
                color: rgba(255,255,255,0.65);
                font-size: 0.875rem;
                font-weight: 500;
                transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
                border-left: 3px solid transparent;
            }
            .nav-item:hover {
                color: #FFFFFF;
                background-color: rgba(37, 99, 235, 0.15);
                border-left-color: rgba(37, 99, 235, 0.5);
                transform: translateX(2px);
            }
            .sidebar-active {
                background: linear-gradient(90deg, rgba(37, 99, 235, 0.2) 0%, rgba(37, 99, 235, 0) 100%);
                color: #FFFFFF !important;
                font-weight: 600;
                border-left: 3px solid #0ea5e9 !important;
            }
            .logout:hover {
                color: #FF6B6B;
                background-color: rgba(255, 107, 107, 0.1);
            }
            /* Separador de sección */
            .nav-section-label {
                font-size: 0.65rem;
                font-weight: 700;
                letter-spacing: 0.1em;
                text-transform: uppercase;
                color: rgba(255,255,255,0.25);
                padding: 0.75rem 1rem 0.25rem;
            }
        </style>

        <?php if ($rol === 'Comprador'): ?>
        <!-- ══════════════════════════════════════
             MENÚ COMPRADOR — vista reducida
        ══════════════════════════════════════ -->

        <a href="../dashboard/index.php"
           class="nav-item flex items-center gap-3 px-4 py-3 rounded-lg">
            <i class="fas fa-home w-4 text-center"></i>
            <span>Inicio</span>
        </a>

        <div class="nav-section-label">Tienda</div>

        <a href="../Catalogo/index.php"
           class="nav-item flex items-center gap-3 px-4 py-3 rounded-lg">
            <i class="fas fa-tags w-4 text-center"></i>
            <span>Catálogo</span>
        </a>

        <a href="../productos/index.php"
           class="nav-item flex items-center gap-3 px-4 py-3 rounded-lg">
            <i class="fas fa-box-open w-4 text-center"></i>
            <span>Productos</span>
        </a>

        <?php else: ?>
        <!-- ══════════════════════════════════════
             MENÚ COMPLETO — otros roles
        ══════════════════════════════════════ -->

        <a href="../dashboard/index.php"
           class="nav-item flex items-center gap-3 px-4 py-3 rounded-lg">
            <i class="fas fa-home w-4 text-center"></i>
            <span>Inicio</span>
        </a>

        <?php if ($rol === 'Administrador'): ?>
        <a href="../dashboard/admin.php"
           class="nav-item flex items-center gap-3 px-4 py-3 rounded-lg">
            <i class="fas fa-users w-4 text-center"></i>
            <span>Usuarios</span>
        </a>
        <?php endif; ?>

        <div class="nav-section-label">Gestión</div>

        <a href="../clientes/index.php"
           class="nav-item flex items-center gap-3 px-4 py-3 rounded-lg">
            <i class="fas fa-user-tie w-4 text-center"></i>
            <span>Clientes</span>
        </a>

        <a href="../proveedores/index.php"
           class="nav-item flex items-center gap-3 px-4 py-3 rounded-lg">
            <i class="fas fa-truck w-4 text-center"></i>
            <span>Proveedores</span>
        </a>

        <div class="nav-section-label">Inventario</div>

        <a href="../Catalogo/index.php"
           class="nav-item flex items-center gap-3 px-4 py-3 rounded-lg">
            <i class="fas fa-tags w-4 text-center"></i>
            <span>Catálogo</span>
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

        <div class="nav-section-label">Comercial</div>

        <a href="../ventas/index.php"
           class="nav-item flex items-center gap-3 px-4 py-3 rounded-lg">
            <i class="fas fa-cash-register w-4 text-center"></i>
            <span>Ventas</span>
        </a>

        <a href="../reportes/index.php"
           class="nav-item flex items-center gap-3 px-4 py-3 rounded-lg">
            <i class="fas fa-chart-line w-4 text-center"></i>
            <span>Reportes / Informes</span>
        </a>

        <?php endif; ?>

        <!-- LOGOUT — siempre visible -->
        <a href="../../controllers/AuthController.php?accion=logout"
           class="nav-item logout flex items-center gap-3 px-4 py-3 rounded-lg mb-3 mt-auto">
            <i class="fas fa-right-from-bracket w-4 text-center"></i>
            <span>Cerrar Sesión</span>
        </a>

        <script>
            document.addEventListener("DOMContentLoaded", function () {
                const currentUrl = window.location.href.toLowerCase();
                document.querySelectorAll('.nav-item').forEach(function (link) {
                    const href = (link.getAttribute('href') || '').toLowerCase();
                    if (href && currentUrl.includes(href.replace(/\.\.\//g, ''))) {
                        link.classList.add('sidebar-active');
                    }
                });
            });
        </script>

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
