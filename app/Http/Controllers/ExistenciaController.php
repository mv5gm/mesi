<?php

namespace App\Http\Controllers;

use App\Models\Almacen;
use App\Models\Existencia;
use App\Models\Producto;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ExistenciaController extends Controller
{
    public function index(): View
    {
        return view('existencias.index', [
            'listaProductos' => Producto::orderBy('nombre')->get(['id', 'sku', 'nombre']),
            'listaAlmacenes' => $this->almacenesParaSelect(),
        ]);
    }

    public function data(Request $request): JsonResponse
    {
        $existencias = Existencia::query()
            ->join('productos', 'productos.id', '=', 'existencias.producto_id')
            ->join('almacenes', 'almacenes.id', '=', 'existencias.almacen_id')
            ->select('existencias.*', 'productos.nombre as producto_nombre', 'almacenes.nombre as almacen_nombre');

        $recordsTotal = $existencias->count();

        if ($search = $request->string('search.value')->toString()) {
            $existencias->where(function (Builder $query) use ($search): void {
                $query->where('productos.nombre', 'like', "%{$search}%")
                    ->orWhere('productos.sku', 'like', "%{$search}%")
                    ->orWhere('almacenes.nombre', 'like', "%{$search}%");
            });
        }

        $recordsFiltered = (clone $existencias)->count();

        /** @var array<int, string> $columnMap */
        $columnMap = [
            0 => 'productos.nombre',
            1 => 'almacenes.nombre',
            2 => 'existencias.cantidad',
            3 => 'existencias.ultima_actualizacion',
        ];
        $column = $columnMap[$request->integer('order.0.column')] ?? 'productos.nombre';
        $direction = $request->string('order.0.dir')->toString() === 'desc' ? 'desc' : 'asc';

        $existencias->orderBy($column, $direction);

        $length = $request->integer('length', 10);
        if ($length !== -1) {
            $existencias->skip($request->integer('start', 0))->take($length);
        }

        $data = $existencias->get()->map(fn (Existencia $existencia): array => [
            'producto' => e($existencia->producto_nombre),
            'almacen' => e($existencia->almacen_nombre),
            'cantidad' => "<span class=\"font-semibold\">{$existencia->cantidad}</span>",
            'ultima_actualizacion' => $existencia->ultima_actualizacion?->format('d/m/Y H:i') ?? '—',
            'actions' => view('existencias.partials.actions', ['existencia' => $existencia])->render(),
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
        $validated = $this->validarDatos($request);

        Existencia::create([
            'producto_id' => $validated['producto_id'],
            'almacen_id' => $validated['almacen_id'],
            'cantidad' => $validated['cantidad'],
        ]);

        return response()->json(['message' => 'Existencia registrada correctamente.']);
    }

    public function update(Request $request, Existencia $existencia): JsonResponse
    {
        $validated = $this->validarDatos($request, $existencia);

        $existencia->update([
            'producto_id' => $validated['producto_id'],
            'almacen_id' => $validated['almacen_id'],
            'cantidad' => $validated['cantidad'],
        ]);

        return response()->json(['message' => 'Existencia actualizada correctamente.']);
    }

    public function destroy(Existencia $existencia): JsonResponse
    {
        $existencia->delete();

        return response()->json(['message' => 'Existencia eliminada correctamente.']);
    }

    /**
     * Validate a stock payload for store/update.
     *
     * @return array<string, mixed>
     */
    private function validarDatos(Request $request, ?Existencia $existencia = null): array
    {
        $validated = $request->validate([
            'producto_id' => ['required', 'integer', 'exists:productos,id'],
            'almacen_id' => ['required', 'integer', 'exists:almacenes,id'],
            'cantidad' => ['required', 'integer', 'min:0'],
        ], [
            'producto_id.required' => 'Debes seleccionar un producto.',
            'producto_id.exists' => 'El producto seleccionado no es válido.',
            'almacen_id.required' => 'Debes seleccionar un almacén.',
            'almacen_id.exists' => 'El almacén seleccionado no es válido.',
            'cantidad.required' => 'La cantidad es obligatoria.',
            'cantidad.integer' => 'La cantidad debe ser un número entero.',
            'cantidad.min' => 'La cantidad no puede ser negativa.',
        ]);

        $duplicada = Existencia::where('producto_id', $validated['producto_id'])
            ->where('almacen_id', $validated['almacen_id'])
            ->when($existencia, fn (Builder $query): Builder => $query->where('existencias.id', '!=', $existencia->id))
            ->exists();

        if ($duplicada) {
            throw ValidationException::withMessages([
                'producto_id' => 'Ese producto ya tiene existencias registradas en el almacén seleccionado.',
            ]);
        }

        return $validated;
    }

    /**
     * Build an almacen list for selects: id => nombre.
     */
    private function almacenesParaSelect(): Collection
    {
        return Almacen::orderBy('nombre')->get(['id', 'nombre'])->mapWithKeys(
            fn (Almacen $almacen): array => [$almacen->id => $almacen->nombre]
        );
    }
}
