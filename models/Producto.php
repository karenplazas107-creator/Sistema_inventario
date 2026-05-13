<?php
class Producto {
    private $conn;
    private $table_name = "productos";

    // Cache para no consultar INFORMATION_SCHEMA múltiples veces por request
    private $colsCache = null;

    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Detecta qué columnas opcionales existen en la tabla productos.
     * Así el modelo funciona aunque aún no hayas corrido el ALTER TABLE.
     */
    private function columnasExistentes() {
        if ($this->colsCache !== null) return $this->colsCache;

        $stmt = $this->conn->prepare(
            "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE()
               AND TABLE_NAME   = :tabla
               AND COLUMN_NAME IN ('codigo_barras','imagen')"
        );
        $stmt->execute([':tabla' => $this->table_name]);
        $found = $stmt->fetchAll(PDO::FETCH_COLUMN);

        $this->colsCache = [
            'codigo_barras' => in_array('codigo_barras', $found),
            'imagen'        => in_array('imagen',        $found),
        ];
        return $this->colsCache;
    }

    /** Construye el SELECT dinámico según columnas disponibles */
    private function selectProducto($alias = 'p') {
        $cols = $this->columnasExistentes();
        $s  = "$alias.id, $alias.nombre, $alias.descripcion, ";
        $s .= "$alias.precio_compra, $alias.precio_venta, $alias.categoria_id";
        $s .= $cols['codigo_barras'] ? ", $alias.codigo_barras" : ", NULL AS codigo_barras";
        $s .= $cols['imagen']        ? ", $alias.imagen"        : ", NULL AS imagen";
        return $s;
    }

    // ─────────────────────────────────────────────────────────────────────────

