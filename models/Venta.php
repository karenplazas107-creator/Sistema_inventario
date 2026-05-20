<?php
class Venta {
    private $conn;
    private $table_name = "ventas";
    private $table_detalle = "detalle_venta";

    public function __construct($db) {
        $this->conn = $db;
    }

    public function obtenerTodas() {
        $query = "SELECT v.*, u.nombres, u.apellidos 
                  FROM " . $this->table_name . " v
                  LEFT JOIN usuarios u ON v.usuario_id = u.id
                  ORDER BY v.fecha DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerPorId($id) {
        $query = "SELECT v.*, u.nombres, u.apellidos 
                  FROM " . $this->table_name . " v
                  LEFT JOIN usuarios u ON v.usuario_id = u.id
                  WHERE v.id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function obtenerDetalles($venta_id) {
        $query = "SELECT d.*, p.nombre as producto_nombre 
                  FROM " . $this->table_detalle . " d
                  LEFT JOIN productos p ON d.producto_id = p.id
                  WHERE d.venta_id = :venta_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":venta_id", $venta_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function crear($usuario_id, $total, $detalles, $metodo_pago = 'Efectivo') {
        try {
            $this->conn->beginTransaction();

            // Insertar venta
            $query = "INSERT INTO " . $this->table_name . " (usuario_id, fecha, total, metodo_pago) VALUES (:usuario_id, NOW(), :total, :metodo_pago)";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":usuario_id", $usuario_id);
            $stmt->bindParam(":total", $total);
            $stmt->bindParam(":metodo_pago", $metodo_pago);
            $stmt->execute();
            
            $venta_id = $this->conn->lastInsertId();

            // Insertar detalles
            $queryDetalle = "INSERT INTO " . $this->table_detalle . " (venta_id, producto_id, cantidad, precio) VALUES (:venta_id, :producto_id, :cantidad, :precio)";
            $stmtDetalle = $this->conn->prepare($queryDetalle);

            foreach ($detalles as $item) {
                $stmtDetalle->bindParam(":venta_id", $venta_id);
                $stmtDetalle->bindParam(":producto_id", $item['producto_id']);
                $stmtDetalle->bindParam(":cantidad", $item['cantidad']);
                $stmtDetalle->bindParam(":precio", $item['precio']);
                $stmtDetalle->execute();

                // Opcional: Descontar stock (esto debería hacerse si el sistema lo requiere)
                $stmtStock = $this->conn->prepare("UPDATE inventario SET stock = stock - :cantidad WHERE producto_id = :producto_id");
                $stmtStock->bindParam(":cantidad", $item['cantidad']);
                $stmtStock->bindParam(":producto_id", $item['producto_id']);
                $stmtStock->execute();
            }

            $this->conn->commit();
            return $venta_id;
        } catch (Exception $e) {
            $this->conn->rollBack();
            return false;
        }
    }

    public function eliminar($id) {
        try {
            $this->conn->beginTransaction();
            
            // Eliminar detalles primero (si no hay cascade en la BD)
            $queryDetalles = "DELETE FROM " . $this->table_detalle . " WHERE venta_id = :id";
            $stmtDetalles = $this->conn->prepare($queryDetalles);
            $stmtDetalles->bindParam(":id", $id);
            $stmtDetalles->execute();

            // Eliminar venta
            $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":id", $id);
            $stmt->execute();

            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            $this->conn->rollBack();
            return false;
        }
    }

    // ── MÉTODOS PARA REPORTES ─────────────────────────────────────────────────

    /** Ventas agrupadas por día (últimos N días) */
    public function ventasPorDia($dias = 30) {
        $query = "SELECT DATE(fecha) as dia,
                         COUNT(*) as total_ventas,
                         SUM(total) as ingresos
                  FROM {$this->table_name}
                  WHERE fecha >= DATE_SUB(CURDATE(), INTERVAL :dias DAY)
                  GROUP BY DATE(fecha)
                  ORDER BY dia ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':dias', $dias, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /** Ventas agrupadas por mes (últimos 12 meses) */
    public function ventasPorMes($meses = 12) {
        $query = "SELECT DATE_FORMAT(fecha, '%Y-%m') as mes,
                         DATE_FORMAT(MIN(fecha), '%b %Y') as mes_label,
                         COUNT(*) as total_ventas,
                         SUM(total) as ingresos
                  FROM {$this->table_name}
                  WHERE fecha >= DATE_SUB(CURDATE(), INTERVAL :meses MONTH)
                  GROUP BY DATE_FORMAT(fecha, '%Y-%m')
                  ORDER BY mes ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':meses', $meses, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /** Productos más vendidos */
    public function productosMasVendidos($limite = 10) {
        $query = "SELECT p.nombre,
                         p.precio_venta,
                         c.nombre AS categoria,
                         SUM(d.cantidad) AS unidades_vendidas,
                         SUM(d.cantidad * d.precio) AS ingresos_generados
                  FROM {$this->table_detalle} d
                  JOIN productos p ON d.producto_id = p.id
                  LEFT JOIN categorias c ON p.categoria_id = c.id
                  GROUP BY d.producto_id, p.nombre, p.precio_venta, c.nombre
                  ORDER BY unidades_vendidas DESC
                  LIMIT :limite";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':limite', $limite, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /** Ventas por vendedor */
    public function ventasPorVendedor() {
        $query = "SELECT u.nombres, u.apellidos, u.rol,
                         COUNT(v.id) AS total_ventas,
                         SUM(v.total) AS ingresos
                  FROM {$this->table_name} v
                  JOIN usuarios u ON v.usuario_id = u.id
                  GROUP BY v.usuario_id, u.nombres, u.apellidos, u.rol
                  ORDER BY ingresos DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /** Resumen general */
    public function resumenGeneral() {
        $query = "SELECT
                    COUNT(*) AS total_ventas,
                    COALESCE(SUM(total), 0) AS ingresos_totales,
                    COALESCE(AVG(total), 0) AS ticket_promedio,
                    COALESCE(MAX(total), 0) AS venta_maxima,
                    COALESCE(SUM(CASE WHEN DATE(fecha) = CURDATE() THEN total ELSE 0 END), 0) AS ingresos_hoy,
                    COUNT(CASE WHEN DATE(fecha) = CURDATE() THEN 1 END) AS ventas_hoy,
                    COALESCE(SUM(CASE WHEN MONTH(fecha) = MONTH(CURDATE()) AND YEAR(fecha) = YEAR(CURDATE()) THEN total ELSE 0 END), 0) AS ingresos_mes
                  FROM {$this->table_name}";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>
