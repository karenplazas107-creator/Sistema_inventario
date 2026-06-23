<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/models/Venta.php';

session_start();
// Simular usuario si no hay sesión
if (!isset($_SESSION['usuario'])) {
    $_SESSION['usuario'] = ['id' => 1];
}

try {
    $database = new Database();
    $db = $database->conectar();
    $ventaModel = new Venta($db);

    $usuario_id = $_SESSION['usuario']['id'];
    $total = 100;
    $detalles = [
        ['producto_id' => 1, 'cantidad' => 1, 'precio' => 100]
    ];
    $metodo_pago = 'Efectivo';

    echo "<h3>Probando registro de venta...</h3>";
    
    $res = $ventaModel->crear($usuario_id, $total, $detalles, $metodo_pago);
    
    if ($res) {
        echo "<h2 style='color: green;'>✅ Venta registrada con éxito. ID: $res</h2>";
    } else {
        echo "<h2 style='color: red;'>❌ Falló el registro de la venta.</h2>";
        // Intentar obtener el error de PDO
        $errorInfo = $db->errorInfo();
        echo "<pre>";
        print_r($errorInfo);
        echo "</pre>";
    }

} catch (Exception $e) {
    echo "<h2 style='color: red;'>❌ Excepción: " . $e->getMessage() . "</h2>";
}
?>
