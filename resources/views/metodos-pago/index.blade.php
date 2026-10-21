<x-admin-layout title="Métodos de pago">
    <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
        <div>
            <h1 class="text-xl font-semibold">Métodos de pago</h1>
            <p class="mt-1 text-sm text-gray-500">Administra los métodos de pago disponibles en el negocio.</p>
        </div>
        <button
            type="button"
            onclick="openRegisterModal()"
            class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500"
        >
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
            </svg>
            Nuevo método de pago
        </button>
    </div>

    <div class="rounded-xl bg-white p-4 shadow md:p-6">
        <table id="metodos-pago-table" class="w-full">
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Moneda</th>
                    <th>Requiere referencia</th>
                    <th>Acciones</th>
                </tr>
            </thead>
        </table>
    </div>

    {{-- Modal registrar --}}
    <div id="register-modal" class="fixed inset-0 z-50 hidden items-center justify-center p-4">
        <div class="absolute inset-0 bg-black/50" onclick="closeModal('register-modal')"></div>
        <div class="relative flex max-h-[90vh] w-full max-w-lg flex-col overflow-hidden rounded-2xl bg-white shadow-xl">
            <div id="register-overlay" class="absolute inset-0 z-10 hidden items-center justify-center rounded-2xl bg-white/70">
                <div class="flex items-center gap-3 rounded-lg bg-gray-900 px-5 py-3 text-sm font-medium text-white">
                    <span class="h-5 w-5 animate-spin rounded-full border-2 border-white border-t-transparent"></span>
                    Procesando...
                </div>
            </div>
            <div class="flex items-center justify-between border-b border-gray-200 px-6 py-4">
                <h2 class="text-lg font-semibold">Registrar método de pago</h2>
                <button type="button" onclick="closeModal('register-modal')" class="rounded-lg p-1 text-gray-400 hover:bg-gray-100 hover:text-gray-600">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <form id="register-form" action="{{ route('metodos-pago.store') }}" method="POST" class="min-h-0 flex-1 space-y-4 overflow-y-auto px-6 py-5">
                @csrf
                <div id="register-errors" class="hidden rounded-lg bg-red-50 px-4 py-3 text-sm text-red-700"></div>

                <div>
                    <label for="register-nombre" class="block text-sm font-medium">Nombre</label>
                    <input
                        id="register-nombre"
                        type="text"
                        name="nombre"
                        required
                        maxlength="50"
                        autocomplete="off"
                        class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                    >
                </div>

                <div>
                    <label for="register-moneda-id" class="block text-sm font-medium">Moneda</label>
                    <select
                        id="register-moneda-id"
                        name="moneda_id"
                        required
                        class="mt-1 w-full rounded-lg border border-gray-300 bg-white px-3 py-2 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                    ></select>
                    <p class="mt-1 text-xs text-gray-500">Moneda en la que se liquida este método de pago.</p>
                </div>

                <label class="flex items-center gap-2 pt-1">
                    <input
                        id="register-requiere-referencia"
                        type="checkbox"
                        name="requiere_referencia"
                        value="1"
                        class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                    >
                    <span class="text-sm font-medium">Requiere número de referencia</span>
                </label>

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
        <div class="relative flex max-h-[90vh] w-full max-w-lg flex-col overflow-hidden rounded-2xl bg-white shadow-xl">
            <div id="edit-overlay" class="absolute inset-0 z-10 hidden items-center justify-center rounded-2xl bg-white/70">
                <div class="flex items-center gap-3 rounded-lg bg-gray-900 px-5 py-3 text-sm font-medium text-white">
                    <span class="h-5 w-5 animate-spin rounded-full border-2 border-white border-t-transparent"></span>
                    Procesando...
                </div>
            </div>
            <div class="flex items-center justify-between border-b border-gray-200 px-6 py-4">
                <h2 class="text-lg font-semibold">Editar método de pago</h2>
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
                    <input id="edit-nombre" type="text" name="nombre" required maxlength="50" autocomplete="off" class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                </div>

                <div>
                    <label for="edit-moneda-id" class="block text-sm font-medium">Moneda</label>
                    <select
                        id="edit-moneda-id"
                        name="moneda_id"
                        required
                        class="mt-1 w-full rounded-lg border border-gray-300 bg-white px-3 py-2 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                    ></select>
                    <p class="mt-1 text-xs text-gray-500">Moneda en la que se liquida este método de pago.</p>
                </div>

                <label class="flex items-center gap-2 pt-1">
                    <input
                        id="edit-requiere-referencia"
                        type="checkbox"
                        name="requiere_referencia"
                        value="1"
                        class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                    >
                    <span class="text-sm font-medium">Requiere número de referencia</span>
                </label>

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
                <h2 class="text-lg font-semibold">Eliminar método de pago</h2>
                <p class="mt-2 text-sm text-gray-600">
                    ¿Estás seguro de que deseas eliminar el método de pago <strong id="delete-metodo-nombre"></strong>? Esta acción no se puede deshacer.
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
        #metodos-pago-table_wrapper .dataTables_filter input {
            padding: 0.5rem 0.75rem;
            border: 1px solid #d1d5db;
            border-radius: 0.5rem;
            outline: none;
            margin-left: 0.5rem;
        }
        #metodos-pago-table_wrapper .dataTables_filter input:focus {
            border-color: #6366f1;
            box-shadow: 0 0 0 2px rgb(99 102 241 / 0.2);
        }
        #metodos-pago-table_wrapper .dataTables_length select {
            padding: 0.5rem 0.75rem;
            border: 1px solid #d1d5db;
            border-radius: 0.5rem;
            outline: none;
            margin: 0 0.25rem;
        }
        #metodos-pago-table_wrapper .dataTables_paginate .paginate_button {
            padding: 0.375rem 0.75rem;
            margin-left: 0.25rem;
            border-radius: 0.5rem;
            border: 1px solid #e5e7eb;
            color: #374151 !important;
            background: #fff;
            cursor: pointer;
        }
        #metodos-pago-table_wrapper .dataTables_paginate .paginate_button:hover:not(.disabled) {
            background: #eef2ff;
            border-color: #6366f1;
            color: #4338ca !important;
        }
        #metodos-pago-table_wrapper .dataTables_paginate .paginate_button.current {
            background: #6366f1;
            border-color: #6366f1;
            color: #fff !important;
        }
        #metodos-pago-table_wrapper .dataTables_paginate .paginate_button.disabled {
            cursor: not-allowed;
            color: #9ca3af !important;
        }
        #metodos-pago-table_wrapper .dataTables_processing {
            color: #374151;
        }
        </style>
        <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
        <script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
        <script>
        const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

        const monedaOptions = @json($listaMonedas->mapWithKeys(fn ($moneda) => [$moneda->id => "{$moneda->codigo} — {$moneda->nombre}"]));

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

        window.openRegisterModal = function () {
            document.getElementById('register-form').reset();
            buildOptions(document.getElementById('register-moneda-id'), monedaOptions, '', 'Selecciona una moneda');
            clearErrors('register-errors');
            openModal('register-modal');
        };

        window.openEditModal = function (button) {
            const form = document.getElementById('edit-form');
            form.elements['nombre'].value = button.dataset.nombre;
            form.elements['requiere_referencia'].checked = button.dataset.requiereReferencia === '1';
            buildOptions(form.elements['moneda_id'], monedaOptions, button.dataset.moneda, 'Selecciona una moneda');
            form.action = '/metodos-pago/' + button.dataset.id;
            clearErrors('edit-errors');
            openModal('edit-modal');
        };

        let deleteMetodoId = null;

        window.openDeleteModal = function (button) {
            deleteMetodoId = button.dataset.id;
            document.getElementById('delete-metodo-nombre').textContent = button.dataset.nombre;
            openModal('delete-modal');
        };

        document.addEventListener('DOMContentLoaded', function () {
            $('#metodos-pago-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: '{{ route('metodos-pago.data') }}',
                language: {
                    url: 'https://cdn.datatables.net/plug-ins/1.13.8/i18n/es-ES.json',
                },
                columns: [
                    { data: 'nombre' },
                    { data: 'moneda', orderable: false },
                    { data: 'requiere_referencia', orderable: false },
                    { data: 'actions', orderable: false, searchable: false },
                ],
                order: [[0, 'asc']],
                pageLength: 10,
            });

            document.getElementById('register-form').addEventListener('submit', submitForm('register-form', 'register-overlay', 'register-modal'));
            document.getElementById('edit-form').addEventListener('submit', submitForm('edit-form', 'edit-overlay', 'edit-modal'));

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
                        $('#metodos-pago-table').DataTable().ajax.reload();
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
            form.action = '/metodos-pago/' + deleteMetodoId;

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
                    toast(data.message || 'No se pudo eliminar el método de pago.');
                    return;
                }

                closeModal('delete-modal');
                deleteMetodoId = null;
                $('#metodos-pago-table').DataTable().ajax.reload();
                toast(data.message || 'Método de pago eliminado correctamente.');
            } catch (error) {
                hideOverlay(overlayId);
                toast('Ocurrió un error inesperado. Inténtalo de nuevo.');
            }
        };
        </script>
    @endpush
</x-admin-layout>