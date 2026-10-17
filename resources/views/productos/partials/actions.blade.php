<div class="flex justify-center gap-2">
    <button
        type="button"
        onclick="openEditModal(this)"
        data-id="{{ $producto->id }}"
        data-sku="{{ $producto->sku }}"
        data-codigo-barras="{{ $producto->codigo_barras }}"
        data-nombre="{{ $producto->nombre }}"
        data-descripcion="{{ $producto->descripcion }}"
        data-categoria="{{ $producto->categoria_id }}"
        data-moneda="{{ $producto->moneda_id }}"
        data-precio-costo="{{ $producto->precio_costo }}"
        data-precio-venta="{{ $producto->precio_venta }}"
        data-stock-minimo="{{ $producto->stock_minimo }}"
        data-stock-maximo="{{ $producto->stock_maximo }}"
        data-unidad-medida="{{ $producto->unidad_medida }}"
        class="inline-flex items-center rounded-lg bg-indigo-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-indigo-500"
    >
        Editar
    </button>
    <button
        type="button"
        onclick="openDeleteModal(this)"
        data-id="{{ $producto->id }}"
        data-nombre="{{ $producto->nombre }}"
        class="inline-flex items-center rounded-lg bg-red-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-red-500"
    >
        Eliminar
    </button>
</div>