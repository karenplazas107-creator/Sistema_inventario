# Puerta de Entrada al Sistema

*Archivos involucrados:*

- Landing page: public/index.php
- Login: views/usuarios/login.php
- Registro: views/usuarios/registre.php
- Controlador de autenticación: controllers/AuthController.php
- Controlador de registro: controllers/UsuarioController.php
- Modelo: models/Usuario.php

---

## 1. Landing Page (public/index.php)

### ¿Qué es y para qué sirve?

Es la cara pública del sistema — la primera pantalla que ve cualquier persona que acceda a la URL raíz. A diferencia de todos los demás archivos del sistema, este *no requiere sesión activa* y no tiene ninguna lógica PHP de negocio. Es un archivo HTML puro con PHP solo para la estructura básica.

Su único propósito funcional es presentar el producto y dirigir al usuario hacia views/usuarios/login.php mediante botones de llamada a la acción (CTA).

### ¿Cómo está construida?

Usa *TailwindCSS CDN* con una configuración personalizada que define la paleta de colores del sistema (beige #f0ede6, negro #0e0e0e, dorado #b8960c). Las fuentes son *Cormorant Garamond* (serif, para títulos elegantes) y *DM Sans* (sans-serif, para texto de cuerpo), ambas cargadas desde Google Fonts.

La página tiene cuatro secciones:

- *Navbar fijo*: barra de navegación con logo, enlaces de ancla (#inicio, #caracteristicas, #filosofia) y botón "Ingresar" que lleva al login. En móvil se convierte en un menú hamburguesa.
- *Hero*: sección de pantalla completa con imagen de fondo de boutique desde Unsplash, overlay negro semitransparente y patrón de líneas doradas sutiles. Contiene el titular principal y dos botones CTA.
- *Características*: grid de 6 tarjetas que describen los módulos del sistema. La última tarjeta tiene fondo negro con acento dorado (diseño premium).
- *Filosofía*: sección de dos columnas con imagen de boutique y texto de misión/visión.
- *Footer*: fondo negro con borde dorado superior, links de navegación y módulos.

### ¿Qué JavaScript usa?

Todo el JavaScript es nativo (sin librerías):

- *Menú móvil*: el botón hamburguesa alterna la clase open en el menú y cambia el ícono entre fa-bars y fa-xmark. Un listener en document cierra el menú al hacer clic fuera.
- *Scroll reveal*: usa IntersectionObserver para detectar cuando los elementos con clase reveal entran en el viewport y les agrega la clase visible, activando una animación CSS de fadeUp (opacidad 0→1 + translateY 28px→0).
- *Navbar shadow*: un listener en window.scroll aumenta la sombra del navbar cuando el usuario baja más de 20px.

---

## 2. Módulo de Login (views/usuarios/login.php)

### ¿Cómo está diseñada la pantalla?

Es una tarjeta de dos paneles con animación de entrada fadeUp:

- *Panel izquierdo* (visible solo en pantallas ≥768px): imagen de boutique con overlay degradado negro, badge de marca y una cita motivacional en un panel de cristal (glassmorphism con backdrop-filter: blur).
- *Panel derecho*: formulario blanco con logo, título en Cormorant Garamond, campos de correo y contraseña con iconos, y botón de envío.

### ¿Qué validaciones hace el JavaScript del login?

Antes de enviar el formulario, el listener del evento submit realiza dos validaciones visuales:

1. *Campo vacío*: si el correo o la contraseña están vacíos, agrega la clase error al contenedor del campo (cambia el borde a rojo y el fondo a #fff5f5) y muestra el mensaje de error debajo.
2. *Formato de correo*: verifica con la expresión regular /^[^\s@]+@[^\s@]+\.[^\s@]+$/ que el correo tenga formato válido.

Si alguna validación falla, llama a e.preventDefault() para detener el envío. Los errores se limpian automáticamente cuando el usuario empieza a escribir en el campo correspondiente.

El botón del ojo (togglePassword()) alterna el tipo del campo entre password y text y cambia el ícono entre fa-eye-slash y fa-eye.

### ¿Cómo se muestran las alertas del servidor?

Al cargar la página, el PHP lee $_SESSION['alert'] y lo elimina inmediatamente (unset). Si existía una alerta (por ejemplo, de un intento fallido previo), la renderiza en un bloque <script> que ejecuta Swal.fire() al cargar el DOM. Esto permite que el servidor comunique resultados al usuario sin necesidad de parámetros en la URL.

### ¿Qué hace el AuthController al recibir las credenciales?

El formulario envía los datos por POST a controllers/AuthController.php. El proceso completo está documentado en el módulo de usuarios, pero en resumen: valida campos vacíos, aplica protección contra fuerza bruta por correo (bloqueo al 3er intento por 1 hora), consulta la BD con INNER JOIN, verifica estado de la cuenta, verifica contraseña con password_verify(), regenera el ID de sesión y redirige según el rol.

---

## 3. Módulo de Registro (views/usuarios/registre.php)

### ¿Cómo está diseñada la pantalla?

Mismo diseño de dos paneles que el login, con imagen diferente y texto adaptado al contexto de registro. El panel derecho tiene un formulario más extenso con layout de dos columnas para los campos de contraseña.

### ¿Qué campos solicita y cuáles son obligatorios?

| Campo | Obligatorio | Validación |
|---|---|---|
| Nombre completo | Sí | No vacío |
| Teléfono | No | Solo formato |
| Correo electrónico | Sí | Formato de email |
| Contraseña | Sí | Mínimo 6 caracteres |
| Confirmar contraseña | Sí | Debe coincidir con contraseña |
| Rol del usuario | Sí | Debe seleccionar Vendedor (2) o Administrador (1) |
| Términos y condiciones | Sí | Checkbox marcado |

A diferencia de la versión anterior del sistema, el rol *no es un campo oculto* — el usuario debe seleccionarlo explícitamente entre "Vendedor" y "Administrador". Esto permite registrar usuarios de cualquier rol desde esta pantalla.

### ¿Qué validaciones hace el JavaScript del registro?

El listener del submit valida todos los campos en orden y muestra mensajes de error inline (clase show en el span.field-error correspondiente):

1. Nombre no vacío.
2. Correo con formato válido (misma regex que el login).
3. Contraseña con mínimo 6 caracteres.
4. Confirmación de contraseña igual a la contraseña.
5. Rol seleccionado (no vacío).
6. Checkbox de términos marcado.

Si alguna validación falla, detiene el envío con e.preventDefault().

### ¿Qué hace el UsuarioController al recibir el registro?

El formulario envía los datos por POST a controllers/UsuarioController.php. El controlador aplica una *segunda capa de validación en el servidor* (independiente del JavaScript del cliente):

1. Verifica que nombre, correo, contraseña y confirmación no estén vacíos.
2. Valida el formato del correo con filter_var($correo, FILTER_VALIDATE_EMAIL).
3. Verifica que las contraseñas coincidan.
4. Verifica que la contraseña tenga al menos 6 caracteres.
5. *Verifica correo duplicado*: llama a $usuario->existeCorreo($correo) que ejecuta SELECT id_persona FROM Persona WHERE correo = ?. Si ya existe, devuelve error "Este correo ya está registrado".
6. Si todo es válido, encripta la contraseña con password_hash($password, PASSWORD_DEFAULT) y llama a $usuario->registrar($datos).

El modelo registrar() abre una transacción e inserta en Persona y luego en Usuario, igual que el método crear() del módulo de administración de usuarios.

Si el registro es exitoso, guarda en $_SESSION['alert'] un mensaje de éxito *con una clave redirect* apuntando a login.php. El JavaScript de la vista de registro detecta esta clave y, después de que el usuario cierra el modal de SweetAlert, redirige automáticamente al login con .then(() => { window.location.href = '...' }).

---

## 4. Flujo completo de entrada al sistema

Usuario abre el navegador
        ↓
public/index.php  (Landing — sin sesión requerida)
        ↓ clic en "Ingresar"
views/usuarios/login.php
        ↓ envía formulario POST
controllers/AuthController.php
        ├── Fuerza bruta? → bloquea 1 hora
        ├── Campos vacíos? → error
        ├── Usuario no existe? → error + contador
        ├── Cuenta inactiva? → error
        ├── Contraseña incorrecta? → error + contador
        └── Todo OK →
                session_regenerate_id(true)
                $_SESSION['usuario'] = [id, nombre, correo, rol_id, rol]
                ├── rol_id = 1 → views/dashboard/admin.php
                └── rol_id = 2 → views/dashboard/vendedor.php

El registro es una ruta alternativa que crea nuevos usuarios y redirige al login al finalizar. No inicia sesión automáticamente — el usuario debe hacer login después de registrarse.
