CREATE TABLE `usuarios` (
  `id` int PRIMARY KEY AUTO_INCREMENT,
  `rol` varchar(255),
  `nombres` varchar(255),
  `apellidos` varchar(255),
  `movil` varchar(255),
  `email` varchar(255) UNIQUE,
  `password` varchar(255),
  `created_at` timestamp
);

CREATE TABLE `categorias` (
  `id` int PRIMARY KEY AUTO_INCREMENT,
  `nombre` varchar(255),
  `descripcion` varchar(255)
);

CREATE TABLE `productos` (
  `id` int PRIMARY KEY AUTO_INCREMENT,
  `nombre` varchar(255),
  `descripcion` varchar(255),
  `precio_compra` decimal,
  `precio_venta` decimal,
  `categoria_id` int,
  `codigo_barras` varchar(100),
  `imagen` varchar(255)
);

-- Si la tabla ya existe, ejecutar estos ALTER TABLE:
-- ALTER TABLE `productos` ADD COLUMN `codigo_barras` varchar(100) NULL AFTER `categoria_id`;
-- ALTER TABLE `productos` ADD COLUMN `imagen` varchar(255) NULL AFTER `codigo_barras`;

CREATE TABLE `inventario` (
  `id` int PRIMARY KEY AUTO_INCREMENT,
  `producto_id` int,
  `stock` int,
  `stock_minimo` int
);

CREATE TABLE `proveedores` (
  `id` int PRIMARY KEY AUTO_INCREMENT,
  `nombre` varchar(255),
  `telefono` varchar(255),
  `email` varchar(255),
  `direccion` varchar(255)
);

CREATE TABLE `compras` (
  `id` int PRIMARY KEY AUTO_INCREMENT,
  `proveedor_id` int,
  `usuario_id` int,
  `fecha` timestamp,
  `total` decimal
);

CREATE TABLE `detalle_compra` (
  `id` int PRIMARY KEY AUTO_INCREMENT,
  `compra_id` int,
  `producto_id` int,
  `cantidad` int,
  `precio` decimal
);

CREATE TABLE `ventas` (
  `id` int PRIMARY KEY AUTO_INCREMENT,
  `usuario_id` int,
  `fecha` timestamp,
  `total` decimal
);

CREATE TABLE `detalle_venta` (
  `id` int PRIMARY KEY AUTO_INCREMENT,
  `venta_id` int,
  `producto_id` int,
  `cantidad` int,
  `precio` decimal
);

ALTER TABLE `productos` ADD FOREIGN KEY (`categoria_id`) REFERENCES `categorias` (`id`);

ALTER TABLE `inventario` ADD FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`);

ALTER TABLE `compras` ADD FOREIGN KEY (`proveedor_id`) REFERENCES `proveedores` (`id`);

ALTER TABLE `compras` ADD FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`);

ALTER TABLE `detalle_compra` ADD FOREIGN KEY (`compra_id`) REFERENCES `compras` (`id`);

ALTER TABLE `detalle_compra` ADD FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`);

ALTER TABLE `ventas` ADD FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`);

ALTER TABLE `detalle_venta` ADD FOREIGN KEY (`venta_id`) REFERENCES `ventas` (`id`);

ALTER TABLE `detalle_venta` ADD FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`);
