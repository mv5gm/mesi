<div class="flex justify-center gap-2">
    <button
        type="button"
        onclick="openEditModal(this)"
        data-id="{{ $orden->id }}"
        data-cliente="{{ $orden->cliente_id }}"
        data-tasa="{{ $orden->tasa_cambio }}"
        data-fecha="{{ $orden->fecha_orden?->format('Y-m-d\TH:i') }}"
        data-estado="{{ $orden->estado->value }}"
        data-estado_pago="{{ $orden->estado_pago->value }}"
        data-detalles="{{ $orden->detalles->map(fn ($detalle) => ['producto_id' => $detalle->producto_id, 'cantidad' => $detalle->cantidad, 'unitario' => $detalle->precio_unitario, 'precio_venta' => $detalle->producto?->precio_venta, 'moneda_producto' => $detalle->producto?->moneda_id, 'stock' => (int) ($detalle->producto?->existencias->sum('cantidad') ?? 0)]) }}"
        data-pagos="{{ $orden->pagos->map(fn ($pago) => ['metodo_pago_id' => $pago->metodo_pago_id, 'monto' => $pago->monto, 'tasa_cambio' => $pago->tasa_cambio, 'monto_base' => $pago->monto_base, 'numero_referencia' => $pago->numero_referencia, 'fecha_pago' => $pago->fecha_pago?->format('Y-m-d\TH:i')]) }}"
        aria-label="Editar"
        class="inline-flex items-center justify-center rounded-lg bg-indigo-600 px-3 py-1.5 text-white hover:bg-indigo-500"
    >
        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 0 0-2 2v11a2 2 0 0 0 2 2h11a2 2 0 0 0 2-2v-5m-1.414-9.414a2 2 0 1 1 2.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
        </svg>
    </button>
    <button
        type="button"
        onclick="openDeleteModal(this)"
        data-id="{{ $orden->id }}"
        data-numero="#{{ $orden->id }}"
        aria-label="Eliminar"
        class="inline-flex items-center justify-center rounded-lg bg-red-600 px-3 py-1.5 text-white hover:bg-red-500"
    >
        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0 1 16.138 21H7.862a2 2 0 0 1-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 0 0-1-1h-4a1 1 0 0 0-1 1v3M4 7h16" />
        </svg>
    </button>
</div>
