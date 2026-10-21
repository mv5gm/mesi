<div class="flex justify-center gap-2">
    <button
        type="button"
        onclick="openEditModal(this)"
        data-id="{{ $existencia->id }}"
        data-producto="{{ $existencia->producto_id }}"
        data-producto-nombre="{{ $existencia->producto_nombre ?? $existencia->producto?->nombre }}"
        data-almacen="{{ $existencia->almacen_id }}"
        data-cantidad="{{ $existencia->cantidad }}"
        class="inline-flex items-center rounded-lg bg-indigo-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-indigo-500"
    >
        Editar
    </button>
    <button
        type="button"
        onclick="openDeleteModal(this)"
        data-id="{{ $existencia->id }}"
        data-producto="{{ $existencia->producto_nombre ?? $existencia->producto?->nombre }}"
        class="inline-flex items-center rounded-lg bg-red-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-red-500"
    >
        Eliminar
    </button>
</div>