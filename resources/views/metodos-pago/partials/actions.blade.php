<div class="flex justify-center gap-2">
    <button
        type="button"
        onclick="openEditModal(this)"
        data-id="{{ $metodoPago->id }}"
        data-nombre="{{ $metodoPago->nombre }}"
        data-moneda="{{ $metodoPago->moneda_id }}"
        data-requiere-referencia="{{ $metodoPago->requiere_referencia ? '1' : '0' }}"
        class="inline-flex items-center rounded-lg bg-indigo-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-indigo-500"
    >
        Editar
    </button>
    <button
        type="button"
        onclick="openDeleteModal(this)"
        data-id="{{ $metodoPago->id }}"
        data-nombre="{{ $metodoPago->nombre }}"
        class="inline-flex items-center rounded-lg bg-red-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-red-500"
    >
        Eliminar
    </button>
</div>