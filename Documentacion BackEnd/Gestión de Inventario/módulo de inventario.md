# Módulo de Gestión de Inventario

Este módulo se encarga del control físico de las existencias de productos, permitiendo ajustes manuales, control de stock mínimo y alertas visuales.

*Archivos involucrados:*

- **Controlador:** `controllers/InventarioController.php`
- **Modelo:** `models/Producto.php` (reutiliza lógica de productos)
- **Vista Principal:** `views/inventario/index.php`

---

## 1. Funcionalidades Principales

### A. Ajuste de Stock
Permite corregir las cantidades físicas sin necesidad de realizar una venta o compra formal:
- **Entrada:** Suma unidades al stock actual (ej. devoluciones, hallazgos).
- **Salida:** Resta unidades (ej. mermas, productos dañados).
- **Corrección:** Sobrescribe el valor actual con uno nuevo (ej. tras un inventario físico).

### B. Control de Stock Mínimo
Cada producto tiene un umbral de seguridad:
- Se puede definir desde la vista de inventario.
- Los productos cuyo stock sea igual o menor al mínimo se resaltan visualmente para alertar la necesidad de reabastecimiento.

---

## 2. Roles y Responsabilidades

- **Administrador / Bodeguero:** Tienen la responsabilidad exclusiva de realizar ajustes, auditorías físicas y modificar stocks mínimos de seguridad.
- **Vendedor:** Puede consultar existencias para asesorar ventas, pero no tiene permisos para alterar las cantidades.

---

## 3. Lógica Técnica

- El controlador `InventarioController.php` utiliza el método `actualizarStock()` del modelo de productos para persistir los cambios.
- Los ajustes manuales deben incluir un **motivo** para mantener la trazabilidad (aunque el sistema actual prioriza la rapidez del ajuste).

---

## 4. Alertas Visuales

En la interfaz de inventario se aplican clases CSS dinámicas:
- **Rojo/Peligro:** Stock en 0 (Agotado).
- **Naranja/Advertencia:** Stock por debajo del mínimo (Reabastecer).
- **Verde/Éxito:** Stock saludable.
