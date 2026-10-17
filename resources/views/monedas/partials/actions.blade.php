<div class="flex justify-center gap-2">
    <button
        type="button"
        onclick="openEditModal(this)"
        data-id="{{ $moneda->id }}"
        data-codigo="{{ $moneda->codigo }}"
        data-nombre="{{ $moneda->nombre }}"
        data-simbolo="{{ $moneda->simbolo }}"
        data-base="{{ $moneda->es_moneda_base ? '1' : '0' }}"
        class="inline-flex items-center rounded-lg bg-indigo-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-indigo-500"
    >
        Editar
    </button>
    <button
        type="button"
        onclick="openDeleteModal(this)"
        data-id="{{ $moneda->id }}"
        data-codigo="{{ $moneda->codigo }}"
        class="inline-flex items-center rounded-lg bg-red-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-red-500"
    >
        Eliminar
    </button>
</div>