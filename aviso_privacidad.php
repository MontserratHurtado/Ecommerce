<?php
if (session_status() === PHP_SESSION_NONE) session_start();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Aviso de Privacidad</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light container py-5">
    <div class="card shadow p-4">
        <h1 class="h2 text-primary mb-4">Aviso de Privacidad Integración E-Commerce</h1>
        <p><strong>Responsable del tratamiento de sus datos:</strong> Mi Empresa E-commerce S.A. de C.V.</p>
        
        <h3 class="h5 mt-3">1. Datos Personales Recabados</h3>
        <p>Recopilamos: nombre completo, correo electrónico, teléfono y dirección de entrega.</p>

        <h3 class="h5 mt-3">2. Tratamiento Financiero (Openpay)</h3>
        <p>Sus datos personales se utilizan exclusivamente para la gestión de pedidos. <strong>No almacenamos datos bancarios ni números de tarjetas de crédito/débito en nuestros servidores.</strong> Los pagos son procesados mediante tokens seguros a través de <strong>Openpay S.A. de C.V.</strong>, cumpliendo con los estándares PCI-DSS SAQ A.</p>

        <h3 class="h5 mt-3">3. Derechos ARCO</h3>
        <p>Usted puede ejercer sus derechos de Acceso, Rectificación, Cancelación u Oposición enviando un correo a <code>privacidad@mitienda.com</code>.</p>
        
        <a href="index.php" class="btn btn-outline-primary mt-3">Volver a la Tienda</a>
    </div>
</body>
</html>