<?php
// 1. Configuración de Cookies de Sesión Seguras (Mitiga Session Hijacking y XSS)
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1); // Impide acceso a la cookie desde JavaScript
    ini_set('session.cookie_samesite', 'Lax'); // Protege contra ataques CSRF
    // ini_set('session.cookie_secure', 1);    // Descomentar si usas un certificado SSL (HTTPS)
    session_start();
}

// 2. Cabeceras de Seguridad HTTP (Parchado de vulnerabilidades detectadas)
header("X-Frame-Options: DENY");                       // Protege contra Clickjacking
header("X-Content-Type-Options: nosniff");             // Previene MIME Sniffing
header("X-XSS-Protection: 1; mode=block");             // Activa filtro XSS en navegadores legados
header("Referrer-Policy: strict-origin-when-cross-origin");

// 3. Parámetros de Conexión a la Base de Datos
$host   = "127.0.0.1";
$user   = "root";
$pass   = "";
$db     = "ecommerce";
$puerto = 3307;

mysqli_report(MYSQLI_REPORT_OFF);

$con = mysqli_connect($host, $user, $pass, $db, $puerto);

// 4. Manejo Seguro de Errores (Mitiga Exposición de Información / Information Disclosure)
if (!$con) {
    // Registra el error internamente en los logs de PHP sin exponer rutas al usuario
    error_log("Error de conexión BD: " . mysqli_connect_error());
    die("Error de conexión. Por favor intente más tarde.");
}

mysqli_set_charset($con, "utf8mb4");

// =========================================================================
// 5. FUNCIONES DE CIFRADO SIMÉTRICO CON OPENSSL (Requisito Fase 1.2)
// =========================================================================
define('ENCRYPTION_KEY', 'ClaveSecretaSuperSegura2026!');
define('ENCRYPTION_METHOD', 'AES-256-CBC');

function cifrarDatos($data) {
    $key = hash('sha256', ENCRYPTION_KEY);
    $ivSize = openssl_cipher_iv_length(ENCRYPTION_METHOD);
    $iv = openssl_random_pseudo_bytes($ivSize);
    $encrypted = openssl_encrypt($data, ENCRYPTION_METHOD, $key, 0, $iv);
    return base64_encode($encrypted . '::' . $iv);
}

function descifrarDatos($data) {
    $key = hash('sha256', ENCRYPTION_KEY);
    $parts = explode('::', base64_decode($data), 2);
    if (count($parts) === 2) {
        return openssl_decrypt($parts[0], ENCRYPTION_METHOD, $key, 0, $parts[1]);
    }
    return $data;
}
?>