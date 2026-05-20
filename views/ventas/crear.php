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
require_once __DIR__ . '/../../config/rutas.php';

$database = new Database();
$db       = $database->conectar();
$productoModel  = new Producto($db);
$categoriaModel = new Categoria($db);

$productos  = $productoModel->obtenerTodos();
$categorias = $categoriaModel->obtenerTodas();

$titulo = "Módulo de Venta - POS";
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/sidebar.php';
?>

<style>
    /* Estilos Premium para el POS */
    :root {
        --pos-header-bg: #1e3a8a;
        --pos-accent: #22c55e;
        --pos-bg: #f8fafc;
    }

    .pos-container {
        display: grid;
        grid-template-columns: 1fr 400px;
        gap: 1.5rem;
        height: calc(100vh - 120px);
        min-height: 650px;
    }

    /* Panel Izquierdo: Productos */
    .products-panel {
        display: flex;
        flex-direction: column;
        background: white;
        border-radius: 1.5rem;
        box-shadow: 0 10px 25px rgba(0,0,0,0.03);
        overflow: hidden;
        border: 1px solid #e2e8f0;
    }

    .pos-header {
        background: var(--pos-header-bg);
        color: white;
        padding: 1.25rem 1.5rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .product-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
        gap: 1rem;
        padding: 1.5rem;
        overflow-y: auto;
        flex-grow: 1;
        background: var(--pos-bg);
    }

    .pos-card {
        background: white;
        border-radius: 1rem;
        padding: 0.75rem;
        text-align: center;
        transition: all 0.2s;
        cursor: pointer;
        border: 1px solid #f1f5f9;
        display: flex;
        flex-direction: column;
        position: relative;
    }

    .pos-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 12px 20px rgba(30,58,138,0.08);
        border-color: #3b82f6;
    }

    .pos-card img {
        width: 100%;
        height: 100px;
        object-fit: cover;
        border-radius: 0.75rem;
        margin-bottom: 0.75rem;
        background: #f8fafc;
    }

    .pos-card .price-tag {
        font-weight: 800;
        color: #16a34a;
        font-size: 1.1rem;
    }

    .pos-card .stock-badge {
        position: absolute;
        top: 0.5rem;
        right: 0.5rem;
        background: rgba(255,255,255,0.9);
        padding: 0.2rem 0.5rem;
        border-radius: 0.5rem;
        font-size: 0.7rem;
        font-weight: 700;
        color: #64748b;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    }

    /* Panel Derecho: Carrito */
    .cart-panel {
        display: flex;
        flex-direction: column;
        background: white;
        border-radius: 1.5rem;
        box-shadow: 0 10px 25px rgba(0,0,0,0.03);
        border: 1px solid #e2e8f0;
        overflow: hidden;
    }

    .cart-table-wrapper {
        flex-grow: 1;
        overflow-y: auto;
    }

    .cart-table {
        width: 100%;
        font-size: 0.85rem;
    }

    .cart-table th {
        background: #f8fafc;
        padding: 0.75rem;
        text-align: left;
        color: #64748b;
        font-weight: 600;
        border-bottom: 1px solid #e2e8f0;
    }

    .cart-table td {
        padding: 0.75rem;
        border-bottom: 1px dotted #f1f5f9;
    }

    .payment-section {
        background: #f8fafc;
        padding: 1.5rem;
        border-top: 2px solid #e2e8f0;
    }

    .total-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1rem;
    }

    .total-amount {
        font-size: 1.75rem;
        font-weight: 900;
        color: #1e3a8a;
    }

    .btn-confirm {
        background: #22c55e;
        color: white;
        width: 100%;
        padding: 1rem;
        border-radius: 1rem;
        font-weight: 700;
        font-size: 1.1rem;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.75rem;
        transition: all 0.2s;
        box-shadow: 0 4px 12px rgba(34,197,94,0.3);
    }

    .btn-confirm:hover {
        background: #16a34a;
        transform: scale(1.02);
    }

    .payment-input {
        background: white;
        border: 1px solid #cbd5e1;
        border-radius: 0.75rem;
        padding: 0.5rem 0.75rem;
        width: 100px;
        text-align: right;
        font-weight: 600;
    }

    .change-display {
        font-size: 0.9rem;
        font-weight: 700;
        color: #ef4444;
    }

    .change-display.positive {
        color: #22c55e;
    }

    /* Animaciones */
    @keyframes addToCart {
        0% { transform: scale(1); }
        50% { transform: scale(0.95); }
        100% { transform: scale(1); }
    }
    .anim-add { animation: addToCart 0.2s ease-out; }
