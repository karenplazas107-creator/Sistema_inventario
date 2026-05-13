# Módulo de Gestión de Ventas

Este módulo centraliza el registro de transacciones comerciales del sistema, permitiendo un control detallado de los ingresos y el historial de ventas por empleado.

*Archivos involucrados:*

- **Controlador:** `controllers/VentaController.php`
- **Modelo:** `models/Venta.php`
- **Vistas:**
    - Listado Histórico: `views/ventas/index.php`
    - Formulario de Registro: `views/ventas/crear.php`
    - Detalle de Venta: `views/ventas/ver.php`

---

## 1. Roles y Permisos

El acceso a las funciones de ventas está segmentado por el rol del usuario:

- **Administrador / Vendedor:** Tienen control total. Pueden listar, ver detalles, registrar nuevas ventas y **son los únicos con permiso para editar o eliminar** registros.
- **Bodeguero:** Solo tiene permiso para **registrar** ventas (útil si también despacha productos). No tiene acceso a la edición ni eliminación.

---

## 2. Proceso de Venta (Carrito de Compras Interno)

La creación de una venta (`crear.php`) utiliza una interfaz dinámica:

1. **Selección de Productos:** Los productos se añaden a una lista temporal (carrito) mediante JavaScript.
2. **Cálculo Automático:** El sistema calcula subtotales y el total general en tiempo real mientras se ajustan las cantidades.
3. **Validación:** No se permite procesar una venta con el carrito vacío (validación tanto en JS como en PHP).
4. **Persistencia:** Al guardar, se abre una **Transacción SQL** que asegura que se inserte la cabecera de la venta y todos sus detalles (productos, cantidades, precios en ese momento) de forma atómica.

---

## 3. Lógica del Modelo (Transaccionalidad)

El método `crear()` en `Venta.php` es crítico:
- Inicia una transacción (`beginTransaction`).
- Inserta en la tabla `ventas` y obtiene el `id` generado.
- Itera los productos e inserta en `detalle_venta` referenciando el `id` de la venta.
- Si todo es correcto, hace `commit`. Si algo falla, hace `rollBack` para evitar datos huérfanos.

---

## 4. Eliminación de Ventas

- **Advertencia:** La eliminación de una venta borra tanto la cabecera como sus detalles asociados.
- **Seguridad:** Solo usuarios autorizados pueden ejecutar esta acción. Se recomienda usarla solo para corrección de errores administrativos.

---

## 5. Reportes Integrados

El modelo `Venta.php` no solo gestiona el CRUD, sino que contiene toda la inteligencia de negocio para el módulo de reportes, incluyendo:
- Ventas por día y mes.
- Ranking de productos más vendidos.
- Desempeño por vendedor (ingresos generados).
- Resumen general (Ticket promedio, venta máxima, etc.).
