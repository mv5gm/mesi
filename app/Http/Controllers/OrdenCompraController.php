<?php

namespace App\Http\Controllers;

use App\Enums\EstadoOrdenCompra;
use App\Enums\EstadoPago;
use App\Models\Moneda;
use App\Models\OrdenCompra;
use App\Models\Producto;
use App\Models\Proveedor;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class OrdenCompraController extends Controller
{
    public function index(): View
    {
        return view('compras.index', [
            'listaProveedores' => $this->proveedoresParaSelect(),
            'listaMonedas' => Moneda::orderBy('codigo')->get(['id', 'codigo', 'nombre']),
            'listaProductos' => Producto::orderBy('nombre')->get(['id', 'sku', 'nombre']),
            'monedaBaseId' => $this->monedaBase()?->id,
        ]);
    }

    public function data(Request $request): JsonResponse
    {
        $ordenes = OrdenCompra::query()
            ->with(['proveedor:id,nombre_empresa', 'moneda:id,codigo,simbolo'])
            ->with('detalles:id,orden_compra_id,producto_id,cantidad,costo_unitario');

        $recordsTotal = $ordenes->count();

        if ($search = $request->string('search.value')->toString()) {
            $ordenes->where(function (Builder $query) use ($search): void {
                $query->where('ordenes_compra.id', 'like', "%{$search}%")
                    ->orWhereHas('proveedor', fn (Builder $proveedor) => $proveedor->where('nombre_empresa', 'like', "%{$search}%"))
                    ->orWhereHas('moneda', fn (Builder $moneda) => $moneda->where('codigo', 'like', "%{$search}%"));
            });
        }

        $recordsFiltered = (clone $ordenes)->count();

        $ordenes->orderByDesc('ordenes_compra.id');

        $length = $request->integer('length', 10);
        if ($length !== -1) {
            $ordenes->skip($request->integer('start', 0))->take($length);
        }

        $data = $ordenes->get()->map(function (OrdenCompra $orden): array {
            $proveedor = $orden->proveedor?->nombre_empresa ?? '—';

            return [
                'id' => "#{$orden->id}",
                'proveedor' => e($proveedor),
                'fecha' => $orden->fecha_orden?->format('d/m/Y H:i') ?? '—',
                'moneda' => e($orden->moneda?->codigo ?? '—'),
                'total' => $this->formatoPrecio($orden->monto_total, $orden->moneda?->simbolo),
                'total_base' => $this->formatoPrecio($orden->monto_total_base),
                'estado' => $this->badgeEstado($orden->estado),
                'estado_pago' => $this->badgeEstadoPago($orden->estado_pago),
                'actions' => view('compras.partials.actions', ['orden' => $orden])->render(),
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

        $detalles = $validated['detalles'];
        $montoTotal = $this->calcularMontoTotal($detalles);
        $montoTotalBase = round($montoTotal * (float) $validated['tasa_cambio'], 2);

        $orden = DB::transaction(function () use ($validated, $montoTotal, $montoTotalBase): OrdenCompra {
            $orden = OrdenCompra::create([
                'proveedor_id' => $validated['proveedor_id'],
                'usuario_id' => auth()->id(),
                'moneda_id' => $validated['moneda_id'],
                'tasa_cambio' => $validated['tasa_cambio'],
                'monto_total' => $montoTotal,
                'monto_total_base' => $montoTotalBase,
                'estado' => $validated['estado'],
                'estado_pago' => $validated['estado_pago'],
                'fecha_orden' => $validated['fecha_orden'],
            ]);

            $orden->detalles()->createMany(
                collect($validated['detalles'])->map(fn (array $detalle): array => [
                    'producto_id' => $detalle['producto_id'],
                    'cantidad' => $detalle['cantidad'],
                    'costo_unitario' => $detalle['costo_unitario'],
                ])
            );

            return $orden;
        });

        return response()->json(['message' => "Orden de compra #{$orden->id} creada correctamente."]);
    }

    public function update(Request $request, OrdenCompra $ordenCompra): JsonResponse
    {
        $validated = $this->validarDatos($request);

        $detalles = $validated['detalles'];
        $montoTotal = $this->calcularMontoTotal($detalles);
        $montoTotalBase = round($montoTotal * (float) $validated['tasa_cambio'], 2);

        DB::transaction(function () use ($ordenCompra, $validated, $montoTotal, $montoTotalBase): void {
            $ordenCompra->update([
                'proveedor_id' => $validated['proveedor_id'],
                'moneda_id' => $validated['moneda_id'],
                'tasa_cambio' => $validated['tasa_cambio'],
                'monto_total' => $montoTotal,
                'monto_total_base' => $montoTotalBase,
                'estado' => $validated['estado'],
                'estado_pago' => $validated['estado_pago'],
                'fecha_orden' => $validated['fecha_orden'],
            ]);

            $ordenCompra->detalles()->delete();
            $ordenCompra->detalles()->createMany(
                collect($validated['detalles'])->map(fn (array $detalle): array => [
                    'producto_id' => $detalle['producto_id'],
                    'cantidad' => $detalle['cantidad'],
                    'costo_unitario' => $detalle['costo_unitario'],
                ])
            );
        });

        return response()->json(['message' => "Orden de compra #{$ordenCompra->id} actualizada correctamente."]);
    }

    public function destroy(OrdenCompra $ordenCompra): JsonResponse
    {
        $ordenCompra->loadCount('pagos');

        if ($ordenCompra->pagos_count > 0) {
            return response()->json([
                'message' => 'No se puede eliminar la orden porque tiene pagos registrados.',
            ], 422);
        }

        $ordenCompra->delete();

        return response()->json(['message' => "Orden de compra #{$ordenCompra->id} eliminada correctamente."]);
    }

    /**
     * Validate an order payload for store/update.
     *
     * @return array<string, mixed>
     */
    private function validarDatos(Request $request): array
    {
        return $request->validate([
            'proveedor_id' => ['required', 'integer', 'exists:proveedores,id'],
            'moneda_id' => ['required', 'integer', 'exists:monedas,id'],
            'tasa_cambio' => ['required', 'numeric', 'min:0'],
            'fecha_orden' => ['required', 'date'],
            'estado' => ['required', Rule::enum(EstadoOrdenCompra::class)],
            'estado_pago' => ['required', Rule::enum(EstadoPago::class)],
            'detalles' => ['required', 'array', 'min:1'],
            'detalles.*.producto_id' => ['required', 'integer', 'distinct', 'exists:productos,id'],
            'detalles.*.cantidad' => ['required', 'integer', 'min:1'],
            'detalles.*.costo_unitario' => ['required', 'numeric', 'min:0'],
        ], [
            'proveedor_id.required' => 'Debes seleccionar un proveedor.',
            'proveedor_id.exists' => 'El proveedor seleccionado no es válido.',
            'moneda_id.required' => 'Debes seleccionar una moneda.',
            'moneda_id.exists' => 'La moneda seleccionada no es válida.',
            'tasa_cambio.required' => 'La tasa de cambio es obligatoria.',
            'tasa_cambio.min' => 'La tasa de cambio no puede ser negativa.',
            'fecha_orden.required' => 'La fecha de la orden es obligatoria.',
            'fecha_orden.date' => 'La fecha de la orden no es válida.',
            'estado.required' => 'Debes seleccionar un estado.',
            'estado_pago.required' => 'Debes seleccionar un estado de pago.',
            'detalles.required' => 'Debes agregar al menos un producto.',
            'detalles.min' => 'Debes agregar al menos un producto.',
            'detalles.*.producto_id.required' => 'Cada línea debe tener un producto.',
            'detalles.*.producto_id.distinct' => 'No puedes repetir el mismo producto en más de una línea.',
            'detalles.*.producto_id.exists' => 'Hay un producto no válido en la lista de detalles.',
            'detalles.*.cantidad.required' => 'Cada línea debe tener una cantidad.',
            'detalles.*.cantidad.integer' => 'La cantidad debe ser un número entero.',
            'detalles.*.cantidad.min' => 'La cantidad mínima es 1.',
            'detalles.*.costo_unitario.required' => 'Cada línea debe tener un costo unitario.',
            'detalles.*.costo_unitario.min' => 'El costo unitario no puede ser negativo.',
        ]);
    }

    /**
     * Compute the order total from its detail lines.
     *
     * @param  array<int, array<string, mixed>>  $detalles
     */
    private function calcularMontoTotal(array $detalles): float
    {
        return collect($detalles)->sum(
            fn (array $detalle): float => (float) $detalle['cantidad'] * (float) $detalle['costo_unitario']
        );
    }

    private function proveedoresParaSelect(): Collection
    {
        return Proveedor::orderBy('nombre_empresa')->get(['id', 'nombre_empresa'])->mapWithKeys(
            fn (Proveedor $proveedor): array => [$proveedor->id => $proveedor->nombre_empresa]
        );
    }

    private function monedaBase(): ?Moneda
    {
        return Moneda::where('es_moneda_base', true)->first();
    }

    private function badgeEstado(EstadoOrdenCompra $estado): string
    {
        $clases = [
            'PENDIENTE' => 'bg-amber-100 text-amber-700',
            'RECIBIDA' => 'bg-emerald-100 text-emerald-700',
            'CANCELADA' => 'bg-red-100 text-red-700',
        ][$estado->value];

        return "<span class=\"inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {$clases}\">{$estado->value}</span>";
    }

    private function badgeEstadoPago(EstadoPago $estado): string
    {
        $clases = [
            'PENDIENTE' => 'bg-gray-100 text-gray-600',
            'PARCIAL' => 'bg-amber-100 text-amber-700',
            'PAGADO' => 'bg-emerald-100 text-emerald-700',
        ][$estado->value];

        return "<span class=\"inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {$clases}\">{$estado->value}</span>";
    }

    private function formatoPrecio(string|int|float $precio, ?string $simbolo = null): string
    {
        $valor = number_format((float) $precio, 2, ',', '.');

        return $simbolo !== null ? "{$simbolo} {$valor}" : $valor;
    }
}
