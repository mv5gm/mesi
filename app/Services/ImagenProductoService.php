<?php

namespace App\Services;

use GdImage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ImagenProductoService
{
    /**
     * Peso máximo permitido para la imagen almacenada (1 MB).
     */
    public const PESO_MAXIMO = 1048576;

    /**
     * Lado máximo al que se escala una imagen antes de comprimirla.
     */
    public const LADO_MAXIMO = 1600;

    /**
     * Lado mínimo al que se puede reducir una imagen al comprimirla.
     */
    public const LADO_MINIMO = 320;

    /**
     * Carpeta relativa a public/ donde se guardan las imágenes.
     */
    public const CARPETA = 'img/productos';

    /**
     * Guarda la imagen subida, redimensionándola y comprimiéndola hasta 1 MB.
     *
     * @return string Ruta relativa a public/ (ej. img/productos/uuid.jpg).
     */
    public function guardar(UploadedFile $archivo): string
    {
        $imagen = $this->cargar($archivo);
        $imagen = $this->corregirOrientacion($imagen, $archivo);
        $imagen = $this->escalar($imagen, self::LADO_MAXIMO);

        $binario = $this->comprimirHastaPesoMaximo($imagen);

        imagedestroy($imagen);

        $directorio = public_path(self::CARPETA);
        File::ensureDirectoryExists($directorio);

        $nombre = Str::uuid()->toString().'.jpg';

        if (File::put($directorio.DIRECTORY_SEPARATOR.$nombre, $binario) === false) {
            throw ValidationException::withMessages([
                'imagen' => 'No se pudo guardar la imagen del producto.',
            ]);
        }

        return self::CARPETA.'/'.$nombre;
    }

    /**
     * Elimina del disco una imagen almacenada previamente.
     */
    public function eliminar(?string $ruta): void
    {
        if ($ruta === null || $ruta === ''
            || ! str_starts_with($ruta, self::CARPETA.'/')
            || str_contains($ruta, '..')) {
            return;
        }

        $rutaAbsoluta = public_path($ruta);

        if (File::isFile($rutaAbsoluta)) {
            File::delete($rutaAbsoluta);
        }
    }

    private function cargar(UploadedFile $archivo): GdImage
    {
        $dimensiones = @getimagesize($archivo->getRealPath());

        if ($dimensiones === false || ($dimensiones[0] * $dimensiones[1]) > 30_000_000) {
            throw ValidationException::withMessages([
                'imagen' => 'La imagen no pudo procesarse o tiene una resolución demasiado alta (máximo 30 megapíxeles).',
            ]);
        }

        $contenido = @file_get_contents($archivo->getRealPath());
        $imagen = $contenido !== false ? @imagecreatefromstring($contenido) : false;

        if (! $imagen instanceof GdImage) {
            throw ValidationException::withMessages([
                'imagen' => 'La imagen no pudo procesarse. Verifica que sea un archivo JPG, PNG o WebP válido.',
            ]);
        }

        if (! imageistruecolor($imagen)) {
            imagepalettetotruecolor($imagen);
        }

        return $imagen;
    }

    /**
     * Corrige la orientación según los metadatos EXIF de las fotos JPEG.
     */
    private function corregirOrientacion(GdImage $imagen, UploadedFile $archivo): GdImage
    {
        if (! function_exists('exif_read_data') || $archivo->getMimeType() !== 'image/jpeg') {
            return $imagen;
        }

        $exif = @exif_read_data($archivo->getRealPath());
        $orientacion = (int) ($exif['Orientation'] ?? 1);

        $angulo = match ($orientacion) {
            3 => 180,
            6 => -90,
            8 => 90,
            default => 0,
        };

        if ($angulo === 0) {
            return $imagen;
        }

        $fondo = imagecolorallocate($imagen, 255, 255, 255);
        $rotada = imagerotate($imagen, $angulo, $fondo);

        if (! $rotada instanceof GdImage) {
            return $imagen;
        }

        imagedestroy($imagen);

        return $rotada;
    }

    private function escalar(GdImage $imagen, int $ladoMaximo): GdImage
    {
        $ancho = imagesx($imagen);
        $alto = imagesy($imagen);
        $mayor = max($ancho, $alto);

        if ($mayor <= $ladoMaximo) {
            return $imagen;
        }

        $factor = $ladoMaximo / $mayor;
        $nueva = $this->redimensionar(
            $imagen,
            max(1, (int) round($ancho * $factor)),
            max(1, (int) round($alto * $factor))
        );

        imagedestroy($imagen);

        return $nueva;
    }

    private function redimensionar(GdImage $imagen, int $ancho, int $alto): GdImage
    {
        $nueva = imagecreatetruecolor($ancho, $alto);
        $blanco = imagecolorallocate($nueva, 255, 255, 255);
        imagefilledrectangle($nueva, 0, 0, $ancho, $alto, $blanco);
        imagecopyresampled($nueva, $imagen, 0, 0, 0, 0, $ancho, $alto, imagesx($imagen), imagesy($imagen));

        return $nueva;
    }

    /**
     * Codifica la imagen como JPEG bajando calidad y dimensiones hasta 1 MB.
     */
    private function comprimirHastaPesoMaximo(GdImage &$imagen): string
    {
        $calidad = 88;

        while (true) {
            $binario = $this->codificarJpeg($imagen, $calidad);

            if (strlen($binario) <= self::PESO_MAXIMO) {
                return $binario;
            }

            if ($calidad > 60) {
                $calidad -= 7;

                continue;
            }

            $ancho = imagesx($imagen);
            $alto = imagesy($imagen);
            $mayor = max($ancho, $alto);

            if ($mayor <= self::LADO_MINIMO) {
                throw ValidationException::withMessages([
                    'imagen' => 'La imagen no pudo reducirse por debajo de 1 MB. Intenta con otra imagen.',
                ]);
            }

            $factor = 0.85;
            $nueva = $this->redimensionar(
                $imagen,
                max(1, (int) round($ancho * $factor)),
                max(1, (int) round($alto * $factor))
            );

            imagedestroy($imagen);
            $imagen = $nueva;
            $calidad = 82;
        }
    }

    private function codificarJpeg(GdImage $imagen, int $calidad): string
    {
        $ancho = imagesx($imagen);
        $alto = imagesy($imagen);

        $lienzo = imagecreatetruecolor($ancho, $alto);
        $blanco = imagecolorallocate($lienzo, 255, 255, 255);
        imagefilledrectangle($lienzo, 0, 0, $ancho, $alto, $blanco);
        imagecopy($lienzo, $imagen, 0, 0, 0, 0, $ancho, $alto);

        ob_start();
        imagejpeg($lienzo, null, $calidad);
        $binario = (string) ob_get_clean();

        imagedestroy($lienzo);

        return $binario;
    }
}