</style>

<div class="pos-container">
    
    <!-- 🛒 PANEL DE PRODUCTOS -->
    <div class="products-panel">
        <div class="pos-header">
            <div class="flex items-center gap-3">
                <i class="fas fa-cash-register text-2xl"></i>
                <h2 class="text-xl font-bold uppercase tracking-wider">Módulo de Venta</h2>
            </div>
            <div class="flex items-center gap-6 text-sm opacity-90">
                <span><i class="fas fa-user-circle mr-2"></i>Cajero: <strong><?= htmlspecialchars($_SESSION['usuario']['nombres']) ?></strong></span>
                <span><i class="fas fa-calendar-alt mr-2"></i><?= date('d/m/Y') ?></span>
                <i class="fas fa-cog cursor-pointer hover:rotate-90 transition-transform"></i>
            </div>
        </div>

        <!-- Filtros -->
        <div class="p-4 bg-white border-b border-gray-100 flex gap-4 items-center">
            <div class="relative flex-1">
                <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-gray-400"></i>
                <input type="text" id="posSearch" placeholder="Buscar producto (nombre o código)..."
                       class="w-full pl-11 pr-4 py-3 bg-gray-50 rounded-2xl border-none focus:ring-2 focus:ring-blue-500 transition-all text-sm">
            </div>
            <div class="flex gap-2 overflow-x-auto pb-1">
                <button onclick="filterPos('')" class="cat-pill active px-4 py-2 rounded-xl text-xs font-bold bg-blue-100 text-blue-700 whitespace-nowrap">TODOS</button>
                <?php foreach ($categorias as $cat): ?>
                <button onclick="filterPos('<?= $cat['id'] ?>')" class="cat-pill px-4 py-2 rounded-xl text-xs font-bold bg-gray-100 text-gray-600 hover:bg-gray-200 transition whitespace-nowrap">
                    <?= mb_strtoupper($cat['nombre']) ?>
                </button>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Grid -->
        <div class="product-grid" id="posGrid">
            <?php foreach ($productos as $p):
                $img = !empty($p['imagen']) ? '../../img/productos/'.$p['imagen'] : 'https://placehold.co/200x200/e2e8f0/94a3b8?text=?';
            ?>
            <div class="pos-card" 
                 data-id="<?= $p['id'] ?>"
                 data-nombre="<?= strtolower(htmlspecialchars($p['nombre'])) ?>"
                 data-cat="<?= $p['categoria_id'] ?>"
                 onclick="addToCart(<?= $p['id'] ?>, '<?= addslashes($p['nombre']) ?>', <?= $p['precio_venta'] ?>, <?= $p['stock'] ?>)">
                <span class="stock-badge"><?= $p['stock'] ?> uds</span>
                <img src="<?= $img ?>" alt="">
                <p class="text-xs font-bold text-gray-800 line-clamp-2 mb-2 leading-tight"><?= htmlspecialchars($p['nombre']) ?></p>
                <p class="price-tag mt-auto">$<?= number_format($p['precio_venta'], 0, ',', '.') ?></p>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Botones de acción inferior -->
        <div class="p-4 border-t border-gray-100 flex justify-center gap-4">
            <button onclick="clearCart()" class="flex items-center gap-2 px-6 py-2 rounded-xl border border-gray-200 text-gray-500 hover:bg-gray-50 transition text-sm font-bold">
                <i class="fas fa-eraser"></i> LIMPIAR VENTA
            </button>
            <button class="flex items-center gap-2 px-6 py-2 rounded-xl bg-blue-50 text-blue-700 hover:bg-blue-100 transition text-sm font-bold">
                <i class="fas fa-file-invoice-dollar"></i> GUARDAR COTIZACIÓN
            </button>
        </div>
    </div>

    <!-- 🧾 PANEL DE CARRITO -->
    <div class="cart-panel">
        <div class="p-5 border-b border-gray-100">
            <h3 class="font-black text-gray-800 flex items-center gap-3">
                <i class="fas fa-shopping-basket text-blue-600"></i>
                DETALLE DE LA VENTA
            </h3>
        </div>

        <div class="cart-table-wrapper">
            <table class="cart-table">
                <thead>
                    <tr>
                        <th>Producto</th>
                        <th class="text-center">Cant.</th>
                        <th class="text-right">Precio</th>
                        <th class="text-right">Total</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody id="cartBody">
                    <!-- Dinámico -->
                </tbody>
            </table>
            
            <div id="cartEmpty" class="flex flex-col items-center justify-center py-20 text-gray-300">
                <i class="fas fa-cart-plus text-6xl mb-4 opacity-20"></i>
                <p class="font-bold">Carrito vacío</p>
            </div>
        </div>

        <!-- Sección de Pago -->
        <div class="payment-section">
            <div class="space-y-3 mb-6">
                <div class="flex justify-between text-sm text-gray-500 font-bold">
                    <span>SUBTOTAL</span>
                    <span id="posSubtotal">$0</span>
                </div>
                <div class="flex justify-between text-sm text-gray-500 font-bold">
                    <span>DESCUENTO</span>
                    <span class="text-blue-500">-$0</span>
                </div>
                <div class="total-row">
                    <span class="font-black text-gray-800">TOTAL</span>
                    <span id="posTotal" class="total-amount text-green-600">$0</span>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-4 mb-6">
                <div class="flex items-center justify-between">
                    <label class="text-xs font-black text-gray-500">FORMA DE PAGO</label>
                    <select id="posMetodo" class="bg-white border border-gray-200 rounded-xl px-3 py-2 text-sm font-bold focus:ring-0">
                        <option value="Efectivo">💵 EFECTIVO</option>
                        <option value="Tarjeta">💳 TARJETA</option>
                        <option value="Transferencia">📱 TRANSFERENCIA</option>
                    </select>
                </div>
                <div class="flex items-center justify-between">
                    <label class="text-xs font-black text-gray-500">RECIBIDO</label>
                    <input type="number" id="posRecibido" oninput="calcChange()" class="payment-input" value="0">
                </div>
                <div class="flex items-center justify-between">
                    <label class="text-xs font-black text-gray-500">CAMBIO</label>
                    <span id="posCambio" class="change-display">$0</span>
                </div>
            </div>

            <form id="formVenta" method="POST" action="../../controllers/VentaController.php?accion=crear">
                <div id="posHiddenInputs"></div>
                <input type="hidden" name="metodo_pago" id="hiddenMetodo">
                
                <button type="button" onclick="submitVenta()" class="btn-confirm">
                    <i class="fas fa-check-circle"></i> CONFIRMAR VENTA
                </button>
            </form>
        </div>
    </div>
