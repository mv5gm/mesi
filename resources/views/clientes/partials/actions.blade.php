<div class="flex justify-center gap-2">
    <button
        type="button"
        onclick="openEditModal(this)"
        data-id="{{ $cliente->id }}"
        data-nombre="{{ $cliente->nombre }}"
        data-correo="{{ $cliente->correo }}"
        data-telefono="{{ $cliente->telefono }}"
        data-direccion="{{ $cliente->direccion }}"
        class="inline-flex items-center rounded-lg bg-indigo-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-indigo-500"
    >
        Editar
    </button>
    <button
        type="button"
        onclick="openDeleteModal(this)"
        data-id="{{ $cliente->id }}"
        data-nombre="{{ $cliente->nombre }}"
        class="inline-flex items-center rounded-lg bg-red-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-red-500"
    >
        Eliminar
    </button>
</div>