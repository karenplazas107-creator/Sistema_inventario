<?php
session_start();
// Manejo de alertas de SweetAlert2
$alert = $_SESSION['alert'] ?? null;
unset($_SESSION['alert']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Almacén Europa | Acceso Seguro</title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Google Fonts: Outfit & Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&family=Outfit:wght@400;500;700;800&display=swap" rel="stylesheet">
    
    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <!-- Icono -->
    <link rel="shortcut icon" type="image/png" href="../../img/ico.png">

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                        heading: ['Outfit', 'sans-serif'],
                    },
                    colors: {
                        brand: {
                            50: '#eff6ff',
                            100: '#dbeafe',
                            500: '#3b82f6',
                            600: '#2563eb',
                            800: '#1e40af',
                            900: '#1e3a8a',
                            950: '#172554',
                        },
                        accent: '#0ea5e9',
                    }
                }
            }
        }
    </script>

    <style>
        .glass-panel {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        /* Efecto sutil de blob animado en el fondo izquierdo */
        .blob {
            position: absolute;
            filter: blur(60px);
            opacity: 0.5;
            animation: moveBlob 8s infinite alternate ease-in-out;
        }

        @keyframes moveBlob {
            0% { transform: translate(0, 0) scale(1); }
            100% { transform: translate(30px, -30px) scale(1.1); }
        }
    </style>
</head>

<body class="bg-gray-50 min-h-screen flex items-center justify-center p-4 selection:bg-brand-500 selection:text-white">

    <div class="bg-white w-full max-w-5xl rounded-[2rem] shadow-2xl overflow-hidden flex flex-col md:flex-row min-h-[600px] relative">
        
        <!-- MITAD IZQUIERDA: DISEÑO VISUAL -->
        <div class="hidden md:flex md:w-[45%] bg-brand-950 relative p-12 text-white flex-col justify-between overflow-hidden">
            <!-- Blobs de fondo -->
            <div class="blob w-64 h-64 bg-brand-600 rounded-full top-10 -left-10"></div>
            <div class="blob w-64 h-64 bg-accent rounded-full bottom-10 -right-10" style="animation-delay: 2s;"></div>
            
            <div class="relative z-10">
                <a href="../../public/index.php" class="inline-flex items-center gap-2 mb-12 hover:text-brand-100 transition-colors">
                    <i class="fas fa-arrow-left text-sm"></i>
                    <span class="text-sm font-medium">Volver al inicio</span>
                </a>
                
                <div class="w-12 h-12 rounded-xl bg-gradient-to-tr from-brand-600 to-accent flex items-center justify-center shadow-lg mb-6">
                    <i class="fas fa-store text-white text-2xl"></i>
                </div>
                
                <h2 class="font-heading text-4xl font-bold mb-4 leading-tight">Gestión<br>Inteligente</h2>
                <p class="text-brand-100/80 font-light text-lg max-w-sm leading-relaxed">
                    Acceda al panel de control central para supervisar el inventario, procesar ventas y analizar el rendimiento en tiempo real.
                </p>
            </div>
            
            <div class="relative z-10 glass-panel p-4 rounded-2xl flex items-center gap-4">
                <div class="w-10 h-10 rounded-full bg-green-500/20 flex items-center justify-center">
                    <div class="w-3 h-3 rounded-full bg-green-400 animate-pulse"></div>
                </div>
                <div>
                    <div class="text-sm font-semibold text-white">Servidor Conectado</div>
                    <div class="text-xs text-brand-100/70">Conexión cifrada SSL</div>
                </div>
            </div>
        </div>

        <!-- MITAD DERECHA: FORMULARIO -->
        <div class="w-full md:w-[55%] p-8 md:p-14 lg:p-20 flex flex-col justify-center bg-white relative">
            
            <!-- Botón de retorno móvil -->
            <a href="../../public/index.php" class="md:hidden inline-flex items-center gap-2 text-gray-500 mb-8 hover:text-brand-600 transition-colors">
                <i class="fas fa-arrow-left text-sm"></i>
                <span class="text-sm font-medium">Volver</span>
            </a>

            <div class="mb-10 text-center md:text-left">
                <h1 class="font-heading text-3xl md:text-4xl font-bold text-gray-900 mb-2">Bienvenido de nuevo</h1>
                <p class="text-gray-500">Ingrese sus credenciales para continuar</p>
            </div>

            <form action="../../controllers/AuthController.php" method="POST" class="space-y-6">
                <!-- Input Email -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Correo Electrónico</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <i class="fas fa-envelope text-gray-400"></i>
                        </div>
                        <input type="email" name="email" required 
                            class="w-full pl-11 pr-4 py-3.5 bg-gray-50 border border-gray-200 rounded-xl text-gray-900 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500 focus:bg-white outline-none transition-all"
                            placeholder="admin@almaceneuropa.com">
                    </div>
                </div>

                <!-- Input Password -->
                <div>
                    <div class="flex justify-between items-center mb-2">
                        <label class="block text-sm font-semibold text-gray-700">Contraseña</label>
                        <a href="#" class="text-xs font-medium text-brand-600 hover:text-brand-800 transition-colors">¿Olvidó su contraseña?</a>
                    </div>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <i class="fas fa-lock text-gray-400"></i>
                        </div>
                        <input type="password" name="password" required 
                            class="w-full pl-11 pr-4 py-3.5 bg-gray-50 border border-gray-200 rounded-xl text-gray-900 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500 focus:bg-white outline-none transition-all"
                            placeholder="••••••••">
                    </div>
                </div>

                <!-- Botón Submit -->
                <button type="submit" 
                    class="w-full relative inline-flex items-center justify-center px-8 py-3.5 mt-4 text-sm font-semibold text-white transition-all duration-300 bg-brand-900 rounded-xl hover:bg-brand-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-brand-600 overflow-hidden group shadow-lg shadow-brand-900/30 hover:shadow-brand-900/50">
                    <span class="absolute inset-0 w-full h-full -mt-1 rounded-lg opacity-30 bg-gradient-to-b from-transparent via-transparent to-black"></span>
                    <span class="relative flex items-center gap-2">
                        Ingresar al Sistema <i class="fas fa-arrow-right text-xs group-hover:translate-x-1 transition-transform"></i>
                    </span>
                </button>
            </form>

            <!-- Registro -->
            <div class="mt-10 text-center">
                <p class="text-sm text-gray-500">
                    ¿Nuevo empleado? 
                    <a href="registre.php" class="font-semibold text-brand-600 hover:text-brand-800 hover:underline transition-all">
                        Regístrese aquí
                    </a>
                </p>
            </div>

        </div>
    </div>

    <!-- Script Alertas -->
    <?php if ($alert): ?>
    <script>
        Swal.fire({
            icon: '<?= $alert['icon'] ?>',
            title: '<span style="font-family: \'Outfit\', sans-serif; font-weight: 700;"><?= $alert['title'] ?></span>',
            html: '<span style="font-family: \'Inter\', sans-serif;"><?= $alert['text'] ?></span>',
            confirmButtonColor: '#1e3a8a',
            customClass: {
                popup: 'rounded-2xl',
                confirmButton: 'rounded-xl px-6 py-2.5 font-semibold text-sm'
            }
        });
    </script>
    <?php endif; ?>

</body>
</html>