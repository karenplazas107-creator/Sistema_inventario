
<?php 
session_start();
$alert = $_SESSION['alert'] ?? null;
unset($_SESSION['alert']);
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Almacén Europa | Registro</title>

<link rel="shortcut icon" href="../../img/icon.png">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">

<style>

*{
  margin:0;
  padding:0;
  box-sizing:border-box;
  font-family:'DM Sans', sans-serif;
}

/* 🎨 COLORES */
:root{
  --primary:#1E2A3A;
  --secondary:#2C3E50;
  --light:#F4F6F8;
  --gray:#E9EDF2;
  --input:#F1F3F5;
  --border:#D6DCE3;
  --text:#1E2A3A;
  --muted:#6C7A89;
  --white:#fff;
}

/* BODY */
body{
  background:var(--gray);
  display:flex;
  justify-content:center;
  align-items:center;
  min-height:100vh;
  padding:20px;
}

/* CONTENEDOR */
.container{
  width:100%;
  max-width:950px;
  display:flex;
  border-radius:18px;
  overflow:hidden;
  box-shadow:0 20px 60px rgba(0,0,0,0.2);
}

/* PANEL IZQUIERDO */
.left{
  flex:1;
  background:linear-gradient(135deg,var(--primary),var(--secondary));
  color:white;
  padding:40px;
  display:flex;
  flex-direction:column;
  justify-content:space-between;
}

.left h1{
  font-size:32px;
  margin-bottom:15px;
}

.left p{
  color:#cfd8e3;
  line-height:1.6;
}

.secure{
  display:flex;
  align-items:center;
  gap:10px;
  opacity:.8;
}

/* PANEL DERECHO */
.right{
  flex:1;
  background:white;
  padding:40px;
  display:flex;
  flex-direction:column;
  justify-content:center;
}

.right h2{
  font-size:26px;
  color:var(--primary);
}

.right p{
  color:var(--muted);
  margin-bottom:20px;
}

/* INPUTS */
.input-group{
  margin-bottom:15px;
}

.input-group label{
  font-size:13px;
  color:var(--muted);
}

.input-group input{
  width:100%;
  padding:12px;
  border-radius:8px;
  border:1px solid var(--border);
  background:var(--input);
  margin-top:5px;
  transition:0.2s;
}

.input-group input:focus{
  outline:none;
  border-color:var(--primary);
  background:#fff;
}

/* BOTÓN */
.btn{
  margin-top:10px;
  padding:12px;
  border:none;
  border-radius:10px;
  background:var(--primary);
  color:white;
  font-weight:600;
  cursor:pointer;
  transition:.3s;
}

.btn:hover{
  background:#16202B;
}

/* LINK */
.link{
  margin-top:15px;
  font-size:14px;
}

.link a{
  color:var(--primary);
  font-weight:600;
  text-decoration:none;
}

/* 📱 RESPONSIVE */
@media (max-width: 768px){
  .container{
    flex-direction:column;
  }

  .left{
    text-align:center;
    padding:30px;
  }

  .right{
    padding:30px;
  }
}

</style>
</head>

<body>

<div class="container">

  <!-- IZQUIERDA -->
  <div class="left">
    <div>
      <h1>Almacén Europa</h1>
      <p>
        Sistema de Gestión de Ventas e Inventario.  
        Acceda para administrar el stock y las operaciones diarias.
      </p>
    </div>

    <div class="secure">
      🔒 <span>Conexión segura al servidor central</span>
    </div>
  </div>

  <!-- DERECHA -->
  <div class="right">
    <h2>Crear Cuenta</h2>
    <p>Ingrese sus datos</p>

    <form action="../../controllers/UsuarioController.php" method="POST">

      <div class="input-group">
        <label>Nombre</label>
        <input type="text" name="nombre" required>
      </div>

      <div class="input-group">
        <label>Correo electrónico</label>
        <input type="email" name="correo" required>
      </div>

      <div class="input-group">
        <label>Teléfono</label>
        <input type="tel" name="telefono">
      </div>

      <div class="input-group">
        <label>Contraseña</label>
        <input type="password" name="password" required>
      </div>

      <div class="input-group">
        <label>Confirmar contraseña</label>
        <input type="password" name="confirmar_password" required>
      </div>

      <button class="btn">Crear Usuario</button>

    </form>

    <div class="link">
      ¿Ya tienes cuenta? <a href="login.php">Iniciar sesión</a>
    </div>

  </div>

</div>

<?php if ($alert): ?>
<script>
Swal.fire({
  icon: '<?= $alert['icon'] ?>',
  title: '<?= $alert['title'] ?>',
  text: '<?= $alert['text'] ?>',
  confirmButtonColor: '#1E2A3A'
});
</script>
<?php endif; ?>

</body>
</html>


