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
    <title>Almacén Europa - Login</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="shortcut icon" type="image/png" href="../../img/ico.png">
    <style>
        .gradient-bg { background: linear-gradient(135deg, #0f172a 0%, #334155 100%); }
    </style>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center p-4">

    <div class="bg-white w-full max-w-4xl rounded-2xl shadow-2xl overflow-hidden flex flex-col md:flex-row min-h-[550px]">
        
        <div class="hidden md:flex md:w-1/2 gradient-bg p-12 text-white flex-col justify-between">
            <div>
                <h2 class="text-3xl font-bold mb-4">Almacén Europa</h2>
                <p class="text-slate-200">Sistema de Gestión de Ventas e Inventario. Acceda para administrar el stock y las operaciones diarias.</p>
            </div>
            <div class="flex items-center space-x-4">
                <div class="bg-white/10 p-3 rounded-lg backdrop-blur-sm">
                    <i class="fas fa-shield-alt text-2xl"></i>
                </div>
                <p class="text-sm font-light italic">Conexión segura al servidor central.</p>
            </div>
        </div>

        <div class="w-full md:w-1/2 p-8 md:p-12 flex flex-col justify-center">
            <div class="mb-8">
                <h1 class="text-2xl font-bold text-slate-800">Iniciar Sesión</h1>
                <p class="text-gray-400">Ingrese sus datos de acceso</p>
            </div>

            <form action="../../controllers/AuthController.php" method="POST" class="space-y-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Correo Electrónico</label>
                    <input type="email" name="email" required 
                        class="w-full px-4 py-3 mt-1 border border-gray-300 rounded-lg focus:ring-2 focus:ring-slate-800 outline-none transition"
                        placeholder="empleado@europa.com">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Contraseña</label>
                    <input type="password" name="password" required 
                        class="w-full px-4 py-3 mt-1 border border-gray-300 rounded-lg focus:ring-2 focus:ring-slate-800 outline-none transition"
                        placeholder="••••••••">
                </div>
                    <!-- REGISTRO -->
<div class="text-center">

<p class="text-sm text-gray-500">

¿No tienes cuenta?

<a href="registre.php"
class="text-[black] font-bold hover:underline">

Regístrate aquí

</a>
<br> <br> 

                <button type="submit" 
                    class="w-full bg-slate-800 text-white font-bold py-3 rounded-lg hover:bg-slate-900 transition shadow-lg">
                    Entrar al Sistema
                </button>
            </form>
        </div>
    </div>

    <?php if ($alert): ?>
    <script>
        Swal.fire({
            icon: '<?= $alert['icon'] ?>',
            title: '<?= $alert['title'] ?>',
            text: '<?= $alert['text'] ?>',
            confirmButtonColor: '#0f172a'
        });
    </script>
    <?php endif; ?>


</body>
</html>