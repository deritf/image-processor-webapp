<?php
require 'vendor/autoload.php';

use Intervention\Image\Drivers\Gd\Driver as GdDriver;
use Intervention\Image\Drivers\Imagick\Driver as ImagickDriver;
use Intervention\Image\ImageManager;
use Intervention\Image\Encoders\JpegEncoder;
use Intervention\Image\Encoders\PngEncoder;
use Intervention\Image\Encoders\WebpEncoder;

function renderMensaje($tipo, $contenido) {
  echo <<<HTML
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Mensaje</title>
  <link rel="stylesheet" href="assets/css/variables.css">
  <link rel="stylesheet" href="assets/css/fonts.css">
  <link rel="stylesheet" href="assets/css/styles.css">
</head>
<body>
  <div class="mensaje-sistema {$tipo}">
    {$contenido}
  </div>
</body>
</html>
HTML;
  exit;
}

// Configuración de directorios
$inputDir  = __DIR__ . '/INPUT/';
$outputDir = __DIR__ . '/OUTPUT/';
$allowedFormats = ['jpg', 'png', 'webp'];
$allowedDrivers = ['gd', 'imagick'];

// Validar formato de salida
$formato = strtolower($_POST['formato'] ?? '');
if (!in_array($formato, $allowedFormats)) {
    renderMensaje('error', '
        <p><strong>Formato no permitido.</strong></p>
        <a href="index.html" class="boton-volver">Volver</a>
    ');
}

// Validar calidad
$calidad = intval($_POST['calidad'] ?? 85);
$calidad = max(1, min(100, $calidad));

// Validar driver
$driver = strtolower($_POST['driver'] ?? 'gd');
if (!in_array($driver, $allowedDrivers)) {
    renderMensaje('error', '
        <p><strong>Driver de procesamiento no válido.</strong></p>
        <a href="index.html" class="boton-volver">Volver</a>
    ');
}
if ($driver === 'imagick' && !extension_loaded('imagick')) {
    renderMensaje('error', '
        <p><strong>Imagick no está disponible</strong></p>
        <p>Has seleccionado el motor <strong>Imagick</strong>, pero la extensión <code>imagick</code> no está instalada o habilitada en tu servidor local.</p>
        <p>Consulta las instrucciones para instalarla correctamente:</p>
        <p><a href="INSTRUCCIONES/Instalar_Imagick.txt" download>Descargar instrucciones (Instalar_Imagick.txt)</a></p>
        <a href="index.html" class="boton-volver">Volver</a>
    ');
}

// Asegurar carpetas
if (!is_dir($inputDir))  mkdir($inputDir, 0755, true);
if (!is_dir($outputDir)) mkdir($outputDir, 0755, true);

// Crear instancia del ImageManager con el driver seleccionado
$driverInstance = match ($driver) {
    'imagick' => new ImagickDriver(),
    default   => new GdDriver(),
};

$manager = new ImageManager($driverInstance);

// Obtener imágenes de INPUT
$imagenes = glob($inputDir . '*.{jpg,jpeg,png,webp,bmp,gif}', GLOB_BRACE);
$totalProcesadas = 0;

$resizeW = isset($_POST['resize_width']) ? intval($_POST['resize_width']) : null;
$resizeH = isset($_POST['resize_height']) ? intval($_POST['resize_height']) : null;

$cropW = isset($_POST['crop_w']) ? max(0, intval($_POST['crop_w'])) : null;
$cropH = isset($_POST['crop_h']) ? max(0, intval($_POST['crop_h'])) : null;
$cropX = isset($_POST['crop_x']) ? max(0, intval($_POST['crop_x'])) : 0;
$cropY = isset($_POST['crop_y']) ? max(0, intval($_POST['crop_y'])) : 0;

$rotate = isset($_POST['rotate']) ? floatval($_POST['rotate']) : null;
$flip = $_POST['flip'] ?? '';

if ($resizeW !== null && $resizeW <= 0) $resizeW = null;
if ($resizeH !== null && $resizeH <= 0) $resizeH = null;

foreach ($imagenes as $rutaOriginal) {
    $nombreBase = pathinfo($rutaOriginal, PATHINFO_FILENAME);
    $nuevoNombre = $nombreBase . '.' . $formato;
    $rutaDestino = $outputDir . $nuevoNombre;

    try {
        $imagen = $manager->read($rutaOriginal);

        //--------------- REDIMENSIONAR --------------
        // Redimensionar proporcional (o exacto si se escribe en ambos campos)
        $originalWidth = $imagen->width();
        $originalHeight = $imagen->height();
        $aspectRatio = $originalWidth / $originalHeight;

        if ($resizeW && !$resizeH) {
            $resizeH = intval($resizeW / $aspectRatio);
        } elseif (!$resizeW && $resizeH) {
            $resizeW = intval($resizeH * $aspectRatio);
        }
        if ($resizeW && $resizeH) {
            $imagen = $imagen->resize($resizeW, $resizeH);
        }

        //--------------- RECORTE --------------
        if ($cropW && $cropH) {
            $imagen = $imagen->crop($cropW, $cropH, $cropX, $cropY);
        }

        //--------------- ROTAR --------------
        if (!is_null($rotate)) {
            $imagen = $imagen->rotate($rotate);
        }

        //--------------- AJUSTES VISUALES --------------
        $brightness = isset($_POST['brightness']) ? intval($_POST['brightness']) : null;
        $contrast   = isset($_POST['contrast']) ? intval($_POST['contrast']) : null;
        $gamma      = isset($_POST['gamma']) ? floatval($_POST['gamma']) : null;
        $blur       = isset($_POST['blur']) ? intval($_POST['blur']) : null;
        $sharpen    = isset($_POST['sharpen']) ? intval($_POST['sharpen']) : null;
        $greyscale  = isset($_POST['greyscale']) && $_POST['greyscale'] === '1';
        $colorR     = isset($_POST['colorize_r']) ? intval($_POST['colorize_r']) : null;
        $colorG     = isset($_POST['colorize_g']) ? intval($_POST['colorize_g']) : null;
        $colorB     = isset($_POST['colorize_b']) ? intval($_POST['colorize_b']) : null;

        if (!is_null($brightness)) $imagen = $imagen->brightness($brightness);
        if (!is_null($contrast))   $imagen = $imagen->contrast($contrast);
        if (!is_null($gamma) && $gamma > 0) $imagen = $imagen->gamma($gamma);
        if (!is_null($blur))       $imagen = $imagen->blur($blur);
        if (!is_null($sharpen))    $imagen = $imagen->sharpen($sharpen);
        if ($greyscale)            $imagen = $imagen->greyscale();
        if (!is_null($colorR) && !is_null($colorG) && !is_null($colorB)) {
            $imagen = $imagen->colorize($colorR, $colorG, $colorB);
        }

        //--------------- EFECTOS --------------
        $invert    = isset($_POST['invert']) && $_POST['invert'] === '1';
        $opacity   = isset($_POST['opacity']) ? intval($_POST['opacity']) : null;
        $pixelate  = isset($_POST['pixelate']) ? intval($_POST['pixelate']) : null;
        $borderSize  = isset($_POST['border_size']) ? intval($_POST['border_size']) : null;
        $borderColor = trim($_POST['border_color'] ?? '');
        $rounded   = isset($_POST['rounded']) ? intval($_POST['rounded']) : null;

        // Invertir colores
        if ($invert) {
            $imagen = $imagen->invert();
        }

        // Pixelado
        if (!is_null($pixelate) && $pixelate > 0) {
            $imagen = $imagen->pixelate($pixelate);
        }

        //--------------- FLIP HORIZONTAL/VERTICAL --------------
        if ($flip === 'h') {
            $imagen = $imagen->rotate(180)->flip('v');
        } elseif ($flip === 'v') {
            $imagen = $imagen->flip('v');
        }

        //--------------- GUARDAR --------------
        $encoder = match ($formato) {
            'jpg'  => new JpegEncoder(quality: $calidad),
            'png'  => new PngEncoder(),
            'webp' => new WebpEncoder(quality: $calidad),
        };

        $encoded = $imagen->encode($encoder);
        file_put_contents($rutaDestino, $encoded);

        $totalProcesadas++;
    } catch (Exception $e) {
        echo "Error al procesar '$rutaOriginal': " . $e->getMessage() . "<br>";
    }
}

renderMensaje('exito', "
    <p><strong>Proceso completado</strong></p>
    <p>Imágenes procesadas: {$totalProcesadas}</p>
    <a href='index.html' class='boton-volver'>Volver</a>
");