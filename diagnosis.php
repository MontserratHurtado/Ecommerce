<?php
// Activar errores
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h2>🔍 Diagnóstico de Servidor Remoto</h2>";

// 1. Validar Vendor
echo "<h3>1. Carpeta Vendor (Librerías Composer):</h3>";
if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    echo "<p style='color:green;'>✅ La carpeta vendor/ y autoload.php existen.</p>";
    require __DIR__ . '/vendor/autoload.php';
} else {
    echo "<p style='color:red;'>❌ ERROR: No se encuentra la carpeta vendor/ o está incompleta.</p>";
}

// 2. Validar .env
echo "<h3>2. Archivo .env:</h3>";
if (file_exists(__DIR__ . '/.env')) {
    echo "<p style='color:green;'>✅ El archivo .env sí existe en el servidor.</p>";
} else {
    echo "<p style='color:red;'>❌ ERROR: El archivo .env NO fue subido al servidor (revisa archivos ocultos en FileZilla).</p>";
}

// 3. Probar Dotenv
echo "<h3>3. Carga de Variables .env:</h3>";
try {
    if (class_exists('Dotenv\Dotenv') && file_exists(__DIR__ . '/.env')) {
        $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
        $dotenv->load();
        echo "<p style='color:green;'>✅ Dotenv cargó el archivo correctamente.</p>";
    } else {
        echo "<p style='color:orange;'>⚠️ Se omitió la prueba de Dotenv porque falta la librería o el archivo .env.</p>";
    }
} catch (Exception $e) {
    echo "<p style='color:red;'>❌ ERROR en Dotenv: " . $e->getMessage() . "</p>";
}
// 4. Probar Base de Datos (Modo Seguro)
echo "<h3>4. Conexión a Base de Datos:</h3>";

if (file_exists(__DIR__ . '/dbcon.php')) {
    echo "<p>Cargando dbcon.php...</p>";
    
    // Desactivar temporalmente die() si ocurre un error en MySQLi
    mysqli_report(MYSQLI_REPORT_OFF);
    
    @include __DIR__ . '/dbcon.php';

    if (isset($con) && $con && !mysqli_connect_error()) {
        echo "<p style='color:green;'>✅ Conexión exitosa a la Base de Datos.</p>";
    } else {
        echo "<p style='color:red;'>❌ ERROR DE CONEXIÓN: " . mysqli_connect_error() . "</p>";
        echo "<p><em>Revisa que las credenciales en dbcon.php correspondan a las del servidor remoto.</em></p>";
    }
} else {
    echo "<p style='color:red;'>❌ ERROR: No se encontró el archivo dbcon.php (revisa si el nombre está en mayúsculas/minúsculas).</p>";
}
?>