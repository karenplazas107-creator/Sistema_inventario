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
                    <a href="#promociones" class="text-sm font-medium text-gray-600 hover:text-brand-600 transition-colors">Promociones</a>
                    <a href="#carrito" class="text-sm font-medium text-gray-600 hover:text-brand-600 transition-colors">Carrito</a>
                    
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

    <!-- SECCIÓN PROMOCIONES -->
    <section id="promociones" class="py-24 bg-gray-50 relative">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-3xl mx-auto mb-10" data-aos="fade-up">
                <span class="text-brand-600 font-semibold tracking-wider uppercase text-sm">Ofertas Especiales</span>
                <h2 class="font-heading text-3xl md:text-5xl font-bold text-gray-900 mt-2 mb-4">Promociones del Mes</h2>
                <p class="text-gray-600 text-lg">Los mejores precios en aseo, ropa, herramientas y abarrotes. ¡Solo en Almacén Europa!</p>
            </div>

            <!-- Pills filtro -->
            <div class="flex flex-wrap justify-center gap-3 mb-10" data-aos="fade-up">
                <button onclick="filtrarPromo('todos',this)" class="promo-pill active-pill px-5 py-2 rounded-full text-sm font-semibold border border-brand-900 transition-all">Todos</button>
                <button onclick="filtrarPromo('aseo',this)" class="promo-pill px-5 py-2 rounded-full text-sm font-semibold bg-white text-gray-600 border border-gray-200 hover:border-brand-400 transition-all">🧹 Aseo</button>
                <button onclick="filtrarPromo('ropa',this)" class="promo-pill px-5 py-2 rounded-full text-sm font-semibold bg-white text-gray-600 border border-gray-200 hover:border-brand-400 transition-all">👕 Ropa</button>
                <button onclick="filtrarPromo('herramientas',this)" class="promo-pill px-5 py-2 rounded-full text-sm font-semibold bg-white text-gray-600 border border-gray-200 hover:border-brand-400 transition-all">🔧 Herramientas</button>
                <button onclick="filtrarPromo('abarrotes',this)" class="promo-pill px-5 py-2 rounded-full text-sm font-semibold bg-white text-gray-600 border border-gray-200 hover:border-brand-400 transition-all">🛒 Abarrotes</button>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-5" id="promoGrid">

                <div class="promo-card bg-white rounded-2xl overflow-hidden shadow-sm border border-gray-100 bento-hover group" data-cat="aseo" data-aos="fade-up" data-aos-delay="50">
                    <div class="relative h-44 overflow-hidden"><img src="../img/promociones/detergente.jpg" onerror="this.src='https://images.unsplash.com/photo-1585421514738-01798e348b17?auto=format&fit=crop&w=400&q=80'" alt="Detergente" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500"><span class="absolute top-2 left-2 bg-red-500 text-white text-xs font-bold px-2.5 py-1 rounded-full">🔥 -20%</span></div>
                    <div class="p-4"><div class="text-xs text-brand-600 font-semibold mb-1">Aseo del Hogar</div><div class="font-bold text-gray-800 text-sm mb-2">Detergente en Polvo 1kg</div><div class="flex items-center justify-between"><div><span class="text-xs text-gray-400 line-through">$8.500</span><span class="text-base font-extrabold text-brand-900 ml-1">$6.800</span></div><a href="../views/usuarios/login.php" class="w-8 h-8 rounded-xl bg-brand-900 text-white flex items-center justify-center hover:bg-brand-700 transition text-xs"><i class="fas fa-cart-plus"></i></a></div></div>
                </div>

                <div class="promo-card bg-white rounded-2xl overflow-hidden shadow-sm border border-gray-100 bento-hover group" data-cat="aseo" data-aos="fade-up" data-aos-delay="100">
                    <div class="relative h-44 overflow-hidden"><img src="../img/promociones/jabon.jpg" onerror="this.src='https://images.unsplash.com/photo-1556909114-f6e7ad7d3136?auto=format&fit=crop&w=400&q=80'" alt="Jabón" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500"><span class="absolute top-2 left-2 bg-green-500 text-white text-xs font-bold px-2.5 py-1 rounded-full">✅ NUEVO</span></div>
                    <div class="p-4"><div class="text-xs text-brand-600 font-semibold mb-1">Aseo Personal</div><div class="font-bold text-gray-800 text-sm mb-2">Jabón de Baño x3 und</div><div class="flex items-center justify-between"><span class="text-base font-extrabold text-brand-900">$4.200</span><a href="../views/usuarios/login.php" class="w-8 h-8 rounded-xl bg-brand-900 text-white flex items-center justify-center hover:bg-brand-700 transition text-xs"><i class="fas fa-cart-plus"></i></a></div></div>
                </div>

                <div class="promo-card bg-white rounded-2xl overflow-hidden shadow-sm border border-gray-100 bento-hover group" data-cat="aseo" data-aos="fade-up" data-aos-delay="150">
                    <div class="relative h-44 overflow-hidden"><img src="../img/promociones/escoba.jpg" onerror="this.src='https://images.unsplash.com/photo-1563453392212-326f5e854473?auto=format&fit=crop&w=400&q=80'" alt="Escoba" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500"><span class="absolute top-2 left-2 bg-yellow-400 text-gray-900 text-xs font-bold px-2.5 py-1 rounded-full">⭐ OFERTA</span></div>
                    <div class="p-4"><div class="text-xs text-brand-600 font-semibold mb-1">Aseo del Hogar</div><div class="font-bold text-gray-800 text-sm mb-2">Escoba + Recogedor</div><div class="flex items-center justify-between"><div><span class="text-xs text-gray-400 line-through">$18.000</span><span class="text-base font-extrabold text-brand-900 ml-1">$14.500</span></div><a href="../views/usuarios/login.php" class="w-8 h-8 rounded-xl bg-brand-900 text-white flex items-center justify-center hover:bg-brand-700 transition text-xs"><i class="fas fa-cart-plus"></i></a></div></div>
                </div>

                <div class="promo-card bg-white rounded-2xl overflow-hidden shadow-sm border border-gray-100 bento-hover group" data-cat="ropa" data-aos="fade-up" data-aos-delay="200">
                    <div class="relative h-44 overflow-hidden"><img src="../img/promociones/camiseta.jpg" onerror="this.src='https://images.unsplash.com/photo-1521572163474-6864f9cf17ab?auto=format&fit=crop&w=400&q=80'" alt="Camiseta" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500"><span class="absolute top-2 left-2 bg-red-500 text-white text-xs font-bold px-2.5 py-1 rounded-full">🔥 -30%</span></div>
                    <div class="p-4"><div class="text-xs text-brand-600 font-semibold mb-1">Ropa</div><div class="font-bold text-gray-800 text-sm mb-2">Camiseta Algodón Unisex</div><div class="flex items-center justify-between"><div><span class="text-xs text-gray-400 line-through">$25.000</span><span class="text-base font-extrabold text-brand-900 ml-1">$17.500</span></div><a href="../views/usuarios/login.php" class="w-8 h-8 rounded-xl bg-brand-900 text-white flex items-center justify-center hover:bg-brand-700 transition text-xs"><i class="fas fa-cart-plus"></i></a></div></div>
                </div>

                <div class="promo-card bg-white rounded-2xl overflow-hidden shadow-sm border border-gray-100 bento-hover group" data-cat="ropa" data-aos="fade-up" data-aos-delay="250">
                    <div class="relative h-44 overflow-hidden"><img src="../img/promociones/pantalon.jpg" onerror="this.src='https://images.unsplash.com/photo-1542272604-787c3835535d?auto=format&fit=crop&w=400&q=80'" alt="Pantalón" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500"><span class="absolute top-2 left-2 bg-green-500 text-white text-xs font-bold px-2.5 py-1 rounded-full">✅ NUEVO</span></div>
                    <div class="p-4"><div class="text-xs text-brand-600 font-semibold mb-1">Ropa</div><div class="font-bold text-gray-800 text-sm mb-2">Pantalón Jean Clásico</div><div class="flex items-center justify-between"><span class="text-base font-extrabold text-brand-900">$45.000</span><a href="../views/usuarios/login.php" class="w-8 h-8 rounded-xl bg-brand-900 text-white flex items-center justify-center hover:bg-brand-700 transition text-xs"><i class="fas fa-cart-plus"></i></a></div></div>
                </div>

                <div class="promo-card bg-white rounded-2xl overflow-hidden shadow-sm border border-gray-100 bento-hover group" data-cat="ropa" data-aos="fade-up" data-aos-delay="300">
                    <div class="relative h-44 overflow-hidden"><img src="../img/promociones/medias.jpg" onerror="this.src='https://images.unsplash.com/photo-1586350977771-b3b0abd50c82?auto=format&fit=crop&w=400&q=80'" alt="Medias" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500"><span class="absolute top-2 left-2 bg-yellow-400 text-gray-900 text-xs font-bold px-2.5 py-1 rounded-full">⭐ 3x2</span></div>
                    <div class="p-4"><div class="text-xs text-brand-600 font-semibold mb-1">Ropa</div><div class="font-bold text-gray-800 text-sm mb-2">Medias Deportivas x6</div><div class="flex items-center justify-between"><span class="text-base font-extrabold text-brand-900">$12.000</span><a href="../views/usuarios/login.php" class="w-8 h-8 rounded-xl bg-brand-900 text-white flex items-center justify-center hover:bg-brand-700 transition text-xs"><i class="fas fa-cart-plus"></i></a></div></div>
                </div>

                <div class="promo-card bg-white rounded-2xl overflow-hidden shadow-sm border border-gray-100 bento-hover group" data-cat="herramientas" data-aos="fade-up" data-aos-delay="350">
                    <div class="relative h-44 overflow-hidden"><img src="../img/promociones/martillo.jpg" onerror="this.src='https://images.unsplash.com/photo-1504148455328-c376907d081c?auto=format&fit=crop&w=400&q=80'" alt="Martillo" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500"><span class="absolute top-2 left-2 bg-red-500 text-white text-xs font-bold px-2.5 py-1 rounded-full">🔥 -15%</span></div>
                    <div class="p-4"><div class="text-xs text-brand-600 font-semibold mb-1">Herramientas</div><div class="font-bold text-gray-800 text-sm mb-2">Martillo de Carpintero</div><div class="flex items-center justify-between"><div><span class="text-xs text-gray-400 line-through">$22.000</span><span class="text-base font-extrabold text-brand-900 ml-1">$18.700</span></div><a href="../views/usuarios/login.php" class="w-8 h-8 rounded-xl bg-brand-900 text-white flex items-center justify-center hover:bg-brand-700 transition text-xs"><i class="fas fa-cart-plus"></i></a></div></div>
                </div>

                <div class="promo-card bg-white rounded-2xl overflow-hidden shadow-sm border border-gray-100 bento-hover group" data-cat="herramientas" data-aos="fade-up" data-aos-delay="400">
                    <div class="relative h-44 overflow-hidden"><img src="../img/promociones/destornilladores.jpg" onerror="this.src='https://images.unsplash.com/photo-1572981779307-38b8cabb2407?auto=format&fit=crop&w=400&q=80'" alt="Destornilladores" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500"><span class="absolute top-2 left-2 bg-green-500 text-white text-xs font-bold px-2.5 py-1 rounded-full">✅ KIT</span></div>
                    <div class="p-4"><div class="text-xs text-brand-600 font-semibold mb-1">Herramientas</div><div class="font-bold text-gray-800 text-sm mb-2">Kit Destornilladores x8</div><div class="flex items-center justify-between"><span class="text-base font-extrabold text-brand-900">$35.000</span><a href="../views/usuarios/login.php" class="w-8 h-8 rounded-xl bg-brand-900 text-white flex items-center justify-center hover:bg-brand-700 transition text-xs"><i class="fas fa-cart-plus"></i></a></div></div>
                </div>

                <div class="promo-card bg-white rounded-2xl overflow-hidden shadow-sm border border-gray-100 bento-hover group" data-cat="herramientas" data-aos="fade-up" data-aos-delay="450">
                    <div class="relative h-44 overflow-hidden"><img src="../img/promociones/cinta.jpg" onerror="this.src='https://images.unsplash.com/photo-1558618666-fcd25c85cd64?auto=format&fit=crop&w=400&q=80'" alt="Cinta métrica" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500"><span class="absolute top-2 left-2 bg-yellow-400 text-gray-900 text-xs font-bold px-2.5 py-1 rounded-full">⭐ OFERTA</span></div>
                    <div class="p-4"><div class="text-xs text-brand-600 font-semibold mb-1">Herramientas</div><div class="font-bold text-gray-800 text-sm mb-2">Cinta Métrica 5m</div><div class="flex items-center justify-between"><div><span class="text-xs text-gray-400 line-through">$12.000</span><span class="text-base font-extrabold text-brand-900 ml-1">$9.500</span></div><a href="../views/usuarios/login.php" class="w-8 h-8 rounded-xl bg-brand-900 text-white flex items-center justify-center hover:bg-brand-700 transition text-xs"><i class="fas fa-cart-plus"></i></a></div></div>
                </div>

                <div class="promo-card bg-white rounded-2xl overflow-hidden shadow-sm border border-gray-100 bento-hover group" data-cat="abarrotes" data-aos="fade-up" data-aos-delay="500">
                    <div class="relative h-44 overflow-hidden"><img src="./img/luxury.jpg" alt="Arroz" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500"><span class="absolute top-2 left-2 bg-red-500 text-white text-xs font-bold px-2.5 py-1 rounded-full">🔥 -10%</span></div>
                    <div class="p-4"><div class="text-xs text-brand-600 font-semibold mb-1">locion luxury </div><div class="font-bold text-gray-800 text-sm mb-2">locion luxury</div><div class="flex items-center justify-between"><div><span class="text-xs text-gray-400 line-through">$18.000</span><span class="text-base font-extrabold text-brand-900 ml-1">$16.200</span></div><a href="../views/usuarios/login.php" class="w-8 h-8 rounded-xl bg-brand-900 text-white flex items-center justify-center hover:bg-brand-700 transition text-xs"><i class="fas fa-cart-plus"></i></a></div></div>
                </div>

                <div class="promo-card bg-white rounded-2xl overflow-hidden shadow-sm border border-gray-100 bento-hover group" data-cat="abarrotes" data-aos="fade-up" data-aos-delay="550">
                    <div class="relative h-44 overflow-hidden"><img src="../img/promociones/aceite.jpg" onerror="this.src='https://images.unsplash.com/photo-1474979266404-7eaacbcd87c5?auto=format&fit=crop&w=400&q=80'" alt="Aceite" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500"><span class="absolute top-2 left-2 bg-green-500 text-white text-xs font-bold px-2.5 py-1 rounded-full">✅ NUEVO</span></div>
                    <div class="p-4"><div class="text-xs text-brand-600 font-semibold mb-1">Abarrotes</div><div class="font-bold text-gray-800 text-sm mb-2">Aceite Vegetal 1 Litro</div><div class="flex items-center justify-between"><span class="text-base font-extrabold text-brand-900">$9.800</span><a href="../views/usuarios/login.php" class="w-8 h-8 rounded-xl bg-brand-900 text-white flex items-center justify-center hover:bg-brand-700 transition text-xs"><i class="fas fa-cart-plus"></i></a></div></div>
                </div>

                <div class="promo-card bg-white rounded-2xl overflow-hidden shadow-sm border border-gray-100 bento-hover group" data-cat="abarrotes" data-aos="fade-up" data-aos-delay="600">
                    <div class="relative h-44 overflow-hidden"><img src="../img/promociones/azucar.jpg" onerror="this.src='https://images.unsplash.com/photo-1559181567-c3190ca9be46?auto=format&fit=crop&w=400&q=80'" alt="Azúcar" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500"><span class="absolute top-2 left-2 bg-yellow-400 text-gray-900 text-xs font-bold px-2.5 py-1 rounded-full">⭐ COMBO</span></div>
                    <div class="p-4"><div class="text-xs text-brand-600 font-semibold mb-1">Abarrotes</div><div class="font-bold text-gray-800 text-sm mb-2">Azúcar Blanca 2kg</div><div class="flex items-center justify-between"><div><span class="text-xs text-gray-400 line-through">$7.500</span><span class="text-base font-extrabold text-brand-900 ml-1">$6.500</span></div><a href="../views/usuarios/login.php" class="w-8 h-8 rounded-xl bg-brand-900 text-white flex items-center justify-center hover:bg-brand-700 transition text-xs"><i class="fas fa-cart-plus"></i></a></div></div>
                </div>

            </div>

            <div class="text-center mt-12" data-aos="fade-up">
                <a href="../views/usuarios/login.php" class="inline-flex items-center gap-2 px-8 py-4 rounded-xl bg-brand-900 text-white font-semibold hover:bg-brand-800 transition shadow-xl">
                    <i class="fas fa-tags"></i> Ver todas las promociones
                </a>
            </div>

            <style>
                .promo-pill { transition: all .2s; }
                .active-pill { background:#1e3a8a !important; color:#fff !important; border-color:#1e3a8a !important; }
            </style>
            <script>
                function filtrarPromo(cat, el) {
                    document.querySelectorAll('.promo-pill').forEach(p => p.classList.remove('active-pill'));
                    el.classList.add('active-pill');
                    document.querySelectorAll('.promo-card').forEach(card => {
                        card.style.display = (cat === 'todos' || card.dataset.cat === cat) ? '' : 'none';
                    });
                }
            </script>
        </div>
    </section>

    <!-- SECCIÓN CARRITO -->
    <section id="carrito" class="py-24 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-3xl mx-auto mb-16" data-aos="fade-up">
                <span class="text-brand-600 font-semibold tracking-wider uppercase text-sm">Compra Fácil</span>
                <h2 class="font-heading text-3xl md:text-5xl font-bold text-gray-900 mt-2 mb-4">Tu Carrito de Compras</h2>
                <p class="text-gray-600 text-lg">Agrega productos, revisa tu pedido y confirma tu compra en segundos. Así de simple.</p>
            </div>

            <div class="grid lg:grid-cols-2 gap-16 items-center">
                <!-- Pasos -->
                <div class="space-y-8" data-aos="fade-right">
                    <div class="flex items-start gap-5">
                        <div class="w-14 h-14 rounded-2xl bg-brand-900 text-white flex items-center justify-center text-xl font-bold flex-shrink-0 shadow-lg shadow-brand-900/30">1</div>
                        <div>
                            <h3 class="font-heading text-xl font-bold text-gray-900 mb-1">Explora el Catálogo</h3>
                            <p class="text-gray-500">Navega por todas las categorías y encuentra los productos que necesitas con fotos, precios y disponibilidad en tiempo real.</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-5">
                        <div class="w-14 h-14 rounded-2xl bg-accent text-white flex items-center justify-center text-xl font-bold flex-shrink-0 shadow-lg shadow-accent/30">2</div>
                        <div>
                            <h3 class="font-heading text-xl font-bold text-gray-900 mb-1">Agrega al Carrito</h3>
                            <p class="text-gray-500">Con un solo clic agrega productos a tu carrito. Ajusta cantidades, elimina items y ve el total actualizado en tiempo real.</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-5">
                        <div class="w-14 h-14 rounded-2xl bg-green-600 text-white flex items-center justify-center text-xl font-bold flex-shrink-0 shadow-lg shadow-green-600/30">3</div>
                        <div>
                            <h3 class="font-heading text-xl font-bold text-gray-900 mb-1">Confirma y Recibe tu Factura</h3>
                            <p class="text-gray-500">Confirma tu pedido y recibe al instante tu factura POS digital lista para imprimir. Historial completo de todas tus compras.</p>
                        </div>
                    </div>

                    <a href="../views/usuarios/login.php"
                       class="inline-flex items-center gap-3 px-8 py-4 rounded-xl bg-brand-900 text-white font-semibold hover:bg-brand-800 transition shadow-xl shadow-brand-900/20 mt-4">
                        <i class="fas fa-shopping-cart"></i> Ir a la Tienda
                        <i class="fas fa-arrow-right text-sm"></i>
                    </a>
                </div>

                <!-- Mockup carrito -->
                <div class="relative" data-aos="fade-left" data-aos-delay="200">
                    <div class="absolute inset-0 bg-gradient-to-tr from-brand-100 to-accent/20 rounded-3xl blur-3xl opacity-60"></div>
                    <div class="relative bg-white rounded-3xl shadow-2xl border border-gray-100 overflow-hidden">
                        <!-- Header mockup -->
                        <div class="bg-brand-950 px-6 py-4 flex items-center justify-between">
                            <span class="text-white font-bold flex items-center gap-2">
                                <i class="fas fa-shopping-cart"></i> Mi Carrito
                            </span>
                            <span class="w-6 h-6 rounded-full bg-red-500 text-white text-xs font-bold flex items-center justify-center">3</span>
                        </div>
                        <!-- Items mockup -->
                        <div class="p-5 space-y-3">
                            <?php
                            $mockItems = [
                                ['nombre'=>'Arroz Diana 500g',    'precio'=>'$3.500', 'qty'=>2, 'color'=>'bg-blue-100 text-blue-600'],
                                ['nombre'=>'Jabón Rey x3',        'precio'=>'$8.200', 'qty'=>1, 'color'=>'bg-green-100 text-green-600'],
                                ['nombre'=>'Aceite Girasol 1L',   'precio'=>'$12.000','qty'=>1, 'color'=>'bg-amber-100 text-amber-600'],
                            ];
                            foreach ($mockItems as $item): ?>
                            <div class="flex items-center gap-3 bg-gray-50 rounded-xl p-3 border border-gray-100">
                                <div class="w-10 h-10 rounded-xl <?= $item['color'] ?> flex items-center justify-center flex-shrink-0">
                                    <i class="fas fa-box text-sm"></i>
                                </div>
                                <div class="flex-1">
                                    <div class="font-semibold text-gray-800 text-sm"><?= $item['nombre'] ?></div>
                                    <div class="text-brand-700 font-bold text-xs"><?= $item['precio'] ?></div>
                                </div>
                                <div class="flex items-center gap-1.5 bg-white border border-gray-200 rounded-lg px-2 py-1">
                                    <span class="text-xs text-gray-500">×</span>
                                    <span class="text-sm font-bold text-gray-800"><?= $item['qty'] ?></span>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <!-- Total mockup -->
                        <div class="px-5 pb-5">
                            <div class="bg-brand-50 rounded-xl p-4 flex items-center justify-between border border-brand-100">
                                <span class="font-bold text-brand-900">Total</span>
                                <span class="text-2xl font-extrabold text-brand-900">$27.200</span>
                            </div>
                            <div class="mt-3 w-full py-3 rounded-xl bg-brand-900 text-white font-bold text-center text-sm">
                                <i class="fas fa-check-circle mr-2"></i> Confirmar Pedido
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