    public function obtenerTodos() {
        $select = $this->selectProducto('p');
        $query  = "SELECT $select,
                          c.nombre AS categoria_nombre,
                          COALESCE(i.stock, 0)         AS stock,
                          COALESCE(i.stock_minimo, 0)  AS stock_minimo
                   FROM {$this->table_name} p
                   LEFT JOIN categorias c  ON p.categoria_id = c.id
                   LEFT JOIN inventario i  ON i.producto_id  = p.id
                   ORDER BY p.nombre ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerPorCategoria($categoria_id) {
        $select = $this->selectProducto('p');
        $query  = "SELECT $select,
                          c.nombre AS categoria_nombre,
                          COALESCE(i.stock, 0)        AS stock,
                          COALESCE(i.stock_minimo, 0) AS stock_minimo
                   FROM {$this->table_name} p
                   LEFT JOIN categorias c ON p.categoria_id = c.id
                   LEFT JOIN inventario i ON i.producto_id  = p.id
                   WHERE p.categoria_id = :categoria_id
                   ORDER BY p.nombre ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':categoria_id', $categoria_id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function buscar($termino) {
        $cols = $this->columnasExistentes();
        $like = '%' . $termino . '%';
        $select = $this->selectProducto('p');

        $whereExtra = $cols['codigo_barras'] ? " OR p.codigo_barras LIKE :termino" : "";

        $query = "SELECT $select,
                         c.nombre AS categoria_nombre,
                         COALESCE(i.stock, 0)        AS stock,
                         COALESCE(i.stock_minimo, 0) AS stock_minimo
                  FROM {$this->table_name} p
                  LEFT JOIN categorias c ON p.categoria_id = c.id
                  LEFT JOIN inventario i ON i.producto_id  = p.id
                  WHERE p.nombre LIKE :termino
                     OR p.descripcion LIKE :termino
                     OR c.nombre LIKE :termino
                     $whereExtra
                  ORDER BY p.nombre ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':termino', $like);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerPorId($id) {
        $select = $this->selectProducto('p');
        $query  = "SELECT $select,
                          COALESCE(i.stock, 0)        AS stock,
                          COALESCE(i.stock_minimo, 0) AS stock_minimo
                   FROM {$this->table_name} p
                   LEFT JOIN inventario i ON i.producto_id = p.id
                   WHERE p.id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function crear($nombre, $descripcion, $precio_compra, $precio_venta, $categoria_id, $codigo_barras = null, $imagen = null) {
        $cols = $this->columnasExistentes();

        $campos = "nombre, descripcion, precio_compra, precio_venta, categoria_id";
        $values = ":nombre, :descripcion, :precio_compra, :precio_venta, :categoria_id";
        if ($cols['codigo_barras']) { $campos .= ", codigo_barras"; $values .= ", :codigo_barras"; }
        if ($cols['imagen'])        { $campos .= ", imagen";        $values .= ", :imagen"; }

        $stmt = $this->conn->prepare(
            "INSERT INTO {$this->table_name} ($campos) VALUES ($values)"
        );
        $stmt->bindParam(':nombre',        $nombre);
        $stmt->bindParam(':descripcion',   $descripcion);
        $stmt->bindParam(':precio_compra', $precio_compra);
        $stmt->bindParam(':precio_venta',  $precio_venta);
        $stmt->bindParam(':categoria_id',  $categoria_id, PDO::PARAM_INT);
        if ($cols['codigo_barras']) $stmt->bindParam(':codigo_barras', $codigo_barras);
        if ($cols['imagen'])        $stmt->bindParam(':imagen',        $imagen);

        if ($stmt->execute()) {
            $producto_id = $this->conn->lastInsertId();
            $inv = $this->conn->prepare(
                "INSERT INTO inventario (producto_id, stock, stock_minimo) VALUES (:pid, 0, 5)"
            );
            $inv->bindParam(':pid', $producto_id, PDO::PARAM_INT);
            $inv->execute();
            return true;
        }
        return false;
    }

    public function editar($id, $nombre, $descripcion, $precio_compra, $precio_venta, $categoria_id, $codigo_barras = null, $imagen = null) {
        $cols = $this->columnasExistentes();

        $set = "nombre = :nombre, descripcion = :descripcion,
                precio_compra = :precio_compra, precio_venta = :precio_venta,
                categoria_id = :categoria_id";
        if ($cols['codigo_barras'])              $set .= ", codigo_barras = :codigo_barras";
        if ($cols['imagen'] && $imagen !== null) $set .= ", imagen = :imagen";

        $stmt = $this->conn->prepare(
            "UPDATE {$this->table_name} SET $set WHERE id = :id"
        );
        $stmt->bindParam(':nombre',        $nombre);
        $stmt->bindParam(':descripcion',   $descripcion);
        $stmt->bindParam(':precio_compra', $precio_compra);
        $stmt->bindParam(':precio_venta',  $precio_venta);
        $stmt->bindParam(':categoria_id',  $categoria_id, PDO::PARAM_INT);
        if ($cols['codigo_barras'])              $stmt->bindParam(':codigo_barras', $codigo_barras);
        if ($cols['imagen'] && $imagen !== null) $stmt->bindParam(':imagen',        $imagen);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);

        return $stmt->execute();
    }

    public function eliminar($id) {
        try {
            $inv = $this->conn->prepare("DELETE FROM inventario WHERE producto_id = :id");
            $inv->bindParam(':id', $id, PDO::PARAM_INT);
            $inv->execute();

            $stmt = $this->conn->prepare("DELETE FROM {$this->table_name} WHERE id = :id");
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Crear producto y asignar stock_minimo personalizado en inventario
     */
    public function crearConStock($nombre, $descripcion, $precio_compra, $precio_venta, $categoria_id, $codigo_barras = null, $imagen = null, $stock_minimo = 5) {
        $cols = $this->columnasExistentes();

        $campos = "nombre, descripcion, precio_compra, precio_venta, categoria_id";
        $values = ":nombre, :descripcion, :precio_compra, :precio_venta, :categoria_id";
        if ($cols['codigo_barras']) { $campos .= ", codigo_barras"; $values .= ", :codigo_barras"; }
        if ($cols['imagen'])        { $campos .= ", imagen";        $values .= ", :imagen"; }

        $stmt = $this->conn->prepare(
            "INSERT INTO {$this->table_name} ($campos) VALUES ($values)"
        );
        $stmt->bindParam(':nombre',        $nombre);
        $stmt->bindParam(':descripcion',   $descripcion);
        $stmt->bindParam(':precio_compra', $precio_compra);
        $stmt->bindParam(':precio_venta',  $precio_venta);
        $stmt->bindParam(':categoria_id',  $categoria_id, PDO::PARAM_INT);
        if ($cols['codigo_barras']) $stmt->bindParam(':codigo_barras', $codigo_barras);
        if ($cols['imagen'])        $stmt->bindParam(':imagen',        $imagen);

        if ($stmt->execute()) {
            $producto_id = $this->conn->lastInsertId();
            $inv = $this->conn->prepare(
                "INSERT INTO inventario (producto_id, stock, stock_minimo) VALUES (:pid, 0, :smin)"
            );
            $inv->bindParam(':pid',  $producto_id, PDO::PARAM_INT);
            $inv->bindParam(':smin', $stock_minimo, PDO::PARAM_INT);
            $inv->execute();
            return true;
        }
        return false;
    }

    /**
     * Editar producto y actualizar stock_minimo en inventario
     */
    public function editarConStock($id, $nombre, $descripcion, $precio_compra, $precio_venta, $categoria_id, $codigo_barras = null, $imagen = null, $stock_minimo = 5) {
        $cols = $this->columnasExistentes();

        $set = "nombre = :nombre, descripcion = :descripcion,
                precio_compra = :precio_compra, precio_venta = :precio_venta,
                categoria_id = :categoria_id";
        if ($cols['codigo_barras'])              $set .= ", codigo_barras = :codigo_barras";
        if ($cols['imagen'] && $imagen !== null) $set .= ", imagen = :imagen";

        $stmt = $this->conn->prepare(
            "UPDATE {$this->table_name} SET $set WHERE id = :id"
        );
        $stmt->bindParam(':nombre',        $nombre);
        $stmt->bindParam(':descripcion',   $descripcion);
        $stmt->bindParam(':precio_compra', $precio_compra);
        $stmt->bindParam(':precio_venta',  $precio_venta);
        $stmt->bindParam(':categoria_id',  $categoria_id, PDO::PARAM_INT);
        if ($cols['codigo_barras'])              $stmt->bindParam(':codigo_barras', $codigo_barras);
        if ($cols['imagen'] && $imagen !== null) $stmt->bindParam(':imagen',        $imagen);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);

        if ($stmt->execute()) {
            // Actualizar stock_minimo en inventario
            $inv = $this->conn->prepare(
                "UPDATE inventario SET stock_minimo = :smin WHERE producto_id = :pid"
            );
            $inv->bindParam(':smin', $stock_minimo, PDO::PARAM_INT);
            $inv->bindParam(':pid',  $id,           PDO::PARAM_INT);
            $inv->execute();
            return true;
        }
        return false;
    }

    /**
     * Actualizar solo el stock de un producto
     */
    public function actualizarStock($producto_id, $nuevo_stock) {
        $stmt = $this->conn->prepare(
            "UPDATE inventario SET stock = :stock WHERE producto_id = :pid"
        );
        $stmt->bindParam(':stock', $nuevo_stock, PDO::PARAM_INT);
        $stmt->bindParam(':pid',   $producto_id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function contarPorCategoria() {
        $query = "SELECT c.id, c.nombre, COUNT(p.id) AS total
                  FROM categorias c
                  LEFT JOIN {$this->table_name} p ON p.categoria_id = c.id
                  GROUP BY c.id, c.nombre
                  ORDER BY total DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
