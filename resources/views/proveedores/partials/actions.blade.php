<div class="flex justify-center gap-2">
    <button
        type="button"
        onclick="openEditModal(this)"
        data-id="{{ $proveedor->id }}"
        data-nombre-empresa="{{ $proveedor->nombre_empresa }}"
        data-nombre-contacto="{{ $proveedor->nombre_contacto }}"
        data-correo="{{ $proveedor->correo }}"
        data-telefono="{{ $proveedor->telefono }}"
        data-direccion="{{ $proveedor->direccion }}"
        class="inline-flex items-center rounded-lg bg-indigo-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-indigo-500"
    >
        Editar
    </button>
    <button
        type="button"
        onclick="openDeleteModal(this)"
        data-id="{{ $proveedor->id }}"
        data-nombre="{{ $proveedor->nombre_empresa }}"
        class="inline-flex items-center rounded-lg bg-red-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-red-500"
    >
        Eliminar
    </button>
</div>