<x-admin-layout title="Existencias">
    <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
        <div>
            <h1 class="text-xl font-semibold">Existencias</h1>
            <p class="mt-1 text-sm text-gray-500">Controla el stock de cada producto en cada almacén.</p>
        </div>
        <button
            type="button"
            onclick="openRegisterModal()"
            class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500"
        >
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
            </svg>
            Registrar existencia
        </button>
    </div>

    <div class="rounded-xl bg-white p-4 shadow md:p-6">
        <table id="existencias-table" class="w-full">
            <thead>
                <tr>
                    <th>Producto</th>
                    <th>Almacén</th>
                    <th>Cantidad</th>
                    <th>Últ. actualización</th>
                    <th>Acciones</th>
                </tr>
            </thead>
        </table>
    </div>

    {{-- Modal registrar --}}
    <div id="register-modal" class="fixed inset-0 z-50 hidden items-center justify-center p-4">
        <div class="absolute inset-0 bg-black/50" onclick="closeModal('register-modal')"></div>
        <div class="relative flex max-h-[90vh] w-full max-w-3xl flex-col overflow-hidden rounded-2xl bg-white shadow-xl">
            <div id="register-overlay" class="absolute inset-0 z-10 hidden items-center justify-center rounded-2xl bg-white/70">
                <div class="flex items-center gap-3 rounded-lg bg-gray-900 px-5 py-3 text-sm font-medium text-white">
                    <span class="h-5 w-5 animate-spin rounded-full border-2 border-white border-t-transparent"></span>
                    Procesando...
                </div>
            </div>
            <div class="flex items-center justify-between border-b border-gray-200 px-6 py-4">
                <h2 class="text-lg font-semibold">Registrar existencia</h2>
                <button type="button" onclick="closeModal('register-modal')" class="rounded-lg p-1 text-gray-400 hover:bg-gray-100 hover:text-gray-600">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <form id="register-form" action="{{ route('existencias.store') }}" method="POST" class="min-h-0 flex-1 space-y-4 overflow-y-auto px-6 py-5">
                @csrf
                <div id="register-errors" class="hidden rounded-lg bg-red-50 px-4 py-3 text-sm text-red-700"></div>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div class="md:col-span-2">
                        <label for="register-producto-id" class="block text-sm font-medium">Producto</label>
                        <div class="mt-1 flex gap-2">
                            <select
                                id="register-producto-id"
                                name="producto_id"
                                required
                                class="flex-1 rounded-lg border border-gray-300 bg-white px-3 py-2 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                            ></select>
                            <button
                                type="button"
                                onclick="openNuevoProductoModal()"
                                class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500"
                                title="Crear un producto nuevo"
                            >
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                                </svg>
                                Nuevo
                            </button>
                        </div>
                        <p class="mt-1 text-xs text-gray-500">Si el producto no existe, créalo aquí y quedará seleccionado.</p>
                    </div>

                    <div>
                        <label for="register-almacen-id" class="block text-sm font-medium">Almacén</label>
                        <select
                            id="register-almacen-id"
                            name="almacen_id"
                            required
                            class="mt-1 w-full rounded-lg border border-gray-300 bg-white px-3 py-2 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                        ></select>
                    </div>

                    <div>
                        <label for="register-cantidad" class="block text-sm font-medium">Cantidad</label>
                        <input
                            id="register-cantidad"
                            type="number"
                            name="cantidad"
                            required
                            min="0"
                            step="1"
                            class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                        >
                    </div>
                </div>

                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" onclick="closeModal('register-modal')" class="rounded-lg bg-gray-100 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-200">
                        Cancelar
                    </button>
                    <button type="button" onclick="submitRegister()" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">
                        Registrar
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Modal editar --}}
    <div id="edit-modal" class="fixed inset-0 z-50 hidden items-center justify-center p-4">
        <div class="absolute inset-0 bg-black/50" onclick="closeModal('edit-modal')"></div>
        <div class="relative flex max-h-[90vh] w-full max-w-lg flex-col overflow-hidden rounded-2xl bg-white shadow-xl">
            <div id="edit-overlay" class="absolute inset-0 z-10 hidden items-center justify-center rounded-2xl bg-white/70">
                <div class="flex items-center gap-3 rounded-lg bg-gray-900 px-5 py-3 text-sm font-medium text-white">
                    <span class="h-5 w-5 animate-spin rounded-full border-2 border-white border-t-transparent"></span>
                    Procesando...
                </div>
            </div>
            <div class="flex items-center justify-between border-b border-gray-200 px-6 py-4">
                <h2 class="text-lg font-semibold">Editar existencia</h2>
                <button type="button" onclick="closeModal('edit-modal')" class="rounded-lg p-1 text-gray-400 hover:bg-gray-100 hover:text-gray-600">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <form id="edit-form" action="" method="POST" class="min-h-0 flex-1 space-y-4 overflow-y-auto px-6 py-5">
                @csrf
                <input type="hidden" name="_method" value="PUT">
                <div id="edit-errors" class="hidden rounded-lg bg-red-50 px-4 py-3 text-sm text-red-700"></div>

                <div>
                    <label for="edit-producto-id" class="block text-sm font-medium">Producto</label>
                    <select
                        id="edit-producto-id"
                        name="producto_id"
                        required
                        class="mt-1 w-full rounded-lg border border-gray-300 bg-white px-3 py-2 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                    ></select>
                </div>

                <div>
                    <label for="edit-almacen-id" class="block text-sm font-medium">Almacén</label>
                    <select
                        id="edit-almacen-id"
                        name="almacen_id"
                        required
                        class="mt-1 w-full rounded-lg border border-gray-300 bg-white px-3 py-2 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                    ></select>
                </div>

                <div>
                    <label for="edit-cantidad" class="block text-sm font-medium">Cantidad</label>
                    <input
                        id="edit-cantidad"
                        type="number"
                        name="cantidad"
                        required
                        min="0"
                        step="1"
                        class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                    >
                </div>

                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" onclick="closeModal('edit-modal')" class="rounded-lg bg-gray-100 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-200">
                        Cancelar
                    </button>
                    <button type="button" onclick="submitEdit()" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">
                        Guardar
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Modal nuevo producto rápido --}}
    <div id="nuevo-producto-modal" class="fixed inset-0 z-50 hidden items-center justify-center p-4">
        <div class="absolute inset-0 bg-black/50" onclick="closeModal('nuevo-producto-modal')"></div>
        <div class="relative flex max-h-[90vh] w-full max-w-3xl flex-col overflow-hidden rounded-2xl bg-white shadow-xl">
            <div id="nuevo-producto-overlay" class="absolute inset-0 z-10 hidden items-center justify-center rounded-2xl bg-white/70">
                <div class="flex items-center gap-3 rounded-lg bg-gray-900 px-5 py-3 text-sm font-medium text-white">
                    <span class="h-5 w-5 animate-spin rounded-full border-2 border-white border-t-transparent"></span>
                    Guardando...
                </div>
            </div>
            <div class="flex items-center justify-between border-b border-gray-200 px-6 py-4">
                <h2 class="text-lg font-semibold">Nuevo producto</h2>
                <button type="button" onclick="closeModal('nuevo-producto-modal')" class="rounded-lg p-1 text-gray-400 hover:bg-gray-100 hover:text-gray-600">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <form id="nuevo-producto-form" class="min-h-0 flex-1 space-y-4 overflow-y-auto px-6 py-5">
                @csrf
                <div id="nuevo-producto-errors" class="hidden rounded-lg bg-red-50 px-4 py-3 text-sm text-red-700"></div>

                <input type="hidden" name="moneda_id" id="nuevo-producto-moneda-id" value="{{ $monedaBaseId }}">

                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div>
                        <label for="nuevo-producto-sku" class="block text-sm font-medium">SKU</label>
                        <input
                            id="nuevo-producto-sku"
                            type="text"
                            name="sku"
                            required
                            maxlength="50"
                            autocomplete="off"
                            class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                        >
                    </div>

                    <div>
                        <label for="nuevo-producto-nombre" class="block text-sm font-medium">Nombre</label>
                        <input
                            id="nuevo-producto-nombre"
                            type="text"
                            name="nombre"
                            required
                            maxlength="150"
                            autocomplete="off"
                            class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                        >
                    </div>

                    <div>
                        <label for="nuevo-producto-categoria-id" class="block text-sm font-medium">Categoría</label>
                        <select
                            id="nuevo-producto-categoria-id"
                            name="categoria_id"
                            required
                            class="mt-1 w-full rounded-lg border border-gray-300 bg-white px-3 py-2 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                        ></select>
                    </div>

                    <div>
                        <label for="nuevo-producto-unidad-medida" class="block text-sm font-medium">Unidad de medida</label>
                        <select
                            id="nuevo-producto-unidad-medida"
                            name="unidad_medida"
                            required
                            class="mt-1 w-full rounded-lg border border-gray-300 bg-white px-3 py-2 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                        >
                            <option value="PZA">PZA (Unidad)</option>
                            <option value="CAJA">CAJA</option>
                            <option value="LT">LT (Litro)</option>
                            <option value="KG">KG (Kilogramo)</option>
                            <option value="UND">UND (Und.)</option>
                        </select>
                    </div>

                    <div>
                        <label for="nuevo-producto-precio-costo" class="block text-sm font-medium">Precio de costo (USD)</label>
                        <input
                            id="nuevo-producto-precio-costo"
                            type="number"
                            name="precio_costo"
                            required
                            min="0"
                            step="0.01"
                            class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                        >
                    </div>

                    <div>
                        <label for="nuevo-producto-precio-venta" class="block text-sm font-medium">Precio de venta (USD)</label>
                        <input
                            id="nuevo-producto-precio-venta"
                            type="number"
                            name="precio_venta"
                            required
                            min="0"
                            step="0.01"
                            class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                        >
                    </div>
                </div>

                <p class="text-xs text-gray-500">
                    Los precios se registran en dólares. El precio en bolívares se calcula con la tasa de cambio vigente.
                </p>

                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" onclick="closeModal('nuevo-producto-modal')" class="rounded-lg bg-gray-100 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-200">
                        Cancelar
                    </button>
                    <button type="button" onclick="guardarNuevoProducto()" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">
                        Guardar producto
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Modal eliminar --}}
    <div id="delete-modal" class="fixed inset-0 z-50 hidden items-center justify-center p-4">
        <div class="absolute inset-0 bg-black/50" onclick="closeModal('delete-modal')"></div>
        <div class="relative w-full max-w-sm rounded-2xl bg-white shadow-xl">
            <div id="delete-overlay" class="absolute inset-0 z-10 hidden items-center justify-center rounded-2xl bg-white/70">
                <div class="flex items-center gap-3 rounded-lg bg-gray-900 px-5 py-3 text-sm font-medium text-white">
                    <span class="h-5 w-5 animate-spin rounded-full border-2 border-white border-t-transparent"></span>
                    Eliminando...
                </div>
            </div>
            <form id="delete-form" method="POST" class="p-6">
                @csrf
                <input type="hidden" name="_method" value="DELETE">
                <h2 class="text-lg font-semibold">Eliminar existencia</h2>
                <p class="mt-2 text-sm text-gray-600">
                    ¿Estás seguro de que deseas eliminar las existencias de <strong id="delete-producto-nombre"></strong>? Esta acción no se puede deshacer.
                </p>
                <div class="mt-6 flex justify-end gap-3">
                    <button type="button" onclick="closeModal('delete-modal')" class="rounded-lg bg-gray-100 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-200">
                        Cancelar
                    </button>
                    <button type="button" onclick="confirmDelete()" class="rounded-lg bg-red-600 px-4 py-2 text-sm font-semibold text-white hover:bg-red-500">
                        Eliminar
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div id="toast" class="pointer-events-none fixed bottom-6 right-6 z-[60] hidden rounded-lg bg-gray-900 px-4 py-3 text-sm font-medium text-white shadow-lg"></div>

    @push('scripts')
        <link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/jquery.dataTables.min.css">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
        <style>
        .select2-container .select2-selection--single {
            height: 2.5rem;
            border: 1px solid #d1d5db;
            border-radius: 0.5rem;
            background: #fff;
        }
        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 2.5rem;
            padding-left: 0.75rem;
            color: #111827;
        }
        .select2-container--default .select2-selection--single .select2-selection__arrow {
            top: 8px;
            color: #6b7280;
        }
        .select2-container--default .select2-dropdown {
            border: 1px solid #d1d5db;
            border-radius: 0.5rem;
            overflow: hidden;
        }
        .select2-container--default .select2-search--dropdown .select2-search__field {
            border: 1px solid #d1d5db;
            border-radius: 0.5rem;
            padding: 0.5rem;
            width: 100%;
        }
        .select2-container--default .select2-results__option--highlighted[aria-selected] {
            background: #eef2ff;
            color: #111827;
        }
        .select2-container.select2-error .select2-selection--single {
            border-color: #ef4444;
            box-shadow: 0 0 0 1px #ef4444;
        }
        </style>
        <style>
        #existencias-table_wrapper .dataTables_filter input {
            padding: 0.5rem 0.75rem;
            border: 1px solid #d1d5db;
            border-radius: 0.5rem;
            outline: none;
            margin-left: 0.5rem;
        }
        #existencias-table_wrapper .dataTables_filter input:focus {
            border-color: #6366f1;
            box-shadow: 0 0 0 2px rgb(99 102 241 / 0.2);
        }
        #existencias-table_wrapper .dataTables_length select {
            padding: 0.5rem 0.75rem;
            border: 1px solid #d1d5db;
            border-radius: 0.5rem;
            outline: none;
            margin: 0 0.25rem;
        }
        #existencias-table_wrapper .dataTables_paginate .paginate_button {
            padding: 0.375rem 0.75rem;
            margin-left: 0.25rem;
            border-radius: 0.5rem;
            border: 1px solid #e5e7eb;
            color: #374151 !important;
            background: #fff;
            cursor: pointer;
        }
        #existencias-table_wrapper .dataTables_paginate .paginate_button:hover:not(.disabled) {
            background: #eef2ff;
            border-color: #6366f1;
            color: #4338ca !important;
        }
        #existencias-table_wrapper .dataTables_paginate .paginate_button.current {
            background: #6366f1;
            border-color: #6366f1;
            color: #fff !important;
        }
        #existencias-table_wrapper .dataTables_paginate .paginate_button.disabled {
            cursor: not-allowed;
            color: #9ca3af !important;
        }
        #existencias-table_wrapper .dataTables_processing {
            color: #374151;
        }
    </style>
        <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/i18n/es.js"></script>
        <script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
        <script>
        const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

        // Tabla de existencias, se recarga al registrar, editar o eliminar.
        let dataTable = null;

        function recargarTabla() {
            if (dataTable) {
                dataTable.ajax.reload();
            }
        }

        const almacenOptions = @json($listaAlmacenes);
        const categoriaOptions = @json($listaCategorias);
        const monedaBaseId = @json($monedaBaseId);

        function toast(message) {
            const el = document.getElementById('toast');
            el.textContent = message;
            el.classList.remove('hidden');
            setTimeout(() => el.classList.add('hidden'), 3000);
        }

        function openModal(modalId) {
            const modal = document.getElementById(modalId);
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }

        function closeModal(modalId) {
            const modal = document.getElementById(modalId);
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }

        function showOverlay(overlayId) {
            const overlay = document.getElementById(overlayId);
            overlay.classList.remove('hidden');
            overlay.classList.add('flex');
        }

        function hideOverlay(overlayId) {
            const overlay = document.getElementById(overlayId);
            overlay.classList.add('hidden');
            overlay.classList.remove('flex');
        }

        function renderErrors(containerId, errors) {
            const container = document.getElementById(containerId);
            container.innerHTML = '';
            if (!errors) {
                container.classList.add('hidden');
                return;
            }
            const items = Object.values(errors).flat().map((msg) => '<li>' + msg + '</li>').join('');
            container.innerHTML = '<ul class="list-disc space-y-1 pl-4">' + items + '</ul>';
            container.classList.remove('hidden');
        }

        function clearErrors(containerId) {
            renderErrors(containerId, null);
        }

        /**
         * Marca los campos que fallaron para que el usuario los identifique.
         */
        function marcarCamposInvalidos(form, errors) {
            form.querySelectorAll('[name]').forEach((input) => {
                input.classList.remove('border-red-500', 'focus:border-red-500');
                $(input).next('.select2-container').removeClass('select2-error');
            });

            Object.keys(errors || {}).forEach((key) => {
                const input = form.querySelector(`[name="${key}"]`);

                if (!input) {
                    return;
                }

                const contenedor = $(input).next('.select2-container');

                if (contenedor.length) {
                    contenedor.addClass('select2-error');
                } else {
                    input.classList.add('border-red-500', 'focus:border-red-500');
                }
            });
        }

        function buildOptions(selectEl, options, selectedValue, placeholder) {
            selectEl.innerHTML = '';

            if (placeholder) {
                const placeholderOption = document.createElement('option');
                placeholderOption.value = '';
                placeholderOption.textContent = placeholder;
                selectEl.appendChild(placeholderOption);
            }

            Object.entries(options).forEach(([id, label]) => {
                const option = document.createElement('option');
                option.value = id;
                option.textContent = label;
                selectEl.appendChild(option);
            });

            selectEl.value = selectedValue || '';
        }

        $(function () {
            $('#register-producto-id, #edit-producto-id').select2({
                placeholder: 'Busca un producto…',
                allowClear: true,
                language: 'es',
                minimumInputLength: 0,
                width: '100%',
                ajax: {
                    url: '{{ route('existencias.productos') }}',
                    dataType: 'json',
                    delay: 250,
                    cache: true,
                    data: function (params) {
                        return { q: params.term || '' };
                    },
                    processResults: function (data) {
                        return { results: data };
                    },
                },
            });
        });

        window.openNuevoProductoModal = function () {
            const form = document.getElementById('nuevo-producto-form');

            form.reset();
            form.elements['moneda_id'].value = monedaBaseId;
            buildOptions(document.getElementById('nuevo-producto-categoria-id'), categoriaOptions, '', 'Selecciona una categoría');
            clearErrors('nuevo-producto-errors');
            openModal('nuevo-producto-modal');
        };

        async function guardarNuevoProducto() {
            const form = document.getElementById('nuevo-producto-form');
            const formData = new FormData(form);
            const overlayId = 'nuevo-producto-overlay';

            showOverlay(overlayId);

            try {
                const response = await fetch('/productos', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                    },
                    body: formData,
                });

                const data = await response.json();

                if (!response.ok) {
                    if (data.errors) {
                        renderErrors('nuevo-producto-errors', data.errors);
                    } else if (response.status === 403) {
                        renderErrors('nuevo-producto-errors', {
                            general: ['No tienes permiso para registrar productos.'],
                        });
                    } else {
                        renderErrors('nuevo-producto-errors', {
                            general: [data.message || 'No se pudo crear el producto.'],
                        });
                    }

                    return;
                }

                closeModal('nuevo-producto-modal');
                toast(data.message);

                ['register', 'edit'].forEach((modo) => {
                    const select = document.getElementById(modo + '-producto-id');

                    if (!select) {
                        return;
                    }

                    select.add(new Option(
                        `${formData.get('sku')} — ${formData.get('nombre')}`,
                        data.producto_id,
                        false,
                        false
                    ));
                    $(select).val(data.producto_id).trigger('change');
                });
            } catch (e) {
                toast('Error al crear el producto.');
            } finally {
                hideOverlay(overlayId);
            }
        }

        window.openRegisterModal = function () {
            const form = document.getElementById('register-form');

            form.reset();
            $('#register-producto-id').val(null).trigger('change');
            buildOptions(document.getElementById('register-almacen-id'), almacenOptions, '', 'Selecciona un almacén');
            clearErrors('register-errors');
            marcarCamposInvalidos(form, {});
            openModal('register-modal');
        };

        window.openEditModal = function (button) {
            const form = document.getElementById('edit-form');
            form.elements['cantidad'].value = button.dataset.cantidad || '0';

            const productoSelect = form.elements['producto_id'];
            $(productoSelect).empty();
            if (button.dataset.producto) {
                $(productoSelect).append(
                    new Option(button.dataset.productoNombre || 'Producto', button.dataset.producto, true, true)
                ).trigger('change');
            }
            buildOptions(form.elements['almacen_id'], almacenOptions, button.dataset.almacen, 'Selecciona un almacén');
            form.action = '/existencias/' + button.dataset.id;
            clearErrors('edit-errors');
            marcarCamposInvalidos(form, {});
            openModal('edit-modal');
        };

        window.openDeleteModal = function (button) {
            document.getElementById('delete-form').action = '/existencias/' + button.dataset.id;
            document.getElementById('delete-producto-nombre').textContent = button.dataset.producto;
            openModal('delete-modal');
        };

        async function enviarFormulario(form, overlayId, successCallback) {
            showOverlay(overlayId);
            try {
                const response = await fetch(form.action, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                    },
                    body: new FormData(form),
                });

                const data = await response.json();

                if (!response.ok) {
                    renderErrors(form.id.replace('-form', '-errors'), data.errors);
                    marcarCamposInvalidos(form, data.errors);

                    if (data.errors) {
                        const firstKey = Object.keys(data.errors)[0];
                        toast(data.errors[firstKey][0]);
                    }

                    return;
                }

                toast(data.message);
                closeModal(form.id.replace('-form', '-modal'));
                recargarTabla();
            } finally {
                hideOverlay(overlayId);
            }
        }

        window.submitRegister = function () {
            enviarFormulario(document.getElementById('register-form'), 'register-overlay');
        };

        window.submitEdit = function () {
            enviarFormulario(document.getElementById('edit-form'), 'edit-overlay');
        };

        window.confirmDelete = async function () {
            const form = document.getElementById('delete-form');
            showOverlay('delete-overlay');
            try {
                const response = await fetch(form.action, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                    },
                    body: new FormData(form),
                });

                const data = await response.json();

                if (!response.ok) {
                    toast(data.message || 'No se pudo eliminar.');
                    closeModal('delete-modal');
                    return;
                }

                toast(data.message);
                closeModal('delete-modal');
                recargarTabla();
            } finally {
                hideOverlay('delete-overlay');
            }
        };

        dataTable = $('#existencias-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: '{{ route('existencias.data') }}',
                data: (d) => { d.draw = d.draw || 1; },
            },
            columns: [
                { data: 'producto' },
                { data: 'almacen' },
                { data: 'cantidad' },
                { data: 'ultima_actualizacion' },
                { data: 'actions', orderable: false, searchable: false },
            ],
            order: [[0, 'asc']],
            language: {
                processing: 'Procesando...',
                search: 'Buscar:',
                lengthMenu: 'Mostrar _MENU_ registros',
                info: 'Mostrando registros del _START_ al _END_ de un total de _TOTAL_ registros',
                infoEmpty: 'Mostrando registros del 0 al 0 de un total de 0 registros',
                infoFiltered: '(filtrado de un total de _MAX_ registros)',
                infoPostFix: '',
                loadingRecords: 'Cargando registros...',
                zeroRecords: 'No se encontraron registros',
                emptyTable: 'No hay datos disponibles en la tabla',
                decimal: ',',
                thousands: '.',
                paginate: {
                    first: 'Primero',
                    previous: 'Anterior',
                    next: 'Siguiente',
                    last: 'Último',
                },
                aria: {
                    sortAscending: ': Activar para ordenar la columna de manera ascendente',
                    sortDescending: ': Activar para ordenar la columna de manera descendente',
                },
            },
        });
        </script>
    @endpush
</x-admin-layout>