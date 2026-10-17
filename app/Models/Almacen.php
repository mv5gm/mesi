<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Almacen extends Model
{
    use HasFactory;

    protected $table = 'almacenes';

    public $timestamps = false;

    protected $fillable = [
        'nombre',
        'ubicacion',
        'esta_activo',
    ];

    protected $casts = [
        'esta_activo' => 'boolean',
    ];

    public function existencias(): HasMany
    {
        return $this->hasMany(Existencia::class, 'almacen_id');
    }

    public function movimientos(): HasMany
    {
        return $this->hasMany(MovimientoInventario::class, 'almacen_id');
    }
}
