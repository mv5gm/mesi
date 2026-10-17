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

    public function moneda(): BelongsTo
    {
        return $this->belongsTo(Moneda::class, 'moneda_id');
    }
}
