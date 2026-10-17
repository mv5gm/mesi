<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PagoOrdenVenta extends Model
{
    use HasFactory;

    protected $table = 'pagos_orden_venta';

    public $timestamps = false;

    protected $fillable = [
        'orden_venta_id',
        'metodo_pago_id',
        'moneda_id',
        'monto',
        'tasa_cambio',
        'monto_base',
        'numero_referencia',
        'fecha_pago',
    ];

    protected $casts = [
        'monto' => 'decimal:2',
        'tasa_cambio' => 'decimal:4',
        'monto_base' => 'decimal:2',
        'fecha_pago' => 'datetime',
    ];

    public function ordenVenta(): BelongsTo
    {
        return $this->belongsTo(OrdenVenta::class, 'orden_venta_id');
    }

    public function metodoPago(): BelongsTo
    {
        return $this->belongsTo(MetodoPago::class, 'metodo_pago_id');
    }

    public function moneda(): BelongsTo
    {
        return $this->belongsTo(Moneda::class, 'moneda_id');
    }
}
