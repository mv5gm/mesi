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

                    <div class="flex flex-col gap-2">
                        <label for="register-proveedor-id" class="block text-sm font-medium">Proveedor</label>
                        <div class="flex gap-2">
                            <select
                                id="register-proveedor-id"
                                name="proveedor_id"
                                required
                                class="flex-1"
                            ></select>
                            <button
                                type="button"
                                onclick="openNuevoProveedorModal()"
                                class="rounded-lg bg-indigo-600 px-3 py-2 text-xs font-semibold text-white hover:bg-indigo-500"
                                title="Agregar proveedor nuevo"
                            >
                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                                </svg>
                            </button>
                        </div>
                        <input type="hidden" name="moneda_id" id="register-moneda-id" value="{{ $monedaBaseId }}">
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
                            <select id="register-tasa-cambio" class="mt-1 w-full"></select>
                            <input type="hidden" name="tasa_cambio" id="register-tasa-cambio-valor" value="1">
                            <p class="mt-1 text-xs text-gray-500">Se preselecciona la última tasa registrada y se ajusta a la moneda del método de pago.</p>
                        </div>
                        <div>
                            <label for="register-estado" class="block text-sm font-medium">Estado</label>
                            <select id="register-estado" name="estado" required class="mt-1 w-full rounded-lg border border-gray-300 bg-white px-3 py-2 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                                <option value="PENDIENTE">PENDIENTE</option>
                                <option value="RECIBIDA" selected>RECIBIDA</option>
                                <option value="CANCELADA">CANCELADA</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label for="register-estado-pago" class="block text-sm font-medium">Estado de pago</label>
                        <select id="register-estado-pago" name="estado_pago" required class="mt-1 w-full rounded-lg border border-gray-300 bg-white px-3 py-2 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                            <option value="PENDIENTE">PENDIENTE</option>
                            <option value="PARCIAL">PARCIAL</option>
                            <option value="PAGADO" selected>PAGADO</option>
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

                    <div class="rounded-lg border border-gray-200 p-4 mt-4">
                        <div class="mb-3 flex items-center justify-between">
                            <h3 class="text-sm font-semibold">Pagos de la orden</h3>
                            <div class="flex items-center gap-3">
                                <span id="register-pagos-cobertura" class="text-xs font-medium text-gray-500">Cubierto: 0,00 de 0,00</span>
                                <button
                                    type="button"
                                    onclick="agregarPago('register-pagos-body')"
                                    class="rounded-lg bg-indigo-50 px-3 py-1.5 text-xs font-medium text-indigo-700 hover:bg-indigo-100"
                                >
                                    <svg class="mr-1 inline h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                                    </svg>
                                    Agregar pago
                                </button>
                            </div>
                        </div>
                        <table class="w-full">
                            <thead>
                                <tr class="text-left text-xs uppercase tracking-wide text-gray-500">
                                    <th class="py-1 pr-2 font-medium">Método de pago</th>
                                    <th class="w-32 px-2 py-1 font-medium">Monto</th>
                                    <th class="w-32 px-2 py-1 font-medium">Tasa</th>
                                    <th class="w-32 px-2 py-1 text-right font-medium">Monto base</th>
                                    <th class="w-32 px-2 py-1 font-medium">Referencia</th>
                                    <th class="w-24 px-2 py-1 font-medium">Fecha</th>
                                    <th class="w-12 py-1"></th>
                                </tr>
                            </thead>
                            <tbody id="register-pagos-body"></tbody>
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

    {{-- Modal nuevo proveedor rápido --}}
    <div id="nuevo-proveedor-modal" class="fixed inset-0 z-50 hidden items-center justify-center p-4">
        <div class="absolute inset-0 bg-black/50" onclick="closeModal('nuevo-proveedor-modal')"></div>
        <div class="relative w-full max-w-md bg-white rounded-2xl shadow-xl overflow-hidden">
            <div class="bg-gray-100 px-4 py-3 flex items-center justify-between">
                <h3 class="text-sm font-semibold">Nuevo proveedor</h3>
                <button type="button" onclick="closeModal('nuevo-proveedor-modal')" class="text-gray-400 hover:text-gray-600">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <form id="nuevo-proveedor-form" class="p-4 space-y-3">
                <div id="nuevo-proveedor-errors" class="hidden rounded-lg bg-red-50 px-3 py-2 text-xs text-red-700"></div>
                <div>
                    <label for="nuevo-proveedor-nombre_empresa" class="block text-sm font-medium">Empresa</label>
                    <input
                        id="nuevo-proveedor-nombre_empresa"
                        type="text"
                        name="nombre_empresa"
                        required
                        class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                    >
                </div>
                <div>
                    <label for="nuevo-proveedor-nombre_contacto" class="block text-sm font-medium">Nombre de contacto</label>
                    <input
                        id="nuevo-proveedor-nombre_contacto"
                        type="text"
                        name="nombre_contacto"
                        class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                    >
                </div>
                <div>
                    <label for="nuevo-proveedor-correo" class="block text-sm font-medium">Correo</label>
                    <input
                        id="nuevo-proveedor-correo"
                        type="email"
                        name="correo"
                        class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                    >
                </div>
                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" onclick="closeModal('nuevo-proveedor-modal')" class="rounded-lg bg-gray-100 px-3 py-1.5 text-sm text-gray-700 hover:bg-gray-200">
                        Cancelar
                    </button>
                    <button type="button" onclick="guardarNuevoProveedor()" class="rounded-lg bg-indigo-600 px-3 py-1.5 text-sm text-white hover:bg-indigo-500">
                        Guardar
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
    </style>
        <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/i18n/es.js"></script>
        <script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
        <script>
        const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
        let monedaOrdenId = null;

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
        const productoCostos = @json($listaProductos->mapWithKeys(fn ($producto) => [$producto->id => (float) $producto->precio_costo]));
        const monedaBaseId = @json($monedaBaseId);

        // Tasas vigentes: cuántas unidades de cada moneda equivalen a 1 dólar.
        const tasasVigentes = @json($tasasVigentes);
        const metodosPagoOptions = @json($metodosPagoOptions);
        const ultimaTasaInicial = @json($ultimaTasa);
        const ultimaTasaMonedaId = @json($ultimaTasaMonedaId);
        const defaultMonedaId = ultimaTasaMonedaId ?? monedaBaseId;
        const defaultTasa = ultimaTasaInicial ?? (tasasVigentes[monedaBaseId] ?? 1);

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

        function initSelect2Ajax(selectId, url, placeholder, dropdownParent) {
            const config = {
                placeholder: placeholder,
                allowClear: true,
                language: 'es',
                minimumInputLength: 0,
                width: '100%',
                ajax: {
                    url: url,
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
            };
            if (dropdownParent) {
                config.dropdownParent = dropdownParent;
            }
            return $(selectId).select2(config);
        }

        function lineaHTML(productoId, cantidad, unitario, bodyId, index) {
            return `
                <tr>
                    <td class="py-1 pr-2">
                        <select name="detalles[${index}][producto_id]" required class="w-full" onchange="onProductoChange(this)">
                            ${productoOptionHTML(productoId)}
                        </select>
                    </td>
                    <td class="px-2 py-1">
                        <input type="number" name="detalles[${index}][cantidad]" required min="1" step="1" value="${cantidad || ''}" class="w-full">
                    </td>
                    <td class="px-2 py-1">
                        <input type="number" name="detalles[${index}][costo_unitario]" required min="0" step="0.01" value="${unitario || ''}" class="w-full" placeholder="0.00">
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
            const index = body.querySelectorAll('tr').length;
            body.insertAdjacentHTML('beforeend', lineaHTML('', '', '', bodyId, index));
            recomputarTotales(bodyId);
        }

        function quitarLinea(button) {
            const body = button.closest('tbody');
            button.closest('tr').remove();
            renumerarCampos(body.id);
            recomputarTotales(body.id);
        }

        function eliminarFilasVacias(bodyId) {
            const body = document.getElementById(bodyId);
            if (!body) return;
            const rows = Array.from(body.querySelectorAll('tr'));
            rows.forEach((row) => {
                const productoSelect = row.querySelector('select[name*="[producto_id]"]');

                if (!productoSelect || productoSelect.value === '') {
                    row.remove();
                }
            });
        }

        function eliminarPagosVacias(bodyId) {
            const body = document.getElementById(bodyId);
            if (!body) return;
            const rows = Array.from(body.querySelectorAll('tr'));
            rows.forEach((row) => {
                const metodoSelect = row.querySelector('select[name*="[metodo_pago_id]"]');
                const montoInput = row.querySelector('input[name*="[monto]"]');
                const tasaInput = row.querySelector('input[name*="[tasa_cambio]"]');
                const fechaInput = row.querySelector('input[name*="[fecha_pago]"]');

                const estaVacia = !metodoSelect
                    || metodoSelect.value === ''
                    || !montoInput
                    || montoInput.value === ''
                    || !tasaInput
                    || tasaInput.value === ''
                    || !fechaInput
                    || fechaInput.value === '';

                if (estaVacia) {
                    row.remove();
                }
            });
        }

        function contarFilasValidas(bodyId) {
            const body = document.getElementById(bodyId);
            if (!body) return 0;
            let count = 0;
            body.querySelectorAll('tr').forEach((row) => {
                const productoSelect = row.querySelector('select[name*="[producto_id]"]');
                if (productoSelect && productoSelect.value !== '') {
                    count++;
                }
            });
            return count;
        }

        function onProductoChange(selectEl) {
            const row = selectEl.closest('tr');
            const body = row ? row.closest('tbody') : null;
            const costoInput = row ? row.querySelector('input[name*="costo_unitario"]') : null;
            const productoId = selectEl.value;

            if (costoInput && productoCostos && productoCostos[productoId] !== undefined) {
                costoInput.value = productoCostos[productoId];
            }

            if (body) {
                recomputarTotales(body.id);
            }
        }

        function recomputarTotales(bodyId) {
            const body = document.getElementById(bodyId);
            const modo = bodyId.startsWith('register') ? 'register' : 'edit';
            let total = 0;

            body.querySelectorAll('tr').forEach((row) => {
                const cantidad = parseFloat(row.querySelector('input[name*="[cantidad]"]').value) || 0;
                const unitario = parseFloat(row.querySelector('input[name*="[costo_unitario]"]').value) || 0;
                const subtotal = cantidad * unitario;
                row.querySelector('.subtotal').textContent = formatoNumero(subtotal);
                total += subtotal;
            });

            const tasaEl = document.getElementById(modo + '-tasa-cambio-valor') || document.getElementById(modo + '-tasa-cambio');
            const tasa = parseFloat(tasaEl ? tasaEl.value : '1') || 0;
            const totalEl = document.getElementById(modo + '-total');
            const totalMonedaOrden = tasa > 0 ? total * tasa : total;
            totalEl.textContent = formatoNumero(totalMonedaOrden);
            totalEl.dataset.valor = totalMonedaOrden;
            document.getElementById(modo + '-total-base').textContent = formatoNumero(total);

            sincronizarMontoUnico(modo);
            recomputarPagos(modo + '-pagos-body');
        }

        function initTasaSelect2(selectId, modo) {
            $(selectId).select2({
                placeholder: 'Busca una tasa…',
                allowClear: true,
                language: 'es',
                minimumInputLength: 0,
                width: '100%',
                ajax: {
                    url: '{{ route('tasas.select2') }}',
                    dataType: 'json',
                    delay: 250,
                    cache: true,
                    data: function (params) {
                        return {
                            q: params.term || '',
                            moneda_id: monedaOrdenId || '',
                        };
                    },
                    processResults: function (data) {
                        return { results: data || [] };
                    },
                    error: function () {
                        return { results: [] };
                    },
                },
            }).on('select2:select', function (e) {
                const tasa = e.params.data.tasa;
                document.getElementById(modo + '-tasa-cambio-valor').value = tasa;
                recomputarTotales(modo + '-detalles-body');
                recomputarPagos(modo + '-pagos-body');
            }).on('select2:clear', function () {
                document.getElementById(modo + '-tasa-cambio-valor').value = '1';
                recomputarTotales(modo + '-detalles-body');
                recomputarPagos(modo + '-pagos-body');
            });
        }

        function aplicarTasaOrden(modo, tasa) {
            if (!tasa) {
                return;
            }

            const select = $(document.getElementById(modo + '-tasa-cambio'));
            const etiqueta = tasa.codigo
                ? `${tasa.codigo} — ${tasa.tasa} (${tasa.fecha_efectiva})`
                : `${tasa.tasa} (${tasa.fecha_efectiva})`;

            select.append(new Option(etiqueta, tasa.id, true, true)).trigger('change');
            document.getElementById(modo + '-tasa-cambio-valor').value = tasa.tasa;
        }

        async function ultimaTasa(monedaId) {
            try {
                const url = '{{ route('tasas.ultima') }}' + (monedaId ? '?moneda_id=' + encodeURIComponent(monedaId) : '');
                const response = await fetch(url, { headers: { 'Accept': 'application/json' } });

                return await response.json();
            } catch (e) {
                return null;
            }
        }

        async function seleccionarUltimaTasa(modo) {
            const tasa = await ultimaTasa(null);

            aplicarTasaOrden(modo, tasa);
        }

        async function aplicarTasaDeLaMoneda(modo, metodo) {
            if (!metodo || !metodo.moneda_id || String(metodo.moneda_id) === String(monedaBaseId)) {
                $(document.getElementById(modo + '-tasa-cambio')).empty().trigger('change');
                document.getElementById(modo + '-tasa-cambio-valor').value = 1;

                return;
            }

            const tasa = await ultimaTasa(metodo.moneda_id);

            aplicarTasaOrden(modo, tasa);
        }

        function initMetodoPagoSelect2(selectEl) {
            $(selectEl).select2({
                placeholder: 'Selecciona un método de pago',
                allowClear: true,
                language: 'es',
                width: '100%',
            }).on('select2:select', function (e) {
                const row = selectEl.closest('tr');
                const modo = modoDeFila(row);
                const metodosAnteriores = pagosConMetodo(modo).length - 1;

                aplicarMonedaMetodo(row, e.params.data.id, metodosAnteriores);
            }).on('select2:clear', function () {
                const row = selectEl.closest('tr');
                const modo = modoDeFila(row);
                const label = row.querySelector('.moneda-pago-label');

                if (label) label.textContent = '';

                actualizarMontosDePagos(modo, pagosConMetodo(modo).length + 1);
            });
        }

        function etiquetaMoneda(metodoPagoId) {
            const metodo = metodosPagoOptions[String(metodoPagoId)];

            if (!metodo) {
                return '';
            }

            return `Moneda: ${metodo.moneda_codigo ?? '—'} — ${metodo.moneda_nombre ?? ''}`;
        }

        function actualizarEtiquetaMoneda(row, metodoPagoId) {
            const label = row.querySelector('.moneda-pago-label');

            if (label) {
                label.textContent = etiquetaMoneda(metodoPagoId);
            }
        }

        async function aplicarMonedaMetodo(row, metodoPagoId, metodosAnteriores) {
            const metodo = metodosPagoOptions[String(metodoPagoId)];
            const tasaInput = row.querySelector('input[name*="[tasa_cambio]"]');
            const montoInput = row.querySelector('input[name*="[monto]"]');
            const montoBaseSpan = row.querySelector('.monto-base-pago');

            actualizarEtiquetaMoneda(row, metodoPagoId);

            if (!metodo) {
                return;
            }

            let tasa = 1;

            if (metodo.moneda_id && String(metodo.moneda_id) !== String(monedaBaseId)) {
                const tasaCambio = await ultimaTasa(metodo.moneda_id);

                if (tasaCambio && tasaCambio.tasa !== undefined) {
                    tasa = tasaCambio.tasa;
                }
            }

            if (tasaInput) tasaInput.value = tasa;

            if (montoInput && montoBaseSpan) {
                montoBaseSpan.textContent = formatoNumero((parseFloat(montoInput.value) || 0) / tasa);
            }

            await heredarMonedaEnLaOrden(row, metodo, tasa);

            actualizarMontosDePagos(modoDeFila(row), metodosAnteriores);
        }

        async function heredarMonedaEnLaOrden(row, metodo, tasa) {
            const body = row.closest('tbody');

            if (!body || row !== body.querySelector('tr')) {
                return;
            }

            const modo = body.id.startsWith('register') ? 'register' : 'edit';

            monedaOrdenId = metodo.moneda_id;

            await aplicarTasaDeLaMoneda(modo, metodo);

            recomputarTotales(modo + '-detalles-body');
        }

        function pagoHTML(metodoPagoId, monto, tasa, montoBase, referencia, fecha, index) {
            const metodoOptionHTML = Object.entries(metodosPagoOptions).map(([id, data]) => {
                const selected = String(id) === String(metodoPagoId) ? ' selected' : '';
                const label = `${data.nombre} (${data.moneda_codigo ?? '—'})`;
                return `<option value="${id}"${selected}>${label}</option>`;
            }).join('');
            const monedaLabel = etiquetaMoneda(metodoPagoId);

            return `
                <tr>
                    <td class="py-1 pr-2">
                        <select name="pagos[${index}][metodo_pago_id]" required class="metodo-pago-select w-full">
                            <option value="">Selecciona un método</option>
                            ${metodoOptionHTML}
                        </select>
                        <p class="mt-1 text-xs text-gray-500 moneda-pago-label">${monedaLabel}</p>
                    </td>
                    <td class="px-2 py-1">
                        <input type="number" name="pagos[${index}][monto]" required min="0" step="0.01" value="${monto || ''}" class="w-full monto-pago-input" placeholder="0.00">
                    </td>
                    <td class="px-2 py-1">
                        <input type="number" name="pagos[${index}][tasa_cambio]" required min="0" step="0.0001" value="${tasa || ''}" class="w-full tasa-pago-input" placeholder="0.0000">
                    </td>
                    <td class="px-2 py-1 text-right font-medium">
                        <span class="monto-base-pago">${formatoNumero(montoBase || 0)}</span>
                    </td>
                    <td class="px-2 py-1">
                        <input type="text" name="pagos[${index}][numero_referencia]" value="${referencia || ''}" class="w-full referencia-pago-input" placeholder="Referencia">
                    </td>
                    <td class="px-2 py-1">
                        <input type="datetime-local" name="pagos[${index}][fecha_pago]" required value="${fecha || ''}" class="w-full fecha-pago-input">
                    </td>
                    <td class="w-12 py-1 text-right">
                        <button type="button" onclick="quitarPago(this)" class="rounded-lg p-1 text-gray-400 hover:bg-red-50 hover:text-red-600">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </td>
                </tr>`;
        }

        function renumerarCampos(bodyId, prefix) {
            const body = document.getElementById(bodyId);
            if (!body) return;
            body.querySelectorAll('tr').forEach((row, index) => {
                row.querySelectorAll('[name]').forEach((input) => {
                    const name = input.getAttribute('name');
                    const newName = name.replace(/\[\d+\]/g, `[${index}]`);
                    input.setAttribute('name', newName);
                });
            });
        }

        function agregarPago(bodyId) {
            const body = document.getElementById(bodyId);
            const index = body.querySelectorAll('tr').length;
            const fechaHoy = new Date().toISOString().slice(0, 16);
            const tasaInput = document.getElementById(bodyId.startsWith('register') ? 'register-tasa-cambio-valor' : 'edit-tasa-cambio-valor');
            const tasa = tasaInput ? tasaInput.value : '1';
            body.insertAdjacentHTML('beforeend', pagoHTML('', '', tasa, '', '', fechaHoy, index));
            const row = body.querySelector('tr:last-child');
            const metodoSelect = row.querySelector('select.metodo-pago-select');
            initMetodoPagoSelect2(metodoSelect);
            configurarPagoListeners(row);
            recomputarPagos(bodyId);
        }

        function quitarPago(button) {
            const body = button.closest('tbody');
            button.closest('tr').remove();
            renumerarCampos(body.id, 'pagos');
            const modo = body.id.startsWith('register') ? 'register' : 'edit';
            sincronizarMontoUnico(modo);
            recomputarPagos(body.id);
        }

        function configurarPagoListeners(row) {
            const montoInput = row.querySelector('input[name*="[monto]"]');
            const tasaInput = row.querySelector('input[name*="[tasa_cambio]"]');
            const montoBaseSpan = row.querySelector('.monto-base-pago');

            const recalcular = () => {
                const monto = parseFloat(montoInput.value) || 0;
                const tasa = parseFloat(tasaInput.value) || 0;
                montoBaseSpan.textContent = formatoNumero(tasa > 0 ? monto / tasa : 0);
                const body = row.closest('tbody');
                recomputarPagos(body.id);
            };

            montoInput.addEventListener('input', () => {
                montoInput.dataset.manual = '1';
                recalcular();
            });
            tasaInput.addEventListener('input', recalcular);
        }

        function modoDeFila(row) {
            const body = row.closest('tbody');

            return body && body.id.startsWith('register') ? 'register' : 'edit';
        }

        function totalDeLaOrden(modo) {
            const total = document.getElementById(modo + '-total');

            return parseFloat(total ? total.dataset.valor || '0' : '0') || 0;
        }

        function pagosConMetodo(modo) {
            const body = document.getElementById(modo + '-pagos-body');

            if (!body) {
                return [];
            }

            return Array.from(body.querySelectorAll('tr')).filter((fila) => {
                const select = fila.querySelector('select.metodo-pago-select');

                return select && select.value !== '';
            });
        }

        function sincronizarMontoUnico(modo) {
            const conMetodo = pagosConMetodo(modo);

            if (conMetodo.length !== 1) {
                return;
            }

            const montoInput = conMetodo[0].querySelector('input[name*="[monto]"]');

            if (!montoInput || montoInput.dataset.manual === '1') {
                return;
            }

            const total = totalDeLaOrden(modo);

            if (total <= 0) {
                return;
            }

            montoInput.value = total.toFixed(2);
            recomputarPagos(modo + '-pagos-body');
        }

        function actualizarMontosDePagos(modo, metodosAnteriores) {
            const conMetodo = pagosConMetodo(modo);

            if (conMetodo.length === 1) {
                const montoInput = conMetodo[0].querySelector('input[name*="[monto]"]');
                const total = totalDeLaOrden(modo);

                if (montoInput && total > 0) {
                    montoInput.value = total.toFixed(2);
                    delete montoInput.dataset.manual;
                }
            } else if (conMetodo.length > 1 && metodosAnteriores < 2) {
                const body = document.getElementById(modo + '-pagos-body');

                body.querySelectorAll('tr').forEach((fila) => {
                    const montoInput = fila.querySelector('input[name*="[monto]"]');

                    if (montoInput) {
                        montoInput.value = '';
                        delete montoInput.dataset.manual;
                    }
                });
            }

            recomputarPagos(modo + '-pagos-body');
        }

        function recomputarPagos(bodyId) {
            const body = document.getElementById(bodyId);

            if (!body) {
                return;
            }

            let sumaMontos = 0;

            body.querySelectorAll('tr').forEach((row) => {
                const monto = parseFloat(row.querySelector('input[name*="[monto]"]').value) || 0;
                const tasa = parseFloat(row.querySelector('input[name*="[tasa_cambio]"]').value) || 0;
                const montoBase = tasa > 0 ? monto / tasa : 0;
                sumaMontos += monto;
                row.querySelector('.monto-base-pago').textContent = formatoNumero(montoBase);
            });

            const modo = bodyId.startsWith('register') ? 'register' : 'edit';
            const indicador = document.getElementById(modo + '-pagos-cobertura');

            if (!indicador) {
                return;
            }

            const total = totalDeLaOrden(modo);
            const falta = total - sumaMontos;

            indicador.textContent = `Cubierto: ${formatoNumero(sumaMontos)} de ${formatoNumero(total)}`;
            indicador.classList.toggle('text-red-600', falta > 0.01);
            indicador.classList.toggle('text-emerald-600', falta <= 0.01);
        }

        function configurarRecalculo() {
            ['register-detalles-body', 'edit-detalles-body'].forEach((bodyId) => {
                const body = document.getElementById(bodyId);
                if (!body) return;
                body.addEventListener('input', () => recomputarTotales(bodyId));
                body.addEventListener('change', (event) => {
                    if (event.target.matches('select[name*="[producto_id]"]')) {
                        onProductoChange(event.target);
                    }
                });
            });

            ['register-tasa-cambio', 'edit-tasa-cambio'].forEach((selectId) => {
                const el = document.getElementById(selectId);
                if (!el) return;
                $(el).on('select2:select', function () {
                    const modo = selectId.startsWith('register') ? 'register' : 'edit';
                    recomputarTotales(modo + '-detalles-body');
                    recomputarPagos(modo + '-pagos-body');
                });
            });

            ['register-moneda-id', 'edit-moneda-id'].forEach((id) => {
                document.getElementById(id).addEventListener('change', (event) => {
                    const modo = id.startsWith('register') ? 'register' : 'edit';
                    const tasa = tasasVigentes[event.target.value] ?? 1;
                    document.getElementById(modo + '-tasa-cambio-valor').value = tasa;
                    recomputarTotales(modo + '-detalles-body');
                });
            });
        }

        window.openNuevoProveedorModal = function () {
            document.getElementById('nuevo-proveedor-form').reset();
            const errors = document.getElementById('nuevo-proveedor-errors');
            if (errors) errors.classList.add('hidden');
            openModal('nuevo-proveedor-modal');
        };

        async function guardarNuevoProveedor() {
            const form = document.getElementById('nuevo-proveedor-form');
            const formData = new FormData(form);
            const errorsContainer = document.getElementById('nuevo-proveedor-errors');
            errorsContainer.innerHTML = '';
            errorsContainer.classList.add('hidden');

            try {
                const response = await fetch('{{ route('proveedores.store') }}', {
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
                        const items = Object.values(data.errors).flat().map((msg) => '<li>' + msg + '</li>').join('');
                        errorsContainer.innerHTML = '<ul class="list-disc space-y-1 pl-4 text-red-600 text-xs">' + items + '</ul>';
                        errorsContainer.classList.remove('hidden');
                    }
                    return;
                }

                closeModal('nuevo-proveedor-modal');
                toast(data.message);

                const select = document.getElementById('register-proveedor-id');
                const option = new Option(data.nombre_empresa, String(data.proveedor_id), false, true);
                select.add(option);
                $(select).val(data.proveedor_id).trigger('change');
            } catch (e) {
                toast('Error al crear el proveedor.');
            }
        };

        window.openRegisterModal = async function () {
            document.getElementById('register-form').reset();
            const body = document.getElementById('register-detalles-body');
            body.innerHTML = '';
            $('#register-proveedor-id').val(null).trigger('change');
            $('#register-tasa-cambio').empty().trigger('change');
            document.getElementById('register-tasa-cambio-valor').value = '1';
            document.getElementById('register-fecha-orden').value = new Date().toISOString().slice(0, 16);
            document.getElementById('register-estado').value = 'RECIBIDA';
            document.getElementById('register-estado-pago').value = 'PAGADO';

            monedaOrdenId = null;

            agregarLinea('register-detalles-body');

            const pagosBody = document.getElementById('register-pagos-body');
            pagosBody.innerHTML = '';
            agregarPago('register-pagos-body');

            await seleccionarUltimaTasa('register');

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
                detalles.forEach((detalle, index) => {
                    body.insertAdjacentHTML('beforeend', lineaHTML(detalle.producto_id, detalle.cantidad, detalle.unitario, 'edit-detalles-body', index));
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
            eliminarFilasVacias('register-detalles-body');
            eliminarPagosVacias('register-pagos-body');
            if (contarFilasValidas('register-detalles-body') === 0) {
                toast('Debes agregar al menos un producto con cantidad y costo unitario.');
                return;
            }
            enviarFormulario(document.getElementById('register-form'), 'register-overlay');
        };

        window.submitEdit = function () {
            eliminarFilasVacias('edit-detalles-body');
            eliminarPagosVacias('edit-pagos-body');
            if (contarFilasValidas('edit-detalles-body') === 0) {
                toast('Debes agregar al menos un producto con cantidad y costo unitario.');
                return;
            }
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

        const proveedorSelect = initSelect2Ajax('#register-proveedor-id', '{{ route('proveedores.select2') }}', 'Busca un proveedor…', $('#register-modal'));

        proveedorSelect
            .on('select2:select', async function (e) {
                recomputarTotales('register-detalles-body');
            })
            .on('select2:clear', function () {
                recomputarTotales('register-detalles-body');
            });

        initTasaSelect2('#register-tasa-cambio', 'register');

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