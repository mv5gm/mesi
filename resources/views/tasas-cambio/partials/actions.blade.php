<div class="flex justify-center gap-2">
    <button
        type="button"
        onclick="openEditModal(this)"
        data-id="{{ $tasa->id }}"
        data-moneda="{{ $tasa->moneda_id }}"
        data-tasa="{{ $tasa->tasa }}"
        data-fecha-efectiva="{{ $tasa->fecha_efectiva?->format('Y-m-d\TH:i') }}"
        class="inline-flex items-center rounded-lg bg-indigo-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-indigo-500"
    >
        Editar
    </button>
    <button
        type="button"
        onclick="openDeleteModal(this)"
        data-id="{{ $tasa->id }}"
        data-moneda="{{ $tasa->moneda->codigo }}"
        class="inline-flex items-center rounded-lg bg-red-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-red-500"
    >
        Eliminar
    </button>
</div>