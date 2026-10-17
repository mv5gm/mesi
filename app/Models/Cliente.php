<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cliente extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'nombre',
        'correo',
        'telefono',
        'direccion',
    ];

    public function ordenesVenta(): HasMany
    {
        return $this->hasMany(OrdenVenta::class, 'cliente_id');
    }
}
