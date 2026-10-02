<?php
// Usar una clave de 32 caracteres (256 bits)
define('ENCRYPTION_KEY', 'a1b2c3d4e5f678901234567890abcdef');
define('ENCRYPTION_METHOD', 'AES-256-CBC');

function encryptData($data) {
    if (empty($data)) return $data;
    $ivLength = openssl_cipher_iv_length(ENCRYPTION_METHOD);
    $iv = openssl_random_pseudo_bytes($ivLength);
    $encrypted = openssl_encrypt($data, ENCRYPTION_METHOD, ENCRYPTION_KEY, 0, $iv);
    return base64_encode($encrypted . '::' . $iv);
}

function decryptData($data) {
    if (empty($data) || strpos($data, '::') === false) return $data;
    list($encryptedData, $iv) = explode('::', base64_decode($data), 2);
    return openssl_decrypt($encryptedData, ENCRYPTION_METHOD, ENCRYPTION_KEY, 0, $iv);
}
?>