<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MetodoPago extends Model
{
    use HasFactory;

    protected $table = 'metodos_pago';

    public $timestamps = false;

    protected $fillable = [
        'nombre',
        'requiere_referencia',
        'moneda_id',
    ];

    protected $casts = [
        'requiere_referencia' => 'boolean',
    ];

    public function moneda(): BelongsTo
    {
        return $this->belongsTo(Moneda::class, 'moneda_id');
    }

    public function pagosOrdenCompra(): HasMany
    {
        return $this->hasMany(PagoOrdenCompra::class, 'metodo_pago_id');
    }

    public function pagosOrdenVenta(): HasMany
    {
        return $this->hasMany(PagoOrdenVenta::class, 'metodo_pago_id');
    }
}
