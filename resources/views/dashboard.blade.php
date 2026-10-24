<x-admin-layout title="Dashboard">
    @php
        $simbolo = $monedaBase?->simbolo ?? '';
        $codigo = $monedaBase?->codigo ?? '';
        $maximo = max($invertido, $ventas, $ganancias);
        $altura = fn (float $valor): int => $maximo > 0 ? max(4, (int) round($valor / $maximo * 150)) : 4;
        $altoInvertido = $altura($invertido);
        $altoGanancias = $altura($ganancias);
        $altoVentas = $altura($ventas);
        $margen = $ventas > 0 ? ($ganancias / $ventas) * 100 : 0.0;
        $colores = ['bg-indigo-500', 'bg-sky-500', 'bg-emerald-500', 'bg-amber-500', 'bg-violet-500', 'bg-rose-500'];
    @endphp

    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <h1 class="text-xl font-semibold">Bienvenido, {{ auth()->user()->name }}</h1>
            <p class="mt-1 text-sm text-gray-500">Resumen del inventario y las ventas.</p>
        </div>
        <span class="rounded-full bg-indigo-100 px-3 py-1 text-xs font-medium text-indigo-700">
            Montos expresados en {{ $codigo !== '' ? $codigo : 'la moneda base' }}
        </span>
    </div>

    @if (auth()->user()->hasRole('admin'))
        <div class="mt-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
            Tienes el rol de <strong>admin</strong> y todos los permisos del sistema.
        </div>
    @endif

    <div class="mt-6 grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <div class="rounded-2xl bg-white p-5 shadow">
            <p class="text-xs font-medium uppercase tracking-wide text-gray-500">Total invertido</p>
            <p class="mt-2 text-2xl font-semibold text-gray-900">{{ $simbolo }} {{ number_format($invertido, 2, ',', '.') }}</p>
            <p class="mt-1 text-xs text-gray-500">Existencias en almacén + costo de lo ya vendido</p>
        </div>
        <div class="rounded-2xl bg-white p-5 shadow">
            <p class="text-xs font-medium uppercase tracking-wide text-gray-500">Total por ventas</p>
            <p class="mt-2 text-2xl font-semibold text-sky-600">{{ $simbolo }} {{ number_format($ventas, 2, ',', '.') }}</p>
            <p class="mt-1 text-xs text-gray-500">Unidades vendidas a precio de venta</p>
        </div>
        <div class="rounded-2xl bg-white p-5 shadow">
            <p class="text-xs font-medium uppercase tracking-wide text-gray-500">Ganancias</p>
            <p class="mt-2 text-2xl font-semibold text-emerald-600">{{ $simbolo }} {{ number_format($ganancias, 2, ',', '.') }}</p>
            <p class="mt-1 text-xs text-gray-500">Precio de venta menos precio de costo</p>
        </div>
        <div class="rounded-2xl bg-white p-5 shadow">
            <p class="text-xs font-medium uppercase tracking-wide text-gray-500">Unidades en existencias</p>
            <p class="mt-2 text-2xl font-semibold text-gray-900">{{ number_format($unidadesExistencias, 0, ',', '.') }}</p>
            <p class="mt-1 text-xs text-gray-500">{{ $totalProductos }} productos · {{ $totalOrdenes }} ventas</p>
        </div>
    </div>

    <div class="mt-6 grid grid-cols-1 gap-6 lg:grid-cols-3">
        <div class="rounded-2xl bg-white p-6 shadow lg:col-span-2">
            <div class="flex flex-wrap items-center justify-between gap-2">
                <h2 class="text-sm font-semibold uppercase tracking-wide text-gray-500">Invertido, ganancias y ventas</h2>
                <span class="text-xs text-gray-500">
                    Margen sobre lo vendido:
                    <strong class="{{ $margen >= 0 ? 'text-emerald-600' : 'text-red-600' }}">{{ number_format($margen, 1, ',', '.') }}%</strong>
                </span>
            </div>

            <div class="mt-6 flex items-end gap-6">
                <div class="flex flex-1 flex-col items-center">
                    <div class="flex h-40 w-full items-end justify-center">
                        <div
                            class="w-full max-w-32 rounded-t-lg bg-indigo-500 transition-all"
                            style="height: {{ $altoInvertido }}px"
                            role="img"
                            aria-label="Total invertido: existencias más costo de lo ya vendido"
                        ></div>
                    </div>
                    <p class="mt-3 text-center text-sm font-semibold text-gray-900">
                        {{ $simbolo }} {{ number_format($invertido, 2, ',', '.') }}
                    </p>
                    <p class="text-center text-xs text-gray-500">Total invertido (existencias + vendidos a costo)</p>
                </div>

                <div class="flex flex-1 flex-col items-center">
                    <div class="flex h-40 w-full items-end justify-center">
                        <div
                            class="w-full max-w-32 rounded-t-lg bg-emerald-500 transition-all"
                            style="height: {{ $altoGanancias }}px"
                            role="img"
                            aria-label="Total de ganancias de las ventas"
                        ></div>
                    </div>
                    <p class="mt-3 text-center text-sm font-semibold text-gray-900">
                        {{ $simbolo }} {{ number_format($ganancias, 2, ',', '.') }}
                    </p>
                    <p class="text-center text-xs text-gray-500">Ganancias (venta − costo)</p>
                </div>

                <div class="flex flex-1 flex-col items-center">
                    <div class="flex h-40 w-full items-end justify-center">
                        <div
                            class="w-full max-w-32 rounded-t-lg bg-sky-500 transition-all"
                            style="height: {{ $altoVentas }}px"
                            role="img"
                            aria-label="Total generado por las ventas"
                        ></div>
                    </div>
                    <p class="mt-3 text-center text-sm font-semibold text-gray-900">
                        {{ $simbolo }} {{ number_format($ventas, 2, ',', '.') }}
                    </p>
                    <p class="text-center text-xs text-gray-500">Total por ventas (precio de venta)</p>
                </div>
            </div>
        </div>

        <div class="rounded-2xl bg-white p-6 shadow">
            <h2 class="text-sm font-semibold uppercase tracking-wide text-gray-500">Detalle</h2>
            <dl class="mt-4 space-y-3 text-sm">
                <div class="flex items-center justify-between gap-3">
                    <dt class="text-gray-600">Total invertido (existencias + vendidos a costo)</dt>
                    <dd class="font-medium text-gray-900">{{ $simbolo }} {{ number_format($invertido, 2, ',', '.') }}</dd>
                </div>
                <div class="flex items-center justify-between gap-3">
                    <dt class="text-gray-600">Total por ventas (precio de venta)</dt>
                    <dd class="font-medium text-gray-900">{{ $simbolo }} {{ number_format($ventas, 2, ',', '.') }}</dd>
                </div>
                <div class="flex items-center justify-between gap-3">
                    <dt class="text-gray-600">Ganancias (venta − costo)</dt>
                    <dd class="font-medium {{ $ganancias >= 0 ? 'text-emerald-600' : 'text-red-600' }}">
                        {{ $simbolo }} {{ number_format($ganancias, 2, ',', '.') }}
                    </dd>
                </div>
                <div class="flex items-center justify-between gap-3 border-t border-gray-100 pt-3">
                    <dt class="text-gray-600">Órdenes de venta</dt>
                    <dd class="font-medium text-gray-900">{{ number_format($totalOrdenes, 0, ',', '.') }}</dd>
                </div>
                <div class="flex items-center justify-between gap-3">
                    <dt class="text-gray-600">Clientes registrados</dt>
                    <dd class="font-medium text-gray-900">{{ number_format($totalClientes, 0, ',', '.') }}</dd>
                </div>
            </dl>

            <div class="mt-6 rounded-lg bg-gray-50 p-4 text-xs text-gray-600">
                <p class="font-medium text-gray-700">Cómo se calcula</p>
                <ul class="mt-2 list-disc space-y-1 pl-4">
                    <li>Total invertido: existencias a costo + costo de los productos ya vendidos.</li>
                    <li>Total por ventas: unidades vendidas × precio de venta del producto.</li>
                    <li>Ganancias: unidades vendidas × (precio de venta − precio de costo).</li>
                    <li>No se cuentan las órdenes canceladas.</li>
                </ul>
            </div>
        </div>
    </div>

    <div class="mt-6 rounded-2xl bg-white p-6 shadow">
        <div class="flex flex-wrap items-center justify-between gap-2">
            <h2 class="text-sm font-semibold uppercase tracking-wide text-gray-500">Ventas por método de pago</h2>
            <span class="text-xs text-gray-500">
                Total cobrado:
                <strong class="text-gray-900">{{ $simbolo }} {{ number_format($totalCobradoBase, 2, ',', '.') }}</strong>
            </span>
        </div>

        @if ($pagosPorMetodo->isEmpty())
            <p class="mt-6 text-sm text-gray-500">Todavía no hay pagos registrados.</p>
        @else
            <div class="mt-6 space-y-5">
                @foreach ($pagosPorMetodo as $pago)
                    @php
                        $porcentaje = $totalCobradoBase > 0 ? ((float) $pago->monto_base / $totalCobradoBase) * 100 : 0.0;
                    @endphp
                    <div>
                        <div class="flex flex-wrap items-baseline justify-between gap-x-3 gap-y-1">
                            <span class="text-sm font-medium text-gray-900">{{ $pago->nombre }}</span>
                            <span class="text-sm font-semibold text-gray-900">
                                {{ $pago->simbolo }} {{ number_format((float) $pago->monto, 2, ',', '.') }}
                            </span>
                        </div>
                        <div class="mt-2 h-2.5 w-full overflow-hidden rounded-full bg-gray-100">
                            <div
                                class="h-2.5 rounded-full {{ $colores[$loop->index % count($colores)] }} transition-all"
                                style="width: {{ number_format($porcentaje, 2, '.', '') }}%"
                                role="img"
                                aria-label="{{ $pago->nombre }}: {{ number_format($porcentaje, 1, ',', '.') }}% del total cobrado"
                            ></div>
                        </div>
                        <p class="mt-1.5 text-xs text-gray-500">
                            {{ number_format($porcentaje, 1, ',', '.') }}% del total · equivale a
                            {{ $simbolo }} {{ number_format((float) $pago->monto_base, 2, ',', '.') }}
                        </p>
                    </div>
                @endforeach
            </div>

            <div class="mt-6 rounded-lg bg-gray-50 p-4 text-xs text-gray-600">
                <p class="font-medium text-gray-700">Cómo se calcula</p>
                <ul class="mt-2 list-disc space-y-1 pl-4">
                    <li>Cada barra muestra el monto cobrado con ese método de pago, en la moneda del propio método.</li>
                    <li>El porcentaje se calcula sobre el equivalente en {{ $codigo !== '' ? $codigo : 'la moneda base' }}.</li>
                    <li>No se cuentan las órdenes canceladas.</li>
                </ul>
            </div>
        @endif
    </div>

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
