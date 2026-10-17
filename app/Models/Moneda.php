<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Moneda extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'codigo',
        'nombre',
        'simbolo',
        'es_moneda_base',
    ];

    protected $casts = [
        'es_moneda_base' => 'boolean',
    ];

    public function tasasCambio(): HasMany
    {
        return $this->hasMany(TasaCambio::class, 'moneda_id');
    }

    public function productos(): HasMany
    {
        return $this->hasMany(Producto::class, 'moneda_id');
    }

    public function ordenesCompra(): HasMany
    {
        return $this->hasMany(OrdenCompra::class, 'moneda_id');
    }

    public function ordenesVenta(): HasMany
    {
        return $this->hasMany(OrdenVenta::class, 'moneda_id');
    }
}
