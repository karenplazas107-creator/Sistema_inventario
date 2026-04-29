<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Almacén Europa - Sistema de Ventas e Inventario</title>
    <link rel="shortcut icon" type="image/png" href="../img/ico.png">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .gradient-bg {
            background: linear-gradient(135deg, #0f172a 0%, #334155 100%);
        }

        .slide {
            display: none;
        }

        .slide.active {
            display: block;
            animation: fadeIn 0.8s;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
    </style>
</head>

<body class="bg-gray-50 font-sans text-gray-800">

    <nav class="bg-white shadow-md sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16 items-center">
                <div class="flex items-center">
                    <div class="flex items-center space-x-2">
                        <div class="bg-slate-800 text-white p-2 rounded-lg">
                            <i class="fas fa-store"></i>
                        </div>
                        <span class="text-xl font-bold tracking-tight text-slate-800">Almacén Europa</span>
                    </div>
                </div>
                <div class="hidden md:flex space-x-8 font-medium">
                    <a href="#inicio" class="hover:text-blue-600 transition">Inicio</a>
                    <a href="#nosotros" class="hover:text-blue-600 transition">Nosotros</a>
                    <a href="#servicios" class="hover:text-blue-600 transition">Funcionalidades</a>
                    <a href="../views/usuario/login.php"
                        class="bg-slate-800 text-white px-4 py-2 rounded-lg hover:bg-slate-700 transition">Acceso Sistema</a>
                </div>
            </div>
        </div>
    </nav>

    <section id="inicio" class="relative h-[550px] overflow-hidden text-white">
        <div class="slide active relative h-full">
            <img src="https://images.unsplash.com/photo-1553413077-190dd305871c?auto=format&fit=crop&w=1600&q=80"
                class="absolute inset-0 w-full h-full object-cover brightness-50" alt="Almacén">
            <div class="relative z-10 flex flex-col items-center justify-center h-full text-center px-4">
                <h1 class="text-4xl md:text-6xl font-bold mb-4">Control Total de Inventario</h1>
                <p class="text-xl max-w-2xl">Gestione sus productos en tiempo real, optimice stock y evite pérdidas en el Almacén Europa.</p>
            </div>
        </div>
        <div class="slide relative h-full">
            <img src="https://images.unsplash.com/photo-1556742044-3c52d6e88c62?auto=format&fit=crop&w=1600&q=80"
                class="absolute inset-0 w-full h-full object-cover brightness-50" alt="Ventas">
            <div class="relative z-10 flex flex-col items-center justify-center h-full text-center px-4">
                <h1 class="text-4xl md:text-6xl font-bold mb-4">Punto de Venta Ágil</h1>
                <p class="text-xl max-w-2xl">Facturación rápida y reportes de ventas detallados para una toma de decisiones inteligente.</p>
            </div>
        </div>

        <button onclick="changeSlide(-1)"
            class="absolute left-4 top-1/2 z-20 bg-black/30 p-3 rounded-full hover:bg-black/50"><i
                class="fas fa-chevron-left"></i></button>
        <button onclick="changeSlide(1)"
            class="absolute right-4 top-1/2 z-20 bg-black/30 p-3 rounded-full hover:bg-black/50"><i
                class="fas fa-chevron-right"></i></button>
    </section>

    <section id="nosotros" class="py-20 max-w-7xl mx-auto px-4">
        <div class="grid md:grid-cols-2 gap-12">
            <div class="bg-white p-8 rounded-2xl shadow-sm border-t-4 border-slate-800">
                <div class="text-slate-800 text-3xl mb-4"><i class="fas fa-rocket"></i></div>
                <h2 class="text-2xl font-bold mb-4">Nuestra Misión</h2>
                <p class="text-gray-600 leading-relaxed">
                    Optimizar la operación comercial del Almacén Europa mediante una plataforma tecnológica robusta que integre de forma eficiente las ventas, el control de existencias y la gestión de proveedores.
                </p>
            </div>
            <div class="bg-white p-8 rounded-2xl shadow-sm border-t-4 border-blue-500">
                <div class="text-blue-500 text-3xl mb-4"><i class="fas fa-globe-europe"></i></div>
                <h2 class="text-2xl font-bold mb-4">Nuestra Visión</h2>
                <p class="text-gray-600 leading-relaxed">
                    Consolidarnos como el sistema de gestión líder en el sector comercial, permitiendo al Almacén Europa expandir sus fronteras con procesos automatizados y datos precisos para el crecimiento sostenido.
                </p>
            </div>
        </div>
    </section>

    <section id="servicios" class="py-20 bg-gray-100">
        <div class="max-w-7xl mx-auto px-4">
            <div class="text-center mb-16">
                <h2 class="text-3xl font-bold text-slate-800">Funcionalidades Estratégicas</h2>
                <div class="w-24 h-1 bg-slate-800 mx-auto mt-4"></div>
            </div>
            <div class="grid md:grid-cols-3 gap-8">
                <div class="bg-white p-8 rounded-xl shadow-md text-center hover:transform hover:-translate-y-2 transition duration-300">
                    <div class="w-16 h-16 gradient-bg rounded-full flex items-center justify-center text-white text-2xl mx-auto mb-4">
                        <i class="fas fa-barcode"></i>
                    </div>
                    <h3 class="font-bold text-lg mb-2">Control de Stock</h3>
                    <p class="text-gray-600 text-sm">Monitoreo automático de entradas y salidas para evitar quiebres de inventario.</p>
                </div>
                <div class="bg-white p-8 rounded-xl shadow-md text-center hover:transform hover:-translate-y-2 transition duration-300">
                    <div class="w-16 h-16 gradient-bg rounded-full flex items-center justify-center text-white text-2xl mx-auto mb-4">
                        <i class="fas fa-cash-register"></i>
                    </div>
                    <h3 class="font-bold text-lg mb-2">Ventas Rápidas</h3>
                    <p class="text-gray-600 text-sm">Interfaz optimizada para procesar transacciones en segundos y generar facturas.</p>
                </div>
                <div class="bg-white p-8 rounded-xl shadow-md text-center hover:transform hover:-translate-y-2 transition duration-300">
                    <div class="w-16 h-16 gradient-bg rounded-full flex items-center justify-center text-white text-2xl mx-auto mb-4">
                        <i class="fas fa-file-invoice-dollar"></i>
                    </div>
                    <h3 class="font-bold text-lg mb-2">Reportes Analíticos</h3>
                    <p class="text-gray-600 text-sm">Análisis detallado de ganancias, productos más vendidos y rendimiento diario.</p>
                </div>
            </div>
        </div>
    </section>

    <footer class="bg-slate-900 text-gray-300 py-12">
        <div class="max-w-7xl mx-auto px-4 grid md:grid-cols-3 gap-12">
            <div>
                <h4 class="text-white text-xl font-bold mb-4 italic">Almacén Europa</h4>
                <p class="text-sm">Soluciones digitales para la gestión comercial moderna. Precisión en cada venta, control en cada producto.</p>
            </div>
            <div>
                <h4 class="text-white font-bold mb-4">Enlaces del Sistema</h4>
                <ul class="space-y-2 text-sm">
                    <li><a href="#" class="hover:text-blue-400">Panel de Control</a></li>
                    <li><a href="#" class="hover:text-blue-400">Guía de Usuario</a></li>
                    <li><a href="#" class="hover:text-blue-400">Soporte Técnico</a></li>
                </ul>
            </div>
            <div>
                <h4 class="text-white font-bold mb-4">Contacto Directo</h4>
                <p class="text-sm"><i class="fas fa-envelope mr-2"></i> admin@almaceneuropa.com</p>
                <p class="text-sm mt-2"><i class="fas fa-map-marker-alt mr-2"></i> Parque Prinicipal Fundadores </p>
                <div class="flex space-x-4 mt-4">
                    <a href="#" class="text-xl hover:text-white"><i class="fab fa-facebook"></i></a>
                    <a href="#" class="text-xl hover:text-white"><i class="fab fa-whatsapp"></i></a>
                    <a href="#" class="text-xl hover:text-white"><i class="fab fa-instagram"></i></a>
                </div>
            </div>
        </div>
        <div class="border-t border-gray-800 mt-12 pt-8 text-center text-xs">
            &copy; 2026 Almacén Europa - Sistema de Ventas e Inventario. Todos los derechos reservados.
        </div>
    </footer>

    <script>
        let currentSlide = 0;
        const slides = document.querySelectorAll('.slide');

        function changeSlide(direction) {
            slides[currentSlide].classList.remove('active');
            currentSlide = (currentSlide + direction + slides.length) % slides.length;
            slides[currentSlide].classList.add('active');
        }

        // Auto slide cada 6 segundos
        setInterval(() => changeSlide(1), 6000);
    </script>
</body>

</html>