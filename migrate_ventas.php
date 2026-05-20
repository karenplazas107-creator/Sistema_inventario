<?php
require_once __DIR__ . '/config/database.php';

try {
    $database = new Database();
    $db = $database->conectar();
    
    $sql = "ALTER TABLE ventas ADD COLUMN metodo_pago VARCHAR(50) DEFAULT 'Efectivo' AFTER total";
    $db->exec($sql);
    
    echo "Migración exitosa: Columna 'metodo_pago' añadida.";
} catch (Exception $e) {
    if (strpos($e->getMessage(), "Duplicate column name") !== false) {
        echo "La columna ya existe.";
    } else {
        echo "Error: " . $e->getMessage();
    }
}
?>
