<!DOCTYPE html>
<html lang="es" class="scroll-smooth">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Almacén Europa | Gestión Inteligente de Inventario</title>
    <link rel="shortcut icon" type="image/png" href="../img/ico.png">
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Google Fonts: Outfit (Headings) & Inter (Body) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&family=Outfit:wght@400;500;700;800&display=swap" rel="stylesheet">
    
    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- AOS Animation Library -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>

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
        /* Custom Utilities */
        .glass-nav {
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.3);
        }
        
        .hero-gradient {
            background: linear-gradient(135deg, #172554 0%, #1e3a8a 50%, #0ea5e9 100%);
        }

        .glass-card {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            box-shadow: 0 4px 30px rgba(0, 0, 0, 0.1);
        }

        .text-gradient {
            background: linear-gradient(to right, #60a5fa, #34d399);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        /* Blob Animations */
        .blob {
            position: absolute;
            filter: blur(80px);
            z-index: 0;
            opacity: 0.6;
            animation: move 10s infinite alternate;
        }

        @keyframes move {
            from { transform: translate(0, 0) scale(1); }
            to { transform: translate(20px, -20px) scale(1.1); }
        }

        .bento-hover {
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .bento-hover:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 40px -5px rgba(37, 99, 235, 0.15);
        }
    </style>
</head>

<body class="bg-gray-50 text-gray-800 antialiased selection:bg-brand-500 selection:text-white">

    <!-- Navbar -->
    <nav class="fixed w-full z-50 glass-nav transition-all duration-300" id="navbar">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-20 items-center">
                <!-- Logo -->
                <div class="flex items-center gap-3 group cursor-pointer" onclick="window.scrollTo(0,0)">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-brand-600 to-accent flex items-center justify-center shadow-lg group-hover:shadow-brand-500/50 transition-all duration-300">
                        <i class="fas fa-store text-white text-xl"></i>
                    </div>
                    <span class="font-heading text-2xl font-bold text-gray-900 tracking-tight">Almacén<span class="text-brand-600">Europa</span></span>
                </div>

                <!-- Desktop Menu -->
                <div class="hidden md:flex items-center space-x-8">
                    <a href="#inicio" class="text-sm font-medium text-gray-600 hover:text-brand-600 transition-colors">Inicio</a>
                    <a href="#nosotros" class="text-sm font-medium text-gray-600 hover:text-brand-600 transition-colors">Nosotros</a>
                    <a href="#funcionalidades" class="text-sm font-medium text-gray-600 hover:text-brand-600 transition-colors">Funcionalidades</a>
                    
                    <a href="../views/usuarios/login.php" class="relative inline-flex items-center justify-center px-6 py-2.5 text-sm font-medium text-white transition-all duration-300 bg-brand-900 rounded-full hover:bg-brand-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-brand-600 overflow-hidden group">
                        <span class="absolute inset-0 w-full h-full -mt-1 rounded-lg opacity-30 bg-gradient-to-b from-transparent via-transparent to-black"></span>
                        <span class="relative flex items-center gap-2">
                            Ingresar <i class="fas fa-arrow-right text-xs group-hover:translate-x-1 transition-transform"></i>
                        </span>
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section id="inicio" class="relative min-h-screen flex items-center pt-20 overflow-hidden bg-brand-950">
        <!-- Animated Background Blobs -->
        <div class="blob bg-brand-600 w-96 h-96 rounded-full top-0 left-0 mix-blend-multiply"></div>
        <div class="blob bg-accent w-96 h-96 rounded-full bottom-0 right-0 mix-blend-multiply animation-delay-2000"></div>
        
        <!-- Background Pattern -->
        <div class="absolute inset-0 bg-[url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjAiIGhlaWdodD0iMjAiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+PGNpcmNsZSBjeD0iMSIgY3k9IjEiIHI9IjEiIGZpbGw9InJnYmEoMjU1LDI1NSwyNTUsMC4wNSkiLz48L3N2Zz4=')] [mask-image:linear-gradient(to_bottom,white,transparent)]"></div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10 w-full py-12 lg:py-0">
            <div class="grid lg:grid-cols-2 gap-12 items-center">
                
                <!-- Hero Text -->
                <div data-aos="fade-right" data-aos-duration="1000">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full glass-card text-brand-100 text-sm font-medium mb-6">
                        <span class="w-2 h-2 rounded-full bg-green-400 animate-pulse"></span>
                        Sistema de Gestión v2.0
                    </div>
                    <h1 class="font-heading text-5xl lg:text-7xl font-extrabold text-white leading-tight mb-6">
                        Control Total para su <br>
                        <span class="text-gradient">Almacén.</span>
                    </h1>
                    <p class="text-lg text-gray-300 mb-8 max-w-xl font-light leading-relaxed">
                        Optimice el inventario, acelere sus ventas y tome decisiones inteligentes en tiempo real con la plataforma diseñada exclusivamente para el Almacén Europa.
                    </p>
                    
                    <div class="flex flex-col sm:flex-row gap-4">
                        <a href="../views/usuarios/login.php" class="px-8 py-4 rounded-xl bg-white text-brand-900 font-semibold text-center hover:bg-brand-50 transition-colors shadow-xl shadow-white/10 flex items-center justify-center gap-2">
                            Comenzar Ahora <i class="fas fa-bolt text-brand-500"></i>
                        </a>
                        <a href="#funcionalidades" class="px-8 py-4 rounded-xl glass-card text-white font-medium text-center hover:bg-white/10 transition-colors flex items-center justify-center gap-2">
                            Ver Características
                        </a>
                    </div>
                    
                    <!-- Stats -->
                    <div class="grid grid-cols-3 gap-6 mt-12 pt-8 border-t border-white/10">
                        <div>
                            <div class="text-3xl font-heading font-bold text-white mb-1">100%</div>
                            <div class="text-sm text-gray-400">Control de Stock</div>
                        </div>
                        <div>
                            <div class="text-3xl font-heading font-bold text-white mb-1">24/7</div>
                            <div class="text-sm text-gray-400">Disponibilidad</div>
                        </div>
                        <div>
                            <div class="text-3xl font-heading font-bold text-white mb-1">+Rápido</div>
                            <div class="text-sm text-gray-400">En facturación</div>
                        </div>
                    </div>
                </div>

                <!-- Hero Image/Dashboard Preview -->
                <div class="relative hidden lg:block" data-aos="fade-left" data-aos-duration="1200" data-aos-delay="200">
                    <div class="absolute inset-0 bg-gradient-to-tr from-brand-500 to-accent blur-3xl opacity-30 rounded-full"></div>
                    <div class="glass-card rounded-2xl border border-white/20 p-2 relative overflow-hidden shadow-2xl transform rotate-2 hover:rotate-0 transition-transform duration-500">
                        <div class="bg-slate-900 rounded-xl overflow-hidden flex flex-col h-[500px]">
                            <!-- Mockup Header -->
                            <div class="h-10 border-b border-white/10 flex items-center px-4 gap-2 bg-slate-800/50">
                                <div class="w-3 h-3 rounded-full bg-red-500"></div>
                                <div class="w-3 h-3 rounded-full bg-yellow-500"></div>
                                <div class="w-3 h-3 rounded-full bg-green-500"></div>
                            </div>
                            <!-- Mockup Body (Image) -->
                            <img src="https://images.unsplash.com/photo-1551288049-bebda4e38f71?auto=format&fit=crop&w=1000&q=80" alt="Dashboard Preview" class="w-full h-full object-cover opacity-80 mix-blend-luminosity hover:mix-blend-normal transition-all duration-700">
                            
                            <!-- Floating Card -->
                            <div class="absolute bottom-8 -left-8 glass-card p-4 rounded-xl flex items-center gap-4 border border-white/20 animate-bounce" style="animation-duration: 3s;">
                                <div class="w-12 h-12 rounded-full bg-green-500/20 flex items-center justify-center text-green-400 text-xl">
                                    <i class="fas fa-arrow-trend-up"></i>
                                </div>
                                <div>
                                    <div class="text-sm text-gray-300">Ventas de Hoy</div>
                                    <div class="text-xl font-bold text-white">+24%</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
        
        <!-- Bottom Wave -->
        <div class="absolute bottom-0 left-0 w-full overflow-hidden leading-none">
            <svg class="relative block w-full h-[50px] md:h-[100px]" data-name="Layer 1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1200 120" preserveAspectRatio="none">
                <path d="M321.39,56.44c58-10.79,114.16-30.13,172-41.86,82.39-16.72,168.19-17.73,250.45-.39C823.78,31,906.67,72,985.66,92.83c70.05,18.48,146.53,26.09,214.34,3V120H0V95.8C59.71,118.08,130.83,121.32,201.29,115.8C242.41,112.6,283.4,103.5,321.39,56.44Z" fill="#f9fafb"></path>
            </svg>
        </div>
    </section>

    <!-- Nosotros / Misión y Visión -->
    <section id="nosotros" class="py-24 bg-gray-50 relative">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-3xl mx-auto mb-16" data-aos="fade-up">
                <span class="text-brand-600 font-semibold tracking-wider uppercase text-sm">Identidad Corporativa</span>
                <h2 class="font-heading text-3xl md:text-5xl font-bold text-gray-900 mt-2 mb-6">El Motor de Nuestro Negocio</h2>
                <p class="text-gray-600 text-lg">En Almacén Europa no solo vendemos productos, gestionamos la confianza de nuestros clientes a través de procesos impecables y tecnología de punta.</p>
            </div>

            <div class="grid md:grid-cols-2 gap-8">
                <!-- Misión -->
                <div class="bg-white rounded-3xl p-10 shadow-[0_8px_30px_rgb(0,0,0,0.04)] relative overflow-hidden group bento-hover" data-aos="fade-up" data-aos-delay="100">
                    <div class="absolute top-0 right-0 w-32 h-32 bg-brand-50 rounded-bl-[100px] -z-10 transition-transform group-hover:scale-110"></div>
                    <div class="w-16 h-16 bg-white rounded-2xl shadow-md border border-gray-100 flex items-center justify-center text-brand-600 text-2xl mb-8 transform -rotate-6 group-hover:rotate-0 transition-transform">
                        <i class="fas fa-bullseye"></i>
                    </div>
                    <h3 class="font-heading text-2xl font-bold text-gray-900 mb-4">Nuestra Misión</h3>
                    <p class="text-gray-600 leading-relaxed">
                        Optimizar la operación comercial del Almacén Europa mediante una plataforma tecnológica robusta que integre de forma eficiente las ventas, el estricto control de existencias y la gestión transparente de proveedores para ofrecer siempre el mejor servicio.
                    </p>
                </div>

                <!-- Visión -->
                <div class="bg-white rounded-3xl p-10 shadow-[0_8px_30px_rgb(0,0,0,0.04)] relative overflow-hidden group bento-hover" data-aos="fade-up" data-aos-delay="200">
                    <div class="absolute top-0 right-0 w-32 h-32 bg-accent/10 rounded-bl-[100px] -z-10 transition-transform group-hover:scale-110"></div>
                    <div class="w-16 h-16 bg-white rounded-2xl shadow-md border border-gray-100 flex items-center justify-center text-accent text-2xl mb-8 transform rotate-6 group-hover:rotate-0 transition-transform">
                        <i class="fas fa-eye"></i>
                    </div>
                    <h3 class="font-heading text-2xl font-bold text-gray-900 mb-4">Nuestra Visión</h3>
                    <p class="text-gray-600 leading-relaxed">
                        Consolidarnos como el referente en gestión automatizada en el sector comercial, permitiendo al Almacén Europa expandir sus fronteras operativas con procesos ágiles y datos precisos para un crecimiento sostenido e inteligente.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- Funcionalidades (Bento Grid) -->
    <section id="funcionalidades" class="py-24 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col md:flex-row md:items-end justify-between mb-16" data-aos="fade-in">
                <div class="max-w-2xl">
                    <span class="text-brand-600 font-semibold tracking-wider uppercase text-sm">Capacidades</span>
                    <h2 class="font-heading text-3xl md:text-5xl font-bold text-gray-900 mt-2">Todo lo que necesita en un solo lugar</h2>
                </div>
                <div class="mt-6 md:mt-0">
                    <a href="../views/usuarios/login.php" class="inline-flex items-center gap-2 text-brand-600 font-medium hover:text-brand-800 transition-colors">
                        Acceder al Panel <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                
                <!-- Card 1 (Large) -->
                <div class="md:col-span-2 bg-slate-50 rounded-3xl p-8 border border-gray-100 bento-hover flex flex-col justify-between overflow-hidden relative" data-aos="fade-up" data-aos-delay="100">
                    <div class="relative z-10 max-w-md">
                        <div class="w-12 h-12 bg-white rounded-full flex items-center justify-center text-gray-800 shadow-sm mb-6">
                            <i class="fas fa-boxes-stacked"></i>
                        </div>
                        <h3 class="font-heading text-2xl font-bold text-gray-900 mb-3">Gestión de Inventario en Tiempo Real</h3>
                        <p class="text-gray-600 mb-6">Controle el stock al detalle. Reciba alertas de stock mínimo, gestione múltiples categorías y evite la pérdida de ventas por falta de productos.</p>
                    </div>
                    <div class="absolute -bottom-10 -right-10 w-2/3 opacity-50 md:opacity-100 transition-transform hover:scale-105">
                        <img src="https://images.unsplash.com/photo-1586528116311-ad8ed79a2779?auto=format&fit=crop&w=600&q=80" alt="Boxes" class="rounded-tl-2xl shadow-2xl border-t border-l border-white/50">
                    </div>
                </div>

                <!-- Card 2 -->
                <div class="bg-brand-900 rounded-3xl p-8 text-white bento-hover relative overflow-hidden" data-aos="fade-up" data-aos-delay="200">
                    <div class="absolute top-0 right-0 w-full h-full bg-gradient-to-br from-brand-600/20 to-transparent"></div>
                    <div class="relative z-10">
                        <div class="w-12 h-12 bg-white/10 rounded-full flex items-center justify-center text-brand-100 mb-6 backdrop-blur-sm">
                            <i class="fas fa-cash-register"></i>
                        </div>
                        <h3 class="font-heading text-xl font-bold mb-3">Punto de Venta (POS)</h3>
                        <p class="text-brand-100 text-sm">Interfaz ultrarrápida diseñada para procesar pagos y generar facturas sin demoras.</p>
                    </div>
                </div>

                <!-- Card 3 -->
                <div class="bg-accent/10 rounded-3xl p-8 border border-accent/20 bento-hover" data-aos="fade-up" data-aos-delay="300">
                    <div class="w-12 h-12 bg-white rounded-full flex items-center justify-center text-accent shadow-sm mb-6">
                        <i class="fas fa-users-gear"></i>
                    </div>
                    <h3 class="font-heading text-xl font-bold text-gray-900 mb-3">Roles y Accesos</h3>
                    <p class="text-gray-600 text-sm">Administradores, Vendedores y Bodegueros. Cada quien con los permisos exactos que necesita.</p>
                </div>

                <!-- Card 4 (Large) -->
                <div class="md:col-span-2 bg-slate-900 rounded-3xl p-8 border border-gray-800 text-white bento-hover relative overflow-hidden" data-aos="fade-up" data-aos-delay="400">
                    <!-- Decoración fondo -->
                    <div class="absolute -right-20 -top-20 w-64 h-64 bg-accent blur-[100px] opacity-20 rounded-full"></div>
                    
                    <div class="relative z-10 flex flex-col md:flex-row gap-8 items-center h-full">
                        <div class="flex-1">
                            <div class="w-12 h-12 bg-white/10 rounded-full flex items-center justify-center text-white mb-6 backdrop-blur-sm">
                                <i class="fas fa-chart-pie"></i>
                            </div>
                            <h3 class="font-heading text-2xl font-bold mb-3">Reportes Analíticos</h3>
                            <p class="text-gray-400 mb-0">Tome decisiones informadas. Visualice el rendimiento de sus ventas, analice los productos estrella y supervise el flujo de caja diario en dashboards interactivos.</p>
                        </div>
                        <div class="flex-1 w-full flex justify-center">
                            <!-- Mini gráfico visual -->
                            <div class="flex items-end gap-3 h-32 w-full max-w-[200px]">
                                <div class="w-1/4 bg-brand-600 rounded-t-md h-[40%] hover:h-[50%] transition-all"></div>
                                <div class="w-1/4 bg-accent rounded-t-md h-[70%] hover:h-[80%] transition-all"></div>
                                <div class="w-1/4 bg-brand-400 rounded-t-md h-[50%] hover:h-[60%] transition-all"></div>
                                <div class="w-1/4 bg-white rounded-t-md h-[90%] hover:h-[100%] transition-all"></div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-gray-950 pt-20 pb-10 border-t border-gray-900">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-12 mb-16">
                <!-- Brand -->
                <div class="md:col-span-2">
                    <div class="flex items-center gap-2 mb-6">
                        <div class="w-8 h-8 rounded-lg bg-brand-600 flex items-center justify-center">
                            <i class="fas fa-store text-white text-sm"></i>
                        </div>
                        <span class="font-heading text-xl font-bold text-white tracking-tight">Almacén<span class="text-brand-500">Europa</span></span>
                    </div>
                    <p class="text-gray-400 text-sm max-w-sm leading-relaxed mb-6">
                        Solución tecnológica integral para la gestión comercial moderna. Innovación y precisión en cada transacción para impulsar su crecimiento.
                    </p>
                    <div class="flex space-x-4">
                        <a href="#" class="w-10 h-10 rounded-full bg-gray-900 border border-gray-800 flex items-center justify-center text-gray-400 hover:text-white hover:border-brand-500 hover:bg-brand-900/30 transition-all">
                            <i class="fab fa-facebook-f"></i>
                        </a>
                        <a href="#" class="w-10 h-10 rounded-full bg-gray-900 border border-gray-800 flex items-center justify-center text-gray-400 hover:text-white hover:border-brand-500 hover:bg-brand-900/30 transition-all">
                            <i class="fab fa-instagram"></i>
                        </a>
                        <a href="#" class="w-10 h-10 rounded-full bg-gray-900 border border-gray-800 flex items-center justify-center text-gray-400 hover:text-white hover:border-brand-500 hover:bg-brand-900/30 transition-all">
                            <i class="fab fa-whatsapp"></i>
                        </a>
                    </div>
                </div>

                <!-- Links -->
                <div>
                    <h4 class="text-white font-semibold mb-6">Sistema</h4>
                    <ul class="space-y-3">
                        <li><a href="../views/usuarios/login.php" class="text-sm text-gray-400 hover:text-brand-400 transition-colors">Iniciar Sesión</a></li>
                        <li><a href="#funcionalidades" class="text-sm text-gray-400 hover:text-brand-400 transition-colors">Características</a></li>
                        <li><a href="#" class="text-sm text-gray-400 hover:text-brand-400 transition-colors">Soporte Técnico</a></li>
                    </ul>
                </div>

                <!-- Contact -->
                <div>
                    <h4 class="text-white font-semibold mb-6">Contacto</h4>
                    <ul class="space-y-4">
                        <li class="flex items-start gap-3">
                            <i class="fas fa-map-marker-alt text-brand-500 mt-1"></i>
                            <span class="text-sm text-gray-400">Parque Principal Fundadores<br>Sede Principal</span>
                        </li>
                        <li class="flex items-center gap-3">
                            <i class="fas fa-envelope text-brand-500"></i>
                            <span class="text-sm text-gray-400">admin@almaceneuropa.com</span>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Copyright -->
            <div class="pt-8 border-t border-gray-800/50 flex flex-col md:flex-row justify-between items-center gap-4">
                <p class="text-sm text-gray-500">
                    &copy; 2026 Almacén Europa. Todos los derechos reservados.
                </p>
                <div class="flex items-center gap-2 text-sm text-gray-500">
                    <span>Desarrollado con</span> <i class="fas fa-heart text-red-500/70"></i> <span>para gestión inteligente.</span>
                </div>
            </div>
        </div>
    </footer>

    <!-- Initialize AOS Animation -->
    <script>
        AOS.init({
            once: true,
            offset: 50,
            duration: 800,
            easing: 'ease-out-cubic',
        });

        // Navbar blur effect on scroll
        window.addEventListener('scroll', () => {
            const nav = document.getElementById('navbar');
            if (window.scrollY > 20) {
                nav.classList.add('shadow-sm');
                nav.classList.replace('glass-nav', 'bg-white/90');
            } else {
                nav.classList.remove('shadow-sm');
                nav.classList.replace('bg-white/90', 'glass-nav');
            }
        });
    </script>
</body>

</html>