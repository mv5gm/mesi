<?php

namespace App\Http\Controllers;

use App\Models\Proveedor;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProveedorController extends Controller
{
    public function index(): View
    {
        return view('proveedores.index');
    }

    public function data(Request $request): JsonResponse
    {
        $proveedores = Proveedor::query();

        $recordsTotal = $proveedores->count();

        if ($search = $request->string('search.value')->toString()) {
            $proveedores->where(function (Builder $query) use ($search): void {
                $query->where('nombre_empresa', 'like', "%{$search}%")
                    ->orWhere('nombre_contacto', 'like', "%{$search}%")
                    ->orWhere('correo', 'like', "%{$search}%")
                    ->orWhere('telefono', 'like', "%{$search}%")
                    ->orWhere('direccion', 'like', "%{$search}%");
            });
        }

        $recordsFiltered = (clone $proveedores)->count();

        /** @var array<int, string> $columnMap */
        $columnMap = [
            0 => 'nombre_empresa',
            1 => 'nombre_contacto',
            2 => 'correo',
            3 => 'telefono',
            4 => 'direccion',
        ];
        $column = $columnMap[$request->integer('order.0.column')] ?? 'nombre_empresa';
        $direction = $request->string('order.0.dir')->toString() === 'desc' ? 'desc' : 'asc';

        $proveedores->orderBy($column, $direction);

        $length = $request->integer('length', 10);
        if ($length !== -1) {
            $proveedores->skip($request->integer('start', 0))->take($length);
        }

        $data = $proveedores->get()->map(fn (Proveedor $proveedor): array => [
            'nombre_empresa' => e($proveedor->nombre_empresa),
            'nombre_contacto' => $proveedor->nombre_contacto
                ? e($proveedor->nombre_contacto)
                : '<span class="text-gray-400">—</span>',
            'correo' => $proveedor->correo
                ? e($proveedor->correo)
                : '<span class="text-gray-400">—</span>',
            'telefono' => $proveedor->telefono
                ? e($proveedor->telefono)
                : '<span class="text-gray-400">—</span>',
            'direccion' => $proveedor->direccion
                ? e($proveedor->direccion)
                : '<span class="text-gray-400">—</span>',
            'actions' => view('proveedores.partials.actions', ['proveedor' => $proveedor])->render(),
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
            'nombre_empresa' => ['required', 'string', 'max:150'],
            'nombre_contacto' => ['nullable', 'string', 'max:100'],
            'correo' => ['nullable', 'email', 'max:150'],
            'telefono' => ['nullable', 'string', 'max:30'],
            'direccion' => ['nullable', 'string'],
        ], [
            'nombre_empresa.required' => 'El nombre de la empresa es obligatorio.',
            'nombre_empresa.max' => 'El nombre de la empresa no puede superar 150 caracteres.',
            'nombre_contacto.max' => 'El nombre del contacto no puede superar 100 caracteres.',
            'correo.email' => 'El correo no tiene un formato válido.',
            'correo.max' => 'El correo no puede superar 150 caracteres.',
            'telefono.max' => 'El teléfono no puede superar 30 caracteres.',
        ]);

        $proveedor = Proveedor::create($validated);

        return response()->json(['message' => "Proveedor {$proveedor->nombre_empresa} creado correctamente."]);
    }

    public function update(Request $request, Proveedor $proveedor): JsonResponse
    {
        $validated = $request->validate([
            'nombre_empresa' => ['required', 'string', 'max:150'],
            'nombre_contacto' => ['nullable', 'string', 'max:100'],
            'correo' => ['nullable', 'email', 'max:150'],
            'telefono' => ['nullable', 'string', 'max:30'],
            'direccion' => ['nullable', 'string'],
        ], [
            'nombre_empresa.required' => 'El nombre de la empresa es obligatorio.',
            'nombre_empresa.max' => 'El nombre de la empresa no puede superar 150 caracteres.',
            'nombre_contacto.max' => 'El nombre del contacto no puede superar 100 caracteres.',
            'correo.email' => 'El correo no tiene un formato válido.',
            'correo.max' => 'El correo no puede superar 150 caracteres.',
            'telefono.max' => 'El teléfono no puede superar 30 caracteres.',
        ]);

        $proveedor->update($validated);

        return response()->json(['message' => "Proveedor {$proveedor->nombre_empresa} actualizado correctamente."]);
    }

    public function destroy(Proveedor $proveedor): JsonResponse
    {
        $proveedor->loadCount('ordenesCompra');

        if ($proveedor->ordenes_compra_count > 0) {
            return response()->json([
                'message' => 'No se puede eliminar el proveedor porque tiene órdenes de compra asociadas.',
            ], 422);
        }

        $proveedor->delete();

        return response()->json(['message' => "Proveedor {$proveedor->nombre_empresa} eliminado correctamente."]);
    }
}
