<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/models/Producto.php';

$database = new Database();
$db = $database->conectar();
$productoModel = new Producto($db);

$productos = $productoModel->obtenerTodos();

echo "Total productos: " . count($productos) . "\n";
print_r($productos);
?>
