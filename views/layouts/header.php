
<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['usuario'])) {
    header("Location: ../usuarios/login.php");
    exit;
}

$usuario = $_SESSION['usuario'];
$titulo = $titulo ?? 'Dashboard';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($titulo) ?> | VentaNet</title>

    <!-- Tailwind -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
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

    <!-- Iconos -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Alertas -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- 🎨 ESTILOS GLOBALES -->
    <style>
        :root{
            --primary:#1E2A3A;
            --secondary:#2C3E50;
            --light:#F4F6F8;
            --border:#D6DCE3;
            --text:#1E2A3A;
        }

        body{
            background: var(--light);
            color: var(--text);
        }

        /* SCROLL BONITO */
        ::-webkit-scrollbar{
            width:8px;
        }
        ::-webkit-scrollbar-thumb{
            background: var(--secondary);
            border-radius:10px;
        }

        /* TARJETAS GENERALES */
        .card{
            background:white;
            border-radius:16px;
            box-shadow:0 4px 12px rgba(0,0,0,0.05);
            border:1px solid var(--border);
        }

        /* BOTONES GENERALES */
        .btn-primary{
            background: var(--primary);
            color:white;
            transition: .3s;
        }
        .btn-primary:hover{
            background:#16202B;
            transform: translateY(-1px);
        }
    </style>

</head>

<body class="min-h-screen">
<div class="flex min-h-screen">
