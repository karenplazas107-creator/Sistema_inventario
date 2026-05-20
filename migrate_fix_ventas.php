<?php
require_once __DIR__ . '/config/database.php';

try {
    $database = new Database();
    $db = $database->conectar();
    
    // Verificar si la columna ya existe
    $check = $db->query("SHOW COLUMNS FROM ventas LIKE 'metodo_pago'");
    if ($check->rowCount() == 0) {
        $db->exec("ALTER TABLE ventas ADD COLUMN metodo_pago VARCHAR(50) DEFAULT 'Efectivo'");
        echo "<h2 style='color: green;'>✅ Columna 'metodo_pago' añadida con éxito.</h2>";
    } else {
        echo "<h2 style='color: blue;'>ℹ️ La columna 'metodo_pago' ya existe.</h2>";
    }
    
    echo "<p>Ya puedes intentar registrar una venta de nuevo. La factura debería aparecer ahora.</p>";
    echo "<a href='views/ventas/index.php'>Volver al historial</a>";

} catch (Exception $e) {
    echo "<h2 style='color: red;'>❌ Error: " . $e->getMessage() . "</h2>";
}
?>
