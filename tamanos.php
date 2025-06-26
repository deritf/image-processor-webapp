<?php
function formatearTamano($bytes) {
    $unidad = ['B', 'KB', 'MB', 'GB', 'TB'];
    $i = 0;
    while ($bytes >= 1024 && $i < count($unidad) - 1) {
        $bytes /= 1024;
        $i++;
    }
    return round($bytes, 2) . ' ' . $unidad[$i];
}

function obtenerInfoCarpeta($ruta) {
    $total = 0;
    $archivos = array_filter(glob($ruta . '*.{jpg,jpeg,png,webp,bmp,gif}', GLOB_BRACE), 'is_file');
    $cantidad = count($archivos);
    $ultimaFecha = null;

    foreach ($archivos as $archivo) {
        if (is_file($archivo)) {
            $total += filesize($archivo);
            $modificado = filemtime($archivo);
            if ($ultimaFecha === null || $modificado > $ultimaFecha) {
                $ultimaFecha = $modificado;
            }
        }
    }

    if ($ultimaFecha !== null) {
        $formatter = new IntlDateFormatter(
            'es_ES',
            IntlDateFormatter::LONG,
            IntlDateFormatter::SHORT,
            date_default_timezone_get(),
            IntlDateFormatter::GREGORIAN,
            "d 'de' MMMM 'de' yyyy, HH:mm"
        );
        $fechaFormateada = $formatter->format($ultimaFecha);
    } else {
        $fechaFormateada = 'Sin archivos';
    }

    return [
        'tamano'   => $cantidad > 0 ? formatearTamano($total) : '—',
        'cantidad' => $cantidad > 0 ? $cantidad : 'Sin archivos',
        'ultima'   => $fechaFormateada,
    ];
}

header('Content-Type: application/json');
echo json_encode([
    'input' => obtenerInfoCarpeta(__DIR__ . '/INPUT/'),
    'output' => obtenerInfoCarpeta(__DIR__ . '/OUTPUT/')
], JSON_UNESCAPED_UNICODE);