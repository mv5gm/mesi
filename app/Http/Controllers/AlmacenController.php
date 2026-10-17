<?php

namespace App\Http\Controllers;

use App\Models\Almacen;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AlmacenController extends Controller
{
    public function index(): View
    {
        return view('almacenes.index');
    }

    public function data(Request $request): JsonResponse
    {
        $almacenes = Almacen::query();

        $recordsTotal = $almacenes->count();

        if ($search = $request->string('search.value')->toString()) {
            $almacenes->where(function (Builder $query) use ($search): void {
                $query->where('nombre', 'like', "%{$search}%")
                    ->orWhere('ubicacion', 'like', "%{$search}%")
                    ->when(in_array(mb_strtolower($search), ['activo', 'inactivo']), function (Builder $query) use ($search): void {
                        $query->orWhere('esta_activo', mb_strtolower($search) === 'activo');
                    });
            });
        }

        $recordsFiltered = (clone $almacenes)->count();

        /** @var array<int, string> $columnMap */
        $columnMap = [
            0 => 'nombre',
            1 => 'ubicacion',
        ];
        $column = $columnMap[$request->integer('order.0.column')] ?? 'nombre';
        $direction = $request->string('order.0.dir')->toString() === 'desc' ? 'desc' : 'asc';

        $almacenes->orderBy($column, $direction);

        $length = $request->integer('length', 10);
        if ($length !== -1) {
            $almacenes->skip($request->integer('start', 0))->take($length);
        }

        $data = $almacenes->get()->map(fn (Almacen $almacen): array => [
            'nombre' => e($almacen->nombre),
            'ubicacion' => $almacen->ubicacion
                ? e($almacen->ubicacion)
                : '<span class="text-gray-400">—</span>',
            'estado' => $almacen->esta_activo
                ? '<span class="inline-flex items-center rounded-full bg-emerald-100 px-2.5 py-0.5 text-xs font-medium text-emerald-700">Activo</span>'
                : '<span class="inline-flex items-center rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-medium text-gray-600">Inactivo</span>',
            'actions' => view('almacenes.partials.actions', ['almacen' => $almacen])->render(),
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
            'nombre' => ['required', 'string', 'max:100', 'unique:almacenes,nombre'],
            'ubicacion' => ['nullable', 'string', 'max:255'],
            'esta_activo' => ['sometimes', 'boolean'],
        ], [
            'nombre.required' => 'El nombre es obligatorio.',
            'nombre.max' => 'El nombre no puede superar 100 caracteres.',
            'nombre.unique' => 'Ya existe un almacén con ese nombre.',
            'ubicacion.max' => 'La ubicación no puede superar 255 caracteres.',
        ]);

        Almacen::create([
            'nombre' => $validated['nombre'],
            'ubicacion' => $validated['ubicacion'] ?? null,
            'esta_activo' => $request->boolean('esta_activo'),
        ]);

        return response()->json(['message' => "Almacén {$validated['nombre']} creado correctamente."]);
    }

    public function update(Request $request, Almacen $almacen): JsonResponse
    {
        $validated = $request->validate([
            'nombre' => ['required', 'string', 'max:100', Rule::unique('almacenes', 'nombre')->ignore($almacen->id)],
            'ubicacion' => ['nullable', 'string', 'max:255'],
            'esta_activo' => ['sometimes', 'boolean'],
        ], [
            'nombre.required' => 'El nombre es obligatorio.',
            'nombre.max' => 'El nombre no puede superar 100 caracteres.',
            'nombre.unique' => 'Ya existe un almacén con ese nombre.',
            'ubicacion.max' => 'La ubicación no puede superar 255 caracteres.',
        ]);

        $almacen->fill([
            'nombre' => $validated['nombre'],
            'ubicacion' => $validated['ubicacion'] ?? null,
            'esta_activo' => $request->boolean('esta_activo'),
        ])->save();

        return response()->json(['message' => "Almacén {$almacen->nombre} actualizado correctamente."]);
    }

    public function destroy(Almacen $almacen): JsonResponse
    {
        $almacen->loadCount('existencias', 'movimientos');

        if ($almacen->existencias_count > 0 || $almacen->movimientos_count > 0) {
            return response()->json([
                'message' => 'No se puede eliminar el almacén porque tiene existencias o movimientos asociados.',
            ], 422);
        }

        $almacen->delete();

        return response()->json(['message' => "Almacén {$almacen->nombre} eliminado correctamente."]);
    }
}
