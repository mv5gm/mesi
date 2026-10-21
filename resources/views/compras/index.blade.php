<x-admin-layout title="Compras">
    <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
        <div>
            <h1 class="text-xl font-semibold">Compras</h1>
            <p class="mt-1 text-sm text-gray-500">Registra las órdenes de compra a tus proveedores.</p>
        </div>
        <button
            type="button"
            onclick="openRegisterModal()"
            class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500"
        >
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
            </svg>
            Nueva compra
        </button>
    </div>

    <div class="rounded-xl bg-white p-4 shadow md:p-6">
        <table id="compras-table" class="w-full">
            <thead>
                <tr>
                    <th>Nº</th>
                    <th>Proveedor</th>
                    <th>Fecha</th>
                    <th>Moneda</th>
                    <th>Total</th>
                    <th>Total base</th>
                    <th>Estado</th>
                    <th>Estado pago</th>
                    <th>Acciones</th>
                </tr>
            </thead>
        </table>
    </div>

    {{-- Modal registrar --}}
    <div id="register-modal" class="fixed inset-0 z-50 hidden items-center justify-center p-4">
        <div class="absolute inset-0 bg-black/50" onclick="closeModal('register-modal')"></div>
        <div class="relative flex max-h-[90vh] w-full max-w-4xl flex-col overflow-hidden rounded-2xl bg-white shadow-xl">
            <div id="register-overlay" class="absolute inset-0 z-10 hidden items-center justify-center rounded-2xl bg-white/70">
                <div class="flex items-center gap-3 rounded-lg bg-gray-900 px-5 py-3 text-sm font-medium text-white">
                    <span class="h-5 w-5 animate-spin rounded-full border-2 border-white border-t-transparent"></span>
                    Procesando...
                </div>
            </div>
            <div class="flex items-center justify-between border-b border-gray-200 px-6 py-4">
                <h2 class="text-lg font-semibold">Registrar compra</h2>
                <button type="button" onclick="closeModal('register-modal')" class="rounded-lg p-1 text-gray-400 hover:bg-gray-100 hover:text-gray-600">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <form id="register-form" action="{{ route('compras.store') }}" method="POST" class="flex min-h-0 flex-1 flex-col overflow-hidden">
                @csrf
                <div class="min-h-0 flex-1 space-y-4 overflow-y-auto px-6 py-5">
                    <div id="register-errors" class="hidden rounded-lg bg-red-50 px-4 py-3 text-sm text-red-700"></div>

                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <div>
                            <label for="register-proveedor-id" class="block text-sm font-medium">Proveedor</label>
                            <select
                                id="register-proveedor-id"
                                name="proveedor_id"
                                required
                                class="mt-1 w-full rounded-lg border border-gray-300 bg-white px-3 py-2 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                            ></select>
                        </div>
                        <div>
                            <label for="register-moneda-id" class="block text-sm font-medium">Moneda</label>
                            <select
                                id="register-moneda-id"
                                name="moneda_id"
                                required
                                class="mt-1 w-full rounded-lg border border-gray-300 bg-white px-3 py-2 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                            ></select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                        <div>
                            <label for="register-fecha-orden" class="block text-sm font-medium">Fecha de la orden</label>
                            <input
                                id="register-fecha-orden"
                                type="datetime-local"
                                name="fecha_orden"
                                required
                                class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                            >
                        </div>
                        <div>
                            <label for="register-tasa-cambio" class="block text-sm font-medium">Tasa de cambio</label>
                            <input
                                id="register-tasa-cambio"
                                type="number"
                                name="tasa_cambio"
                                required
                                min="0"
                                step="0.0001"
                                placeholder="1.0000"
                                class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                            >
                        </div>
                        <div>
                            <label for="register-estado" class="block text-sm font-medium">Estado</label>
                            <select id="register-estado" name="estado" required class="mt-1 w-full rounded-lg border border-gray-300 bg-white px-3 py-2 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                                <option value="PENDIENTE">PENDIENTE</option>
                                <option value="RECIBIDA">RECIBIDA</option>
                                <option value="CANCELADA">CANCELADA</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label for="register-estado-pago" class="block text-sm font-medium">Estado de pago</label>
                        <select id="register-estado-pago" name="estado_pago" required class="mt-1 w-full rounded-lg border border-gray-300 bg-white px-3 py-2 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                            <option value="PENDIENTE">PENDIENTE</option>
                            <option value="PARCIAL">PARCIAL</option>
                            <option value="PAGADO">PAGADO</option>
                        </select>
                    </div>

                    <div class="rounded-lg border border-gray-200 p-4">
                        <div class="mb-3 flex items-center justify-between">
                            <h3 class="text-sm font-semibold">Productos de la orden</h3>
                            <button type="button" onclick="agregarLinea('register-detalles-body')" class="rounded-lg bg-gray-100 px-3 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-200">
                                <svg class="mr-1 inline h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                                </svg>
                                Agregar línea
                            </button>
                        </div>
                        <table class="w-full">
                            <thead>
                                <tr class="text-left text-xs uppercase tracking-wide text-gray-500">
                                    <th class="py-1 pr-2 font-medium">Producto</th>
                                    <th class="w-24 px-2 py-1 font-medium">Cantidad</th>
                                    <th class="w-32 px-2 py-1 font-medium">Costo unitario</th>
                                    <th class="w-32 px-2 py-1 text-right font-medium">Subtotal</th>
                                    <th class="w-12 py-1"></th>
                                </tr>
                            </thead>
                            <tbody id="register-detalles-body"></tbody>
                        </table>
                    </div>

                    <div class="flex flex-col items-end gap-1 text-sm">
                        <div class="flex items-center gap-2">
                            <span class="text-gray-500">Total:</span>
                            <span id="register-total" class="text-base font-semibold">0,00</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="text-gray-500">Total en moneda base:</span>
                            <span id="register-total-base" class="text-base font-semibold">0,00</span>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end gap-3 border-t border-gray-200 px-6 py-4">
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
        <div class="relative flex max-h-[90vh] w-full max-w-4xl flex-col overflow-hidden rounded-2xl bg-white shadow-xl">
            <div id="edit-overlay" class="absolute inset-0 z-10 hidden items-center justify-center rounded-2xl bg-white/70">
                <div class="flex items-center gap-3 rounded-lg bg-gray-900 px-5 py-3 text-sm font-medium text-white">
                    <span class="h-5 w-5 animate-spin rounded-full border-2 border-white border-t-transparent"></span>
                    Procesando...
                </div>
            </div>
            <div class="flex items-center justify-between border-b border-gray-200 px-6 py-4">
                <h2 class="text-lg font-semibold">Editar compra</h2>
                <button type="button" onclick="closeModal('edit-modal')" class="rounded-lg p-1 text-gray-400 hover:bg-gray-100 hover:text-gray-600">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <form id="edit-form" action="" method="POST" class="flex min-h-0 flex-1 flex-col overflow-hidden">
                @csrf
                <input type="hidden" name="_method" value="PUT">
                <div class="min-h-0 flex-1 space-y-4 overflow-y-auto px-6 py-5">
                    <div id="edit-errors" class="hidden rounded-lg bg-red-50 px-4 py-3 text-sm text-red-700"></div>

                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <div>
                            <label for="edit-proveedor-id" class="block text-sm font-medium">Proveedor</label>
                            <select
                                id="edit-proveedor-id"
                                name="proveedor_id"
                                required
                                class="mt-1 w-full rounded-lg border border-gray-300 bg-white px-3 py-2 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                            ></select>
                        </div>
                        <div>
                            <label for="edit-moneda-id" class="block text-sm font-medium">Moneda</label>
                            <select
                                id="edit-moneda-id"
                                name="moneda_id"
                                required
                                class="mt-1 w-full rounded-lg border border-gray-300 bg-white px-3 py-2 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                            ></select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                        <div>
                            <label for="edit-fecha-orden" class="block text-sm font-medium">Fecha de la orden</label>
                            <input
                                id="edit-fecha-orden"
                                type="datetime-local"
                                name="fecha_orden"
                                required
                                class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                            >
                        </div>
                        <div>
                            <label for="edit-tasa-cambio" class="block text-sm font-medium">Tasa de cambio</label>
                            <input
                                id="edit-tasa-cambio"
                                type="number"
                                name="tasa_cambio"
                                required
                                min="0"
                                step="0.0001"
                                class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                            >
                        </div>
                        <div>
                            <label for="edit-estado" class="block text-sm font-medium">Estado</label>
                            <select id="edit-estado" name="estado" required class="mt-1 w-full rounded-lg border border-gray-300 bg-white px-3 py-2 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                                <option value="PENDIENTE">PENDIENTE</option>
                                <option value="RECIBIDA">RECIBIDA</option>
                                <option value="CANCELADA">CANCELADA</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label for="edit-estado-pago" class="block text-sm font-medium">Estado de pago</label>
                        <select id="edit-estado-pago" name="estado_pago" required class="mt-1 w-full rounded-lg border border-gray-300 bg-white px-3 py-2 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                            <option value="PENDIENTE">PENDIENTE</option>
                            <option value="PARCIAL">PARCIAL</option>
                            <option value="PAGADO">PAGADO</option>
                        </select>
                    </div>

                    <div class="rounded-lg border border-gray-200 p-4">
                        <div class="mb-3 flex items-center justify-between">
                            <h3 class="text-sm font-semibold">Productos de la orden</h3>
                            <button type="button" onclick="agregarLinea('edit-detalles-body')" class="rounded-lg bg-gray-100 px-3 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-200">
                                <svg class="mr-1 inline h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                                </svg>
                                Agregar línea
                            </button>
                        </div>
                        <table class="w-full">
                            <thead>
                                <tr class="text-left text-xs uppercase tracking-wide text-gray-500">
                                    <th class="py-1 pr-2 font-medium">Producto</th>
                                    <th class="w-24 px-2 py-1 font-medium">Cantidad</th>
                                    <th class="w-32 px-2 py-1 font-medium">Costo unitario</th>
                                    <th class="w-32 px-2 py-1 text-right font-medium">Subtotal</th>
                                    <th class="w-12 py-1"></th>
                                </tr>
                            </thead>
                            <tbody id="edit-detalles-body"></tbody>
                        </table>
                    </div>

                    <div class="flex flex-col items-end gap-1 text-sm">
                        <div class="flex items-center gap-2">
                            <span class="text-gray-500">Total:</span>
                            <span id="edit-total" class="text-base font-semibold">0,00</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="text-gray-500">Total en moneda base:</span>
                            <span id="edit-total-base" class="text-base font-semibold">0,00</span>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end gap-3 border-t border-gray-200 px-6 py-4">
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
                <h2 class="text-lg font-semibold">Eliminar compra</h2>
                <p class="mt-2 text-sm text-gray-600">
                    ¿Estás seguro de que deseas eliminar la orden <strong id="delete-orden-numero"></strong>? Se eliminarán también sus productos y sus pagos. Esta acción no se puede deshacer.
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
        <style>
        #compras-table_wrapper .dataTables_filter input {
            padding: 0.5rem 0.75rem;
            border: 1px solid #d1d5db;
            border-radius: 0.5rem;
            outline: none;
            margin-left: 0.5rem;
        }
        #compras-table_wrapper .dataTables_filter input:focus {
            border-color: #6366f1;
            box-shadow: 0 0 0 2px rgb(99 102 241 / 0.2);
        }
        #compras-table_wrapper .dataTables_length select {
            padding: 0.5rem 0.75rem;
            border: 1px solid #d1d5db;
            border-radius: 0.5rem;
            outline: none;
            margin: 0 0.25rem;
        }
        #compras-table_wrapper .dataTables_paginate .paginate_button {
            padding: 0.375rem 0.75rem;
            margin-left: 0.25rem;
            border-radius: 0.5rem;
            border: 1px solid #e5e7eb;
            color: #374151 !important;
            background: #fff;
            cursor: pointer;
        }
        #compras-table_wrapper .dataTables_paginate .paginate_button:hover:not(.disabled) {
            background: #eef2ff;
            border-color: #6366f1;
            color: #4338ca !important;
        }
        #compras-table_wrapper .dataTables_paginate .paginate_button.current {
            background: #6366f1;
            border-color: #6366f1;
            color: #fff !important;
        }
        #compras-table_wrapper .dataTables_paginate .paginate_button.disabled {
            cursor: not-allowed;
            color: #9ca3af !important;
        }
        #compras-table_wrapper .dataTables_processing {
            color: #374151;
        }
        #register-detalles-body input[type="number"], #edit-detalles-body input[type="number"] {
            border: 1px solid #d1d5db;
            border-radius: 0.5rem;
            padding: 0.375rem 0.625rem;
            outline: none;
        }
        #register-detalles-body input[type="number"]:focus, #edit-detalles-body input[type="number"]:focus {
            border-color: #6366f1;
        }
        #register-detalles-body select, #edit-detalles-body select {
            border: 1px solid #d1d5db;
            border-radius: 0.5rem;
            padding: 0.375rem 0.625rem;
            background: #fff;
            outline: none;
        }
    </style>
        <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
        <script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
        <script>
        const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

        // Tabla de compras, se recarga al registrar, editar o eliminar una orden.
        let dataTable = null;

        function recargarTabla() {
            if (dataTable) {
                dataTable.ajax.reload();
            }
        }

        const proveedorOptions = @json($listaProveedores);
        const monedaOptions = @json($listaMonedas->mapWithKeys(fn ($moneda) => [$moneda->id => "{$moneda->codigo} — {$moneda->nombre}"]));
        const productoOptions = @json($listaProductos->mapWithKeys(fn ($producto) => [$producto->id => "{$producto->sku} — {$producto->nombre}"]));
        const monedaBaseId = @json($monedaBaseId);

        // Tasas vigentes: cuántas unidades de cada moneda equivalen a 1 dólar.
        const tasasVigentes = @json($tasasVigentes);

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

        function formatoNumero(valor) {
            return parseFloat(valor || 0).toLocaleString('es-VE', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        }

        function lineaHTML(productoId, cantidad, unitario, bodyId) {
            return `
                <tr>
                    <td class="py-1 pr-2">
                        <select name="detalles[][producto_id]" required class="w-full">
                            ${productoOptionHTML(productoId)}
                        </select>
                    </td>
                    <td class="px-2 py-1">
                        <input type="number" name="detalles[][cantidad]" required min="1" step="1" value="${cantidad || ''}" class="w-full">
                    </td>
                    <td class="px-2 py-1">
                        <input type="number" name="detalles[][costo_unitario]" required min="0" step="0.01" value="${unitario || ''}" class="w-full" placeholder="0.00">
                    </td>
                    <td class="px-2 py-1 text-right font-medium subtotal">${formatoNumero(0)}</td>
                    <td class="w-12 py-1 text-right">
                        <button type="button" onclick="quitarLinea(this)" class="rounded-lg p-1 text-gray-400 hover:bg-red-50 hover:text-red-600">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </td>
                </tr>`;
        }

        function productoOptionHTML(productoIdSeleccionado) {
            let html = '<option value="">Selecciona un producto</option>';
            Object.entries(productoOptions).forEach(([id, label]) => {
                const selected = String(id) === String(productoIdSeleccionado) ? ' selected' : '';
                html += `<option value="${id}"${selected}>${label}</option>`;
            });
            return html;
        }

        function agregarLinea(bodyId) {
            const body = document.getElementById(bodyId);
            body.insertAdjacentHTML('beforeend', lineaHTML('', '', '', bodyId));
            recomputarTotales(bodyId);
        }

        function quitarLinea(button) {
            const body = button.closest('tbody');
            button.closest('tr').remove();
            recomputarTotales(body.id);
        }

        function recomputarTotales(bodyId) {
            const body = document.getElementById(bodyId);
            const modo = bodyId.startsWith('register') ? 'register' : 'edit';
            let total = 0;

            body.querySelectorAll('tr').forEach((row) => {
                const cantidad = parseFloat(row.querySelector('input[name="detalles[][cantidad]"]').value) || 0;
                const unitario = parseFloat(row.querySelector('input[name="detalles[][costo_unitario]"]').value) || 0;
                const subtotal = cantidad * unitario;
                row.querySelector('.subtotal').textContent = formatoNumero(subtotal);
                total += subtotal;
            });

            const tasa = parseFloat(document.getElementById(modo + '-tasa-cambio').value) || 0;
            document.getElementById(modo + '-total').textContent = formatoNumero(total);
            document.getElementById(modo + '-total-base').textContent = formatoNumero(tasa > 0 ? total / tasa : 0);
        }

        function configurarRecalculo() {
            ['register-detalles-body', 'edit-detalles-body'].forEach((bodyId) => {
                document.getElementById(bodyId).addEventListener('input', (event) => recomputarTotales(bodyId));
            });

            ['register-tasa-cambio', 'edit-tasa-cambio'].forEach((id) => {
                document.getElementById(id).addEventListener('input', () => {
                    const modo = id.startsWith('register') ? 'register' : 'edit';
                    recomputarTotales(modo + '-detalles-body');
                });
            });

            ['register-moneda-id', 'edit-moneda-id'].forEach((id) => {
                document.getElementById(id).addEventListener('change', (event) => {
                    const modo = id.startsWith('register') ? 'register' : 'edit';
                    const tasa = tasasVigentes[event.target.value] ?? 1;
                    document.getElementById(modo + '-tasa-cambio').value = tasa;
                    recomputarTotales(modo + '-detalles-body');
                });
            });
        }

        window.openRegisterModal = function () {
            document.getElementById('register-form').reset();
            const body = document.getElementById('register-detalles-body');
            body.innerHTML = '';
            buildOptions(document.getElementById('register-proveedor-id'), proveedorOptions, '', 'Selecciona un proveedor');
            buildOptions(document.getElementById('register-moneda-id'), monedaOptions, monedaBaseId, 'Selecciona una moneda');
            document.getElementById('register-fecha-orden').value = new Date().toISOString().slice(0, 16);
            document.getElementById('register-tasa-cambio').value = tasasVigentes[monedaBaseId] ?? 1;
            document.getElementById('register-estado').value = 'PENDIENTE';
            document.getElementById('register-estado-pago').value = 'PENDIENTE';
            agregarLinea('register-detalles-body');
            clearErrors('register-errors');
            openModal('register-modal');
        };

        window.openEditModal = function (button) {
            const form = document.getElementById('edit-form');
            const body = document.getElementById('edit-detalles-body');
            body.innerHTML = '';

            buildOptions(form.elements['proveedor_id'], proveedorOptions, button.dataset.proveedor, 'Selecciona un proveedor');
            buildOptions(form.elements['moneda_id'], monedaOptions, button.dataset.moneda, 'Selecciona una moneda');
            form.elements['fecha_orden'].value = button.dataset.fecha || '';
            form.elements['tasa_cambio'].value = button.dataset.tasa || '1';
            form.elements['estado'].value = button.dataset.estado || 'PENDIENTE';
            form.elements['estado_pago'].value = button.dataset.estadoPago || 'PENDIENTE';

            try {
                const detalles = JSON.parse(button.dataset.detalles || '[]');
                detalles.forEach((detalle) => {
                    body.insertAdjacentHTML('beforeend', lineaHTML(detalle.producto_id, detalle.cantidad, detalle.unitario, 'edit-detalles-body'));
                });
            } catch (e) {
                agregarLinea('edit-detalles-body');
            }

            if (!body.querySelectorAll('tr').length) {
                agregarLinea('edit-detalles-body');
            }

            form.action = '/compras/' + button.dataset.id;
            clearErrors('edit-errors');
            recomputarTotales('edit-detalles-body');
            openModal('edit-modal');
        };

        window.openDeleteModal = function (button) {
            document.getElementById('delete-form').action = '/compras/' + button.dataset.id;
            document.getElementById('delete-orden-numero').textContent = button.dataset.numero;
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

        configurarRecalculo();

        dataTable = $('#compras-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: '{{ route('compras.data') }}',
                data: (d) => { d.draw = d.draw || 1; },
            },
            columns: [
                { data: 'id' },
                { data: 'proveedor' },
                { data: 'fecha' },
                { data: 'moneda' },
                { data: 'total' },
                { data: 'total_base' },
                { data: 'estado' },
                { data: 'estado_pago' },
                { data: 'actions', orderable: false, searchable: false },
            ],
            order: [[0, 'desc']],
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.8/i18n/es-ES.json',
            },
        });
        </script>
    @endpush
</x-admin-layout>