# Módulo de Informes y Reportes

Este módulo transforma los datos crudos de la base de datos en información visual y estadística para la toma de decisiones estratégicas.

*Archivos involucrados:*

- **Vista Principal:** `views/reportes/index.php`
- **Modelos de Datos:** `models/Venta.php`, `models/Producto.php`
- **Librería Externa:** Chart.js (vía CDN)

---

## 1. Indicadores Clave (KPIs)

El módulo presenta un resumen ejecutivo con 4 métricas principales:
- **Ingresos Totales:** Suma histórica de todas las ventas confirmadas.
- **Ingresos del Día:** Monitoreo en tiempo real de las ventas actuales.
- **Ingresos del Mes:** Comparativa mensual.
- **Ticket Promedio:** Valor medio de las transacciones realizadas.

---

## 2. Visualización de Datos (Gráficas)

Utiliza la librería **Chart.js** para generar representaciones dinámicas:
- **Gráfica de Líneas:** Muestra la evolución de los ingresos en los últimos 12 meses.
- **Gráfica de Barras:** Detalla las ventas diarias de los últimos 30 días, permitiendo identificar picos de demanda.

---

## 3. Análisis de Rendimiento

- **Ranking de Productos:** Un gráfico de barras horizontales (progreso) que destaca los productos más vendidos por unidades e ingresos generados.
- **Rendimiento por Vendedor:** Compara el desempeño de los empleados basándose en el volumen de ventas y el dinero recaudado.

---

## 4. Auditoría de Inventario

El módulo también proporciona un vistazo rápido al estado físico del negocio:
- Total de productos y unidades en stock.
- Conteo de productos con stock bajo o agotados.
- **Valor del Inventario:** Cálculo del capital invertido basado en (Stock * Precio de Compra).

---

## 5. Lógica de Obtención de Datos

Toda la inteligencia de este módulo reside en los modelos, especialmente en `Venta.php`, el cual contiene consultas SQL complejas con agrupamientos (`GROUP BY`) y funciones de agregación (`SUM`, `COUNT`, `AVG`) para procesar miles de registros en milisegundos.
