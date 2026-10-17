<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CategoriaController extends Controller
{
    public function index(): View
    {
        return view('categorias.index', [
            'listaCategorias' => $this->categoriasParaSelect(),
        ]);
    }

    public function data(Request $request): JsonResponse
    {
        $categorias = Categoria::with('categoriaPadre')->withCount('productos');

        $recordsTotal = $categorias->count();

        if ($search = $request->string('search.value')->toString()) {
            $categorias->where(function (Builder $query) use ($search): void {
                $query->where('nombre', 'like', "%{$search}%")
                    ->orWhere('descripcion', 'like', "%{$search}%")
                    ->orWhereHas('categoriaPadre', static fn (Builder $padre) => $padre->where('nombre', 'like', "%{$search}%"));
            });
        }

        $recordsFiltered = (clone $categorias)->count();

        /** @var array<int, string> $columnMap */
        $columnMap = [
            0 => 'nombre',
            2 => 'descripcion',
            3 => 'productos_count',
        ];
        $column = $columnMap[$request->integer('order.0.column')] ?? 'nombre';
        $direction = $request->string('order.0.dir')->toString() === 'desc' ? 'desc' : 'asc';

        $categorias->orderBy($column, $direction);

        $length = $request->integer('length', 10);
        if ($length !== -1) {
            $categorias->skip($request->integer('start', 0))->take($length);
        }

        $data = $categorias->get()->map(fn (Categoria $categoria): array => [
            'nombre' => e($categoria->nombre),
            'padre' => $categoria->categoriaPadre
                ? e($categoria->categoriaPadre->nombre)
                : '<span class="text-gray-400">—</span>',
            'descripcion' => e($categoria->descripcion ?? ''),
            'productos' => $categoria->productos_count,
            'actions' => view('categorias.partials.actions', ['categoria' => $categoria])->render(),
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
            'nombre' => ['required', 'string', 'max:100'],
            'categoria_padre_id' => ['nullable', 'integer', 'exists:categorias,id'],
            'descripcion' => ['nullable', 'string'],
        ], [
            'nombre.required' => 'El nombre es obligatorio.',
            'nombre.max' => 'El nombre no puede superar 100 caracteres.',
            'categoria_padre_id.exists' => 'La categoría padre seleccionada no es válida.',
        ]);

        $categoria = Categoria::create($validated);

        return response()->json(['message' => "Categoría {$categoria->nombre} creada correctamente."]);
    }

    public function update(Request $request, Categoria $categoria): JsonResponse
    {
        $validated = $request->validate([
            'nombre' => ['required', 'string', 'max:100'],
            'categoria_padre_id' => ['nullable', 'integer', 'exists:categorias,id', Rule::notIn([$categoria->id])],
            'descripcion' => ['nullable', 'string'],
        ], [
            'nombre.required' => 'El nombre es obligatorio.',
            'nombre.max' => 'El nombre no puede superar 100 caracteres.',
            'categoria_padre_id.exists' => 'La categoría padre seleccionada no es válida.',
            'categoria_padre_id.not_in' => 'Una categoría no puede ser su propia categoría padre.',
        ]);

        $categoria->update($validated);

        return response()->json(['message' => 'Categoría actualizada correctamente.']);
    }

    public function destroy(Categoria $categoria): JsonResponse
    {
        $categoria->loadCount('subcategorias', 'productos');

        if ($categoria->subcategorias_count > 0 || $categoria->productos_count > 0) {
            return response()->json([
                'message' => 'No se puede eliminar la categoría porque tiene subcategorías o productos asociados.',
            ], 422);
        }

        $categoria->delete();

        return response()->json(['message' => 'Categoría eliminada correctamente.']);
    }

    /**
     * Build an indented category list for selects: id => "-- Nombre".
     */
    private function categoriasParaSelect(): Collection
    {
        $todas = Categoria::orderBy('nombre')->get(['id', 'categoria_padre_id', 'nombre']);
        $hijos = $todas->groupBy('categoria_padre_id');
        $resultado = [];

        $agregar = function (?int $padreId, int $nivel) use (&$agregar, $hijos, &$resultado): void {
            foreach ($hijos->get($padreId, collect()) as $categoria) {
                $resultado[$categoria->id] = str_repeat('— ', $nivel).$categoria->nombre;
                $agregar($categoria->id, $nivel + 1);
            }
        };

        $agregar(null, 0);

        return collect($resultado);
    }
}
