# Módulo de Catálogo y Categorías

Este módulo proporciona una vista unificada para la gestión de la estructura del catálogo, permitiendo administrar tanto los productos como las categorías que los agrupan.

*Archivos involucrados:*

- **Controlador:** `controllers/CatalogoController.php`
- **Modelos:** `models/Producto.php`, `models/Categoria.php`
- **Vista Principal:** `views/Catalogo/index.php`

---

## 1. Gestión de Categorías

Las categorías permiten organizar el inventario y son fundamentales para el filtrado en el frontend y reportes.

- **Atributos:** Nombre y descripción.
- **Validación de Eliminación:** El sistema impide eliminar una categoría si existen productos asociados a ella (integridad referencial lógica), solicitando al usuario que reasigne los productos primero.

---

## 2. Diferencia con el Módulo de Productos

Mientras que el módulo de **Productos** se enfoca en el inventario y stock, el módulo de **Catálogo** se enfoca en la presentación y organización:
- Permite crear categorías rápidamente.
- Ofrece una interfaz de gestión simplificada para el catálogo general.

---

## 3. Roles y Responsabilidades

- **Administrador / Bodeguero / Vendedor:** Todos los roles operativos tienen acceso a la visualización y gestión del catálogo para asegurar que la información comercial esté siempre actualizada.

---

## 4. Lógica Técnica

- El `CatalogoController` actúa como un orquestador que utiliza múltiples modelos (`Producto` y `Categoria`) para centralizar la administración de la estructura comercial.
- Utiliza redirecciones con alertas de sesión (`Swal.fire`) para confirmar cada cambio estructural.
