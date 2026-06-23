
<?php
session_start();
if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['rol'] !== 'Comprador') {
    header("Location: ../usuarios/login.php");
    exit;
}
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/rutas.php';
require_once __DIR__ . '/../../models/Producto.php';
require_once __DIR__ . '/../../models/Categoria.php';
$database       = new Database();
$db             = $database->conectar();
$productoModel  = new Producto($db);
$categoriaModel = new Categoria($db);
$productos      = $productoModel->obtenerTodos();
$categorias     = $categoriaModel->obtenerTodas();
$nombre         = htmlspecialchars($_SESSION['usuario']['nombres']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Almacén Europa | Tienda</title>
<script src="https://cdn.tailwindcss.com"></script>
<script>tailwind.config={theme:{extend:{colors:{brand:{50:'#eff6ff',100:'#dbeafe',500:'#3b82f6',600:'#2563eb',800:'#1e40af',900:'#1e3a8a',950:'#172554'},accent:'#0ea5e9'},fontFamily:{sans:['Inter','sans-serif'],heading:['Outfit','sans-serif']}}}}</script>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&family=Outfit:wght@700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<style>
body{background:#f1f5f9;font-family:'Inter',sans-serif;}
.prod-card{transition:transform .2s,box-shadow .2s,border-color .2s;cursor:pointer;}
.prod-card:hover{transform:translateY(-4px);box-shadow:0 16px 40px rgba(30,58,138,.13);border-color:#2563eb;}
.cat-pill{cursor:pointer;transition:all .2s;}
.cat-pill.active{background:#1e3a8a;color:#fff;}
.carrito-panel{transition:transform .35s cubic-bezier(.4,0,.2,1);}
.carrito-panel.open{transform:translateX(0);}
.carrito-panel.closed{transform:translateX(100%);}
.overlay{transition:opacity .3s;}
.hero-gradient{background:linear-gradient(135deg,#172554 0%,#1e3a8a 55%,#0ea5e9 100%);}
.glass{background:rgba(255,255,255,.12);backdrop-filter:blur(12px);border:1px solid rgba(255,255,255,.2);}
::-webkit-scrollbar{width:6px;}
::-webkit-scrollbar-thumb{background:#cbd5e1;border-radius:10px;}
</style>
</head>
<body>

<!-- NAVBAR -->
<nav class="sticky top-0 z-40 bg-white/90 backdrop-blur-md border-b border-gray-200 shadow-sm">
  <div class="max-w-7xl mx-auto px-4 h-16 flex items-center justify-between">
    <div class="flex items-center gap-2.5">
      <div class="w-9 h-9 rounded-xl bg-brand-900 flex items-center justify-center"><i class="fas fa-store text-white text-sm"></i></div>
      <span class="font-heading text-xl font-bold text-gray-900">Almacén<span class="text-brand-600">Europa</span></span>
    </div>
    <div class="hidden md:flex flex-1 max-w-md mx-8">
      <div class="relative w-full">
        <i class="fas fa-search absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
        <input id="navBuscador" type="text" placeholder="Buscar productos..." class="w-full pl-10 pr-4 py-2 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500 bg-gray-50">
      </div>
    </div>
    <div class="flex items-center gap-3">
      <div class="hidden sm:flex items-center gap-2 text-sm text-gray-600">
        <div class="w-8 h-8 rounded-full bg-brand-100 text-brand-700 flex items-center justify-center font-bold text-xs"><?= strtoupper(substr($_SESSION['usuario']['nombres'],0,1)) ?></div>
        <span class="font-medium"><?= $nombre ?></span>
      </div>
      <button onclick="toggleCarrito()" class="relative w-10 h-10 rounded-xl bg-brand-900 text-white flex items-center justify-center hover:bg-brand-800 transition shadow-md">
        <i class="fas fa-shopping-cart text-sm"></i>
        <span id="carritoCount" class="absolute -top-1.5 -right-1.5 w-5 h-5 rounded-full bg-red-500 text-white text-xs font-bold flex items-center justify-center hidden">0</span>
      </button>
      <a href="../../controllers/AuthController.php?accion=logout" class="w-10 h-10 rounded-xl bg-gray-100 text-gray-500 flex items-center justify-center hover:bg-red-50 hover:text-red-500 transition" title="Cerrar sesión">
        <i class="fas fa-right-from-bracket text-sm"></i>
      </a>
    </div>
  </div>
</nav>

<!-- HERO -->
<section class="hero-gradient relative overflow-hidden">
  <div class="absolute top-0 left-0 w-72 h-72 bg-blue-600 rounded-full blur-3xl opacity-30 -translate-x-1/2 -translate-y-1/2"></div>
  <div class="absolute bottom-0 right-0 w-96 h-96 bg-cyan-400 rounded-full blur-3xl opacity-20 translate-x-1/3 translate-y-1/3"></div>
  <div class="max-w-7xl mx-auto px-4 py-14 relative z-10">
    <div class="flex flex-col lg:flex-row items-center gap-10">
      <div class="flex-1 text-white">
        <div class="inline-flex items-center gap-2 glass px-3 py-1.5 rounded-full text-xs font-semibold text-blue-100 mb-5">
          <span class="w-2 h-2 rounded-full bg-green-400 animate-pulse"></span> Tienda en línea — Almacén Europa
        </div>
        <h1 class="font-heading text-4xl lg:text-5xl font-extrabold leading-tight mb-4">
          Hola, <span class="text-cyan-300"><?= $nombre ?></span> 👋<br>¿Qué vas a llevar hoy?
        </h1>
        <p class="text-blue-100 text-lg font-light mb-7 max-w-lg">Explora nuestro catálogo completo. Encuentra los mejores productos al mejor precio.</p>
        <div class="flex flex-wrap gap-3">
          <a href="#catalogo" class="px-6 py-3 rounded-xl bg-white text-brand-900 font-semibold text-sm hover:bg-blue-50 transition shadow-lg flex items-center gap-2"><i class="fas fa-tags"></i> Ver Catálogo</a>
          <button onclick="toggleCarrito()" class="px-6 py-3 rounded-xl glass text-white font-semibold text-sm hover:bg-white/20 transition flex items-center gap-2"><i class="fas fa-shopping-cart"></i> Mi Carrito</button>
        </div>
      </div>
      <div class="hidden lg:flex gap-4 flex-shrink-0">
        <div class="glass rounded-2xl p-5 text-white text-center w-36"><i class="fas fa-box-open text-3xl text-cyan-300 mb-2 block"></i><div class="text-2xl font-bold"><?= count($productos) ?></div><div class="text-xs text-blue-200 mt-0.5">Productos</div></div>
        <div class="glass rounded-2xl p-5 text-white text-center w-36 mt-6"><i class="fas fa-tags text-3xl text-green-300 mb-2 block"></i><div class="text-2xl font-bold"><?= count($categorias) ?></div><div class="text-xs text-blue-200 mt-0.5">Categorías</div></div>
        <div class="glass rounded-2xl p-5 text-white text-center w-36"><i class="fas fa-truck text-3xl text-yellow-300 mb-2 block"></i><div class="text-lg font-bold">Rápido</div><div class="text-xs text-blue-200 mt-0.5">Despacho</div></div>
      </div>
    </div>
  </div>
</section>

<!-- CATÁLOGO -->
<main id="catalogo" class="max-w-7xl mx-auto px-4 py-10">
  <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
    <div>
      <h2 class="text-2xl font-bold text-gray-800 font-heading">Nuestros Productos</h2>
      <p class="text-sm text-gray-500 mt-0.5"><?= count($productos) ?> productos disponibles</p>
    </div>
  </div>

  <!-- Pills categorías -->
  <div class="flex flex-wrap gap-2 mb-7">
    <span class="cat-pill active px-4 py-1.5 rounded-full text-sm font-semibold bg-brand-900 text-white" data-cat="" onclick="filtrarCat(this)"><i class="fas fa-th-large mr-1"></i> Todos</span>
    <?php foreach ($categorias as $cat): ?>
    <span class="cat-pill px-4 py-1.5 rounded-full text-sm font-semibold bg-white text-gray-600 border border-gray-200 shadow-sm" data-cat="<?= $cat['id'] ?>" onclick="filtrarCat(this)"><?= htmlspecialchars($cat['nombre']) ?></span>
    <?php endforeach; ?>
  </div>

  <!-- Grid -->
  <div id="gridProductos" class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-5">
  <?php foreach ($productos as $p):
    $imgSrc = !empty($p['imagen']) ? IMG_PRODUCTOS . htmlspecialchars($p['imagen']) : 'https://placehold.co/400x320/e2e8f0/94a3b8?text=Sin+Imagen';
    $disponible = $p['stock'] > 0;
  ?>
  <div class="prod-card bg-white rounded-2xl border-2 border-transparent overflow-hidden shadow-sm"
       data-id="<?= $p['id'] ?>" data-nombre="<?= strtolower(htmlspecialchars($p['nombre'])) ?>" data-categoria="<?= $p['categoria_id'] ?>"
       onclick="agregarAlCarrito(<?= $p['id'] ?>, '<?= addslashes(htmlspecialchars($p['nombre'])) ?>', <?= $p['precio_venta'] ?>, '<?= $imgSrc ?>')">
    <div class="relative overflow-hidden bg-gray-50" style="height:160px;">
      <img src="<?= $imgSrc ?>" alt="" class="w-full h-full object-cover transition-transform duration-500 hover:scale-110" onerror="this.src='https://placehold.co/400x320/e2e8f0/94a3b8?text=?'">
      <span class="absolute top-2 left-2 text-xs font-semibold px-2 py-0.5 rounded-full bg-white/90 text-gray-700 shadow-sm"><?= htmlspecialchars($p['categoria_nombre'] ?? '') ?></span>
      <?php if (!$disponible): ?>
      <div class="absolute inset-0 bg-white/70 flex items-center justify-center"><span class="text-xs font-bold text-red-500 bg-red-50 px-3 py-1 rounded-full border border-red-200">Agotado</span></div>
      <?php endif; ?>
    </div>
    <div class="p-3">
      <div class="font-semibold text-gray-800 text-sm leading-tight line-clamp-2 mb-2 min-h-[2.5rem]"><?= htmlspecialchars($p['nombre']) ?></div>
      <div class="flex items-center justify-between">
        <div>
          <div class="text-lg font-bold text-brand-900">$<?= number_format($p['precio_venta'],2,',','.') ?></div>
          <div class="text-xs text-gray-400"><?= $disponible ? $p['stock'].' disponibles' : 'Sin stock' ?></div>
        </div>
        <?php if ($disponible): ?>
        <div class="w-9 h-9 rounded-xl bg-brand-50 text-brand-700 flex items-center justify-center hover:bg-brand-900 hover:text-white transition shadow-sm"><i class="fas fa-cart-plus text-sm"></i></div>
        <?php endif; ?>
      </div>
    </div>
  </div>
  <?php endforeach; ?>
  </div>
  <div id="sinResultados" class="hidden text-center py-16 text-gray-400">
    <i class="fas fa-search text-5xl mb-4 block opacity-20"></i>
    <p class="text-lg font-medium">No se encontraron productos.</p>
  </div>
</main>

<!-- OVERLAY -->
<div id="overlay" class="overlay fixed inset-0 bg-black/40 z-40 hidden" onclick="toggleCarrito()"></div>

<!-- PANEL CARRITO -->
<div id="carritoPanel" class="carrito-panel closed fixed top-0 right-0 h-full w-full sm:w-96 bg-white z-50 shadow-2xl flex flex-col">
  <div class="flex items-center justify-between px-6 py-5 border-b border-gray-100 bg-brand-950 text-white">
    <h3 class="font-heading text-lg font-bold flex items-center gap-2"><i class="fas fa-shopping-cart"></i> Mi Carrito</h3>
    <button onclick="toggleCarrito()" class="w-8 h-8 rounded-full bg-white/10 flex items-center justify-center hover:bg-white/20 transition"><i class="fas fa-times text-sm"></i></button>
  </div>
  <div id="carritoItems" class="flex-1 overflow-y-auto p-5 space-y-3">
    <div id="carritoVacio" class="flex flex-col items-center justify-center h-full text-gray-400 py-12">
      <i class="fas fa-shopping-basket text-5xl mb-4 opacity-20"></i>
      <p class="font-medium">Tu carrito está vacío</p>
      <p class="text-sm mt-1">Agrega productos para continuar</p>
    </div>
  </div>
  <div id="carritoFooter" class="hidden border-t border-gray-100 p-5 bg-gray-50">
    <div class="flex justify-between text-sm text-gray-600 mb-1.5"><span>Subtotal</span><span id="resSubtotal">$0,00</span></div>
    <div class="flex justify-between text-xl font-bold text-gray-900 mb-5"><span>Total</span><span id="resTotal" class="text-brand-900">$0,00</span></div>
    <button onclick="confirmarPedido()" class="w-full py-3.5 rounded-xl bg-brand-900 text-white font-bold text-base hover:bg-brand-800 transition shadow-lg flex items-center justify-center gap-2"><i class="fas fa-check-circle"></i> Confirmar Pedido</button>
    <button onclick="limpiarCarrito()" class="w-full mt-2 py-2.5 rounded-xl border border-gray-200 text-gray-500 text-sm font-medium hover:bg-gray-100 transition"><i class="fas fa-trash mr-1.5"></i> Vaciar carrito</button>
  </div>
</div>

<script>
let carrito = {};
let carritoAbierto = false;

function toggleCarrito() {
    carritoAbierto = !carritoAbierto;
    const panel = document.getElementById('carritoPanel');
    const overlay = document.getElementById('overlay');
    if (carritoAbierto) { panel.classList.replace('closed','open'); overlay.classList.remove('hidden'); document.body.style.overflow='hidden'; }
    else { panel.classList.replace('open','closed'); overlay.classList.add('hidden'); document.body.style.overflow=''; }
}

function agregarAlCarrito(id, nombre, precio, img) {
    if (carrito[id]) { carrito[id].cantidad++; }
    else { carrito[id] = {id, nombre, precio:parseFloat(precio), cantidad:1, img}; }
    renderCarrito();
    Swal.fire({icon:'success',title:'¡Agregado!',text:nombre+' fue añadido al carrito.',timer:1200,timerProgressBar:true,showConfirmButton:false,toast:true,position:'bottom-end',background:'#1e3a8a',color:'#fff',iconColor:'#34d399'});
}

function cambiarCantidad(id, delta) {
    if (!carrito[id]) return;
    carrito[id].cantidad += delta;
    if (carrito[id].cantidad <= 0) delete carrito[id];
    renderCarrito();
}

function limpiarCarrito() { carrito = {}; renderCarrito(); }

function renderCarrito() {
    const items = Object.values(carrito);
    const container = document.getElementById('carritoItems');
    const footer = document.getElementById('carritoFooter');
    const vacio = document.getElementById('carritoVacio');
    const countEl = document.getElementById('carritoCount');
    const totalItems = items.reduce((s,i) => s+i.cantidad, 0);
    countEl.textContent = totalItems;
    totalItems > 0 ? countEl.classList.remove('hidden') : countEl.classList.add('hidden');
    if (items.length === 0) { container.innerHTML=''; container.appendChild(vacio); vacio.style.display='flex'; footer.classList.add('hidden'); return; }
    vacio.style.display = 'none';
    footer.classList.remove('hidden');
    let html = '', total = 0;
    items.forEach(function(item) {
        const linea = item.precio * item.cantidad;
        total += linea;
        html += `<div class="flex items-center gap-3 bg-gray-50 rounded-xl p-3 border border-gray-100">
            <img src="${item.img}" alt="" class="w-14 h-14 rounded-xl object-cover flex-shrink-0 border border-gray-200" onerror="this.src='https://placehold.co/56x56/e2e8f0/94a3b8?text=?'">
            <div class="flex-1 min-w-0">
                <div class="font-semibold text-gray-800 text-xs leading-tight line-clamp-2">${item.nombre}</div>
                <div class="text-brand-700 font-bold text-sm mt-0.5">$${linea.toLocaleString('es-CO',{minimumFractionDigits:2})}</div>
                <div class="flex items-center gap-1.5 mt-1.5">
                    <button onclick="cambiarCantidad(${item.id},-1)" class="w-6 h-6 rounded-lg bg-white border border-gray-200 text-gray-600 hover:bg-red-50 hover:text-red-500 flex items-center justify-center transition text-xs"><i class="fas fa-minus"></i></button>
                    <span class="w-6 text-center text-sm font-bold text-gray-800">${item.cantidad}</span>
                    <button onclick="cambiarCantidad(${item.id},1)" class="w-6 h-6 rounded-lg bg-white border border-gray-200 text-gray-600 hover:bg-green-50 hover:text-green-600 flex items-center justify-center transition text-xs"><i class="fas fa-plus"></i></button>
                </div>
            </div>
        </div>`;
    });
    container.innerHTML = html;
    const fmt = total.toLocaleString('es-CO',{minimumFractionDigits:2});
    document.getElementById('resSubtotal').textContent = '$'+fmt;
    document.getElementById('resTotal').textContent = '$'+fmt;
}

function confirmarPedido() {
    const items = Object.values(carrito);
    if (items.length === 0) return;
    const total = items.reduce((s,i) => s+i.precio*i.cantidad, 0);
    Swal.fire({
        title:'¿Confirmar pedido?',
        html:`<div class="text-gray-600 text-sm"><strong>${items.length} producto(s)</strong><br>Total: <strong class="text-brand-900 text-lg">$${total.toLocaleString('es-CO',{minimumFractionDigits:2})}</strong></div>`,
        icon:'question',showCancelButton:true,confirmButtonColor:'#1e3a8a',cancelButtonColor:'#6b7280',confirmButtonText:'<i class="fas fa-check mr-1"></i> Confirmar',cancelButtonText:'Cancelar'
    }).then(function(result) {
        if (result.isConfirmed) {
            const form = document.createElement('form');
            form.method='POST'; form.action='../../controllers/VentaController.php?accion=crear';
            items.forEach(function(item) {
                ['productos[]','precios[]','cantidades[]'].forEach(function(name,i) {
                    const inp = document.createElement('input');
                    inp.type='hidden'; inp.name=name; inp.value=[item.id,item.precio,item.cantidad][i];
                    form.appendChild(inp);
                });
            });
            document.body.appendChild(form); form.submit();
        }
    });
}

let catActiva = '';
function filtrarCat(el) {
    document.querySelectorAll('.cat-pill').forEach(p => p.classList.remove('active'));
    el.classList.add('active'); catActiva = el.dataset.cat; filtrar();
}
function filtrar() {
    const q = (document.getElementById('navBuscador').value || '').toLowerCase().trim();
    const cards = document.querySelectorAll('.prod-card');
    let visible = 0;
    cards.forEach(function(card) {
        const show = (!q || card.dataset.nombre.includes(q)) && (!catActiva || card.dataset.categoria === catActiva);
        card.style.display = show ? '' : 'none';
        if (show) visible++;
    });
    document.getElementById('sinResultados').classList.toggle('hidden', visible > 0);
}
document.getElementById('navBuscador').addEventListener('input', filtrar);
</script>
</body>
</html>
