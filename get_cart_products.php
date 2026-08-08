<?php
header('Content-Type: application/json; charset=utf-8');
require 'dbcon.php';

// Validar que se reciban los IDs de los productos
if (!isset($_POST['ids']) || empty($_POST['ids'])) {
    echo json_encode([]);
    exit;
}

$ids = $_POST['ids'];

// Si viene como string separado por comas, lo convertimos a array
if (!is_array($ids)) {
    $ids = explode(',', $ids);
}

// Sanitizar los IDs recibidos
$sanitized_ids = array_map(function($id) use ($con) {
    return "'" . mysqli_real_escape_string($con, trim($id)) . "'";
}, $ids);

if (empty($sanitized_ids)) {
    echo json_encode([]);
    exit;
}

$ids_string = implode(',', $sanitized_ids);

// Consulta para traer los datos exactos del carrito
$query = "SELECT 
            p.id AS productoID, 
            p.titulo, 
            p.subtitulo, 
            p.preciounitario, 
            p.descuento, 
            p.preciomayoreo, 
            p.cantidadmayoreo,
            (SELECT medio FROM mediosventa WHERE idproducto = p.id ORDER BY id LIMIT 1) AS primer_medio
          FROM productosventa p
          WHERE p.id IN ($ids_string)";

$result = mysqli_query($con, $query);

if (!$result) {
    echo json_encode(['error' => mysqli_error($con)]);
    exit;
}

$productos = [];
while ($row = mysqli_fetch_assoc($result)) {
    // Asegurar tipos numéricos para Javascript
    $row['preciounitario'] = (float)$row['preciounitario'];
    $row['descuento'] = (float)$row['descuento'];
    $row['preciomayoreo'] = (float)$row['preciomayoreo'];
    $row['cantidadmayoreo'] = (int)$row['cantidadmayoreo'];
    
    $productos[] = $row;
}

echo json_encode($productos, JSON_UNESCAPED_UNICODE);