</div>

<script>
    let cart = {};

    function addToCart(id, nombre, precio, stock) {
        if (cart[id]) {
            if (cart[id].qty >= stock) {
                Swal.fire({ icon: 'warning', title: 'Stock insuficiente', text: 'No hay más existencias de este producto.', timer: 2000, showConfirmButton: false });
                return;
            }
            cart[id].qty++;
        } else {
            cart[id] = { id, nombre, precio, qty: 1 };
        }
        
        // Animación visual
        const card = document.querySelector(`.pos-card[data-id="${id}"]`);
        card.classList.add('anim-add');
        setTimeout(() => card.classList.remove('anim-add'), 200);

        renderCart();
    }

    function removeOne(id) {
        if (cart[id].qty > 1) {
            cart[id].qty--;
        } else {
            delete cart[id];
        }
        renderCart();
    }

    function clearCart() {
        if (Object.keys(cart).length === 0) return;
        Swal.fire({
            title: '¿Vaciar carrito?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            confirmButtonText: 'Sí, vaciar'
        }).then((res) => { if(res.isConfirmed) { cart = {}; renderCart(); } });
    }

    function renderCart() {
        const body = document.getElementById('cartBody');
        const empty = document.getElementById('cartEmpty');
        const items = Object.values(cart);
        
        body.innerHTML = '';
        let total = 0;
        let inputs = '';

        if (items.length === 0) {
            empty.classList.remove('hidden');
        } else {
            empty.classList.add('hidden');
            items.forEach(item => {
                const sub = item.precio * item.qty;
                total += sub;
                body.innerHTML += `
                    <tr class="hover:bg-blue-50 transition-colors">
                        <td class="font-bold text-gray-700 py-4">${item.nombre}</td>
                        <td class="text-center font-black">${item.qty}</td>
                        <td class="text-right text-gray-500 font-bold">$${item.precio.toLocaleString()}</td>
                        <td class="text-right font-black text-blue-900">$${sub.toLocaleString()}</td>
                        <td class="text-center">
                            <button onclick="removeOne(${item.id})" class="text-red-300 hover:text-red-500 transition-colors">
                                <i class="fas fa-minus-circle"></i>
                            </button>
                        </td>
                    </tr>
                `;
                inputs += `
                    <input type="hidden" name="productos[]" value="${item.id}">
                    <input type="hidden" name="cantidades[]" value="${item.qty}">
                    <input type="hidden" name="precios[]" value="${item.precio}">
                `;
            });
        }

        document.getElementById('posSubtotal').textContent = '$' + total.toLocaleString();
        document.getElementById('posTotal').textContent = '$' + total.toLocaleString();
        document.getElementById('posHiddenInputs').innerHTML = inputs;
        calcChange();
    }

    function calcChange() {
        const total = Object.values(cart).reduce((acc, i) => acc + (i.precio * i.qty), 0);
        const recibo = parseFloat(document.getElementById('posRecibido').value) || 0;
        const cambio = recibo - total;
        const display = document.getElementById('posCambio');
        
        display.textContent = '$' + (cambio < 0 ? 0 : cambio).toLocaleString();
        display.classList.toggle('positive', cambio >= 0);
    }

    function submitVenta() {
        if (Object.keys(cart).length === 0) {
            Swal.fire({ icon: 'error', title: 'Ups...', text: 'El carrito está vacío' });
            return;
        }

        const total = Object.values(cart).reduce((acc, i) => acc + (i.precio * i.qty), 0);
        const recibo = parseFloat(document.getElementById('posRecibido').value) || 0;
        const metodo = document.getElementById('posMetodo').value;

        if (metodo === 'Efectivo' && recibo < total) {
            Swal.fire({ icon: 'warning', title: 'Pago insuficiente', text: 'El monto recibido es menor al total.' });
            return;
        }

        document.getElementById('hiddenMetodo').value = metodo;

        Swal.fire({
            title: '¿Confirmar venta?',
            html: `Total a cobrar: <b class="text-green-600">$${total.toLocaleString()}</b>`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#22c55e',
            confirmButtonText: 'Sí, finalizar'
        }).then(res => {
            if (res.isConfirmed) document.getElementById('formVenta').submit();
        });
    }

    // Filtros visuales
    document.getElementById('posSearch').addEventListener('input', e => {
        const q = e.target.value.toLowerCase();
        document.querySelectorAll('.pos-card').forEach(card => {
            card.style.display = card.dataset.nombre.includes(q) ? 'flex' : 'none';
        });
    });

    function filterPos(catId) {
        document.querySelectorAll('.cat-pill').forEach(btn => btn.classList.remove('active', 'bg-blue-100', 'text-blue-700'));
        event.target.classList.add('active', 'bg-blue-100', 'text-blue-700');
        
        document.querySelectorAll('.pos-card').forEach(card => {
            card.style.display = (!catId || card.dataset.cat == catId) ? 'flex' : 'none';
        });
    }
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
