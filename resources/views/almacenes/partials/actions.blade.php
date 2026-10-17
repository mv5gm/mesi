<div class="flex justify-center gap-2">
    <button
        type="button"
        onclick="openEditModal(this)"
        data-id="{{ $almacen->id }}"
        data-nombre="{{ $almacen->nombre }}"
        data-ubicacion="{{ $almacen->ubicacion }}"
        data-activo="{{ $almacen->esta_activo ? '1' : '0' }}"
        class="inline-flex items-center rounded-lg bg-indigo-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-indigo-500"
    >
        Editar
    </button>
    <button
        type="button"
        onclick="openDeleteModal(this)"
        data-id="{{ $almacen->id }}"
        data-nombre="{{ $almacen->nombre }}"
        class="inline-flex items-center rounded-lg bg-red-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-red-500"
    >
        Eliminar
    </button>
</div>