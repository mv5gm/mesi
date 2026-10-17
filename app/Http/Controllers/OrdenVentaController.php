<?php

namespace App\Http\Controllers;

use App\Enums\EstadoOrdenVenta;
use App\Enums\EstadoPago;
use App\Models\Cliente;
use App\Models\Moneda;
use App\Models\OrdenVenta;
use App\Models\Producto;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class OrdenVentaController extends Controller
{
    public function index(): View
    {
        return view('ventas.index', [
            'listaClientes' => Cliente::orderBy('nombre')->get(['id', 'nombre'])->mapWithKeys(
                fn (Cliente $cliente): array => [$cliente->id => $cliente->nombre]
            ),
            'listaMonedas' => Moneda::orderBy('codigo')->get(['id', 'codigo', 'nombre']),
            'listaProductos' => Producto::orderBy('nombre')->get(['id', 'sku', 'nombre']),
            'monedaBaseId' => Moneda::where('es_moneda_base', true)->first()?->id,
        ]);
    }

    public function data(Request $request): JsonResponse
    {
        $ordenes = OrdenVenta::query()
            ->with(['cliente:id,nombre', 'moneda:id,codigo,simbolo'])
            ->with('detalles:id,orden_venta_id,producto_id,cantidad,precio_unitario');

        $recordsTotal = $ordenes->count();

        if ($search = $request->string('search.value')->toString()) {
            $ordenes->where(function (Builder $query) use ($search): void {
                $query->where('ordenes_venta.id', 'like', "%{$search}%")
                    ->orWhereHas('cliente', fn (Builder $cliente) => $cliente->where('nombre', 'like', "%{$search}%"))
                    ->orWhereHas('moneda', fn (Builder $moneda) => $moneda->where('codigo', 'like', "%{$search}%"));
            });
        }

        $recordsFiltered = (clone $ordenes)->count();

        $ordenes->orderByDesc('ordenes_venta.id');

        $length = $request->integer('length', 10);
        if ($length !== -1) {
            $ordenes->skip($request->integer('start', 0))->take($length);
        }

        $data = $ordenes->get()->map(fn (OrdenVenta $orden): array => [
            'id' => "#{$orden->id}",
            'cliente' => e($orden->cliente?->nombre ?? '—'),
            'fecha' => $orden->fecha_orden?->format('d/m/Y H:i') ?? '—',
            'moneda' => e($orden->moneda?->codigo ?? '—'),
            'total' => $this->formatoPrecio($orden->monto_total, $orden->moneda?->simbolo),
            'total_base' => $this->formatoPrecio($orden->monto_total_base),
            'estado' => $this->badgeEstado($orden->estado),
            'estado_pago' => $this->badgeEstadoPago($orden->estado_pago),
            'actions' => view('ventas.partials.actions', ['orden' => $orden])->render(),
        ]);

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
        $montoTotal = collect($detalles)->sum(
            fn (array $detalle): float => (float) $detalle['cantidad'] * (float) $detalle['precio_unitario']
        );
        $montoTotalBase = round($montoTotal * (float) $validated['tasa_cambio'], 2);

        $orden = DB::transaction(function () use ($validated, $montoTotal, $montoTotalBase): OrdenVenta {
            $orden = OrdenVenta::create([
                'cliente_id' => $validated['cliente_id'],
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
                    'precio_unitario' => $detalle['precio_unitario'],
                ])
            );

            return $orden;
        });

        return response()->json(['message' => "Orden de venta #{$orden->id} creada correctamente."]);
    }

    public function update(Request $request, OrdenVenta $ordenVenta): JsonResponse
    {
        $validated = $this->validarDatos($request);

        $detalles = $validated['detalles'];
        $montoTotal = collect($detalles)->sum(
            fn (array $detalle): float => (float) $detalle['cantidad'] * (float) $detalle['precio_unitario']
        );
        $montoTotalBase = round($montoTotal * (float) $validated['tasa_cambio'], 2);

        DB::transaction(function () use ($ordenVenta, $validated, $montoTotal, $montoTotalBase): void {
            $ordenVenta->update([
                'cliente_id' => $validated['cliente_id'],
                'moneda_id' => $validated['moneda_id'],
                'tasa_cambio' => $validated['tasa_cambio'],
                'monto_total' => $montoTotal,
                'monto_total_base' => $montoTotalBase,
                'estado' => $validated['estado'],
                'estado_pago' => $validated['estado_pago'],
                'fecha_orden' => $validated['fecha_orden'],
            ]);

            $ordenVenta->detalles()->delete();
            $ordenVenta->detalles()->createMany(
                collect($validated['detalles'])->map(fn (array $detalle): array => [
                    'producto_id' => $detalle['producto_id'],
                    'cantidad' => $detalle['cantidad'],
                    'precio_unitario' => $detalle['precio_unitario'],
                ])
            );
        });

        return response()->json(['message' => "Orden de venta #{$ordenVenta->id} actualizada correctamente."]);
    }

    public function destroy(OrdenVenta $ordenVenta): JsonResponse
    {
        $ordenVenta->loadCount('pagos');

        if ($ordenVenta->pagos_count > 0) {
            return response()->json([
                'message' => 'No se puede eliminar la orden porque tiene pagos registrados.',
            ], 422);
        }

        $ordenVenta->delete();

        return response()->json(['message' => "Orden de venta #{$ordenVenta->id} eliminada correctamente."]);
    }

    /**
     * Validate an order payload for store/update.
     *
     * @return array<string, mixed>
     */
    private function validarDatos(Request $request): array
    {
        return $request->validate([
            'cliente_id' => ['required', 'integer', 'exists:clientes,id'],
            'moneda_id' => ['required', 'integer', 'exists:monedas,id'],
            'tasa_cambio' => ['required', 'numeric', 'min:0'],
            'fecha_orden' => ['required', 'date'],
            'estado' => ['required', Rule::enum(EstadoOrdenVenta::class)],
            'estado_pago' => ['required', Rule::enum(EstadoPago::class)],
            'detalles' => ['required', 'array', 'min:1'],
            'detalles.*.producto_id' => ['required', 'integer', 'distinct', 'exists:productos,id'],
            'detalles.*.cantidad' => ['required', 'integer', 'min:1'],
            'detalles.*.precio_unitario' => ['required', 'numeric', 'min:0'],
        ], [
            'cliente_id.required' => 'Debes seleccionar un cliente.',
            'cliente_id.exists' => 'El cliente seleccionado no es válido.',
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
            'detalles.*.precio_unitario.required' => 'Cada línea debe tener un precio unitario.',
            'detalles.*.precio_unitario.min' => 'El precio unitario no puede ser negativo.',
        ]);
    }

    private function badgeEstado(EstadoOrdenVenta $estado): string
    {
        $clases = [
            'PENDIENTE' => 'bg-amber-100 text-amber-700',
            'COMPLETADA' => 'bg-emerald-100 text-emerald-700',
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
