# Módulo de Gestión de Clientes

En este sistema, los clientes se consideran usuarios con el rol específico de **Comprador**. Este módulo permite visualizar y gestionar el directorio de personas que han interactuado con la plataforma.

*Archivos involucrados:*

- **Controlador Administrativo:** `controllers/AdminUsuarioController.php`
- **Modelo:** `models/Usuario.php`
- **Vista Principal:** `views/clientes/index.php`

---

## 1. Listado y Filtrado

La interfaz de clientes ofrece una experiencia de búsqueda optimizada:
- **Filtro Automático:** El sistema solo lista usuarios cuyo `rol` sea exactamente 'Comprador'.
- **Buscador en Tiempo Real:** Implementado con JavaScript nativo para buscar por nombre, apellido, email o móvil sin necesidad de recargar la página ni realizar peticiones adicionales al servidor.

---

## 2. Gestión de Registros

- **Registro:** Los clientes suelen registrarse de forma autónoma a través de la pantalla de registro público (`views/usuarios/registre.php`).
- **Eliminación:** Los administradores pueden eliminar cuentas de clientes desde esta vista. La acción es procesada por el `AdminUsuarioController` para mantener la lógica de usuarios centralizada.

---

## 3. Lógica del Modelo

El modelo `Usuario.php` cuenta con el método específico `obtenerClientes()`, el cual ejecuta una consulta SQL filtrada:
```sql
SELECT * FROM usuarios WHERE rol = 'Comprador' ORDER BY nombres ASC
```
Esto asegura que la lista de clientes sea independiente de la lista de empleados (vendedores/bodegueros).
