<?php

namespace App\Http\Controllers;

use App\Models\MetodoPago;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class MetodoPagoController extends Controller
{
    public function index(): View
    {
        return view('metodos-pago.index');
    }

    public function data(Request $request): JsonResponse
    {
        $metodosPago = MetodoPago::query();

        $recordsTotal = $metodosPago->count();

        if ($search = $request->string('search.value')->toString()) {
            $metodosPago->where(function (Builder $query) use ($search): void {
                $query->where('nombre', 'like', "%{$search}%")
                    ->when(in_array(mb_strtolower($search), ['si', 'sí', 'no']), function (Builder $query) use ($search): void {
                        $query->orWhere('requiere_referencia', in_array(mb_strtolower($search), ['si', 'sí']));
                    });
            });
        }

        $recordsFiltered = (clone $metodosPago)->count();

        $columnMap = [
            0 => 'nombre',
        ];
        $column = $columnMap[$request->integer('order.0.column')] ?? 'nombre';
        $direction = $request->string('order.0.dir')->toString() === 'desc' ? 'desc' : 'asc';

        $metodosPago->orderBy($column, $direction);

        $length = $request->integer('length', 10);
        if ($length !== -1) {
            $metodosPago->skip($request->integer('start', 0))->take($length);
        }

        $data = $metodosPago->get()->map(fn (MetodoPago $metodoPago): array => [
            'nombre' => e($metodoPago->nombre),
            'requiere_referencia' => $metodoPago->requiere_referencia
                ? '<span class="inline-flex items-center rounded-full bg-emerald-100 px-2.5 py-0.5 text-xs font-medium text-emerald-700">Sí</span>'
                : '<span class="inline-flex items-center rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-medium text-gray-600">No</span>',
            'actions' => view('metodos-pago.partials.actions', ['metodoPago' => $metodoPago])->render(),
        ]);

        return response()->json([
            'draw' => $request->integer('draw'),
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => $data->values(),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'nombre' => ['required', 'string', 'max:50', 'unique:metodos_pago,nombre'],
            'requiere_referencia' => ['sometimes', 'boolean'],
        ], [
            'nombre.required' => 'El nombre es obligatorio.',
            'nombre.max' => 'El nombre no puede superar 50 caracteres.',
            'nombre.unique' => 'Ya existe un método de pago con ese nombre.',
        ]);

        $metodoPago = MetodoPago::create([
            'nombre' => $validated['nombre'],
            'requiere_referencia' => $request->boolean('requiere_referencia'),
        ]);

        return response()->json(['message' => "Método de pago {$metodoPago->nombre} creado correctamente."]);
    }

    public function update(Request $request, MetodoPago $metodoPago): JsonResponse
    {
        $validated = $request->validate([
            'nombre' => ['required', 'string', 'max:50', Rule::unique('metodos_pago', 'nombre')->ignore($metodoPago->id)],
            'requiere_referencia' => ['sometimes', 'boolean'],
        ], [
            'nombre.required' => 'El nombre es obligatorio.',
            'nombre.max' => 'El nombre no puede superar 50 caracteres.',
            'nombre.unique' => 'Ya existe un método de pago con ese nombre.',
        ]);

        $metodoPago->fill([
            'nombre' => $validated['nombre'],
            'requiere_referencia' => $request->boolean('requiere_referencia'),
        ])->save();

        return response()->json(['message' => "Método de pago {$metodoPago->nombre} actualizado correctamente."]);
    }

    public function destroy(MetodoPago $metodoPago): JsonResponse
    {
        $metodoPago->loadCount('pagosOrdenCompra', 'pagosOrdenVenta');

        if ($metodoPago->pagos_orden_compra_count > 0 || $metodoPago->pagos_orden_venta_count > 0) {
            return response()->json([
                'message' => 'No se puede eliminar el método de pago porque tiene pagos asociados.',
            ], 422);
        }

        $metodoPago->delete();

        return response()->json(['message' => "Método de pago {$metodoPago->nombre} eliminado correctamente."]);
    }
}
