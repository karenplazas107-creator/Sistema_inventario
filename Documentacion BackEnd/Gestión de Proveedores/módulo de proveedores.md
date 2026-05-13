# Módulo de Gestión de Proveedores

Permite administrar el directorio de empresas o personas que suministran los productos al inventario.

*Archivos involucrados:*

- **Controlador:** `controllers/ProveedorController.php`
- **Modelo:** `models/Proveedor.php`
- **Vista Principal:** `views/proveedores/index.php`

---

## 1. Información del Proveedor

Cada registro de proveedor contiene:
- Nombre o Razón Social.
- Teléfono de contacto.
- Correo electrónico (validado para evitar duplicados).
- Dirección física.

---

## 2. Validaciones de Seguridad

- **Emails Únicos:** El controlador verifica contra el modelo si el correo electrónico ya existe para otro proveedor antes de crear o editar un registro.
- **Integridad Referencial:** El sistema impide eliminar proveedores que tengan compras o registros asociados en la base de datos (si la lógica de negocio así lo requiere en el futuro), devolviendo una alerta de error controlada.

---

## 3. Roles y Responsabilidades

- **Administrador / Proveedor:** Estos son los perfiles encargados de mantener actualizada la base de datos de suministros y contactos comerciales.
