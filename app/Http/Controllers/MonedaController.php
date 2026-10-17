<?php

namespace App\Http\Controllers;

use App\Models\Moneda;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class MonedaController extends Controller
{
    public function index(): View
    {
        return view('monedas.index');
    }

    public function data(Request $request): JsonResponse
    {
        $monedas = Moneda::query();

        $recordsTotal = $monedas->count();

        if ($search = $request->string('search.value')->toString()) {
            $monedas->where(function (Builder $query) use ($search): void {
                $query->where('codigo', 'like', "%{$search}%")
                    ->orWhere('nombre', 'like', "%{$search}%")
                    ->orWhere('simbolo', 'like', "%{$search}%");
            });
        }

        $recordsFiltered = (clone $monedas)->count();

        /** @var array<int, string> $columnMap */
        $columnMap = [
            0 => 'codigo',
            1 => 'nombre',
            2 => 'simbolo',
        ];
        $column = $columnMap[$request->integer('order.0.column')] ?? 'codigo';
        $direction = $request->string('order.0.dir')->toString() === 'desc' ? 'desc' : 'asc';

        $monedas->orderBy($column, $direction);

        $length = $request->integer('length', 10);
        if ($length !== -1) {
            $monedas->skip($request->integer('start', 0))->take($length);
        }

        $data = $monedas->get()->map(fn (Moneda $moneda): array => [
            'codigo' => e($moneda->codigo),
            'nombre' => e($moneda->nombre),
            'simbolo' => e($moneda->simbolo),
            'base' => $moneda->es_moneda_base
                ? '<span class="inline-flex items-center rounded-full bg-indigo-100 px-2.5 py-0.5 text-xs font-medium text-indigo-700">Base</span>'
                : '<span class="text-gray-400">—</span>',
            'actions' => view('monedas.partials.actions', ['moneda' => $moneda])->render(),
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
            'codigo' => ['required', 'string', 'max:10', 'unique:monedas,codigo'],
            'nombre' => ['required', 'string', 'max:50'],
            'simbolo' => ['required', 'string', 'max:5'],
            'es_moneda_base' => ['sometimes', 'boolean'],
        ], [
            'codigo.required' => 'El código es obligatorio.',
            'codigo.unique' => 'Ese código ya está registrado.',
            'nombre.required' => 'El nombre es obligatorio.',
            'simbolo.required' => 'El símbolo es obligatorio.',
        ]);

        $moneda = Moneda::create([...$validated, 'es_moneda_base' => $request->boolean('es_moneda_base')]);

        if ($moneda->es_moneda_base) {
            Moneda::whereKeyNot($moneda->id)->where('es_moneda_base', true)->update(['es_moneda_base' => false]);
        }

        return response()->json(['message' => "Moneda {$moneda->codigo} creada correctamente."]);
    }

    public function update(Request $request, Moneda $moneda): JsonResponse
    {
        $validated = $request->validate([
            'codigo' => ['required', 'string', 'max:10', Rule::unique('monedas', 'codigo')->ignore($moneda->id)],
            'nombre' => ['required', 'string', 'max:50'],
            'simbolo' => ['required', 'string', 'max:5'],
            'es_moneda_base' => ['sometimes', 'boolean'],
        ], [
            'codigo.required' => 'El código es obligatorio.',
            'codigo.unique' => 'Ese código ya está registrado.',
            'nombre.required' => 'El nombre es obligatorio.',
            'simbolo.required' => 'El símbolo es obligatorio.',
        ]);

        $moneda->fill([
            'codigo' => $validated['codigo'],
            'nombre' => $validated['nombre'],
            'simbolo' => $validated['simbolo'],
            'es_moneda_base' => $request->boolean('es_moneda_base'),
        ])->save();

        if ($moneda->es_moneda_base) {
            Moneda::whereKeyNot($moneda->id)->where('es_moneda_base', true)->update(['es_moneda_base' => false]);
        }

        return response()->json(['message' => "Moneda {$moneda->codigo} actualizada correctamente."]);
    }

    public function destroy(Moneda $moneda): JsonResponse
    {
        $moneda->loadCount('tasasCambio', 'productos', 'ordenesCompra', 'ordenesVenta');

        if ($moneda->tasas_cambio_count > 0 || $moneda->productos_count > 0
            || $moneda->ordenes_compra_count > 0 || $moneda->ordenes_venta_count > 0) {
            return response()->json([
                'message' => 'No se puede eliminar la moneda porque tiene tasas de cambio, productos u órdenes asociadas.',
            ], 422);
        }

        $moneda->delete();

        return response()->json(['message' => "Moneda {$moneda->codigo} eliminada correctamente."]);
    }
}
