<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DetalleOrdenCompra extends Model
{
    use HasFactory;

    protected $table = 'detalles_orden_compra';

    public $timestamps = false;

    protected $fillable = [
        'orden_compra_id',
        'producto_id',
        'cantidad',
        'costo_unitario',
    ];

    protected $casts = [
        'cantidad' => 'integer',
        'costo_unitario' => 'decimal:2',
    ];

    public function ordenCompra(): BelongsTo
    {
        return $this->belongsTo(OrdenCompra::class, 'orden_compra_id');
    }

    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class, 'producto_id');
    }
}
