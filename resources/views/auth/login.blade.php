<x-layout title="Iniciar sesión">
    <div class="flex min-h-screen items-center justify-center px-4">
        <div class="w-full max-w-md">
            <div class="rounded-2xl bg-white p-8 shadow-lg">
                <div class="flex justify-center">
                    <img
                        src="{{ asset('img/mesi-logo.png') }}"
                        alt="Mesi - Motor & Engine Spare Inventory"
                        class="w-full max-w-sm"
                    >
                </div>
                <p class="mt-4 text-center text-sm text-gray-500">Inicia sesión con tu usuario</p>

                @if ($errors->any())
                    <div class="mt-4 rounded-lg bg-red-50 px-4 py-3 text-sm text-red-700">
                        <ul class="list-disc space-y-1 pl-4">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="/login" class="mt-6 space-y-4">
                    @csrf

                    <div>
                        <label for="username" class="block text-sm font-medium">Usuario</label>
                        <input
                            id="username"
                            type="text"
                            name="username"
                            value="{{ old('username') }}"
                            required
                            autofocus
                            autocomplete="username"
                            class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                        >
                    </div>

                    <div>
                        <label for="password" class="block text-sm font-medium">Contraseña</label>
                        <input
                            id="password"
                            type="password"
                            name="password"
                            required
                            autocomplete="current-password"
                            class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                        >
                    </div>

                    <div class="flex items-center justify-between">
                        <label class="flex items-center text-sm">
                            <input type="checkbox" name="remember" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                            <span class="ml-2">Recordarme</span>
                        </label>
                    </div>

                    <button
                        type="submit"
                        class="w-full rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-indigo-500"
                    >
                        Iniciar sesión
                    </button>
                </form>
            </div>
        </div>
    </div>
</x-layout>