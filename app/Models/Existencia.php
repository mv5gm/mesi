<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Existencia extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'producto_id',
        'almacen_id',
        'cantidad',
    ];

    protected $casts = [
        'cantidad' => 'integer',
        'ultima_actualizacion' => 'datetime',
    ];

    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class, 'producto_id');
    }

    public function almacen(): BelongsTo
    {
        return $this->belongsTo(Almacen::class, 'almacen_id');
    }
}
