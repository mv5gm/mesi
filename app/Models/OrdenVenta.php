<?php

namespace App\Models;

use App\Enums\EstadoOrdenVenta;
use App\Enums\EstadoPago;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OrdenVenta extends Model
{
    use HasFactory;

    protected $table = 'ordenes_venta';

    public $timestamps = false;

    protected $fillable = [
        'cliente_id',
        'usuario_id',
        'moneda_id',
        'tasa_cambio',
        'monto_total',
        'monto_total_base',
        'estado',
        'estado_pago',
        'fecha_orden',
    ];

    protected $casts = [
        'tasa_cambio' => 'decimal:4',
        'monto_total' => 'decimal:2',
        'monto_total_base' => 'decimal:2',
        'estado' => EstadoOrdenVenta::class,
        'estado_pago' => EstadoPago::class,
        'fecha_orden' => 'datetime',
    ];

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class, 'cliente_id');
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    public function moneda(): BelongsTo
    {
        return $this->belongsTo(Moneda::class, 'moneda_id');
    }

    public function detalles(): HasMany
    {
        return $this->hasMany(DetalleOrdenVenta::class, 'orden_venta_id');
    }

    public function pagos(): HasMany
    {
        return $this->hasMany(PagoOrdenVenta::class, 'orden_venta_id');
    }
}
