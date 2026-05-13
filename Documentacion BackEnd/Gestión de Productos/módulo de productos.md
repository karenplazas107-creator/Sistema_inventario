# Módulo de Gestión de Productos

Este módulo es el corazón del catálogo, permitiendo la definición técnica de los artículos, sus precios, categorías e imágenes.

*Archivos involucrados:*

- **Controlador:** `controllers/ProductoController.php`
- **Modelo:** `models/Producto.php`
- **Vista Principal:** `views/productos/index.php`
- **Carpeta de Imágenes:** `img/productos/`

---

## 1. Gestión de Información

El registro de un producto incluye:
- **Datos Básicos:** Nombre, descripción y código de barras.
- **Precios:** Diferenciación entre precio de compra (para cálculo de valor de inventario) y precio de venta.
- **Categorización:** Asociación obligatoria a una categoría para facilitar la navegación y reportes.
- **Imagen:** Soporte para carga de archivos multimedia (JPG, PNG, WEBP).

---

## 2. Manejo de Imágenes (Lógica del Controlador)

El `ProductoController` gestiona el ciclo de vida de las imágenes:
1. **Validación:** Verifica extensiones permitidas.
2. **Renombrado:** Asigna nombres únicos basados en `timestamp` para evitar colisiones y caché del navegador.
3. **Limpieza:** Al editar o eliminar un producto, el sistema borra automáticamente el archivo de imagen anterior del servidor para ahorrar espacio.

---

## 3. Integración con Inventario

Al crear un producto a través del método `crearConStock()`, el sistema no solo inserta el registro en la tabla `productos`, sino que inicializa su configuración en la tabla `inventario` (stock inicial y stock mínimo), asegurando que el producto sea rastreable desde el momento de su creación.

---
## 4. Roles y Responsabilidades

- **Administrador / Bodeguero:** Son los encargados de la gestión técnica de los productos (crear, editar, eliminar y ajustar stock).
- **Vendedor:** Puede consultar la lista de productos y sus detalles (precios/stock) para informar al cliente, pero no tiene permisos de modificación.
