<?php
$outputDir = __DIR__ . '/OUTPUT/';

if (is_dir($outputDir)) {
    $archivos = glob($outputDir . '*');

    foreach ($archivos as $archivo) {
        if (is_file($archivo)) {
            unlink($archivo);
        }
    }
}

http_response_code(200);
echo json_encode(['status' => 'ok']);