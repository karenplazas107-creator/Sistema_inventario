<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header("Location: ../usuarios/login.php");
    exit;
}

$rol = $_SESSION['usuario']['rol'];
if (!in_array($rol, ['Administrador', 'Vendedor', 'Bodeguero'])) {
    header("Location: ../dashboard/index.php");
    exit;
}

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../models/Producto.php';
require_once __DIR__ . '/../../models/Categoria.php';

$database = new Database();
$db       = $database->conectar();
$productoModel  = new Producto($db);
$categoriaModel = new Categoria($db);

$productos  = $productoModel->obtenerTodos();
$categorias = $categoriaModel->obtenerTodas();

$titulo = "Nueva Venta";
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/sidebar.php';
?>

<?php if (isset($_SESSION['alert'])): ?>
<script>
document.addEventListener('DOMContentLoaded', function () {
    Swal.fire({
        icon:  '<?= $_SESSION['alert']['icon'] ?>',
        title: '<?= addslashes($_SESSION['alert']['title']) ?>',
        text:  '<?= addslashes($_SESSION['alert']['text']) ?>',
        confirmButtonColor: '#1e3a8a'
    });
});
</script>
<?php unset($_SESSION['alert']); endif; ?>

<style>
    .prod-card {
        transition: all .2s ease;
        cursor: pointer;
        border: 2px solid transparent;
    }
    .prod-card:hover {
        border-color: #2563eb;
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(37,99,235,.12);
    }
    .prod-card.agotado {
        opacity: .7;
        border-color: #fca5a5;
    }
    .carrito-item { animation: slideIn .2s ease; }
    @keyframes slideIn {
        from { opacity:0; transform:translateY(-6px); }
        to   { opacity:1; transform:translateY(0); }
    }
    .cat-pill { cursor:pointer; transition:all .2s; }
    .cat-pill.active { background:#1e3a8a; color:#fff; }
</style>

<!-- ── Encabezado ─────────────────────────────────────────────────────────── -->
<div class="mb-5 flex items-center gap-4">
    <a href="index.php" class="w-9 h-9 rounded-xl bg-gray-100 flex items-center justify-center text-gray-500 hover:bg-gray-200 transition flex-shrink-0">
        <i class="fas fa-arrow-left text-sm"></i>
    </a>
    <div>
        <h2 class="text-2xl font-bold text-gray-800">Nueva Venta</h2>
        <p class="text-sm text-gray-500">Selecciona productos y procesa la venta.</p>
    </div>
</div>

<div class="flex gap-6" style="height: calc(100vh - 220px); min-height: 500px;">

    <!-- ── Panel izquierdo: Productos ──────────────────────────────────────── -->
    <div class="flex-1 flex flex-col card overflow-hidden">

        <!-- Buscador + filtro categoría -->
        <div class="p-4 border-b border-gray-100 space-y-3">
            <div class="relative">
                <i class="fas fa-search absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
                <input id="buscarProducto" type="text" placeholder="Buscar producto por nombre o código..."
                       class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500 transition">
            </div>
            <!-- Pills categorías -->
            <div class="flex flex-wrap gap-2">
                <span class="cat-pill active px-3 py-1 rounded-full text-xs font-semibold bg-brand-900 text-white"
                      data-cat="" onclick="filtrarCategoria(this)">Todos</span>
                <?php foreach ($categorias as $cat): ?>
                <span class="cat-pill px-3 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-600"
                      data-cat="<?= $cat['id'] ?>" onclick="filtrarCategoria(this)">
                    <?= htmlspecialchars($cat['nombre']) ?>
                </span>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Grid de productos -->
        <div class="flex-1 overflow-y-auto p-4">
            <div id="gridProductos" class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-4 gap-3">
            <?php foreach ($productos as $p):
                $agotado = $p['stock'] <= 0;
                $imgSrc  = !empty($p['imagen'])
                    ? '../../img/productos/' . htmlspecialchars($p['imagen'])
                    : 'https://placehold.co/200x160/e2e8f0/94a3b8?text=?';
            ?>
            <div class="prod-card bg-white rounded-xl p-3 border border-gray-100 shadow-sm <?= $agotado ? 'agotado' : '' ?>"
                 data-id="<?= $p['id'] ?>"
                 data-nombre="<?= strtolower(htmlspecialchars($p['nombre'])) ?>"
                 data-categoria="<?= $p['categoria_id'] ?>"
                 data-precio="<?= $p['precio_venta'] ?>"
                 data-stock="<?= $p['stock'] ?>"
                 onclick="agregarAlCarrito(<?= $p['id'] ?>, '<?= addslashes(htmlspecialchars($p['nombre'])) ?>', <?= $p['precio_venta'] ?>, <?= $p['stock'] ?>)">

                <!-- Imagen -->
                <div class="relative mb-2 rounded-lg overflow-hidden bg-gray-50" style="height:90px;">
                    <img src="<?= $imgSrc ?>" alt=""
                         class="w-full h-full object-cover"
                         onerror="this.src='https://placehold.co/200x160/e2e8f0/94a3b8?text=?'">
                    <?php if ($agotado): ?>
                    <div class="absolute inset-0 bg-white/70 flex items-center justify-center">
                        <span class="text-xs font-bold text-red-500 bg-red-50 px-2 py-0.5 rounded-full border border-red-200">Agotado</span>
                    </div>
                    <?php else: ?>
                    <span class="absolute top-1.5 right-1.5 text-xs bg-white/90 text-gray-600 px-1.5 py-0.5 rounded-md font-medium shadow-sm">
                        <?= $p['stock'] ?> uds
                    </span>
                    <?php endif; ?>
                </div>

                <!-- Info -->
                <div class="text-xs text-brand-600 font-semibold mb-0.5 truncate">
                    <?= htmlspecialchars($p['categoria_nombre'] ?? '') ?>
                </div>
                <div class="font-semibold text-gray-800 text-sm leading-tight mb-1 line-clamp-2 prod-nombre">
                    <?= htmlspecialchars($p['nombre']) ?>
                </div>
                <div class="flex items-center justify-between mt-auto">
                    <span class="font-bold text-gray-900">$<?= number_format($p['precio_venta'], 2, ',', '.') ?></span>
                    <div class="w-7 h-7 rounded-full bg-brand-50 text-brand-600 flex items-center justify-center hover:bg-brand-600 hover:text-white transition">
                        <i class="fas fa-plus text-xs"></i>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
            </div>

            <!-- Sin resultados -->
            <div id="sinResultados" class="hidden text-center py-12 text-gray-400">
                <i class="fas fa-box-open text-4xl mb-3 block opacity-20"></i>
                <p class="text-sm">No se encontraron productos.</p>
            </div>
        </div>
    </div>

    <!-- ── Panel derecho: Carrito ───────────────────────────────────────────── -->
    <div class="flex flex-col card overflow-hidden" style="width:320px;min-width:300px;">

        <!-- Header carrito -->
        <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
            <h3 class="font-bold text-gray-800 flex items-center gap-2">
                <i class="fas fa-shopping-cart text-brand-600"></i>
                Carrito
            </h3>
            <span id="carritoCount" class="w-6 h-6 rounded-full bg-brand-900 text-white text-xs font-bold flex items-center justify-center">0</span>
        </div>

        <!-- Items del carrito -->
        <div id="carritoItems" class="flex-1 overflow-y-auto p-4 space-y-2">
            <div id="carritoVacio" class="flex flex-col items-center justify-center h-full text-gray-400 py-8">
                <i class="fas fa-shopping-basket text-4xl mb-3 opacity-20"></i>
                <p class="text-sm">El carrito está vacío</p>
                <p class="text-xs mt-1">Haz clic en un producto para agregarlo</p>
            </div>
        </div>

        <!-- Totales + form -->
        <form id="formVenta" method="POST" action="../../controllers/VentaController.php?accion=crear">
            <div id="inputsOcultos"></div>

            <div class="p-5 border-t border-gray-100 bg-gray-50">
                <div class="flex justify-between text-sm text-gray-600 mb-1.5">
                    <span>Subtotal</span>
                    <span id="resSubtotal">$0,00</span>
                </div>
                <div class="flex justify-between text-xl font-bold text-gray-900 mb-5">
                    <span>Total</span>
                    <span id="resTotal" class="text-brand-900">$0,00</span>
                </div>

                <button type="button" onclick="confirmarVenta()"
                        class="w-full py-3.5 rounded-xl bg-brand-900 text-white font-bold text-base hover:bg-brand-800 transition shadow-lg flex items-center justify-center gap-2">
                    <i class="fas fa-check-circle"></i> Procesar Venta
                </button>
                <button type="button" onclick="limpiarCarrito()"
                        class="w-full mt-2 py-2.5 rounded-xl border border-gray-200 text-gray-500 text-sm font-medium hover:bg-gray-100 transition">
                    <i class="fas fa-trash mr-1.5"></i> Vaciar carrito
                </button>
            </div>
        </form>
    </div>

</div>

<script>
// ── Estado del carrito ─────────────────────────────────────────────────────
let carrito = {}; // { id: { id, nombre, precio, cantidad, stock } }

// ── Filtros ────────────────────────────────────────────────────────────────
let catActiva = '';

document.getElementById('buscarProducto').addEventListener('input', filtrar);

function filtrarCategoria(el) {
    document.querySelectorAll('.cat-pill').forEach(p => p.classList.remove('active'));
    el.classList.add('active');
    catActiva = el.dataset.cat;
    filtrar();
}

function filtrar() {
    const q    = document.getElementById('buscarProducto').value.toLowerCase().trim();
    const cards = document.querySelectorAll('.prod-card');
    let visible = 0;

    cards.forEach(function (card) {
        const nombre = card.dataset.nombre || '';
        const cat    = card.dataset.categoria || '';
        const matchQ   = !q        || nombre.includes(q);
        const matchCat = !catActiva || cat === catActiva;
        const show = matchQ && matchCat;
        card.style.display = show ? '' : 'none';
        if (show) visible++;
    });

    document.getElementById('sinResultados').classList.toggle('hidden', visible > 0);
}

// ── Agregar al carrito ─────────────────────────────────────────────────────
function agregarAlCarrito(id, nombre, precio, stock) {
    if (carrito[id]) {
        carrito[id].cantidad++;
    } else {
        carrito[id] = { id, nombre, precio: parseFloat(precio), cantidad: 1, stock: parseInt(stock) };
    }
    renderCarrito();
}

// ── Cambiar cantidad ───────────────────────────────────────────────────────
function cambiarCantidad(id, delta) {
    if (!carrito[id]) return;
    const nueva = carrito[id].cantidad + delta;
    if (nueva <= 0) {
        delete carrito[id];
    } else {
        carrito[id].cantidad = nueva;
    }
    renderCarrito();
}

function eliminarItem(id) {
    delete carrito[id];
    renderCarrito();
}

function limpiarCarrito() {
    carrito = {};
    renderCarrito();
}

// ── Renderizar carrito ─────────────────────────────────────────────────────
function renderCarrito() {
    const items    = Object.values(carrito);
    const container = document.getElementById('carritoItems');
    const vacio    = document.getElementById('carritoVacio');
    const count    = document.getElementById('carritoCount');
    const inputs   = document.getElementById('inputsOcultos');

    count.textContent = items.reduce((s, i) => s + i.cantidad, 0);

    if (items.length === 0) {
        container.innerHTML = '';
        container.appendChild(vacio);
        vacio.style.display = 'flex';
        actualizarTotales(0);
        inputs.innerHTML = '';
        return;
    }

    vacio.style.display = 'none';

    let html = '';
    let total = 0;
    let inputsHtml = '';

    items.forEach(function (item) {
        const linea = item.precio * item.cantidad;
        total += linea;
        html += `
        <div class="carrito-item bg-white rounded-xl border border-gray-100 p-3 shadow-sm">
            <div class="flex items-start justify-between gap-2 mb-2">
                <div class="font-semibold text-gray-800 text-xs leading-tight flex-1">${item.nombre}</div>
                <button type="button" onclick="eliminarItem(${item.id})"
                        class="w-5 h-5 rounded-full bg-red-50 text-red-400 hover:bg-red-100 flex items-center justify-center flex-shrink-0 transition">
                    <i class="fas fa-times text-xs"></i>
                </button>
            </div>
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-1.5 bg-gray-50 rounded-lg p-1 border border-gray-100">
                    <button type="button" onclick="cambiarCantidad(${item.id}, -1)"
                            class="w-6 h-6 rounded bg-white border border-gray-200 text-gray-600 hover:bg-gray-100 flex items-center justify-center transition">
                        <i class="fas fa-minus text-xs"></i>
                    </button>
                    <span class="w-7 text-center text-sm font-bold text-gray-800">${item.cantidad}</span>
                    <button type="button" onclick="cambiarCantidad(${item.id}, 1)"
                            class="w-6 h-6 rounded bg-white border border-gray-200 text-gray-600 hover:bg-gray-100 flex items-center justify-center transition">
                        <i class="fas fa-plus text-xs"></i>
                    </button>
                </div>
                <span class="font-bold text-gray-800 text-sm">
                    $${linea.toLocaleString('es-CO', {minimumFractionDigits:2})}
                </span>
            </div>
        </div>`;

        inputsHtml += `
            <input type="hidden" name="productos[]"  value="${item.id}">
            <input type="hidden" name="precios[]"    value="${item.precio}">
            <input type="hidden" name="cantidades[]" value="${item.cantidad}">`;
    });

    container.innerHTML = html;
    inputs.innerHTML = inputsHtml;
    actualizarTotales(total);
}

function actualizarTotales(total) {
    const fmt = total.toLocaleString('es-CO', { minimumFractionDigits: 2 });
    document.getElementById('resSubtotal').textContent = '$' + fmt;
    document.getElementById('resTotal').textContent    = '$' + fmt;
}

// ── Confirmar y procesar ───────────────────────────────────────────────────
function confirmarVenta() {
    const items = Object.values(carrito);
    if (items.length === 0) {
        Swal.fire({ icon:'warning', title:'Carrito vacío', text:'Agrega al menos un producto para continuar.', confirmButtonColor:'#1e3a8a' });
        return;
    }

    const total = items.reduce((s, i) => s + i.precio * i.cantidad, 0);

    Swal.fire({
        title: '¿Procesar venta?',
        html: `<div class="text-gray-600 text-sm">
                   <strong>${items.length} producto(s)</strong> — Total:
                   <strong class="text-brand-900 text-lg">$${total.toLocaleString('es-CO',{minimumFractionDigits:2})}</strong>
               </div>`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#1e3a8a',
        cancelButtonColor: '#6b7280',
        confirmButtonText: '<i class="fas fa-check mr-1"></i> Confirmar',
        cancelButtonText: 'Cancelar'
    }).then(function (result) {
        if (result.isConfirmed) {
            document.getElementById('formVenta').submit();
        }
    });
}
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
