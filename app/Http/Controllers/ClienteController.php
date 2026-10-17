<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ClienteController extends Controller
{
    public function index(): View
    {
        return view('clientes.index');
    }

    public function data(Request $request): JsonResponse
    {
        $clientes = Cliente::query();

        $recordsTotal = $clientes->count();

        if ($search = $request->string('search.value')->toString()) {
            $clientes->where(function (Builder $query) use ($search): void {
                $query->where('nombre', 'like', "%{$search}%")
                    ->orWhere('correo', 'like', "%{$search}%")
                    ->orWhere('telefono', 'like', "%{$search}%")
                    ->orWhere('direccion', 'like', "%{$search}%");
            });
        }

        $recordsFiltered = (clone $clientes)->count();

        /** @var array<int, string> $columnMap */
        $columnMap = [
            0 => 'nombre',
            1 => 'correo',
            2 => 'telefono',
            3 => 'direccion',
        ];
        $column = $columnMap[$request->integer('order.0.column')] ?? 'nombre';
        $direction = $request->string('order.0.dir')->toString() === 'desc' ? 'desc' : 'asc';

        $clientes->orderBy($column, $direction);

        $length = $request->integer('length', 10);
        if ($length !== -1) {
            $clientes->skip($request->integer('start', 0))->take($length);
        }

        $data = $clientes->get()->map(fn (Cliente $cliente): array => [
            'nombre' => e($cliente->nombre),
            'correo' => $cliente->correo
                ? e($cliente->correo)
                : '<span class="text-gray-400">—</span>',
            'telefono' => $cliente->telefono
                ? e($cliente->telefono)
                : '<span class="text-gray-400">—</span>',
            'direccion' => $cliente->direccion
                ? e($cliente->direccion)
                : '<span class="text-gray-400">—</span>',
            'actions' => view('clientes.partials.actions', ['cliente' => $cliente])->render(),
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
            'nombre' => ['required', 'string', 'max:150'],
            'correo' => ['nullable', 'email', 'max:150'],
            'telefono' => ['nullable', 'string', 'max:30'],
            'direccion' => ['nullable', 'string'],
        ], [
            'nombre.required' => 'El nombre es obligatorio.',
            'nombre.max' => 'El nombre no puede superar 150 caracteres.',
            'correo.email' => 'El correo no tiene un formato válido.',
            'correo.max' => 'El correo no puede superar 150 caracteres.',
            'telefono.max' => 'El teléfono no puede superar 30 caracteres.',
        ]);

        $cliente = Cliente::create($validated);

        return response()->json(['message' => "Cliente {$cliente->nombre} creado correctamente."]);
    }

    public function update(Request $request, Cliente $cliente): JsonResponse
    {
        $validated = $request->validate([
            'nombre' => ['required', 'string', 'max:150'],
            'correo' => ['nullable', 'email', 'max:150'],
            'telefono' => ['nullable', 'string', 'max:30'],
            'direccion' => ['nullable', 'string'],
        ], [
            'nombre.required' => 'El nombre es obligatorio.',
            'nombre.max' => 'El nombre no puede superar 150 caracteres.',
            'correo.email' => 'El correo no tiene un formato válido.',
            'correo.max' => 'El correo no puede superar 150 caracteres.',
            'telefono.max' => 'El teléfono no puede superar 30 caracteres.',
        ]);

        $cliente->update($validated);

        return response()->json(['message' => "Cliente {$cliente->nombre} actualizado correctamente."]);
    }

    public function destroy(Cliente $cliente): JsonResponse
    {
        $cliente->loadCount('ordenesVenta');

        if ($cliente->ordenes_venta_count > 0) {
            return response()->json([
                'message' => 'No se puede eliminar el cliente porque tiene órdenes de venta asociadas.',
            ], 422);
        }

        $cliente->delete();

        return response()->json(['message' => "Cliente {$cliente->nombre} eliminado correctamente."]);
    }
}
