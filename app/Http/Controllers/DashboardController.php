<?php

namespace App\Http\Controllers;

use App\Enums\EstadoOrdenVenta;
use App\Models\Cliente;
use App\Models\Moneda;
use App\Models\OrdenVenta;
use App\Models\Producto;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $monedaBase = Moneda::where('es_moneda_base', true)->first();
        $tasas = Moneda::tasasVigentes();

        $invertido = $this->totalInvertido([
            DB::table('existencias')
                ->join('productos', 'productos.id', '=', 'existencias.producto_id')
                ->groupBy('productos.moneda_id')
                ->selectRaw('productos.moneda_id, SUM(existencias.cantidad * productos.precio_costo) as total')
                ->get(),
            DB::table('detalles_orden_venta')
                ->join('productos', 'productos.id', '=', 'detalles_orden_venta.producto_id')
                ->join('ordenes_venta', 'ordenes_venta.id', '=', 'detalles_orden_venta.orden_venta_id')
                ->where('ordenes_venta.estado', '!=', EstadoOrdenVenta::Cancelada->value)
                ->groupBy('productos.moneda_id')
                ->selectRaw('productos.moneda_id, SUM(detalles_orden_venta.cantidad * productos.precio_costo) as total')
                ->get(),
        ], $tasas);

        $ventas = $this->totalEnMonedaBase(
            DB::table('detalles_orden_venta')
                ->join('productos', 'productos.id', '=', 'detalles_orden_venta.producto_id')
                ->join('ordenes_venta', 'ordenes_venta.id', '=', 'detalles_orden_venta.orden_venta_id')
                ->where('ordenes_venta.estado', '!=', EstadoOrdenVenta::Cancelada->value)
                ->groupBy('productos.moneda_id')
                ->selectRaw('productos.moneda_id, SUM(detalles_orden_venta.cantidad * productos.precio_venta) as total')
                ->get(),
            $tasas
        );

        $ganancias = $this->totalEnMonedaBase(
            DB::table('detalles_orden_venta')
                ->join('productos', 'productos.id', '=', 'detalles_orden_venta.producto_id')
                ->join('ordenes_venta', 'ordenes_venta.id', '=', 'detalles_orden_venta.orden_venta_id')
                ->where('ordenes_venta.estado', '!=', EstadoOrdenVenta::Cancelada->value)
                ->groupBy('productos.moneda_id')
                ->selectRaw('productos.moneda_id, SUM(detalles_orden_venta.cantidad * (productos.precio_venta - productos.precio_costo)) as total')
                ->get(),
            $tasas
        );

        $unidades = (int) DB::table('existencias')->sum('cantidad');

        $pagosPorMetodo = DB::table('pagos_orden_venta')
            ->join('ordenes_venta', 'ordenes_venta.id', '=', 'pagos_orden_venta.orden_venta_id')
            ->join('metodos_pago', 'metodos_pago.id', '=', 'pagos_orden_venta.metodo_pago_id')
            ->join('monedas', 'monedas.id', '=', 'metodos_pago.moneda_id')
            ->where('ordenes_venta.estado', '!=', EstadoOrdenVenta::Cancelada->value)
            ->groupBy('metodos_pago.id', 'metodos_pago.nombre', 'monedas.simbolo', 'monedas.codigo')
            ->selectRaw('metodos_pago.nombre, monedas.simbolo, monedas.codigo, SUM(pagos_orden_venta.monto) as monto, SUM(pagos_orden_venta.monto_base) as monto_base')
            ->orderByDesc('monto_base')
            ->get();

        $totalCobradoBase = (float) $pagosPorMetodo->sum('monto_base');

        return view('dashboard', [
            'monedaBase' => $monedaBase,
            'invertido' => $invertido,
            'ventas' => $ventas,
            'ganancias' => $ganancias,
            'unidadesExistencias' => $unidades,
            'totalProductos' => Producto::count(),
            'totalOrdenes' => OrdenVenta::where('estado', '!=', EstadoOrdenVenta::Cancelada->value)->count(),
            'totalClientes' => Cliente::count(),
            'pagosPorMetodo' => $pagosPorMetodo,
            'totalCobradoBase' => $totalCobradoBase,
        ]);
    }

    /**
     * Suma los montos de cada moneda y los convierte a la moneda base.
     *
     * @param  Collection<int, object>  $grupos
     * @param  array<int, float>  $tasas
     */
    private function totalEnMonedaBase($grupos, array $tasas): float
    {
        $total = 0.0;

        foreach ($grupos as $grupo) {
            $total += (float) $grupo->total / ($tasas[$grupo->moneda_id] ?? 1.0);
        }

        return round($total, 2);
    }

    /**
     * Total invertido: el costo de lo que sigue en existencias más el de lo ya vendido.
     *
     * @param  array<int, Collection<int, object>>  $grupos
     * @param  array<int, float>  $tasas
     */
    private function totalInvertido(array $grupos, array $tasas): float
    {
        $porMoneda = [];

        foreach ($grupos as $grupo) {
            foreach ($grupo as $fila) {
                $porMoneda[$fila->moneda_id] = ($porMoneda[$fila->moneda_id] ?? 0.0) + (float) $fila->total;
            }
        }

        $total = 0.0;

        foreach ($porMoneda as $monedaId => $monto) {
            $total += $monto / ($tasas[$monedaId] ?? 1.0);
        }

        return round($total, 2);
    }
}
