<?php
require_once __DIR__ . '/config/database.php';
$db = (new Database())->conectar();
$res = $db->query("SELECT * FROM productos");
$rows = $res->fetchAll(PDO::FETCH_ASSOC);
echo "Total rows: " . count($rows) . "\n";
print_r($rows);
?>
