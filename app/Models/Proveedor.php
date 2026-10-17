<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Proveedor extends Model
{
    use HasFactory;

    protected $table = 'proveedores';

    public $timestamps = false;

    protected $fillable = [
        'nombre_empresa',
        'nombre_contacto',
        'correo',
        'telefono',
        'direccion',
    ];

    public function ordenesCompra(): HasMany
    {
        return $this->hasMany(OrdenCompra::class, 'proveedor_id');
    }
}
