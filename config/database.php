<?php
class Database
{
    private $host = "127.0.0.1"; // Permiso Host
    private $port = "3320"; // ← Este es el puerto de tu MYSQL
    private $db_name = "bdventas_inventario"; // permiso base de datos
    private $username = "root"; // permiso usuario 
    private $password = ""; //permiso contraseña 

    public $conn; 

    public function conectar()
    {
        $this->conn = null;

        try {
            
            $dsn = "mysql:host={$this->host};port={$this->port};dbname={$this->db_name};charset=utf8mb4";

            $this->conn = new PDO($dsn, $this->username, $this->password);

            
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        }
        catch (PDOException $e) {
            
            die("Error de conexión al sistema Almacén Europa: " . $e->getMessage());
        }

        return $this->conn;
    }
}
?>