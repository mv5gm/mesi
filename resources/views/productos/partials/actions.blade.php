<div class="flex justify-center gap-2">
    <button
        type="button"
        onclick="openViewModal(this)"
        data-id="{{ $producto->id }}"
        data-sku="{{ $producto->sku }}"
        data-codigo-barras="{{ $producto->codigo_barras }}"
        data-nombre="{{ $producto->nombre }}"
        data-descripcion="{{ $producto->descripcion }}"
        data-imagen="{{ $producto->imagen_url }}"
        data-categoria-nombre="{{ $producto->categoria?->nombre }}"
        data-moneda="{{ $producto->moneda ? $producto->moneda->codigo.' — '.$producto->moneda->nombre : '' }}"
        data-precio-costo="{{ $precioCosto }}"
        data-precio-venta="{{ $precioVenta }}"
        data-precio-venta-bolivares="{{ $precioVentaBolivares }}"
        data-stock="{{ (int) ($producto->existencias_sum_cantidad ?? 0) }}"
        data-stock-minimo="{{ $producto->stock_minimo }}"
        data-stock-maximo="{{ $producto->stock_maximo }}"
        data-unidad-medida="{{ $producto->unidad_medida }}"
        data-creado-en="{{ $producto->creado_en?->format('d/m/Y h:i A') }}"
        aria-label="Ver"
        class="inline-flex items-center justify-center rounded-lg bg-slate-600 px-3.5 py-2 text-white hover:bg-slate-500"
    >
        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
        </svg>
    </button>
    <button
        type="button"
        onclick="openEditModal(this)"
        data-id="{{ $producto->id }}"
        data-sku="{{ $producto->sku }}"
        data-codigo-barras="{{ $producto->codigo_barras }}"
        data-nombre="{{ $producto->nombre }}"
        data-descripcion="{{ $producto->descripcion }}"
        data-imagen="{{ $producto->imagen_url }}"
        data-categoria="{{ $producto->categoria_id }}"
        data-moneda="{{ $producto->moneda_id }}"
        data-precio-costo="{{ $producto->precio_costo }}"
        data-precio-venta="{{ $producto->precio_venta }}"
        data-stock-minimo="{{ $producto->stock_minimo }}"
        data-stock-maximo="{{ $producto->stock_maximo }}"
        data-unidad-medida="{{ $producto->unidad_medida }}"
        aria-label="Editar"
        class="inline-flex items-center justify-center rounded-lg bg-indigo-600 px-3.5 py-2 text-white hover:bg-indigo-500"
    >
        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 0 0-2 2v11a2 2 0 0 0 2 2h11a2 2 0 0 0 2-2v-5m-1.414-9.414a2 2 0 1 1 2.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
        </svg>
    </button>
    <button
        type="button"
        onclick="openDeleteModal(this)"
        data-id="{{ $producto->id }}"
        data-nombre="{{ $producto->nombre }}"
        aria-label="Eliminar"
        class="inline-flex items-center justify-center rounded-lg bg-red-600 px-3.5 py-2 text-white hover:bg-red-500"
    >
        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0 1 16.138 21H7.862a2 2 0 0 1-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 0 0-1-1h-4a1 1 0 0 0-1 1v3M4 7h16" />
        </svg>
    </button>
</div>
