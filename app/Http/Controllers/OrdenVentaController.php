<?php

namespace App\Http\Controllers;

use App\Enums\EstadoOrdenVenta;
use App\Enums\EstadoPago;
use App\Enums\TipoMovimiento;
use App\Models\Cliente;
use App\Models\DetalleOrdenVenta;
use App\Models\Existencia;
use App\Models\MetodoPago;
use App\Models\Moneda;
use App\Models\MovimientoInventario;
use App\Models\OrdenVenta;
use App\Models\PagoOrdenVenta;
use App\Models\Producto;
use App\Models\TasaCambio;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class OrdenVentaController extends Controller
{
    public function index(): View
    {
        return view('ventas.index', [
            'metodosPagoOptions' => $this->metodosPagoOptions(),
            'monedaBaseId' => Moneda::where('es_moneda_base', true)->first()?->id,
            'tasasVigentes' => Moneda::tasasVigentes(),
        ]);
    }

    /**
     * Payment methods with the currency they are settled in.
     *
     * @return Collection<int, array<string, mixed>>
     */
    private function metodosPagoOptions(): Collection
    {
        return MetodoPago::with('moneda:id,codigo,nombre,simbolo')
            ->orderBy('nombre')
            ->get(['id', 'nombre', 'requiere_referencia', 'moneda_id'])
            ->mapWithKeys(fn (MetodoPago $metodo): array => [
                $metodo->id => [
                    'nombre' => $metodo->nombre,
                    'requiere_referencia' => $metodo->requiere_referencia,
                    'moneda_id' => $metodo->moneda_id,
                    'moneda_codigo' => $metodo->moneda?->codigo,
                    'moneda_nombre' => $metodo->moneda?->nombre,
                    'moneda_simbolo' => $metodo->moneda?->simbolo,
                ],
            ]);
    }

    public function clientesSelect2(Request $request): JsonResponse
    {
        $busqueda = $request->string('q')->toString();

        $clientes = Cliente::query()
            ->select('id', 'nombre')
            ->orderBy('nombre')
            ->limit(20);

        if ($busqueda !== '') {
            $clientes->where('nombre', 'like', "%{$busqueda}%");
        }

        return response()->json(
            $clientes->get()->map(fn (Cliente $cliente): array => [
                'id' => $cliente->id,
                'text' => $cliente->nombre,
            ])->values()
        );
    }

    public function productosSelect2(Request $request): JsonResponse
    {
        $busqueda = $request->string('q')->toString();

        $productos = Producto::query()
            ->select('id', 'sku', 'nombre')
            ->withSum('existencias', 'cantidad')
            ->orderBy('nombre')
            ->limit(20);

        if ($busqueda !== '') {
            $productos->where(function (Builder $query) use ($busqueda): void {
                $query->where('nombre', 'like', "%{$busqueda}%")
                    ->orWhere('sku', 'like', "%{$busqueda}%");
            });
        }

        return response()->json(
            $productos->get()->map(fn (Producto $producto): array => [
                'id' => $producto->id,
                'text' => "{$producto->sku} — {$producto->nombre}",
                'stock' => (int) $producto->existencias_sum_cantidad,
            ])->values()
        );
    }

    public function productoPrecio(Request $request): JsonResponse
    {
        $producto = Producto::find($request->query('producto_id'));
        $precioUsd = (float) ($producto?->precio_venta ?? 0);
        $tasa = $this->tasaDeMoneda($request->query('moneda_id'));

        return response()->json([
            'precio_venta' => $precioUsd,
            'precio_orden' => round($precioUsd * $tasa, 2),
            'tasa' => $tasa,
        ]);
    }

    public function tasasSelect2(Request $request): JsonResponse
    {
        $busqueda = $request->string('q')->toString();
        $monedaId = $request->query('moneda_id');

        $tasas = TasaCambio::query()
            ->join('monedas', 'monedas.id', '=', 'tasas_cambio.moneda_id')
            ->select(
                'tasas_cambio.id',
                'tasas_cambio.tasa',
                'tasas_cambio.fecha_efectiva',
                'tasas_cambio.moneda_id',
                'monedas.codigo',
                'monedas.nombre as moneda_nombre'
            )
            ->orderByDesc('tasas_cambio.fecha_efectiva')
            ->limit(20);

        if ($monedaId !== '' && $monedaId !== null) {
            $tasas->where('tasas_cambio.moneda_id', (int) $monedaId);
        }

        if ($busqueda !== '') {
            $tasas->where(function (Builder $query) use ($busqueda): void {
                $query->where('monedas.codigo', 'like', "%{$busqueda}%")
                    ->orWhere('monedas.nombre', 'like', "%{$busqueda}%");
            });
        }

        return response()->json(
            $tasas->get()->map(fn (TasaCambio $tasa): array => [
                'id' => $tasa->id,
                'text' => "{$tasa->codigo} — {$tasa->tasa} ({$tasa->fecha_efectiva})",
                'codigo' => $tasa->codigo,
                'tasa' => (float) $tasa->tasa,
                'fecha_efectiva' => $tasa->fecha_efectiva,
                'moneda_id' => $tasa->moneda_id,
            ])->values()
        );
    }

    public function tasaUltima(Request $request): JsonResponse
    {
        $monedaId = $request->query('moneda_id');

        $tasa = TasaCambio::query()
            ->join('monedas', 'monedas.id', '=', 'tasas_cambio.moneda_id')
            ->when($monedaId !== '' && $monedaId !== null, fn (Builder $q): Builder => $q->where('tasas_cambio.moneda_id', (int) $monedaId))
            ->orderByDesc('tasas_cambio.fecha_efectiva')
            ->orderByDesc('tasas_cambio.id')
            ->select(
                'tasas_cambio.id',
                'tasas_cambio.tasa',
                'tasas_cambio.fecha_efectiva',
                'tasas_cambio.moneda_id',
                'monedas.codigo',
                'monedas.nombre as moneda_nombre'
            )
            ->first();

        return response()->json($tasa ? [
            'id' => $tasa->id,
            'tasa' => (float) $tasa->tasa,
            'fecha_efectiva' => $tasa->fecha_efectiva,
            'moneda_id' => $tasa->moneda_id,
            'codigo' => $tasa->codigo,
            'moneda_nombre' => $tasa->moneda_nombre,
        ] : null);
    }

    public function data(Request $request): JsonResponse
    {
        $ordenes = OrdenVenta::query()
            ->with(['cliente:id,nombre', 'moneda:id,codigo,simbolo'])
            ->with('detalles:id,orden_venta_id,producto_id,cantidad,precio_unitario')
            ->with('detalles.producto:id,sku,nombre,precio_venta,moneda_id')
            ->with('detalles.producto.existencias:id,producto_id,cantidad')
            ->with('pagos:id,orden_venta_id,metodo_pago_id,monto,tasa_cambio,monto_base,numero_referencia,fecha_pago');

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

    public function show(OrdenVenta $ordenVenta): JsonResponse
    {
        $ordenVenta->load([
            'cliente:id,nombre',
            'moneda:id,codigo,nombre,simbolo',
            'detalles.producto:id,sku,nombre',
            'pagos.metodoPago.moneda:id,codigo,simbolo',
        ]);

        $simbolo = $ordenVenta->moneda?->simbolo;

        return response()->json([
            'id' => $ordenVenta->id,
            'cliente' => $ordenVenta->cliente?->nombre ?? '—',
            'fecha' => $ordenVenta->fecha_orden?->format('d/m/Y H:i') ?? '—',
            'moneda' => $ordenVenta->moneda?->codigo ?? '—',
            'simbolo' => $simbolo,
            'tasa_cambio' => (float) $ordenVenta->tasa_cambio,
            'total' => $this->formatoPrecio($ordenVenta->monto_total, $simbolo),
            'total_base' => $this->formatoPrecio($ordenVenta->monto_total_base),
            'estado' => $this->badgeEstado($ordenVenta->estado),
            'estado_pago' => $this->badgeEstadoPago($ordenVenta->estado_pago),
            'detalles' => $ordenVenta->detalles->map(fn (DetalleOrdenVenta $detalle): array => [
                'sku' => $detalle->producto?->sku ?? '—',
                'producto' => $detalle->producto?->nombre ?? '—',
                'cantidad' => $detalle->cantidad,
                'precio_unitario' => $this->formatoPrecio($detalle->precio_unitario, $simbolo),
                'subtotal' => $this->formatoPrecio(
                    (float) $detalle->cantidad * (float) $detalle->precio_unitario,
                    $simbolo
                ),
            ])->values(),
            'pagos' => $ordenVenta->pagos->map(fn (PagoOrdenVenta $pago): array => [
                'metodo' => $pago->metodoPago?->nombre ?? '—',
                'moneda' => $pago->metodoPago?->moneda?->codigo ?? '—',
                'simbolo' => $pago->metodoPago?->moneda?->simbolo,
                'monto' => $this->formatoPrecio($pago->monto, $pago->metodoPago?->moneda?->simbolo),
                'tasa_cambio' => (float) $pago->tasa_cambio,
                'monto_base' => $this->formatoPrecio($pago->monto_base),
                'numero_referencia' => $pago->numero_referencia ?? '—',
                'fecha_pago' => $pago->fecha_pago?->format('d/m/Y H:i') ?? '—',
            ])->values(),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $this->validarDatos($request);

        $detalles = $validated['detalles'];
        $pagos = $validated['pagos'];
        $monedaId = $this->monedaDeLosPagos($pagos);
        $montoTotal = collect($detalles)->sum(
            fn (array $detalle): float => (float) $detalle['cantidad'] * (float) $detalle['precio_unitario']
        );
        $montoTotalBase = round($montoTotal / (float) $validated['tasa_cambio'], 2);

        $orden = DB::transaction(function () use ($validated, $detalles, $pagos, $monedaId, $montoTotal, $montoTotalBase): OrdenVenta {
            $orden = OrdenVenta::create([
                'cliente_id' => $validated['cliente_id'],
                'usuario_id' => auth()->id(),
                'moneda_id' => $monedaId,
                'tasa_cambio' => $validated['tasa_cambio'],
                'monto_total' => $montoTotal,
                'monto_total_base' => $montoTotalBase,
                'estado' => $validated['estado'],
                'estado_pago' => $validated['estado_pago'],
                'fecha_orden' => $validated['fecha_orden'],
            ]);

            $orden->detalles()->createMany(
                collect($detalles)->map(fn (array $detalle): array => [
                    'producto_id' => $detalle['producto_id'],
                    'cantidad' => $detalle['cantidad'],
                    'precio_unitario' => $detalle['precio_unitario'],
                ])
            );

            $orden->pagos()->createMany($this->prepararPagos($pagos));

            if ($validated['estado'] !== EstadoOrdenVenta::Cancelada->value) {
                $this->descontarExistencias($orden, $detalles);
            }

            return $orden;
        });

        return response()->json(['message' => "Orden de venta #{$orden->id} creada correctamente."]);
    }

    public function update(Request $request, OrdenVenta $ordenVenta): JsonResponse
    {
        $validated = $this->validarDatos($request, $ordenVenta);

        $detalles = $validated['detalles'];
        $pagos = $validated['pagos'];
        $monedaId = $this->monedaDeLosPagos($pagos);
        $montoTotal = collect($detalles)->sum(
            fn (array $detalle): float => (float) $detalle['cantidad'] * (float) $detalle['precio_unitario']
        );
        $montoTotalBase = round($montoTotal / (float) $validated['tasa_cambio'], 2);

        DB::transaction(function () use ($ordenVenta, $validated, $detalles, $pagos, $monedaId, $montoTotal, $montoTotalBase): void {
            $this->restituirExistencias($ordenVenta);

            $ordenVenta->update([
                'cliente_id' => $validated['cliente_id'],
                'moneda_id' => $monedaId,
                'tasa_cambio' => $validated['tasa_cambio'],
                'monto_total' => $montoTotal,
                'monto_total_base' => $montoTotalBase,
                'estado' => $validated['estado'],
                'estado_pago' => $validated['estado_pago'],
                'fecha_orden' => $validated['fecha_orden'],
            ]);

            $ordenVenta->detalles()->delete();
            $ordenVenta->detalles()->createMany(
                collect($detalles)->map(fn (array $detalle): array => [
                    'producto_id' => $detalle['producto_id'],
                    'cantidad' => $detalle['cantidad'],
                    'precio_unitario' => $detalle['precio_unitario'],
                ])
            );

            $ordenVenta->pagos()->delete();
            $ordenVenta->pagos()->createMany($this->prepararPagos($pagos));

            if ($validated['estado'] !== EstadoOrdenVenta::Cancelada->value) {
                $this->descontarExistencias($ordenVenta, $detalles);
            }
        });

        return response()->json(['message' => "Orden de venta #{$ordenVenta->id} actualizada correctamente."]);
    }

    public function destroy(OrdenVenta $ordenVenta): JsonResponse
    {
        DB::transaction(function () use ($ordenVenta): void {
            $this->restituirExistencias($ordenVenta);
            $ordenVenta->pagos()->delete();
            $ordenVenta->detalles()->delete();
            $ordenVenta->delete();
        });

        return response()->json(['message' => "Orden de venta #{$ordenVenta->id} eliminada correctamente."]);
    }

    /**
     * Validate an order payload for store/update.
     *
     * @return array<string, mixed>
     */
    private function validarDatos(Request $request, ?OrdenVenta $ordenVenta = null): array
    {
        $validated = $request->validate([
            'cliente_id' => ['required', 'integer', 'exists:clientes,id'],
            'tasa_cambio' => ['required', 'numeric', 'min:0'],
            'fecha_orden' => ['required', 'date'],
            'estado' => ['required', Rule::enum(EstadoOrdenVenta::class)],
            'estado_pago' => ['required', Rule::enum(EstadoPago::class)],
            'detalles' => ['required', 'array', 'min:1'],
            'detalles.*.producto_id' => ['required', 'integer', 'distinct', 'exists:productos,id'],
            'detalles.*.cantidad' => ['required', 'integer', 'min:1'],
            'detalles.*.precio_unitario' => ['required', 'numeric', 'min:0'],
            'pagos' => ['required', 'array', 'min:1'],
            'pagos.*.metodo_pago_id' => ['required', 'integer', 'exists:metodos_pago,id'],
            'pagos.*.monto' => ['required', 'numeric', 'min:0'],
            'pagos.*.tasa_cambio' => ['required', 'numeric', 'min:0'],
            'pagos.*.numero_referencia' => ['nullable', 'string', 'max:100'],
            'pagos.*.fecha_pago' => ['required', 'date'],
        ], [
            'cliente_id.required' => 'Debes seleccionar un cliente.',
            'cliente_id.exists' => 'El cliente seleccionado no es válido.',
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
            'pagos.required' => 'Debes agregar al menos un pago.',
            'pagos.min' => 'Debes agregar al menos un pago.',
            'pagos.*.metodo_pago_id.required' => 'Cada pago debe tener un método de pago.',
            'pagos.*.metodo_pago_id.exists' => 'El método de pago seleccionado no es válido.',
            'pagos.*.monto.required' => 'Cada pago debe tener un monto.',
            'pagos.*.monto.numeric' => 'El monto debe ser un número.',
            'pagos.*.monto.min' => 'El monto no puede ser negativo.',
            'pagos.*.tasa_cambio.required' => 'Cada pago debe tener una tasa de cambio.',
            'pagos.*.tasa_cambio.min' => 'La tasa de cambio no puede ser negativa.',
            'pagos.*.fecha_pago.required' => 'Cada pago debe tener una fecha.',
            'pagos.*.fecha_pago.date' => 'La fecha de pago no es válida.',
        ]);

        $this->validarExistenciasDeProductos($validated['detalles'], $ordenVenta);
        $this->validarCoberturaDePagos($validated);

        return $validated;
    }

    /**
     * Los pagos de una orden pagada deben cubrir al menos el total de la orden.
     *
     * @param  array<string, mixed>  $validated
     */
    private function validarCoberturaDePagos(array $validated): void
    {
        if ($validated['estado_pago'] !== EstadoPago::Pagado->value) {
            return;
        }

        $montoTotal = collect($validated['detalles'])->sum(
            fn (array $detalle): float => (float) $detalle['cantidad'] * (float) $detalle['precio_unitario']
        );

        $montoPagos = collect($validated['pagos'])->sum(
            fn (array $pago): float => (float) $pago['monto']
        );

        if (round($montoPagos, 2) < round($montoTotal, 2)) {
            throw ValidationException::withMessages([
                'pagos' => [
                    sprintf(
                        'La suma de los pagos (%s) no puede ser menor al total de la orden (%s).',
                        $this->formatoPrecio($montoPagos),
                        $this->formatoPrecio($montoTotal)
                    ),
                ],
            ]);
        }
    }

    /**
     * Build the payment rows. The currency of each payment comes from its payment method.
     *
     * @param  array<int, array<string, mixed>>  $pagos
     * @return array<int, array<string, mixed>>
     */
    private function prepararPagos(array $pagos): array
    {
        return collect($pagos)->map(fn (array $pago): array => [
            'metodo_pago_id' => $pago['metodo_pago_id'],
            'monto' => $pago['monto'],
            'tasa_cambio' => $pago['tasa_cambio'],
            'monto_base' => round((float) $pago['monto'] / (float) $pago['tasa_cambio'], 2),
            'numero_referencia' => $pago['numero_referencia'] ?? null,
            'fecha_pago' => $pago['fecha_pago'],
        ])->all();
    }

    /**
     * Los productos vendidos deben tener existencias disponibles.
     *
     * @param  array<int, array<string, mixed>>  $detalles
     */
    private function validarExistenciasDeProductos(array $detalles, ?OrdenVenta $ordenVenta = null): void
    {
        $cantidades = collect($detalles)
            ->groupBy('producto_id')
            ->map(fn (Collection $lineas): int => $lineas->sum(fn (array $linea): int => (int) $linea['cantidad']));

        if ($cantidades->isEmpty()) {
            return;
        }

        $disponibles = Existencia::query()
            ->whereIn('producto_id', $cantidades->keys())
            ->groupBy('producto_id')
            ->selectRaw('producto_id, SUM(cantidad) as total')
            ->pluck('total', 'producto_id')
            ->all();

        // Al editar una orden, sus propias unidades vuelven a estar disponibles.
        if ($ordenVenta !== null) {
            foreach ($ordenVenta->detalles as $detalle) {
                $disponibles[$detalle->producto_id] = ($disponibles[$detalle->producto_id] ?? 0) + $detalle->cantidad;
            }
        }

        $nombres = Producto::whereIn('id', $cantidades->keys())->pluck('nombre', 'id');

        foreach ($cantidades as $productoId => $cantidad) {
            $disponible = (int) ($disponibles[$productoId] ?? 0);

            if ($cantidad <= $disponible) {
                continue;
            }

            $nombre = $nombres[$productoId] ?? "#{$productoId}";

            throw ValidationException::withMessages([
                'detalles' => sprintf(
                    'El producto %s no tiene existencias suficientes (disponible: %d, solicitado: %d).',
                    $nombre,
                    $disponible,
                    $cantidad
                ),
            ]);
        }
    }

    /**
     * Descuenta del inventario las unidades vendidas y deja el movimiento registrado.
     *
     * @param  array<int, array<string, mixed>>  $detalles
     */
    private function descontarExistencias(OrdenVenta $orden, array $detalles): void
    {
        foreach ($detalles as $detalle) {
            $productoId = (int) $detalle['producto_id'];
            $cantidad = (int) $detalle['cantidad'];

            if ($cantidad <= 0) {
                continue;
            }

            foreach (Existencia::descontar($productoId, $cantidad) as $movimiento) {
                MovimientoInventario::create([
                    'producto_id' => $productoId,
                    'almacen_id' => $movimiento['almacen_id'],
                    'usuario_id' => auth()->id(),
                    'tipo_movimiento' => TipoMovimiento::Salida,
                    'cantidad' => $movimiento['cantidad'],
                    'tipo_referencia' => 'ORDEN_VENTA',
                    'referencia_id' => $orden->id,
                ]);
            }
        }
    }

    /**
     * Devuelve al inventario las unidades de una orden y reverses sus movimientos.
     */
    private function restituirExistencias(OrdenVenta $orden): void
    {
        $movimientos = MovimientoInventario::query()
            ->where('tipo_referencia', 'ORDEN_VENTA')
            ->where('referencia_id', $orden->id)
            ->where('tipo_movimiento', TipoMovimiento::Salida)
            ->get();

        foreach ($movimientos as $movimiento) {
            Existencia::devolver($movimiento->producto_id, $movimiento->almacen_id, $movimiento->cantidad);
            $movimiento->delete();
        }
    }

    /**
     * The order currency is inherited from its payments: the currency of the first payment method.
     *
     * @param  array<int, array<string, mixed>>  $pagos
     */
    private function monedaDeLosPagos(array $pagos): int
    {
        $metodoPagoId = $pagos[0]['metodo_pago_id'] ?? null;

        $monedaId = $metodoPagoId !== null
            ? MetodoPago::whereKey($metodoPagoId)->value('moneda_id')
            : null;

        if ($monedaId === null) {
            throw ValidationException::withMessages([
                'pagos.0.metodo_pago_id' => 'El método de pago debe tener una moneda asignada.',
            ]);
        }

        return (int) $monedaId;
    }

    /**
     * Factor de conversión de la moneda base (dólares) a la moneda indicada.
     */
    private function tasaDeMoneda(mixed $monedaId): float
    {
        if ($monedaId === null || $monedaId === '') {
            return 1.0;
        }

        $moneda = Moneda::find((int) $monedaId);

        if ($moneda === null || $moneda->es_moneda_base) {
            return 1.0;
        }

        $tasa = TasaCambio::query()
            ->where('moneda_id', $moneda->id)
            ->orderByDesc('fecha_efectiva')
            ->value('tasa');

        return $tasa !== null && (float) $tasa > 0 ? (float) $tasa : 1.0;
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
