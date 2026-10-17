<div class="flex justify-center gap-2">
    <button
        type="button"
        onclick="openEditModal(this)"
        data-id="{{ $orden->id }}"
        data-cliente="{{ $orden->cliente_id }}"
        data-moneda="{{ $orden->moneda_id }}"
        data-tasa="{{ $orden->tasa_cambio }}"
        data-fecha="{{ $orden->fecha_orden?->format('Y-m-d\TH:i') }}"
        data-estado="{{ $orden->estado->value }}"
        data-estado-pago="{{ $orden->estado_pago->value }}"
        data-detalles="{{ $orden->detalles->map(fn ($detalle) => ['producto_id' => $detalle->producto_id, 'cantidad' => $detalle->cantidad, 'unitario' => $detalle->precio_unitario]) }}"
        class="inline-flex items-center rounded-lg bg-indigo-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-indigo-500"
    >
        Editar
    </button>
    <button
        type="button"
        onclick="openDeleteModal(this)"
        data-id="{{ $orden->id }}"
        data-numero="#{{ $orden->id }}"
        class="inline-flex items-center rounded-lg bg-red-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-red-500"
    >
        Eliminar
    </button>
</div>