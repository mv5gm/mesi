<?php

namespace App\Http\Controllers;

use App\Models\Moneda;
use App\Models\TasaCambio;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TasaCambioController extends Controller
{
    public function index(): View
    {
        return view('tasas-cambio.index', [
            'listaMonedas' => Moneda::orderBy('codigo')
                ->get()
                ->mapWithKeys(fn (Moneda $moneda): array => [$moneda->id => "{$moneda->codigo} — {$moneda->nombre}"]),
        ]);
    }

    public function data(Request $request): JsonResponse
    {
        $tasas = TasaCambio::query()
            ->join('monedas', 'monedas.id', '=', 'tasas_cambio.moneda_id')
            ->select('tasas_cambio.*')
            ->with('moneda');

        $recordsTotal = $tasas->count();

        if ($search = $request->string('search.value')->toString()) {
            $tasas->where(function (Builder $query) use ($search): void {
                $query->where('monedas.codigo', 'like', "%{$search}%")
                    ->orWhere('monedas.nombre', 'like', "%{$search}%")
                    ->orWhere('tasas_cambio.tasa', 'like', "%{$search}%")
                    ->orWhere('tasas_cambio.fecha_efectiva', 'like', "%{$search}%");
            });
        }

        $recordsFiltered = (clone $tasas)->count();

        /** @var array<int, string> $columnMap */
        $columnMap = [
            0 => 'monedas.codigo',
            1 => 'tasas_cambio.tasa',
            2 => 'tasas_cambio.fecha_efectiva',
        ];
        $column = $columnMap[$request->integer('order.0.column')] ?? 'monedas.codigo';
        $direction = $request->string('order.0.dir')->toString() === 'desc' ? 'desc' : 'asc';

        $tasas->orderBy($column, $direction);

        $length = $request->integer('length', 10);
        if ($length !== -1) {
            $tasas->skip($request->integer('start', 0))->take($length);
        }

        $data = $tasas->get()->map(fn (TasaCambio $tasa): array => [
            'moneda' => e($tasa->moneda->codigo).' <span class="text-gray-400">— '.e($tasa->moneda->nombre).'</span>',
            'tasa' => number_format((float) $tasa->tasa, 2),
            'fecha_efectiva' => $tasa->fecha_efectiva?->format('d/m/Y H:i'),
            'actions' => view('tasas-cambio.partials.actions', ['tasa' => $tasa])->render(),
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
            'moneda_id' => ['required', 'integer', 'exists:monedas,id'],
            'tasa' => ['required', 'numeric', 'gt:0'],
            'fecha_efectiva' => ['required', 'date'],
        ], [
            'moneda_id.required' => 'Debes seleccionar una moneda.',
            'moneda_id.exists' => 'La moneda seleccionada no es válida.',
            'tasa.required' => 'La tasa es obligatoria.',
            'tasa.gt' => 'La tasa debe ser mayor que cero.',
            'fecha_efectiva.required' => 'La fecha es obligatoria.',
        ]);

        $moneda = Moneda::findOrFail($validated['moneda_id']);

        TasaCambio::create([
            'moneda_id' => $validated['moneda_id'],
            'tasa' => $validated['tasa'],
            'fecha_efectiva' => $validated['fecha_efectiva'],
        ]);

        return response()->json(['message' => "Tasa de {$moneda->codigo} creada correctamente."]);
    }

    public function update(Request $request, TasaCambio $tasaCambio): JsonResponse
    {
        $validated = $request->validate([
            'moneda_id' => ['required', 'integer', 'exists:monedas,id'],
            'tasa' => ['required', 'numeric', 'gt:0'],
            'fecha_efectiva' => ['required', 'date'],
        ], [
            'moneda_id.required' => 'Debes seleccionar una moneda.',
            'moneda_id.exists' => 'La moneda seleccionada no es válida.',
            'tasa.required' => 'La tasa es obligatoria.',
            'tasa.gt' => 'La tasa debe ser mayor que cero.',
            'fecha_efectiva.required' => 'La fecha es obligatoria.',
        ]);

        $moneda = Moneda::findOrFail($validated['moneda_id']);

        $tasaCambio->update($validated);

        return response()->json(['message' => "Tasa de {$moneda->codigo} actualizada correctamente."]);
    }

    public function destroy(TasaCambio $tasaCambio): JsonResponse
    {
        $tasaCambio->delete();

        return response()->json(['message' => 'Tasa de cambio eliminada correctamente.']);
    }
}
