<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DetalleOrdenVenta extends Model
{
    use HasFactory;

    protected $table = 'detalles_orden_venta';

    public $timestamps = false;

    protected $fillable = [
        'orden_venta_id',
        'producto_id',
        'cantidad',
        'precio_unitario',
    ];

    protected $casts = [
        'cantidad' => 'integer',
        'precio_unitario' => 'decimal:2',
    ];

    public function ordenVenta(): BelongsTo
    {
        return $this->belongsTo(OrdenVenta::class, 'orden_venta_id');
    }

    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class, 'producto_id');
    }
}
