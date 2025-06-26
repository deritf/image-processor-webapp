<?php
if (!isset($_GET['carpeta'])) {
    http_response_code(400);
    exit('Falta parámetro');
}

$carpeta = $_GET['carpeta'];
$carpetasValidas = ['INPUT', 'OUTPUT'];

if (!in_array($carpeta, $carpetasValidas)) {
    http_response_code(403);
    exit('Carpeta no permitida');
}

$ruta = realpath(__DIR__ . DIRECTORY_SEPARATOR . $carpeta);

if (PHP_OS_FAMILY === 'Windows' && is_dir($ruta)) {
    exec('start "" "' . $ruta . '"');
    http_response_code(200);
    exit('Carpeta abierta');
} else {
    http_response_code(500);
    exit('No se pudo abrir la carpeta');
}