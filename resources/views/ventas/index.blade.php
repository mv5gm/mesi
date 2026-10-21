<x-admin-layout title="Ventas">
    <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
        <div>
            <h1 class="text-xl font-semibold">Ventas</h1>
            <p class="mt-1 text-sm text-gray-500">Registra las órdenes de venta a tus clientes.</p>
        </div>
        <button
            type="button"
            onclick="openRegisterModal()"
            class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500"
        >
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
            </svg>
            Nueva venta
        </button>
    </div>

    <div class="rounded-xl bg-white p-4 shadow md:p-6">
        <table id="ventas-table" class="w-full">
            <thead>
                <tr>
                    <th>Nº</th>
                    <th>Cliente</th>
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
                <h2 class="text-lg font-semibold">Registrar venta</h2>
                <button type="button" onclick="closeModal('register-modal')" class="rounded-lg p-1 text-gray-400 hover:bg-gray-100 hover:text-gray-600">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <form id="register-form" action="{{ route('ventas.store') }}" method="POST" class="flex min-h-0 flex-1 flex-col overflow-hidden">
                @csrf
                <div class="min-h-0 flex-1 space-y-4 overflow-y-auto px-6 py-5">
                    <div id="register-errors" class="hidden rounded-lg bg-red-50 px-4 py-3 text-sm text-red-700"></div>

                    <div class="flex flex-col gap-2">
                        <label for="register-cliente-id" class="block text-sm font-medium">Cliente</label>
                        <div class="flex gap-2">
                            <select
                                id="register-cliente-id"
                                name="cliente_id"
                                required
                                class="flex-1"
                            ></select>
                            <button
                                type="button"
                                onclick="openNuevoClienteModal()"
                                class="rounded-lg bg-indigo-600 px-3 py-2 text-xs font-semibold text-white hover:bg-indigo-500"
                                title="Agregar cliente nuevo"
                            >
                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                                </svg>
                            </button>
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
                            <select
                                id="register-tasa-cambio"
                                class="mt-1 w-full"
                            ></select>
                            <input type="hidden" name="tasa_cambio" id="register-tasa-cambio-valor" value="1">
                            <p class="mt-1 text-xs text-gray-500">Se preselecciona la última tasa registrada y se ajusta a la moneda del método de pago.</p>
                        </div>
                        <div>
                            <label for="register-estado" class="block text-sm font-medium">Estado</label>
                            <select id="register-estado" name="estado" required class="mt-1 w-full rounded-lg border border-gray-300 bg-white px-3 py-2 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                                <option value="PENDIENTE">PENDIENTE</option>
                                <option value="COMPLETADA" selected>COMPLETADA</option>
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
                                    <th class="w-32 px-2 py-1 font-medium">Precio unitario</th>
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
                <h2 class="text-lg font-semibold">Editar venta</h2>
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

                    <div class="flex flex-col gap-2">
                        <label for="edit-cliente-id" class="block text-sm font-medium">Cliente</label>
                        <div class="flex gap-2">
                            <select
                                id="edit-cliente-id"
                                name="cliente_id"
                                required
                                class="flex-1"
                            ></select>
                            <button
                                type="button"
                                onclick="openNuevoClienteModal()"
                                class="rounded-lg bg-indigo-600 px-3 py-2 text-xs font-semibold text-white hover:bg-indigo-500"
                                title="Agregar cliente nuevo"
                            >
                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                                </svg>
                            </button>
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
                            <select
                                id="edit-tasa-cambio"
                                class="mt-1 w-full"
                            ></select>
                            <input type="hidden" name="tasa_cambio" id="edit-tasa-cambio-valor" value="1">
                            <p class="mt-1 text-xs text-gray-500">La moneda de la orden se hereda del método de pago.</p>
                        </div>
                        <div>
                            <label for="edit-estado" class="block text-sm font-medium">Estado</label>
                            <select id="edit-estado" name="estado" required class="mt-1 w-full rounded-lg border border-gray-300 bg-white px-3 py-2 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                                <option value="PENDIENTE">PENDIENTE</option>
                                <option value="COMPLETADA" selected>COMPLETADA</option>
                                <option value="CANCELADA">CANCELADA</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label for="edit-estado-pago" class="block text-sm font-medium">Estado de pago</label>
                        <select id="edit-estado-pago" name="estado_pago" required class="mt-1 w-full rounded-lg border border-gray-300 bg-white px-3 py-2 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                            <option value="PENDIENTE">PENDIENTE</option>
                            <option value="PARCIAL">PARCIAL</option>
                            <option value="PAGADO" selected>PAGADO</option>
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
                                    <th class="w-32 px-2 py-1 font-medium">Precio unitario</th>
                                    <th class="w-32 px-2 py-1 text-right font-medium">Subtotal</th>
                                    <th class="w-12 py-1"></th>
                                </tr>
                            </thead>
                            <tbody id="edit-detalles-body"></tbody>
                        </table>
                    </div>

                    <div class="rounded-lg border border-gray-200 p-4 mt-4">
                        <div class="mb-3 flex items-center justify-between">
                            <h3 class="text-sm font-semibold">Pagos de la orden</h3>
                            <div class="flex items-center gap-3">
                                <span id="edit-pagos-cobertura" class="text-xs font-medium text-gray-500">Cubierto: 0,00 de 0,00</span>
                                <button
                                    type="button"
                                    onclick="agregarPago('edit-pagos-body')"
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
                            <tbody id="edit-pagos-body"></tbody>
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
                <h2 class="text-lg font-semibold">Eliminar venta</h2>
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

    {{-- Modal ver venta --}}
    <div id="view-modal" class="fixed inset-0 z-50 hidden items-center justify-center p-4">
        <div class="absolute inset-0 bg-black/50" onclick="closeModal('view-modal')"></div>
        <div class="relative flex max-h-[90vh] w-full max-w-4xl flex-col overflow-hidden rounded-2xl bg-white shadow-xl">
            <div id="view-overlay" class="absolute inset-0 z-10 hidden items-center justify-center rounded-2xl bg-white/70">
                <div class="flex items-center gap-3 rounded-lg bg-gray-900 px-5 py-3 text-sm font-medium text-white">
                    <span class="h-5 w-5 animate-spin rounded-full border-2 border-white border-t-transparent"></span>
                    Cargando...
                </div>
            </div>
            <div class="flex items-center justify-between border-b border-gray-200 px-6 py-4">
                <h2 class="text-lg font-semibold">Venta <span id="view-orden-numero"></span></h2>
                <button type="button" onclick="closeModal('view-modal')" class="rounded-lg p-1 text-gray-400 hover:bg-gray-100 hover:text-gray-600">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <div class="min-h-0 flex-1 space-y-4 overflow-y-auto px-6 py-5">
                <div class="grid grid-cols-2 gap-4 md:grid-cols-4">
                    <div>
                        <p class="text-xs uppercase tracking-wide text-gray-500">Cliente</p>
                        <p id="view-cliente" class="mt-1 text-sm font-medium text-gray-900">—</p>
                    </div>
                    <div>
                        <p class="text-xs uppercase tracking-wide text-gray-500">Fecha</p>
                        <p id="view-fecha" class="mt-1 text-sm font-medium text-gray-900">—</p>
                    </div>
                    <div>
                        <p class="text-xs uppercase tracking-wide text-gray-500">Moneda</p>
                        <p id="view-moneda" class="mt-1 text-sm font-medium text-gray-900">—</p>
                    </div>
                    <div>
                        <p class="text-xs uppercase tracking-wide text-gray-500">Tasa de cambio</p>
                        <p id="view-tasa" class="mt-1 text-sm font-medium text-gray-900">—</p>
                    </div>
                    <div>
                        <p class="text-xs uppercase tracking-wide text-gray-500">Total</p>
                        <p id="view-total" class="mt-1 text-sm font-semibold text-gray-900">—</p>
                    </div>
                    <div>
                        <p class="text-xs uppercase tracking-wide text-gray-500">Total en moneda base</p>
                        <p id="view-total-base" class="mt-1 text-sm font-semibold text-gray-900">—</p>
                    </div>
                    <div>
                        <p class="text-xs uppercase tracking-wide text-gray-500">Estado</p>
                        <p id="view-estado" class="mt-1">—</p>
                    </div>
                    <div>
                        <p class="text-xs uppercase tracking-wide text-gray-500">Estado de pago</p>
                        <p id="view-estado-pago" class="mt-1">—</p>
                    </div>
                </div>

                <div class="rounded-lg border border-gray-200 p-4">
                    <h3 class="mb-3 text-sm font-semibold">Productos vendidos</h3>
                    <table class="w-full">
                        <thead>
                            <tr class="text-left text-xs uppercase tracking-wide text-gray-500">
                                <th class="py-1 pr-2 font-medium">Producto</th>
                                <th class="w-20 px-2 py-1 font-medium">Cantidad</th>
                                <th class="w-32 px-2 py-1 text-right font-medium">Precio unitario</th>
                                <th class="w-32 px-2 py-1 text-right font-medium">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody id="view-detalles-body"></tbody>
                    </table>
                </div>

                <div class="rounded-lg border border-gray-200 p-4">
                    <h3 class="mb-3 text-sm font-semibold">Pagos</h3>
                    <table class="w-full">
                        <thead>
                            <tr class="text-left text-xs uppercase tracking-wide text-gray-500">
                                <th class="py-1 pr-2 font-medium">Método de pago</th>
                                <th class="w-20 px-2 py-1 font-medium">Moneda</th>
                                <th class="w-32 px-2 py-1 text-right font-medium">Monto</th>
                                <th class="w-24 px-2 py-1 text-right font-medium">Tasa</th>
                                <th class="w-32 px-2 py-1 text-right font-medium">Monto base</th>
                                <th class="w-32 px-2 py-1 font-medium">Referencia</th>
                                <th class="w-28 px-2 py-1 font-medium">Fecha</th>
                            </tr>
                        </thead>
                        <tbody id="view-pagos-body"></tbody>
                    </table>
                </div>
            </div>
            <div class="flex justify-end gap-3 border-t border-gray-200 px-6 py-4">
                <button type="button" onclick="closeModal('view-modal')" class="rounded-lg bg-gray-100 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-200">
                    Cerrar
                </button>
            </div>
        </div>
    </div>

    {{-- Modal nuevo cliente rápido --}}
    <div id="nuevo-cliente-modal" class="fixed inset-0 z-50 hidden items-center justify-center p-4">
        <div class="absolute inset-0 bg-black/50" onclick="closeModal('nuevo-cliente-modal')"></div>
        <div class="relative w-full max-w-md bg-white rounded-2xl shadow-xl overflow-hidden">
            <div class="bg-gray-100 px-4 py-3 flex items-center justify-between">
                <h3 class="text-sm font-semibold">Nuevo cliente</h3>
                <button type="button" onclick="closeModal('nuevo-cliente-modal')" class="text-gray-400 hover:text-gray-600">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <form id="nuevo-cliente-form" class="p-4 space-y-3">
                <div id="nuevo-cliente-errors" class="hidden rounded-lg bg-red-50 px-3 py-2 text-xs text-red-700"></div>
                <div>
                    <label for="nuevo-cliente-nombre" class="block text-sm font-medium">Nombre</label>
                    <input
                        id="nuevo-cliente-nombre"
                        type="text"
                        name="nombre"
                        required
                        class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                    >
                </div>
                <div>
                    <label for="nuevo-cliente-correo" class="block text-sm font-medium">Correo</label>
                    <input
                        id="nuevo-cliente-correo"
                        type="email"
                        name="correo"
                        class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                    >
                </div>
                <div>
                    <label for="nuevo-cliente-telefono" class="block text-sm font-medium">Teléfono</label>
                    <input
                        id="nuevo-cliente-telefono"
                        type="text"
                        name="telefono"
                        class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                    >
                </div>
                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" onclick="closeModal('nuevo-cliente-modal')" class="rounded-lg bg-gray-100 px-3 py-1.5 text-sm text-gray-700 hover:bg-gray-200">
                        Cancelar
                    </button>
                    <button type="button" onclick="guardarNuevoCliente()" class="rounded-lg bg-indigo-600 px-3 py-1.5 text-sm text-white hover:bg-indigo-500">
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
        <style>
        #ventas-table_wrapper .dataTables_filter input {
            padding: 0.5rem 0.75rem;
            border: 1px solid #d1d5db;
            border-radius: 0.5rem;
            outline: none;
            margin-left: 0.5rem;
        }
        #ventas-table_wrapper .dataTables_filter input:focus {
            border-color: #6366f1;
            box-shadow: 0 0 0 2px rgb(99 102 241 / 0.2);
        }
        #ventas-table_wrapper .dataTables_length select {
            padding: 0.5rem 0.75rem;
            border: 1px solid #d1d5db;
            border-radius: 0.5rem;
            outline: none;
            margin: 0 0.25rem;
        }
        #ventas-table_wrapper .dataTables_paginate .paginate_button {
            padding: 0.375rem 0.75rem;
            margin-left: 0.25rem;
            border-radius: 0.5rem;
            border: 1px solid #e5e7eb;
            color: #374151 !important;
            background: #fff;
            cursor: pointer;
        }
        #ventas-table_wrapper .dataTables_paginate .paginate_button:hover:not(.disabled) {
            background: #eef2ff;
            border-color: #6366f1;
            color: #4338ca !important;
        }
        #ventas-table_wrapper .dataTables_paginate .paginate_button.current {
            background: #6366f1;
            border-color: #6366f1;
            color: #fff !important;
        }
        #ventas-table_wrapper .dataTables_paginate .paginate_button.disabled {
            cursor: not-allowed;
            color: #9ca3af !important;
        }
        #ventas-table_wrapper .dataTables_processing {
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
        <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/i18n/es.js"></script>
        <script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
        <script>
        const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

        // Tabla de ventas, se recarga al registrar, editar o eliminar una orden.
        let dataTable = null;

        function recargarTabla() {
            if (dataTable) {
                dataTable.ajax.reload();
            }
        }

        const metodosPagoOptions = @json($metodosPagoOptions);
        const monedaBaseId = @json($monedaBaseId);

        // La orden hereda la moneda de su primer pago, por lo que la tasa se busca en esa moneda.
        let monedaOrdenId = null;

        // Tasas vigentes: cuántas unidades de cada moneda equivalen a 1 dólar.
        const tasasVigentes = @json($tasasVigentes);

        function tasaDe(monedaId) {
            return Number(tasasVigentes[monedaId] ?? 1);
        }

        function convertirADolares(monto, monedaId) {
            const tasa = tasaDe(monedaId);

            return tasa > 0 ? (parseFloat(monto) || 0) / tasa : parseFloat(monto) || 0;
        }

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

        function formatoNumero(valor) {
            return parseFloat(valor || 0).toLocaleString('es-VE', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        }

        function initSelect2Ajax(selectId, url, placeholder) {
            $(selectId).select2({
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
            });
        }

        /**
         * Etiqueta de disponibilidad del producto: verde con las unidades, roja sin existencias.
         */
        function etiquetaDisponibilidad(stock) {
            const unidades = parseInt(stock || '0', 10);

            if (unidades > 0) {
                return $('<span class="text-emerald-600 font-semibold"></span>').text(unidades + ' disp.');
            }

            return $('<span class="text-red-600 font-semibold"></span>').text('sin existencias');
        }

        function initProductoSelect2(selectEl) {
            $(selectEl).select2({
                placeholder: 'Busca un producto…',
                allowClear: true,
                language: 'es',
                minimumInputLength: 0,
                width: '100%',
                templateResult: function (producto) {
                    if (!producto.id) {
                        return producto.text;
                    }

                    return $('<div class="flex items-center justify-between gap-3"></div>')
                        .append($('<span></span>').text(producto.text))
                        .append(etiquetaDisponibilidad(producto.stock));
                },
                templateSelection: function (producto) {
                    if (!producto.id) {
                        return producto.text;
                    }

                    return $('<span></span>')
                        .append(producto.text + ' · ')
                        .append(etiquetaDisponibilidad(producto.stock));
                },
                ajax: {
                    url: '{{ route('ventas.productos.select2') }}',
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
            }).on('select2:select', async function (e) {
                const productoId = e.params.data.id;
                const row = selectEl.closest('tr');
                const precioInput = row.querySelector('input[name$="[precio_unitario]"]');
                const cantidadInput = row.querySelector('input[name$="[cantidad]"]');
                const stock = parseInt(e.params.data.stock || '0', 10);

                row.dataset.stock = stock;

                if (stock <= 0) {
                    toast('El producto no tiene existencias registradas, no se puede vender.');
                    $(selectEl).val(null).trigger('change');
                    row.dataset.stock = 0;

                    return;
                }

                if (cantidadInput) {
                    cantidadInput.max = stock;
                    cantidadInput.value = 1;
                }

                validarCantidad(row);

                try {
                    const response = await fetch('{{ route('ventas.producto.precio') }}?producto_id=' + encodeURIComponent(productoId) + '&moneda_id=' + encodeURIComponent(monedaOrdenId || ''), {
                        headers: { 'Accept': 'application/json' },
                    });
                    const data = await response.json();

                    if (precioInput && data.precio_orden !== undefined) {
                        row.dataset.precioUsd = data.precio_venta;
                        precioInput.value = data.precio_orden;
                        recomputarTotales(row.closest('tbody').id);
                    }
                } catch (ex) {
                    // silencioso
                }
            }).on('select2:clear', function () {
                const row = selectEl.closest('tr');
                const precioInput = row.querySelector('input[name$="[precio_unitario]"]');
                const cantidadInput = row.querySelector('input[name$="[cantidad]"]');
                delete row.dataset.precioUsd;
                delete row.dataset.stock;
                if (precioInput) precioInput.value = '';
                if (cantidadInput) {
                    cantidadInput.value = '';
                    cantidadInput.removeAttribute('max');
                    cantidadInput.classList.remove('border-red-500', 'focus:border-red-500');
                }
                recomputarTotales(row.closest('tbody').id);
            });
        }

        /**
         * Marca la cantidad cuando supera las existencias disponibles.
         */
        function validarCantidad(row) {
            const cantidadInput = row.querySelector('input[name$="[cantidad]"]');

            if (!cantidadInput) {
                return;
            }

            const stock = parseInt(row.dataset.stock || '0', 10);
            const cantidad = parseInt(cantidadInput.value || '0', 10);
            const excede = stock > 0 && cantidad > stock;

            cantidadInput.classList.toggle('border-red-500', excede);
            cantidadInput.classList.toggle('focus:border-red-500', excede);
            cantidadInput.title = excede ? `Solo hay ${stock} unidades en existencias` : '';
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

        async function seleccionarTasaDeLaOrden(modo, tasaValor) {
            if (!monedaOrdenId) {
                return;
            }

            try {
                const response = await fetch('{{ route('tasas.select2') }}?moneda_id=' + encodeURIComponent(monedaOrdenId), {
                    headers: { 'Accept': 'application/json' },
                });
                const tasas = await response.json();
                const tasa = (tasas || []).find((item) => Number(item.tasa) === Number(tasaValor));

                aplicarTasaOrden(modo, tasa);
            } catch (e) {
                // silencioso
            }
        }

        async function aplicarTasaDeLaMoneda(modo, metodo) {
            if (!metodo.moneda_id || String(metodo.moneda_id) === String(monedaBaseId)) {
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

        /**
         * La orden toma la moneda de su primer pago y, con ella, la tasa de cambio vigente.
         */
        async function heredarMonedaEnLaOrden(row, metodo, tasa) {
            const body = row.closest('tbody');

            if (!body || row !== body.querySelector('tr')) {
                return;
            }

            const modo = body.id.startsWith('register') ? 'register' : 'edit';

            monedaOrdenId = metodo.moneda_id;

            await aplicarTasaDeLaMoneda(modo, metodo);

            convertirPreciosDeLaOrden(modo, tasa);

            recomputarTotales(modo + '-detalles-body');
        }

        /**
         * Convierte los precios de los productos (en dólares) a la moneda de la orden.
         */
        function convertirPreciosDeLaOrden(modo, tasa) {
            const body = document.getElementById(modo + '-detalles-body');

            if (!body) {
                return;
            }

            body.querySelectorAll('tr').forEach((row) => {
                const precioUsd = parseFloat(row.dataset.precioUsd || '0');
                const precioInput = row.querySelector('input[name$="[precio_unitario]"]');

                if (precioInput && precioUsd > 0) {
                    precioInput.value = (precioUsd * tasa).toFixed(2);
                }
            });
        }

        function lineaHTML(productoId, cantidad, unitario, index) {
            return `
                <tr>
                    <td class="py-1 pr-2">
                        <select name="detalles[${index}][producto_id]" required class="producto-select w-full"></select>
                    </td>
                    <td class="px-2 py-1">
                        <input type="number" name="detalles[${index}][cantidad]" required min="1" step="1" value="${cantidad || ''}" class="w-full">
                    </td>
                    <td class="px-2 py-1">
                        <input type="number" name="detalles[${index}][precio_unitario]" required min="0" step="0.01" value="${unitario || ''}" class="w-full" placeholder="0.00">
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

        function agregarLinea(bodyId) {
            const body = document.getElementById(bodyId);
            const index = body.querySelectorAll('tr').length;
            body.insertAdjacentHTML('beforeend', lineaHTML('', '', '', index));
            const select = body.querySelector('tr:last-child select.producto-select');
            initProductoSelect2(select);
            recomputarTotales(bodyId);
        }

        function quitarLinea(button) {
            const body = button.closest('tbody');
            button.closest('tr').remove();
            renumerarCampos(body.id, 'detalles');
            recomputarTotales(body.id);
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

        /**
         * Con un solo método de pago el monto es el total de la orden, siempre que el
         * usuario no lo haya digitado a mano.
         */
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

        /**
         * Al elegir un método de pago el monto se completa con el total de la orden.
         * Si se agrega un segundo método, los montos se vacían para cargarlos a mano.
         */
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

        function recomputarTotales(bodyId) {
            const body = document.getElementById(bodyId);
            const modo = bodyId.startsWith('register') ? 'register' : 'edit';
            let total = 0;

            body.querySelectorAll('tr').forEach((row) => {
                const cantidad = parseFloat(row.querySelector('input[name$="[cantidad]"]').value) || 0;
                const unitario = parseFloat(row.querySelector('input[name$="[precio_unitario]"]').value) || 0;
                const subtotal = cantidad * unitario;
                row.querySelector('.subtotal').textContent = formatoNumero(subtotal);
                total += subtotal;
                validarCantidad(row);
            });

            const tasaInput = document.getElementById(modo + '-tasa-cambio-valor');
            const tasa = parseFloat(tasaInput ? tasaInput.value : '1') || 0;
            const totalEl = document.getElementById(modo + '-total');
            totalEl.textContent = formatoNumero(total);
            totalEl.dataset.valor = total;
            document.getElementById(modo + '-total-base').textContent = formatoNumero(tasa > 0 ? total / tasa : 0);

            sincronizarMontoUnico(modo);
            recomputarPagos(modo + '-pagos-body');
        }

        function recomputarPagos(bodyId) {
            const body = document.getElementById(bodyId);

            if (!body) {
                return;
            }

            let sumaMontos = 0;

            body.querySelectorAll('tr').forEach((row) => {
                const monto = parseFloat(row.querySelector('input[name$="[monto]"]').value) || 0;
                const tasa = parseFloat(row.querySelector('input[name$="[tasa_cambio]"]').value) || 0;
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
                document.getElementById(bodyId).addEventListener('input', (event) => recomputarTotales(bodyId));
            });

            ['register-tasa-cambio', 'edit-tasa-cambio'].forEach((selectId) => {
                document.getElementById(selectId).addEventListener('select2:select', function () {
                    const modo = selectId.startsWith('register') ? 'register' : 'edit';
                    recomputarTotales(modo + '-detalles-body');
                    recomputarPagos(modo + '-pagos-body');
                });
            });
        }

        window.openNuevoClienteModal = function () {
            document.getElementById('nuevo-cliente-form').reset();
            const errors = document.getElementById('nuevo-cliente-errors');
            if (errors) errors.classList.add('hidden');
            openModal('nuevo-cliente-modal');
        };

        async function guardarNuevoCliente() {
            const form = document.getElementById('nuevo-cliente-form');
            const formData = new FormData(form);
            const errorsContainer = document.getElementById('nuevo-cliente-errors');
            errorsContainer.innerHTML = '';
            errorsContainer.classList.add('hidden');

            try {
                const response = await fetch('/clientes', {
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

                closeModal('nuevo-cliente-modal');
                toast(data.message);

                ['register', 'edit'].forEach((modo) => {
                    const select = document.getElementById(modo + '-cliente-id');
                    const option = new Option(formData.get('nombre'), data.cliente_id, false, false);
                    select.add(option);
                    $(select).val(data.cliente_id).trigger('change');
                });
            } catch (e) {
                toast('Error al crear el cliente.');
            }
        }

        window.openRegisterModal = async function () {
            document.getElementById('register-form').reset();
            $('#register-cliente-id').val(null).trigger('change');
            $('#register-tasa-cambio').empty().trigger('change');
            document.getElementById('register-tasa-cambio-valor').value = '1';
            document.getElementById('register-fecha-orden').value = new Date().toISOString().slice(0, 16);
            document.getElementById('register-estado').value = 'COMPLETADA';
            document.getElementById('register-estado-pago').value = 'PAGADO';

            monedaOrdenId = null;

            const body = document.getElementById('register-detalles-body');
            body.innerHTML = '';
            agregarLinea('register-detalles-body');

            const pagosBody = document.getElementById('register-pagos-body');
            pagosBody.innerHTML = '';
            agregarPago('register-pagos-body');

            await seleccionarUltimaTasa('register');

            clearErrors('register-errors');
            openModal('register-modal');
        };

        window.openEditModal = async function (button) {
            const form = document.getElementById('edit-form');
            const body = document.getElementById('edit-detalles-body');
            body.innerHTML = '';

            $('#edit-cliente-id').val(button.dataset.cliente || null).trigger('change');

            form.elements['fecha_orden'].value = button.dataset.fecha || '';
            form.elements['estado'].value = button.dataset.estado || 'COMPLETADA';
            form.elements['estado_pago'].value = button.dataset.estadoPago || 'PAGADO';

            const tasaId = button.dataset.tasaId || null;
            const tasaValor = button.dataset.tasa || '1';
            document.getElementById('edit-tasa-cambio-valor').value = tasaValor;
            if (tasaId) {
                $('#edit-tasa-cambio').val(tasaId).trigger('change');
            }

            monedaOrdenId = null;

            try {
                const detalles = JSON.parse(button.dataset.detalles || '[]');
                detalles.forEach((detalle, index) => {
                    body.insertAdjacentHTML('beforeend', lineaHTML(detalle.producto_id, detalle.cantidad, detalle.unitario, index));
                });
                body.querySelectorAll('tr').forEach((row, index) => {
                    initProductoSelect2(row.querySelector('select.producto-select'));
                    if (detalles[index]?.producto_id) {
                        $(row.querySelector('select.producto-select')).val(detalles[index].producto_id).trigger('change');
                        row.dataset.precioUsd = convertirADolares(detalles[index].precio_venta, detalles[index].moneda_producto);
                        row.dataset.stock = detalles[index].stock ?? 0;
                        const cantidadInput = row.querySelector('input[name$="[cantidad]"]');
                        if (cantidadInput && row.dataset.stock > 0) {
                            cantidadInput.max = row.dataset.stock;
                        }
                        validarCantidad(row);
                    }
                });
            } catch (e) {
                agregarLinea('edit-detalles-body');
            }

            if (!body.querySelectorAll('tr').length) {
                agregarLinea('edit-detalles-body');
            }

            const pagosBody = document.getElementById('edit-pagos-body');
            pagosBody.innerHTML = '';
            try {
                const pagos = JSON.parse(button.dataset.pagos || '[]');
                pagos.forEach((pago, index) => {
                    pagosBody.insertAdjacentHTML('beforeend', pagoHTML(
                        pago.metodo_pago_id,
                        pago.monto,
                        pago.tasa_cambio,
                        pago.monto_base,
                        pago.numero_referencia,
                        pago.fecha_pago,
                        index
                    ));
                });
                pagosBody.querySelectorAll('tr').forEach((row, index) => {
                    const pago = pagos[index];
                    const metodoSelect = row.querySelector('select.metodo-pago-select');
                    initMetodoPagoSelect2(metodoSelect);
                    if (pago.metodo_pago_id) {
                        $(metodoSelect).val(pago.metodo_pago_id).trigger('change');
                        actualizarEtiquetaMoneda(row, pago.metodo_pago_id);
                    }
                    if (index === 0) {
                        monedaOrdenId = metodosPagoOptions[String(pago.metodo_pago_id)]?.moneda_id ?? null;
                    }
                    // El monto guardado se respeta hasta que se vuelva a elegir el método de pago.
                    const montoInput = row.querySelector('input[name*="[monto]"]');
                    if (montoInput) {
                        montoInput.dataset.manual = '1';
                    }
                    configurarPagoListeners(row);
                });
            } catch (e) {
                agregarPago('edit-pagos-body');
            }

            if (!pagosBody.querySelectorAll('tr').length) {
                agregarPago('edit-pagos-body');
            }

            form.action = '/ventas/' + button.dataset.id;
            clearErrors('edit-errors');
            await seleccionarTasaDeLaOrden('edit', tasaValor);
            recomputarTotales('edit-detalles-body');
            recomputarPagos('edit-pagos-body');
            openModal('edit-modal');
        };

        window.openDeleteModal = function (button) {
            document.getElementById('delete-form').action = '/ventas/' + button.dataset.id;
            document.getElementById('delete-orden-numero').textContent = button.dataset.numero;
            openModal('delete-modal');
        };

        window.openViewModal = async function (button) {
            const overlayId = 'view-overlay';

            showOverlay(overlayId);
            openModal('view-modal');

            try {
                const response = await fetch('/ventas/' + button.dataset.id, {
                    headers: { 'Accept': 'application/json' },
                });

                const data = await response.json();

                if (!response.ok) {
                    toast(data.message || 'No se pudo cargar la venta.');
                    closeModal('view-modal');

                    return;
                }

                document.getElementById('view-orden-numero').textContent = '#' + data.id;
                document.getElementById('view-cliente').textContent = data.cliente;
                document.getElementById('view-fecha').textContent = data.fecha;
                document.getElementById('view-moneda').textContent = data.moneda;
                document.getElementById('view-tasa').textContent = formatoNumero(data.tasa_cambio);
                document.getElementById('view-total').textContent = data.total;
                document.getElementById('view-total-base').textContent = data.total_base;
                document.getElementById('view-estado').innerHTML = data.estado;
                document.getElementById('view-estado-pago').innerHTML = data.estado_pago;

                const detallesBody = document.getElementById('view-detalles-body');
                detallesBody.innerHTML = data.detalles.map((detalle) => `
                    <tr class="border-t border-gray-100">
                        <td class="py-1 pr-2">
                            <span class="block text-sm font-medium text-gray-900">${detalle.producto}</span>
                            <span class="block text-xs text-gray-500">${detalle.sku}</span>
                        </td>
                        <td class="px-2 py-1 text-sm">${detalle.cantidad}</td>
                        <td class="px-2 py-1 text-right text-sm">${detalle.precio_unitario}</td>
                        <td class="px-2 py-1 text-right text-sm font-medium">${detalle.subtotal}</td>
                    </tr>`).join('');

                const pagosBody = document.getElementById('view-pagos-body');
                pagosBody.innerHTML = data.pagos.map((pago) => `
                    <tr class="border-t border-gray-100">
                        <td class="py-1 pr-2 text-sm font-medium text-gray-900">${pago.metodo}</td>
                        <td class="px-2 py-1 text-sm">${pago.moneda}</td>
                        <td class="px-2 py-1 text-right text-sm">${pago.monto}</td>
                        <td class="px-2 py-1 text-right text-sm">${formatoNumero(pago.tasa_cambio)}</td>
                        <td class="px-2 py-1 text-right text-sm font-medium">${pago.monto_base}</td>
                        <td class="px-2 py-1 text-sm">${pago.numero_referencia}</td>
                        <td class="px-2 py-1 text-sm">${pago.fecha_pago}</td>
                    </tr>`).join('');
            } catch (e) {
                toast('No se pudo cargar la venta.');
                closeModal('view-modal');
            } finally {
                hideOverlay(overlayId);
            }
        };

        async function enviarFormulario(form, overlayId) {
            showOverlay(overlayId);
            try {
                renumerarCampos('register-detalles-body', 'detalles');
                renumerarCampos('register-pagos-body', 'pagos');
                renumerarCampos('edit-detalles-body', 'detalles');
                renumerarCampos('edit-pagos-body', 'pagos');
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
                        const firstMsg = data.errors[firstKey][0];
                        toast(firstMsg);
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

        $(function () {
            initSelect2Ajax('#register-cliente-id', '{{ route('ventas.clientes.select2') }}', 'Busca un cliente…');
            initSelect2Ajax('#edit-cliente-id', '{{ route('ventas.clientes.select2') }}', 'Busca un cliente…');
            initTasaSelect2('#register-tasa-cambio', 'register');
            initTasaSelect2('#edit-tasa-cambio', 'edit');

            configurarRecalculo();

            dataTable = $('#ventas-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '{{ route('ventas.data') }}',
                    data: (d) => { d.draw = d.draw || 1; },
                },
                columns: [
                    { data: 'id' },
                    { data: 'cliente' },
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
        });
        </script>
    @endpush
</x-admin-layout>