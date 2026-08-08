<?php
require 'dbcon.php';
$id = $_GET['id'] ?? '';

$stmt = $con->prepare("SELECT * FROM pedidos WHERE identificador = ?");
$stmt->bind_param("s", $id);
$stmt->execute();
$pedido = $stmt->get_result()->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Confirmación de Orden</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light p-5">
    <div class="card max-w-lg mx-auto shadow p-4 text-center">
        <h2 class="text-success">¡Pago Procesado Exitosamente!</h2>
        <p class="mt-3">Folio de pedido: <strong><?= htmlspecialchars($id) ?></strong></p>
        <p>Estatus: <strong><?= htmlspecialchars($pedido['status_pago'] ?? 'Completado') ?></strong></p>
        <a href="tienda-en-linea.php" class="btn btn-primary mt-3">Volver a la Tienda</a>
    </div>
</body>
</html>