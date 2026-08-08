<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$host   = "127.0.0.1";
$user   = "root";
$pass   = "";
$db     = "ecommerce"; // Nombre de tu base de datos previa
$puerto = 3307;        // Puerto de MySQL en tu XAMPP

mysqli_report(MYSQLI_REPORT_OFF);

$con = mysqli_connect($host, $user, $pass, $db, $puerto);

if (!$con) {
    die("Error de conexión con la Base de Datos: " . mysqli_connect_error());
}

mysqli_set_charset($con, "utf8mb4");
?>