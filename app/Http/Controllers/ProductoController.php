<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use App\Models\Moneda;
use App\Models\Producto;
use App\Services\ImagenProductoService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class ProductoController extends Controller
{
    public function __construct(private readonly ImagenProductoService $imagenes) {}

    public function index(): View
    {
        return view('productos.index', [
            'listaCategorias' => Categoria::paraSelect(),
            'listaMonedas' => Moneda::orderBy('codigo')->get(['id', 'codigo', 'nombre', 'simbolo']),
            'monedaBaseId' => Moneda::where('es_moneda_base', true)->value('id'),
            'monedaBolivarId' => Moneda::where('codigo', 'VES')->value('id'),
            'tasasVigentes' => Moneda::tasasVigentes(),
        ]);
    }

    public function data(Request $request): JsonResponse
    {
        $productos = Producto::query()
            ->with(['categoria:id,nombre', 'moneda:id,codigo,nombre,simbolo'])
            ->withSum('existencias', 'cantidad');

        $recordsTotal = $productos->count();

        if ($search = $request->string('search.value')->toString()) {
            $productos->where(function (Builder $query) use ($search): void {
                $query->where('sku', 'like', "%{$search}%")
                    ->orWhere('codigo_barras', 'like', "%{$search}%")
                    ->orWhere('nombre', 'like', "%{$search}%")
                    ->orWhereHas('categoria', static fn (Builder $categoria) => $categoria->where('nombre', 'like', "%{$search}%"));
            });
        }

        $recordsFiltered = (clone $productos)->count();

        /** @var array<int, string> $columnMap */
        $columnMap = [
            0 => 'sku',
            1 => 'nombre',
            4 => 'precio_costo',
            5 => 'precio_venta',
            6 => 'existencias_sum_cantidad',
        ];
        $column = $columnMap[$request->integer('order.0.column')] ?? 'nombre';
        $direction = $request->string('order.0.dir')->toString() === 'desc' ? 'desc' : 'asc';

        $productos->orderBy($column, $direction);

        $length = $request->integer('length', 10);
        if ($length !== -1) {
            $productos->skip($request->integer('start', 0))->take($length);
        }

        $tasas = Moneda::tasasVigentes();
        $monedaBolivarId = Moneda::where('codigo', 'VES')->value('id');

        $data = $productos->get()->map(function (Producto $producto) use ($tasas, $monedaBolivarId): array {
            $precioCosto = $this->formatoPrecio($producto->precio_costo, $producto->moneda?->simbolo);
            $precioVenta = $this->formatoPrecio($producto->precio_venta, $producto->moneda?->simbolo);
            $precioVentaBolivares = $this->formatoPrecio(
                $this->convertirABolivares((float) $producto->precio_venta, $producto->moneda_id, $tasas, $monedaBolivarId),
                'Bs.'
            );

            return [
                'sku' => e($producto->sku),
                'nombre' => e($producto->nombre),
                'imagen' => $producto->imagen_url !== null
                    ? '<img src="'.e($producto->imagen_url).'" alt="'.e($producto->nombre).'" class="producto-imagen">'
                    : '<span class="text-gray-400">—</span>',
                'categoria' => $producto->categoria
                    ? e($producto->categoria->nombre)
                    : '<span class="text-gray-400">—</span>',
                'precio_costo' => $precioCosto,
                'precio_venta' => $precioVenta,
                'precio_venta_bolivares' => $precioVentaBolivares,
                'stock' => (int) $producto->existencias_sum_cantidad,
                'actions' => view('productos.partials.actions', [
                    'producto' => $producto,
                    'precioCosto' => $precioCosto,
                    'precioVenta' => $precioVenta,
                    'precioVentaBolivares' => $precioVentaBolivares,
                ])->render(),
            ];
        });

        return response()->json([
            'draw' => $request->integer('draw'),
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => $data->values(),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $this->validarDatos($request);
        $datos = $this->procesarImagen($validated, $request);

        $producto = Producto::create([
            ...$datos,
            'stock_minimo' => $datos['stock_minimo'] ?? 0,
        ]);

        return response()->json([
            'message' => "Producto {$producto->nombre} creado correctamente.",
            'producto_id' => $producto->id,
        ]);
    }

    public function update(Request $request, Producto $producto): JsonResponse
    {
        $validated = $this->validarDatos($request, $producto);
        $datos = $this->procesarImagen($validated, $request, $producto);

        $producto->update([
            ...$datos,
            'stock_minimo' => $datos['stock_minimo'] ?? 0,
        ]);

        return response()->json(['message' => 'Producto actualizado correctamente.']);
    }

    public function destroy(Producto $producto): JsonResponse
    {
        $producto->loadCount('existencias', 'movimientos', 'detallesOrdenCompra', 'detallesOrdenVenta');

        if ($producto->existencias_count > 0 || $producto->movimientos_count > 0
            || $producto->detalles_orden_compra_count > 0 || $producto->detalles_orden_venta_count > 0) {
            return response()->json([
                'message' => 'No se puede eliminar el producto porque tiene existencias, movimientos u órdenes asociados.',
            ], 422);
        }

        $this->imagenes->eliminar($producto->imagen);
        $producto->delete();

        return response()->json(['message' => 'Producto eliminado correctamente.']);
    }

    /**
     * Validate a product payload for store/update.
     *
     * @return array<string, mixed>
     */
    private function validarDatos(Request $request, ?Producto $producto = null): array
    {
        $skuUnique = $producto ? 'unique:productos,sku,'.$producto->id : 'unique:productos,sku';
        $barrasUnique = $producto ? 'unique:productos,codigo_barras,'.$producto->id : 'unique:productos,codigo_barras';

        return $request->validate([
            'sku' => ['required', 'string', 'max:50', $skuUnique],
            'codigo_barras' => ['nullable', 'string', 'max:100', $barrasUnique],
            'nombre' => ['required', 'string', 'max:150'],
            'descripcion' => ['nullable', 'string'],
            'imagen' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:10240'],
            'quitar_imagen' => ['nullable', 'boolean'],
            'categoria_id' => ['required', 'integer', 'exists:categorias,id'],
            'moneda_id' => ['required', 'integer', 'exists:monedas,id'],
            'precio_costo' => ['required', 'numeric', 'min:0'],
            'precio_venta' => ['required', 'numeric', 'min:0'],
            'stock_minimo' => ['nullable', 'integer', 'min:0'],
            'stock_maximo' => ['nullable', 'integer', 'min:0', 'gte:stock_minimo'],
            'unidad_medida' => ['required', 'string', 'max:20'],
        ], [
            'sku.required' => 'El SKU es obligatorio.',
            'sku.unique' => 'Ese SKU ya está registrado.',
            'codigo_barras.unique' => 'Ese código de barras ya está registrado.',
            'nombre.required' => 'El nombre es obligatorio.',
            'imagen.image' => 'El archivo debe ser una imagen válida.',
            'imagen.mimes' => 'La imagen debe ser de tipo JPG, PNG o WebP.',
            'imagen.max' => 'La imagen no puede superar los 10 MB.',
            'categoria_id.required' => 'Debes seleccionar una categoría.',
            'categoria_id.exists' => 'La categoría seleccionada no es válida.',
            'moneda_id.required' => 'Debes seleccionar una moneda.',
            'moneda_id.exists' => 'La moneda seleccionada no es válida.',
            'precio_costo.required' => 'El precio de costo es obligatorio.',
            'precio_costo.min' => 'El precio de costo no puede ser negativo.',
            'precio_venta.required' => 'El precio de venta es obligatorio.',
            'precio_venta.min' => 'El precio de venta no puede ser negativo.',
            'stock_maximo.gte' => 'El stock máximo debe ser mayor o igual al stock mínimo.',
        ]);
    }

    /**
     * Procesa la imagen enviada y devuelve los datos listos para guardar.
     *
     * @param  array<string, mixed>  $validated
     * @return array<string, mixed>
     */
    private function procesarImagen(array $validated, Request $request, ?Producto $producto = null): array
    {
        $archivo = $validated['imagen'] ?? null;
        $quitarImagen = $request->boolean('quitar_imagen');

        unset($validated['imagen'], $validated['quitar_imagen']);

        if ($archivo instanceof UploadedFile) {
            $validated['imagen'] = $this->imagenes->guardar($archivo);

            if ($producto?->imagen !== null) {
                $this->imagenes->eliminar($producto->imagen);
            }

            return $validated;
        }

        if ($quitarImagen && $producto?->imagen !== null) {
            $this->imagenes->eliminar($producto->imagen);
            $validated['imagen'] = null;
        }

        return $validated;
    }

    /**
     * Build an indented category list for selects: id => "-- Nombre".
     */
    private function categoriasParaSelect(): Collection
    {
        return Categoria::paraSelect();
    }

    private function formatoPrecio(string|int|float $precio, ?string $simbolo): string
    {
        $valor = number_format((float) $precio, 2, ',', '.');

        return $simbolo !== null ? "{$simbolo} {$valor}" : $valor;
    }

    /**
     * Convierte un monto de la moneda del producto a bolívares.
     *
     * @param  array<int, float>  $tasas
     */
    private function convertirABolivares(float $monto, int $monedaId, array $tasas, ?int $monedaBolivarId): float
    {
        $tasaOrigen = $tasas[$monedaId] ?? 1.0;
        $tasaDestino = $monedaBolivarId !== null ? ($tasas[$monedaBolivarId] ?? null) : null;

        if ($tasaDestino === null || $tasaOrigen <= 0) {
            return 0.0;
        }

        return $monto * ($tasaDestino / $tasaOrigen);
    }
}
