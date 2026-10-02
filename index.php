<?php
// Carga las cabeceras de seguridad y la configuración de sesión antes de redireccionar
require_once 'dbcon.php';

header("Location: login.php");
exit();
?>