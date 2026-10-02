<?php
if (session_status() === PHP_SESSION_NONE) session_start();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Términos y Condiciones</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light container py-5">
    <div class="card shadow p-4">
        <h1 class="h2 text-primary mb-4">Términos, Condiciones y Deslinde de Responsabilidad</h1>

        <h3 class="h5 mt-3">1. Responsabilidad de la Cuenta</h3>
        <p>El usuario es el único responsable de mantener la confidencialidad de su contraseña de acceso.</p>

        <h3 class="h5 mt-3">2. Disponibilidad del Servicio</h3>
        <p>No garantizamos la disponibilidad ininterrumpida de la plataforma debido a mantenimientos del servidor o problemas de red ajenos a nuestro control.</p>

        <h3 class="h5 mt-3">3. Envíos y Reembolsos</h3>
        <p>Las devoluciones podrán solicitarse dentro de los primeros 7 días naturales tras la recepción del paquete, sujeto a inspección previa.</p>
        
        <a href="index.php" class="btn btn-outline-primary mt-3">Volver a la Tienda</a>
    </div>
</body>
</html>