<?php

namespace App\Console\Commands;

use App\Models\Producto;
use App\Services\ImagenProductoService;
use GdImage;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

#[Signature('productos:generar-imagenes {--force : Regenera también las imágenes de productos que ya tienen una}')]
#[Description('Genera imágenes de referencia para los productos que aún no tienen imagen')]
class GenerarImagenesProductos extends Command
{
    /**
     * Paleta de colores usada para los marcadores de posición.
     *
     * @var array<int, array{0: int, 1: int, 2: int}>
     */
    private const PALETA = [
        [79, 70, 229],
        [13, 148, 136],
        [217, 119, 6],
        [225, 29, 72],
        [71, 85, 105],
        [5, 150, 105],
        [124, 58, 237],
        [2, 132, 199],
    ];

    public function handle(ImagenProductoService $imagenes): int
    {
        $forzar = (bool) $this->option('force');
        $fuente = $this->buscarFuente();

        $productos = Producto::query()
            ->when(! $forzar, fn ($query) => $query->where(
                fn ($query) => $query->whereNull('imagen')->orWhere('imagen', '')
            ))
            ->orderBy('id')
            ->get();

        if ($productos->isEmpty()) {
            $this->info('Todos los productos ya tienen imagen. Usa --force para regenerarlas.');

            return self::SUCCESS;
        }

        $directorio = public_path(ImagenProductoService::CARPETA);
        File::ensureDirectoryExists($directorio);

        $productos->load('categoria:id,nombre');

        $creadas = 0;

        foreach ($productos as $producto) {
            $ruta = ImagenProductoService::CARPETA.'/'
                .Str::slug($producto->sku !== '' ? $producto->sku : 'producto').'-'.$producto->id.'.jpg';

            $imagen = $this->crearMarcador($producto, $fuente);
            $guardada = imagejpeg($imagen, public_path($ruta), 82);
            imagedestroy($imagen);

            if ($guardada === false) {
                $this->error("No se pudo generar la imagen de {$producto->sku}.");

                continue;
            }

            if ($producto->imagen !== null && $producto->imagen !== $ruta) {
                $imagenes->eliminar($producto->imagen);
            }

            $producto->forceFill(['imagen' => $ruta])->save();

            $creadas++;
            $this->line("  ✓ {$producto->sku} — {$producto->nombre}");
        }

        $this->info("Se generaron {$creadas} imágenes en public/".ImagenProductoService::CARPETA.'.');

        return self::SUCCESS;
    }

    private function crearMarcador(Producto $producto, ?string $fuente): GdImage
    {
        $lado = 800;
        $imagen = imagecreatetruecolor($lado, $lado);

        [$rojo, $verde, $azul] = self::PALETA[abs(crc32((string) $producto->sku)) % count(self::PALETA)];

        for ($y = 0; $y < $lado; $y++) {
            $factor = 1 - ($y / $lado) * 0.35;
            $color = imagecolorallocate(
                $imagen,
                (int) round($rojo * $factor),
                (int) round($verde * $factor),
                (int) round($azul * $factor)
            );
            imagefilledrectangle($imagen, 0, $y, $lado, $y, $color);
        }

        $blanco = imagecolorallocate($imagen, 255, 255, 255);
        $claro = imagecolorallocate($imagen, 226, 232, 240);

        $categoria = $producto->categoria?->nombre ?? '';
        $this->textoCentrado($imagen, mb_strtoupper($categoria), 22, 120, $claro, $fuente);

        $iniciales = $this->iniciales($producto->nombre);
        $this->textoCentrado($imagen, $iniciales, 150, 440, $blanco, $fuente);

        $lineas = $this->partirTexto($producto->nombre, $fuente, 34, 680);
        $y = 610;

        foreach ($lineas as $linea) {
            $this->textoCentrado($imagen, $linea, 34, $y, $blanco, $fuente);
            $y += 48;
        }

        $this->textoCentrado($imagen, mb_strtoupper($producto->sku), 24, $y + 40, $claro, $fuente);

        return $imagen;
    }

    private function iniciales(string $nombre): string
    {
        $palabras = preg_split('/\s+/', trim(Str::ascii($nombre))) ?: [];
        $iniciales = '';

        foreach (array_slice($palabras, 0, 2) as $palabra) {
            if ($palabra !== '') {
                $iniciales .= mb_strtoupper(mb_substr($palabra, 0, 1));
            }
        }

        return $iniciales !== '' ? $iniciales : 'P';
    }

    /**
     * Divide un texto en líneas que quepan en el ancho indicado.
     *
     * @return array<int, string>
     */
    private function partirTexto(string $texto, ?string $fuente, int $tamano, int $anchoMaximo): array
    {
        $palabras = preg_split('/\s+/', trim($texto)) ?: [];
        $lineas = [];
        $linea = '';

        foreach ($palabras as $palabra) {
            $candidata = $linea === '' ? $palabra : $linea.' '.$palabra;

            if ($linea !== '' && $this->anchoTexto($candidata, $fuente, $tamano) > $anchoMaximo) {
                $lineas[] = $linea;
                $linea = $palabra;
            } else {
                $linea = $candidata;
            }
        }

        if ($linea !== '') {
            $lineas[] = $linea;
        }

        return array_slice($lineas, 0, 2);
    }

    private function textoCentrado(GdImage $imagen, string $texto, int $tamano, int $y, int $color, ?string $fuente): void
    {
        $ancho = $this->anchoTexto($texto, $fuente, $tamano);
        $x = (int) max(0, (imagesx($imagen) - $ancho) / 2);

        if ($fuente !== null) {
            imagettftext($imagen, $tamano, 0, $x, $y, $color, $fuente, $texto);

            return;
        }

        imagestring($imagen, 5, $x, max(0, $y - imagefontheight(5)), $color, Str::ascii($texto));
    }

    private function anchoTexto(string $texto, ?string $fuente, int $tamano): int
    {
        if ($fuente === null) {
            return imagefontwidth(5) * mb_strlen(Str::ascii($texto));
        }

        $caja = imagettfbbox($tamano, 0, $fuente, $texto);

        return (int) abs($caja[2] - $caja[0]);
    }

    private function buscarFuente(): ?string
    {
        $candidatas = [
            resource_path('fonts/DejaVuSans-Bold.ttf'),
            public_path('fonts/Inter-Bold.ttf'),
            'C:/Windows/Fonts/arialbd.ttf',
            'C:/Windows/Fonts/arial.ttf',
            '/usr/share/fonts/truetype/dejavu/DejaVuSans-Bold.ttf',
            '/usr/share/fonts/truetype/dejavu/DejaVuSans.ttf',
        ];

        foreach ($candidatas as $ruta) {
            if (File::isFile($ruta)) {
                return $ruta;
            }
        }

        return null;
    }
}
