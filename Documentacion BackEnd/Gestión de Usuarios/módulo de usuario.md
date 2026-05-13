# Módulo de Gestión de Usuarios

Este módulo permite a los administradores del sistema gestionar las cuentas de usuario (vendedores, bodegueros y otros administradores), controlando sus accesos y perfiles dentro de la plataforma.

*Archivos involucrados:*

- **Vista Principal (Dashboard Admin):** `views/dashboard/admin.php`
- **Controlador Administrativo:** `controllers/AdminUsuarioController.php`
- **Controlador de Registro:** `controllers/UsuarioController.php`
- **Modelo de Datos:** `models/Usuario.php`

---

## 0. Responsabilidades por Rol

- **Administrador:** Tiene control total del sistema. Es el único responsable de registrar y gestionar a los **Vendedores** y **Bodegueros**.
- **Comprador (Cliente):** Es el único rol que puede **registrarse de forma autónoma** a través del formulario público.
- **Vendedor / Bodeguero:** Son registrados exclusivamente por el Administrador desde el panel interno.

---

## 1. Interfaz de Gestión (views/dashboard/admin.php)

### ¿Qué es y para qué sirve?

Es el centro de control para la administración de personal. Presenta una tabla dinámica que lista a todos los usuarios del sistema, excluyendo a los clientes (compradores), para mantener la interfaz enfocada en el equipo de trabajo.

### Componentes de la interfaz:

- **Tabla de Usuarios:** Muestra nombre completo, correo electrónico, rol asignado y botones de acción rápida.
- **Badges de Rol:** Etiquetas visuales de colores para identificar rápidamente el cargo (Azul para Administrador, Ámbar para Vendedor, Índigo para Bodeguero).
- **Botón "Agregar Usuario":** Despliega un modal con el formulario de registro administrativo.
- **Acciones Rápidas:** Iconos de edición (lápiz) y eliminación (papelera) por cada registro.

---

## 2. Creación de Usuarios (Lógica Administrativa)

Aunque el sistema permite el registro público, el administrador tiene una vía dedicada para crear empleados:

- **Formulario:** Solicita nombres, apellidos, móvil, correo, contraseña y la selección explícita del rol.
- **Envío:** Los datos viajan por POST hacia `controllers/UsuarioController.php`.
- **Diferenciación:** Se envía un campo oculto `desde_admin = 1`. Esto indica al controlador que, tras el registro exitoso, debe redirigir de vuelta al dashboard administrativo y no a la página de login.

---

## 3. Edición de Usuarios (Modales Dinámicos)

La edición se realiza sin recargar la página mediante modales:

- **Carga de Datos:** Al hacer clic en el icono de editar, la función JS `openEditModal(u)` recibe el objeto JSON del usuario y rellena automáticamente los campos del formulario.
- **Controlador:** El formulario envía los cambios a `controllers/AdminUsuarioController.php?accion=editar`.
- **Campos Editables:** Nombres, apellidos, móvil y rol. La contraseña no se edita desde aquí por seguridad.

---

## 4. Eliminación de Usuarios (Seguridad y Confirmación)

Para prevenir eliminaciones accidentales, el sistema implementa una doble validación:

1. **Confirmación Visual:** Se utiliza la librería *SweetAlert2* para mostrar un modal de advertencia ("¿Estás seguro?").
2. **Ejecución:** Solo si el usuario confirma, se redirige a `controllers/AdminUsuarioController.php?accion=eliminar&id=...`.
3. **Persistencia:** El modelo ejecuta un `DELETE` físico en la base de datos para el ID correspondiente.

---

## 5. Lógica del Modelo (models/Usuario.php)

El modelo es el encargado de interactuar con la base de datos mediante PDO:

- `obtenerTodos()`: Ejecuta un `SELECT` filtrando por `rol != 'Comprador'`.
- `registrar($datos)`: Inserta los datos en la tabla `usuarios`. Las contraseñas deben llegar ya encriptadas desde el controlador.
- `editarCompleto($id, ...)`: Actualiza los campos básicos del usuario.
- `eliminarCompleto($id)`: Remueve el registro de la tabla por su ID.

---

## 6. Sistema de Alertas y Retroalimentación

El módulo utiliza variables de sesión (`$_SESSION['alert']`) para comunicar resultados:

1. El controlador procesa la acción y guarda un array con `icon`, `title` y `text`.
2. Al redirigir a `admin.php`, el script PHP detecta la alerta.
3. Se renderiza un bloque `<script>` que dispara `Swal.fire()`.
4. La alerta se destruye (`unset`) inmediatamente para que no reaparezca al recargar.

---

## 7. Flujo de Trabajo

Acceso Admin → Sidebar "Usuarios" → `admin.php`
       ↓
[ TABLA DE USUARIOS ]
       ├── Botón Nuevo  → Modal → `UsuarioController` → Éxito → `admin.php` (Alert)
       ├── Icono Editar → Modal → `AdminUsuarioController` → Actualizar → `admin.php`
       └── Icono Borrar → SweetAlert → `AdminUsuarioController` → Eliminar → `admin.php`
