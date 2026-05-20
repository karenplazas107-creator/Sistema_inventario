# Gestión de Ventas

El módulo de ventas es el núcleo comercial del sistema **Almacén Europa**. Permite procesar transacciones de forma rápida, segura y profesional, integrando el control de inventario en tiempo real.

---

## 1. Roles y Permisos

| Acción | Descripción | Rol Autorizado |
| :--- | :--- | :--- |
| **Registrar Venta** | Registro de nuevos pedidos a través de la interfaz POS premium. | Administrador, Vendedor, Bodeguero |
| **Calcular Cambio** | Cálculo automático del dinero a devolver al cliente en tiempo real. | Administrador, Vendedor, Bodeguero |
| **Método de Pago** | Selección entre Efectivo, Tarjeta o Transferencia. | Administrador, Vendedor, Bodeguero |
| **Ver Historial** | Listado completo de ventas realizadas. | Administrador, Vendedor |
| **Eliminar Venta** | Cancelación de un registro de venta (requiere permisos). | Administrador, Vendedor |

---

## 2. Funcionalidades del POS Premium

El nuevo módulo de ventas incluye:
- **Interfaz Dividida**: Panel de productos con imágenes y filtros rápidos, y panel de carrito tipo factura para una mejor visualización.
- **Validación de Stock**: El sistema impide agregar productos sin existencias en tiempo real, evitando errores de facturación.
- **Persistencia de Datos**: Se registra automáticamente el empleado (cajero) que realiza la operación y el método de pago utilizado.
- **Control de Inventario**: Al confirmar la venta, el stock se descuenta automáticamente de la tabla `inventario` para mantener los datos actualizados.

---

## 3. Flujo de Operación

1.  **Búsqueda**: El cajero busca el producto por nombre o código.
2.  **Selección**: Se añaden los productos al carrito con un clic.
3.  **Pago**: Se selecciona el método de pago y se ingresa el monto recibido (si es efectivo).
4.  **Confirmación**: Se valida la transacción y se guarda en la base de datos, actualizando el stock.
