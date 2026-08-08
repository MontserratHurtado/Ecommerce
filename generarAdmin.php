<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require 'dbcon.php';

// Datos requeridos por la estructura exacta de tu tabla de usuarios
$nombre          = 'Cristobal Emilio';
$apellidopaterno = 'Romo';
$apellidomaterno = 'Calvillo';
$username        = 'romoccemilio@gmail.com'; 
$password_plana  = 'GatoCalico004'; 
$password_hash   = password_hash($password_plana, PASSWORD_DEFAULT);
$rol             = 1; // 1 = Administrador
$estatus         = 1; // 1 = Activo

// Validar si el usuario ya existe en la base de datos remota
$check_user = "SELECT id FROM usuarios WHERE username='$username' LIMIT 1";
$result = mysqli_query($con, $check_user);

if ($result && mysqli_num_rows($result) > 0) {
    // Si ya existe, actualizamos sus datos y su contraseña encriptada
    $query = "UPDATE usuarios SET 
                nombre='$nombre', 
                apellidopaterno='$apellidopaterno', 
                apellidomaterno='$apellidomaterno', 
                password='$password_hash', 
                rol='$rol', 
                estatus='$estatus' 
              WHERE username='$username'";
    $accion = "actualizado";
} else {
    // Si no existe, lo insertamos nuevo
    $query = "INSERT INTO usuarios SET 
                nombre='$nombre', 
                apellidopaterno='$apellidopaterno', 
                apellidomaterno='$apellidomaterno', 
                username='$username', 
                password='$password_hash', 
                rol='$rol', 
                estatus='$estatus'";
    $accion = "creado";
}

if (mysqli_query($con, $query)) {
    echo "<h2 style='color:green;'>✅ ¡Usuario Administrador $accion con éxito!</h2>";
    echo "<strong>Nombre:</strong> $nombre $apellidopaterno $apellidomaterno<br>";
    echo "<strong>Correo / Username:</strong> $username<br>";
    echo "<strong>Contraseña:</strong> $password_plana<br><br>";
    echo "<p>👉 Ya puedes ir a <a href='login.php'>login.php</a> e iniciar sesión con estas credenciales.</p>";
    echo "<p style='color:red;'>⚠️ <strong>IMPORTANTE:</strong> Elimina este archivo (<code>generarAdmin.php</code>) de FileZilla una vez que inicies sesión.</p>";
} else {
    echo "<h2 style='color:red;'>❌ Error de MySQL:</h2> " . mysqli_error($con);
}
?>