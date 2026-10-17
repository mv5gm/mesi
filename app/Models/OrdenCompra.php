<?php

namespace App\Models;

use App\Enums\EstadoOrdenCompra;
use App\Enums\EstadoPago;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OrdenCompra extends Model
{
    use HasFactory;

    protected $table = 'ordenes_compra';

    public $timestamps = false;

    protected $fillable = [
        'proveedor_id',
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
        'estado' => EstadoOrdenCompra::class,
        'estado_pago' => EstadoPago::class,
        'fecha_orden' => 'datetime',
    ];

    public function proveedor(): BelongsTo
    {
        return $this->belongsTo(Proveedor::class, 'proveedor_id');
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
        return $this->hasMany(DetalleOrdenCompra::class, 'orden_compra_id');
    }

    public function pagos(): HasMany
    {
        return $this->hasMany(PagoOrdenCompra::class, 'orden_compra_id');
    }
}
