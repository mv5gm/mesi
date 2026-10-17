<x-admin-layout title="Dashboard">
    <h1 class="text-xl font-semibold">Bienvenido, {{ auth()->user()->name }}</h1>

    @if (auth()->user()->hasRole('admin'))
        <div class="mt-3 rounded-lg bg-emerald-50 border border-emerald-200 px-4 py-3 text-sm text-emerald-800">
            Tienes el rol de <strong>admin</strong> y todos los permisos del sistema.
        </div>
    @endif

    <div class="mt-6 rounded-2xl bg-white p-6 shadow">
        <h2 class="text-sm font-semibold uppercase tracking-wide text-gray-500">Roles asignados</h2>
        <div class="mt-3 flex flex-wrap gap-2">
            @forelse (auth()->user()->roles as $rol)
                <span class="rounded-full bg-indigo-100 px-3 py-1 text-sm font-medium text-indigo-700">
                    {{ $rol->name }}
                </span>
            @empty
                <span class="text-sm text-gray-500">Sin roles asignados.</span>
            @endforelse
        </div>
    </div>
</x-admin-layout>