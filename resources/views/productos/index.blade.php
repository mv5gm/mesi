<x-admin-layout title="Productos">
    <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
        <div>
            <h1 class="text-xl font-semibold">Productos</h1>
            <p class="mt-1 text-sm text-gray-500">Administra el catálogo de productos, precios y stock.</p>
        </div>
        <button
            type="button"
            onclick="openRegisterModal()"
            class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500"
        >
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
            </svg>
            Nuevo producto
        </button>
    </div>

    <div class="rounded-xl bg-white p-4 shadow md:p-6">
        <table id="productos-table" class="w-full">
            <thead>
                <tr>
                    <th>SKU</th>
                    <th>Nombre</th>
                    <th>Imagen</th>
                    <th>Categoría</th>
                    <th>Precio costo</th>
                    <th>Precio venta</th>
                    <th>Stock</th>
                    <th>Acciones</th>
                </tr>
            </thead>
        </table>
    </div>

    {{-- Modal registrar --}}
    <div id="register-modal" class="fixed inset-0 z-50 hidden items-center justify-center p-4">
        <div class="absolute inset-0 bg-black/50" onclick="closeModal('register-modal')"></div>
        <div class="relative flex max-h-[90vh] w-full max-w-2xl flex-col overflow-hidden rounded-2xl bg-white shadow-xl">
            <div id="register-overlay" class="absolute inset-0 z-10 hidden items-center justify-center rounded-2xl bg-white/70">
                <div class="flex items-center gap-3 rounded-lg bg-gray-900 px-5 py-3 text-sm font-medium text-white">
                    <span class="h-5 w-5 animate-spin rounded-full border-2 border-white border-t-transparent"></span>
                    Procesando...
                </div>
            </div>
            <div class="flex items-center justify-between border-b border-gray-200 px-6 py-4">
                <h2 class="text-lg font-semibold">Registrar producto</h2>
                <button type="button" onclick="closeModal('register-modal')" class="rounded-lg p-1 text-gray-400 hover:bg-gray-100 hover:text-gray-600">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <form id="register-form" action="{{ route('productos.store') }}" method="POST" class="min-h-0 flex-1 space-y-4 overflow-y-auto px-6 py-5">
                @csrf
                <div id="register-errors" class="hidden rounded-lg bg-red-50 px-4 py-3 text-sm text-red-700"></div>

                <div>
                    <label for="register-nombre" class="block text-sm font-medium">Nombre</label>
                    <input
                        id="register-nombre"
                        type="text"
                        name="nombre"
                        required
                        autocomplete="off"
                        class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                    >
                </div>

                <div>
                    <span class="block text-sm font-medium">Imagen del producto</span>
                    <div class="mt-1 flex flex-wrap items-center gap-4">
                        <div class="flex h-24 w-24 shrink-0 items-center justify-center overflow-hidden rounded-xl border border-dashed border-gray-300 bg-gray-50">
                            <img id="register-imagen-preview" src="" alt="Vista previa de la imagen" class="hidden h-full w-full object-cover">
                            <svg id="register-imagen-placeholder" class="h-8 w-8 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909M3.75 18.75h16.5a1.5 1.5 0 001.5-1.5V6.75a1.5 1.5 0 00-1.5-1.5H3.75a1.5 1.5 0 00-1.5 1.5v10.5a1.5 1.5 0 001.5 1.5z" />
                            </svg>
                        </div>
                        <div class="space-y-2">
                            <input id="register-imagen" type="file" name="imagen" accept="image/*" class="hidden">
                            <input id="register-camera-input" type="file" accept="image/*" capture="environment" class="hidden">
                            <input id="register-quitar-imagen" type="hidden" name="quitar_imagen" value="0">
                            <div class="flex flex-wrap gap-2">
                                <button type="button" onclick="document.getElementById('register-imagen').click()" class="inline-flex items-center gap-1.5 rounded-lg bg-gray-100 px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-200">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 7.5 7.5 12M12 7.5V21" />
                                    </svg>
                                    Subir imagen
                                </button>
                                <button type="button" onclick="abrirCamara('register')" class="inline-flex items-center gap-1.5 rounded-lg bg-gray-100 px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-200">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 015.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 00-1.134-.175 2.31 2.31 0 01-1.64-1.055l-.822-1.316a2.192 2.192 0 00-1.736-1.039 48.774 48.774 0 00-5.232 0 2.192 2.192 0 00-1.736 1.039l-.821 1.316z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12.75a4.5 4.5 0 11-9 0 4.5 4.5 0 019 0z" />
                                    </svg>
                                    Tomar foto
                                </button>
                                <button type="button" id="register-imagen-quitar" onclick="quitarImagen('register')" class="hidden rounded-lg bg-red-50 px-3 py-2 text-sm font-medium text-red-600 hover:bg-red-100">
                                    Quitar
                                </button>
                            </div>
                            <p class="text-xs text-gray-500">JPG, PNG o WebP. Si supera 1 MB se optimiza automáticamente.</p>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div>
                        <label for="register-sku" class="block text-sm font-medium">SKU</label>
                        <input
                            id="register-sku"
                            type="text"
                            name="sku"
                            required
                            autocomplete="off"
                            class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                        >
                    </div>
                    <div>
                        <label for="register-codigo-barras" class="block text-sm font-medium">Código de barras</label>
                        <input
                            id="register-codigo-barras"
                            type="text"
                            name="codigo_barras"
                            autocomplete="off"
                            class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                        >
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div>
                        <label for="register-categoria-id" class="block text-sm font-medium">Categoría</label>
                        <select
                            id="register-categoria-id"
                            name="categoria_id"
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

                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div>
                        <label for="register-precio-costo" class="block text-sm font-medium">Precio de costo</label>
                        <input
                            id="register-precio-costo"
                            type="number"
                            name="precio_costo"
                            step="0.01"
                            min="0"
                            required
                            class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                        >
                    </div>
                    <div>
                        <label for="register-precio-venta" class="block text-sm font-medium">Precio de venta</label>
                        <input
                            id="register-precio-venta"
                            type="number"
                            name="precio_venta"
                            step="0.01"
                            min="0"
                            required
                            class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                        >
                        <p id="register-precio-venta-bs" class="mt-1 text-xs text-gray-500"></p>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div>
                        <label for="register-stock-minimo" class="block text-sm font-medium">Stock mínimo</label>
                        <input
                            id="register-stock-minimo"
                            type="number"
                            name="stock_minimo"
                            min="0"
                            value="0"
                            class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                        >
                    </div>
                    <div>
                        <label for="register-stock-maximo" class="block text-sm font-medium">Stock máximo</label>
                        <input
                            id="register-stock-maximo"
                            type="number"
                            name="stock_maximo"
                            min="0"
                            class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                        >
                    </div>
                </div>

                <div>
                    <label for="register-unidad-medida" class="block text-sm font-medium">Unidad de medida</label>
                    <select
                        id="register-unidad-medida"
                        name="unidad_medida"
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
                    <label for="register-descripcion" class="block text-sm font-medium">Descripción</label>
                    <textarea
                        id="register-descripcion"
                        name="descripcion"
                        rows="2"
                        class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                    ></textarea>
                </div>

                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" onclick="closeModal('register-modal')" class="rounded-lg bg-gray-100 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-200">
                        Cancelar
                    </button>
                    <button type="submit" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">
                        Registrar
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Modal editar --}}
    <div id="edit-modal" class="fixed inset-0 z-50 hidden items-center justify-center p-4">
        <div class="absolute inset-0 bg-black/50" onclick="closeModal('edit-modal')"></div>
        <div class="relative flex max-h-[90vh] w-full max-w-2xl flex-col overflow-hidden rounded-2xl bg-white shadow-xl">
            <div id="edit-overlay" class="absolute inset-0 z-10 hidden items-center justify-center rounded-2xl bg-white/70">
                <div class="flex items-center gap-3 rounded-lg bg-gray-900 px-5 py-3 text-sm font-medium text-white">
                    <span class="h-5 w-5 animate-spin rounded-full border-2 border-white border-t-transparent"></span>
                    Procesando...
                </div>
            </div>
            <div class="flex items-center justify-between border-b border-gray-200 px-6 py-4">
                <h2 class="text-lg font-semibold">Editar producto</h2>
                <button type="button" onclick="closeModal('edit-modal')" class="rounded-lg p-1 text-gray-400 hover:bg-gray-100 hover:text-gray-600">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <form id="edit-form" method="POST" class="min-h-0 flex-1 space-y-4 overflow-y-auto px-6 py-5">
                @csrf
                <input type="hidden" name="_method" value="PUT">
                <div id="edit-errors" class="hidden rounded-lg bg-red-50 px-4 py-3 text-sm text-red-700"></div>

                <div>
                    <label for="edit-nombre" class="block text-sm font-medium">Nombre</label>
                    <input id="edit-nombre" type="text" name="nombre" required autocomplete="off" class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                </div>

                <div>
                    <span class="block text-sm font-medium">Imagen del producto</span>
                    <div class="mt-1 flex flex-wrap items-center gap-4">
                        <div class="flex h-24 w-24 shrink-0 items-center justify-center overflow-hidden rounded-xl border border-dashed border-gray-300 bg-gray-50">
                            <img id="edit-imagen-preview" src="" alt="Vista previa de la imagen" class="hidden h-full w-full object-cover">
                            <svg id="edit-imagen-placeholder" class="h-8 w-8 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909M3.75 18.75h16.5a1.5 1.5 0 001.5-1.5V6.75a1.5 1.5 0 00-1.5-1.5H3.75a1.5 1.5 0 00-1.5 1.5v10.5a1.5 1.5 0 001.5 1.5z" />
                            </svg>
                        </div>
                        <div class="space-y-2">
                            <input id="edit-imagen" type="file" name="imagen" accept="image/*" class="hidden">
                            <input id="edit-camera-input" type="file" accept="image/*" capture="environment" class="hidden">
                            <input id="edit-quitar-imagen" type="hidden" name="quitar_imagen" value="0">
                            <div class="flex flex-wrap gap-2">
                                <button type="button" onclick="document.getElementById('edit-imagen').click()" class="inline-flex items-center gap-1.5 rounded-lg bg-gray-100 px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-200">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 7.5 7.5 12M12 7.5V21" />
                                    </svg>
                                    Subir imagen
                                </button>
                                <button type="button" onclick="abrirCamara('edit')" class="inline-flex items-center gap-1.5 rounded-lg bg-gray-100 px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-200">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 015.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 00-1.134-.175 2.31 2.31 0 01-1.64-1.055l-.822-1.316a2.192 2.192 0 00-1.736-1.039 48.774 48.774 0 00-5.232 0 2.192 2.192 0 00-1.736 1.039l-.821 1.316z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12.75a4.5 4.5 0 11-9 0 4.5 4.5 0 019 0z" />
                                    </svg>
                                    Tomar foto
                                </button>
                                <button type="button" id="edit-imagen-quitar" onclick="quitarImagen('edit')" class="hidden rounded-lg bg-red-50 px-3 py-2 text-sm font-medium text-red-600 hover:bg-red-100">
                                    Quitar
                                </button>
                            </div>
                            <p class="text-xs text-gray-500">JPG, PNG o WebP. Si supera 1 MB se optimiza automáticamente.</p>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div>
                        <label for="edit-sku" class="block text-sm font-medium">SKU</label>
                        <input id="edit-sku" type="text" name="sku" required autocomplete="off" class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label for="edit-codigo-barras" class="block text-sm font-medium">Código de barras</label>
                        <input id="edit-codigo-barras" type="text" name="codigo_barras" autocomplete="off" class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div>
                        <label for="edit-categoria-id" class="block text-sm font-medium">Categoría</label>
                        <select id="edit-categoria-id" name="categoria_id" required class="mt-1 w-full rounded-lg border border-gray-300 bg-white px-3 py-2 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"></select>
                    </div>
                    <div>
                        <label for="edit-moneda-id" class="block text-sm font-medium">Moneda</label>
                        <select id="edit-moneda-id" name="moneda_id" required class="mt-1 w-full rounded-lg border border-gray-300 bg-white px-3 py-2 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"></select>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div>
                        <label for="edit-precio-costo" class="block text-sm font-medium">Precio de costo</label>
                        <input id="edit-precio-costo" type="number" name="precio_costo" step="0.01" min="0" required class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label for="edit-precio-venta" class="block text-sm font-medium">Precio de venta</label>
                        <input id="edit-precio-venta" type="number" name="precio_venta" step="0.01" min="0" required class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                        <p id="edit-precio-venta-bs" class="mt-1 text-xs text-gray-500"></p>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div>
                        <label for="edit-stock-minimo" class="block text-sm font-medium">Stock mínimo</label>
                        <input id="edit-stock-minimo" type="number" name="stock_minimo" min="0" class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label for="edit-stock-maximo" class="block text-sm font-medium">Stock máximo</label>
                        <input id="edit-stock-maximo" type="number" name="stock_maximo" min="0" class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                    </div>
                </div>

                <div>
                    <label for="edit-unidad-medida" class="block text-sm font-medium">Unidad de medida</label>
                    <select id="edit-unidad-medida" name="unidad_medida" class="mt-1 w-full rounded-lg border border-gray-300 bg-white px-3 py-2 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                        <option value="PZA">PZA (Unidad)</option>
                        <option value="CAJA">CAJA</option>
                        <option value="LT">LT (Litro)</option>
                        <option value="KG">KG (Kilogramo)</option>
                        <option value="UND">UND (Und.)</option>
                    </select>
                </div>

                <div>
                    <label for="edit-descripcion" class="block text-sm font-medium">Descripción</label>
                    <textarea id="edit-descripcion" name="descripcion" rows="2" class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"></textarea>
                </div>

                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" onclick="closeModal('edit-modal')" class="rounded-lg bg-gray-100 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-200">
                        Cancelar
                    </button>
                    <button type="submit" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">
                        Guardar cambios
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Modal ver --}}
    <div id="view-modal" class="fixed inset-0 z-50 hidden items-center justify-center p-4">
        <div class="absolute inset-0 bg-black/50" onclick="closeModal('view-modal')"></div>
        <div class="relative flex max-h-[90vh] w-full max-w-3xl flex-col overflow-hidden rounded-2xl bg-white shadow-xl">
            <div class="flex items-center justify-between border-b border-gray-200 px-6 py-4">
                <h2 class="text-lg font-semibold">Detalle del producto</h2>
                <button type="button" onclick="closeModal('view-modal')" class="rounded-lg p-1 text-gray-400 hover:bg-gray-100 hover:text-gray-600">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <div class="min-h-0 flex-1 overflow-y-auto px-6 py-5">
                <div class="mb-5 flex flex-wrap items-center gap-4">
                    <div class="flex h-32 w-32 shrink-0 items-center justify-center overflow-hidden rounded-xl border border-gray-200 bg-gray-50">
                        <img id="view-imagen" src="" alt="Imagen del producto" class="hidden h-full w-full object-cover">
                        <span id="view-imagen-placeholder" class="px-2 text-center text-xs text-gray-400">Sin imagen</span>
                    </div>
                    <div class="min-w-0">
                        <p id="view-nombre" class="text-lg font-semibold text-gray-900"></p>
                        <p id="view-sku-cabecera" class="mt-1 text-sm text-gray-500"></p>
                    </div>
                </div>

                <table class="w-full text-sm">
                    <tbody class="divide-y divide-gray-100">
                        <tr>
                            <th scope="row" class="w-1/3 py-2.5 pr-4 text-left font-medium text-gray-500">ID</th>
                            <td id="view-id" class="py-2.5 text-gray-900"></td>
                        </tr>
                        <tr>
                            <th scope="row" class="py-2.5 pr-4 text-left font-medium text-gray-500">SKU</th>
                            <td id="view-sku" class="py-2.5 text-gray-900"></td>
                        </tr>
                        <tr>
                            <th scope="row" class="py-2.5 pr-4 text-left font-medium text-gray-500">Nombre</th>
                            <td id="view-nombre-tabla" class="py-2.5 text-gray-900"></td>
                        </tr>
                        <tr>
                            <th scope="row" class="py-2.5 pr-4 text-left font-medium text-gray-500">Código de barras</th>
                            <td id="view-codigo-barras" class="py-2.5 text-gray-900"></td>
                        </tr>
                        <tr>
                            <th scope="row" class="py-2.5 pr-4 text-left font-medium text-gray-500">Categoría</th>
                            <td id="view-categoria" class="py-2.5 text-gray-900"></td>
                        </tr>
                        <tr>
                            <th scope="row" class="py-2.5 pr-4 text-left font-medium text-gray-500">Moneda</th>
                            <td id="view-moneda" class="py-2.5 text-gray-900"></td>
                        </tr>
                        <tr>
                            <th scope="row" class="py-2.5 pr-4 text-left font-medium text-gray-500">Descripción</th>
                            <td id="view-descripcion" class="whitespace-pre-line py-2.5 text-gray-900"></td>
                        </tr>
                        <tr>
                            <th scope="row" class="py-2.5 pr-4 text-left font-medium text-gray-500">Precio de costo</th>
                            <td id="view-precio-costo" class="py-2.5 text-gray-900"></td>
                        </tr>
                        <tr>
                            <th scope="row" class="py-2.5 pr-4 text-left font-medium text-gray-500">Precio de venta</th>
                            <td id="view-precio-venta" class="py-2.5 text-gray-900"></td>
                        </tr>
                        <tr>
                            <th scope="row" class="py-2.5 pr-4 text-left font-medium text-gray-500">Precio de venta en bolívares</th>
                            <td id="view-precio-venta-bolivares" class="py-2.5 text-gray-900"></td>
                        </tr>
                        <tr>
                            <th scope="row" class="py-2.5 pr-4 text-left font-medium text-gray-500">Stock actual</th>
                            <td id="view-stock" class="py-2.5 text-gray-900"></td>
                        </tr>
                        <tr>
                            <th scope="row" class="py-2.5 pr-4 text-left font-medium text-gray-500">Stock mínimo</th>
                            <td id="view-stock-minimo" class="py-2.5 text-gray-900"></td>
                        </tr>
                        <tr>
                            <th scope="row" class="py-2.5 pr-4 text-left font-medium text-gray-500">Stock máximo</th>
                            <td id="view-stock-maximo" class="py-2.5 text-gray-900"></td>
                        </tr>
                        <tr>
                            <th scope="row" class="py-2.5 pr-4 text-left font-medium text-gray-500">Unidad de medida</th>
                            <td id="view-unidad-medida" class="py-2.5 text-gray-900"></td>
                        </tr>
                        <tr>
                            <th scope="row" class="py-2.5 pr-4 text-left font-medium text-gray-500">Fecha de creación</th>
                            <td id="view-creado-en" class="py-2.5 text-gray-900"></td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="flex justify-end border-t border-gray-200 px-6 py-4">
                <button type="button" onclick="closeModal('view-modal')" class="rounded-lg bg-gray-100 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-200">
                    Cerrar
                </button>
            </div>
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
                <h2 class="text-lg font-semibold">Eliminar producto</h2>
                <p class="mt-2 text-sm text-gray-600">
                    ¿Estás seguro de que deseas eliminar el producto <strong id="delete-producto-nombre"></strong>? Esta acción no se puede deshacer.
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

    {{-- Modal cámara --}}
    <div id="camera-modal" class="fixed inset-0 z-50 hidden items-center justify-center p-4">
        <div class="absolute inset-0 bg-black/60" onclick="cerrarCamara()"></div>
        <div class="relative w-full max-w-lg overflow-hidden rounded-2xl bg-white shadow-xl">
            <div class="flex items-center justify-between border-b border-gray-200 px-6 py-4">
                <h2 class="text-lg font-semibold">Tomar foto</h2>
                <button type="button" onclick="cerrarCamara()" class="rounded-lg p-1 text-gray-400 hover:bg-gray-100 hover:text-gray-600">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <div class="px-6 py-5">
                <div class="relative overflow-hidden rounded-xl bg-black">
                    <video id="camera-video" autoplay playsinline muted class="max-h-[55vh] w-full object-contain"></video>
                </div>
                <canvas id="camera-canvas" class="hidden"></canvas>
                <p id="camera-error" class="mt-3 hidden rounded-lg bg-red-50 px-4 py-3 text-sm text-red-700"></p>
                <p class="mt-3 text-xs text-gray-500">Enfoca el producto y presiona «Capturar foto».</p>
            </div>
            <div class="flex justify-end gap-3 border-t border-gray-200 px-6 py-4">
                <button type="button" onclick="cerrarCamara()" class="rounded-lg bg-gray-100 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-200">
                    Cancelar
                </button>
                <button type="button" onclick="capturarFoto()" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">
                    Capturar foto
                </button>
            </div>
        </div>
    </div>

    <div id="toast" class="pointer-events-none fixed bottom-6 right-6 z-[60] hidden rounded-lg bg-gray-900 px-4 py-3 text-sm font-medium text-white shadow-lg"></div>

    @push('scripts')
        <link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/jquery.dataTables.min.css">
        <style>
        #productos-table_wrapper .dataTables_filter input {
            padding: 0.5rem 0.75rem;
            border: 1px solid #d1d5db;
            border-radius: 0.5rem;
            outline: none;
            margin-left: 0.5rem;
        }
        #productos-table_wrapper .dataTables_filter input:focus {
            border-color: #6366f1;
            box-shadow: 0 0 0 2px rgb(99 102 241 / 0.2);
        }
        #productos-table_wrapper .dataTables_length select {
            padding: 0.5rem 0.75rem;
            border: 1px solid #d1d5db;
            border-radius: 0.5rem;
            outline: none;
            margin: 0 0.25rem;
        }
        #productos-table_wrapper .dataTables_paginate .paginate_button {
            padding: 0.375rem 0.75rem;
            margin-left: 0.25rem;
            border-radius: 0.5rem;
            border: 1px solid #e5e7eb;
            color: #374151 !important;
            background: #fff;
            cursor: pointer;
        }
        #productos-table_wrapper .dataTables_paginate .paginate_button:hover:not(.disabled) {
            background: #eef2ff;
            border-color: #6366f1;
            color: #4338ca !important;
        }
        #productos-table_wrapper .dataTables_paginate .paginate_button.current {
            background: #6366f1;
            border-color: #6366f1;
            color: #fff !important;
        }
        #productos-table_wrapper .dataTables_paginate .paginate_button.disabled {
            cursor: not-allowed;
            color: #9ca3af !important;
        }
        #productos-table_wrapper .dataTables_processing {
            color: #374151;
        }
        .producto-imagen {
            height: 3rem;
            width: 3rem;
            border-radius: 0.5rem;
            border: 1px solid #e5e7eb;
            object-fit: cover;
        }
    </style>
        <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
        <script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
        <script>
        const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

        const categoriaOptions = @json($listaCategorias);
        const monedaOptions = @json($listaMonedas->mapWithKeys(fn ($moneda) => [$moneda->id => "{$moneda->codigo} — {$moneda->nombre}"]));

        // Tasas vigentes: cuántas unidades de cada moneda equivalen a 1 dólar.
        const tasasVigentes = @json($tasasVigentes);
        const monedaBaseId = @json($monedaBaseId);
        const monedaBolivarId = @json($monedaBolivarId);

        function tasaDe(monedaId) {
            return Number(tasasVigentes[monedaId] ?? 1);
        }

        function precioEnBolivares(monto, monedaId) {
            const tasaOrigen = tasaDe(monedaId);
            const tasaDestino = monedaBolivarId !== null ? tasaDe(monedaBolivarId) : null;

            if (!tasaDestino || !tasaOrigen) {
                return null;
            }

            return (parseFloat(monto) || 0) * (tasaDestino / tasaOrigen);
        }

        function formatoNumero(valor) {
            return parseFloat(valor || 0).toLocaleString('es-VE', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        }

        function actualizarEquivalenteBolivares(modo) {
            const form = document.getElementById(modo + '-form');
            const monedaId = form.elements['moneda_id'].value;
            const venta = document.getElementById(modo + '-precio-venta');
            const equivalente = document.getElementById(modo + '-precio-venta-bs');

            if (!venta || !equivalente) {
                return;
            }

            const precioBs = precioEnBolivares(venta.value, monedaId);

            equivalente.textContent = precioBs === null
                ? ''
                : `Equivale a Bs. ${formatoNumero(precioBs)}`;
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

        const imagenObjectUrls = {};

        function liberarVistaPrevia(modo) {
            if (imagenObjectUrls[modo]) {
                URL.revokeObjectURL(imagenObjectUrls[modo]);
                delete imagenObjectUrls[modo];
            }
        }

        function actualizarVistaPrevia(modo, url, objectUrl = false) {
            const preview = document.getElementById(modo + '-imagen-preview');
            const placeholder = document.getElementById(modo + '-imagen-placeholder');
            const quitar = document.getElementById(modo + '-imagen-quitar');

            liberarVistaPrevia(modo);

            if (objectUrl) {
                imagenObjectUrls[modo] = url;
            }

            if (!url) {
                preview.removeAttribute('src');
                preview.classList.add('hidden');
                placeholder.classList.remove('hidden');
                quitar.classList.add('hidden');
                return;
            }

            preview.src = url;
            preview.classList.remove('hidden');
            placeholder.classList.add('hidden');
            quitar.classList.remove('hidden');
        }

        function previsualizarArchivo(modo, archivo) {
            actualizarVistaPrevia(modo, URL.createObjectURL(archivo), true);
        }

        function asignarArchivo(modo, archivo) {
            const input = document.getElementById(modo + '-imagen');

            try {
                const transferencia = new DataTransfer();
                transferencia.items.add(archivo);
                input.files = transferencia.files;
            } catch (error) {
                input.value = '';
            }

            document.getElementById(modo + '-quitar-imagen').value = '0';
            previsualizarArchivo(modo, archivo);
        }

        function quitarImagen(modo) {
            document.getElementById(modo + '-imagen').value = '';
            document.getElementById(modo + '-camera-input').value = '';
            document.getElementById(modo + '-quitar-imagen').value = '1';
            actualizarVistaPrevia(modo, null);
        }

        let cameraStream = null;
        let cameraModo = 'register';

        window.abrirCamara = async function (modo) {
            cameraModo = modo;
            const cameraInput = document.getElementById(modo + '-camera-input');

            if (!window.isSecureContext || !navigator.mediaDevices?.getUserMedia) {
                cameraInput.click();
                return;
            }

            try {
                cameraStream = await navigator.mediaDevices.getUserMedia({
                    video: { facingMode: { ideal: 'environment' } },
                    audio: false,
                });

                const video = document.getElementById('camera-video');
                video.srcObject = cameraStream;
                document.getElementById('camera-error').classList.add('hidden');
                openModal('camera-modal');
            } catch (error) {
                cameraInput.click();
            }
        };

        window.cerrarCamara = function () {
            if (cameraStream) {
                cameraStream.getTracks().forEach((track) => track.stop());
                cameraStream = null;
            }

            const video = document.getElementById('camera-video');

            if (video) {
                video.srcObject = null;
            }

            closeModal('camera-modal');
        };

        window.capturarFoto = function () {
            const video = document.getElementById('camera-video');
            const canvas = document.getElementById('camera-canvas');
            const errorEl = document.getElementById('camera-error');

            if (!video.videoWidth || !video.videoHeight) {
                errorEl.textContent = 'La cámara aún no está lista. Espera un momento e intenta de nuevo.';
                errorEl.classList.remove('hidden');
                return;
            }

            const ladoMaximo = 1600;
            const escala = Math.min(1, ladoMaximo / Math.max(video.videoWidth, video.videoHeight));
            canvas.width = Math.round(video.videoWidth * escala);
            canvas.height = Math.round(video.videoHeight * escala);
            canvas.getContext('2d').drawImage(video, 0, 0, canvas.width, canvas.height);

            canvas.toBlob((blob) => {
                if (!blob) {
                    errorEl.textContent = 'No se pudo capturar la foto. Intenta de nuevo.';
                    errorEl.classList.remove('hidden');
                    return;
                }

                const archivo = new File([blob], 'foto-' + Date.now() + '.jpg', { type: 'image/jpeg' });
                asignarArchivo(cameraModo, archivo);
                cerrarCamara();
                toast('Foto lista para guardar.');
            }, 'image/jpeg', 0.92);
        };

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

        window.openRegisterModal = function () {
            document.getElementById('register-form').reset();
            buildOptions(document.getElementById('register-categoria-id'), categoriaOptions, '', 'Selecciona una categoría');
            buildOptions(document.getElementById('register-moneda-id'), monedaOptions, monedaBaseId, 'Selecciona una moneda');
            document.getElementById('register-imagen').value = '';
            document.getElementById('register-camera-input').value = '';
            document.getElementById('register-quitar-imagen').value = '0';
            actualizarVistaPrevia('register', null);
            actualizarEquivalenteBolivares('register');
            clearErrors('register-errors');
            openModal('register-modal');
        };

        window.openEditModal = function (button) {
            const form = document.getElementById('edit-form');
            document.getElementById('edit-imagen').value = '';
            document.getElementById('edit-camera-input').value = '';
            document.getElementById('edit-quitar-imagen').value = '0';
            actualizarVistaPrevia('edit', button.dataset.imagen || null);
            form.elements['sku'].value = button.dataset.sku || '';
            form.elements['codigo_barras'].value = button.dataset.codigoBarras || '';
            form.elements['nombre'].value = button.dataset.nombre;
            form.elements['descripcion'].value = button.dataset.descripcion || '';
            form.elements['precio_costo'].value = button.dataset.precioCosto || '';
            form.elements['precio_venta'].value = button.dataset.precioVenta || '';
            form.elements['stock_minimo'].value = button.dataset.stockMinimo || '';
            form.elements['stock_maximo'].value = button.dataset.stockMaximo || '';
            form.elements['unidad_medida'].value = button.dataset.unidadMedida || 'PZA';
            buildOptions(form.elements['categoria_id'], categoriaOptions, button.dataset.categoria, 'Selecciona una categoría');
            buildOptions(form.elements['moneda_id'], monedaOptions, button.dataset.moneda, 'Selecciona una moneda');
            form.action = '/productos/' + button.dataset.id;
            actualizarEquivalenteBolivares('edit');
            clearErrors('edit-errors');
            openModal('edit-modal');
        };

        let deleteProductoId = null;

        function establecerTexto(id, valor, textoVacio = '—') {
            const elemento = document.getElementById(id);

            if (!elemento) {
                return;
            }

            const texto = valor === null || valor === undefined || String(valor).trim() === ''
                ? textoVacio
                : String(valor);

            elemento.textContent = texto;
        }

        window.openViewModal = function (button) {
            const datos = button.dataset;
            const unidades = {
                PZA: 'PZA (Unidad)',
                CAJA: 'CAJA',
                LT: 'LT (Litro)',
                KG: 'KG (Kilogramo)',
                UND: 'UND (Und.)',
            };

            establecerTexto('view-id', datos.id);
            establecerTexto('view-sku', datos.sku);
            establecerTexto('view-sku-cabecera', datos.sku ? 'SKU: ' + datos.sku : '');
            establecerTexto('view-nombre', datos.nombre);
            establecerTexto('view-nombre-tabla', datos.nombre);
            establecerTexto('view-codigo-barras', datos.codigoBarras);
            establecerTexto('view-categoria', datos.categoriaNombre);
            establecerTexto('view-moneda', datos.moneda);
            establecerTexto('view-descripcion', datos.descripcion, 'Sin descripción');
            establecerTexto('view-precio-costo', datos.precioCosto);
            establecerTexto('view-precio-venta', datos.precioVenta);
            establecerTexto('view-precio-venta-bolivares', datos.precioVentaBolivares);
            establecerTexto('view-stock', datos.stock);
            establecerTexto('view-stock-minimo', datos.stockMinimo);
            establecerTexto('view-stock-maximo', datos.stockMaximo, 'Sin límite');
            establecerTexto('view-unidad-medida', unidades[datos.unidadMedida] || datos.unidadMedida);
            establecerTexto('view-creado-en', datos.creadoEn);

            const imagen = document.getElementById('view-imagen');
            const placeholder = document.getElementById('view-imagen-placeholder');

            if (datos.imagen) {
                imagen.src = datos.imagen;
                imagen.alt = 'Imagen de ' + (datos.nombre || 'producto');
                imagen.classList.remove('hidden');
                placeholder.classList.add('hidden');
            } else {
                imagen.removeAttribute('src');
                imagen.classList.add('hidden');
                placeholder.classList.remove('hidden');
            }

            openModal('view-modal');
        };

        window.openDeleteModal = function (button) {
            deleteProductoId = button.dataset.id;
            document.getElementById('delete-producto-nombre').textContent = button.dataset.nombre;
            openModal('delete-modal');
        };

        document.addEventListener('DOMContentLoaded', function () {
            const table = $('#productos-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: '{{ route('productos.data') }}',
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
                columns: [
                    { data: 'sku' },
                    { data: 'nombre' },
                    { data: 'imagen', orderable: false, searchable: false },
                    { data: 'categoria', orderable: false },
                    { data: 'precio_costo' },
                    {
                        data: 'precio_venta',
                        render: function (data, type, row) {
                            if (type !== 'display') {
                                return data;
                            }

                            return data + '<span class="block text-xs text-gray-500">' + (row.precio_venta_bolivares || '') + '</span>';
                        },
                    },
                    { data: 'stock' },
                    { data: 'actions', orderable: false, searchable: false },
                ],
                order: [[0, 'asc']],
                pageLength: 10,
            });

            document.getElementById('register-form').addEventListener('submit', submitForm('register-form', 'register-overlay', 'register-modal'));
            document.getElementById('edit-form').addEventListener('submit', submitForm('edit-form', 'edit-overlay', 'edit-modal'));

            ['register', 'edit'].forEach((modo) => {
                document.getElementById(modo + '-precio-venta').addEventListener('input', () => actualizarEquivalenteBolivares(modo));
                document.getElementById(modo + '-moneda-id').addEventListener('change', () => actualizarEquivalenteBolivares(modo));

                document.getElementById(modo + '-imagen').addEventListener('change', function () {
                    if (this.files && this.files[0]) {
                        document.getElementById(modo + '-quitar-imagen').value = '0';
                        previsualizarArchivo(modo, this.files[0]);
                    }
                });

                document.getElementById(modo + '-camera-input').addEventListener('change', function () {
                    if (this.files && this.files[0]) {
                        asignarArchivo(modo, this.files[0]);
                    }

                    this.value = '';
                });
            });

            function submitForm(formId, overlayId, modalId) {
                return async function (event) {
                    event.preventDefault();
                    const form = event.target;
                    const overlay = document.getElementById(overlayId);
                    const errorsContainer = formId === 'register-form' ? 'register-errors' : 'edit-errors';

                    clearErrors(errorsContainer);
                    showOverlay(overlayId);

                    try {
                        const response = await fetch(form.action, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': csrfToken,
                                'Accept': 'application/json',
                            },
                            body: new FormData(form),
                            credentials: 'same-origin',
                        });

                        const data = await response.json().catch(() => ({}));

                        hideOverlay(overlayId);

                        if (!response.ok) {
                            renderErrors(errorsContainer, data.errors);
                            return;
                        }

                        closeModal(modalId);
                        $('#productos-table').DataTable().ajax.reload();
                        toast(data.message || 'Operación realizada correctamente.');
                    } catch (error) {
                        hideOverlay(overlayId);
                        toast('Ocurrió un error inesperado. Inténtalo de nuevo.');
                    }
                };
            }
        });

        window.confirmDelete = async function () {
            const overlayId = 'delete-overlay';
            showOverlay(overlayId);

            const form = document.getElementById('delete-form');
            form.action = '/productos/' + deleteProductoId;

            try {
                const response = await fetch(form.action, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                    },
                    body: new FormData(form),
                    credentials: 'same-origin',
                });

                const data = await response.json().catch(() => ({}));

                hideOverlay(overlayId);

                if (!response.ok) {
                    toast(data.message || 'No se pudo eliminar el producto.');
                    return;
                }

                closeModal('delete-modal');
                deleteProductoId = null;
                $('#productos-table').DataTable().ajax.reload();
                toast(data.message || 'Producto eliminado correctamente.');
            } catch (error) {
                hideOverlay(overlayId);
                toast('Ocurrió un error inesperado. Inténtalo de nuevo.');
            }
        };
    </script>
    @endpush
</x-admin-layout>