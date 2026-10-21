<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TasaCambio extends Model
{
    use HasFactory;

    protected $table = 'tasas_cambio';

    public $timestamps = false;

    protected $fillable = [
        'moneda_id',
        'tasa',
        'fecha_efectiva',
    ];

    protected $casts = [
        'tasa' => 'decimal:4',
        'fecha_efectiva' => 'datetime',
    ];

    /**
     * Última tasa vigente por moneda: cuántas unidades de cada moneda equivalen a 1 de la base.
     *
     * @return array<int, float>
     */
    public static function vigentes(): array
    {
        $vigentes = [];

        static::query()
            ->orderByDesc('fecha_efectiva')
            ->orderByDesc('id')
            ->get(['moneda_id', 'tasa'])
            ->each(function (self $tasa) use (&$vigentes): void {
                if (! array_key_exists($tasa->moneda_id, $vigentes)) {
                    $vigentes[$tasa->moneda_id] = (float) $tasa->tasa;
                }
            });

        return $vigentes;
    }

    public function moneda(): BelongsTo
    {
        return $this->belongsTo(Moneda::class, 'moneda_id');
    }
}
