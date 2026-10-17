<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Producto extends Model
{
    use HasFactory;

    public const CREATED_AT = 'creado_en';

    public const UPDATED_AT = null;

    protected $fillable = [
        'categoria_id',
        'moneda_id',
        'sku',
        'codigo_barras',
        'nombre',
        'descripcion',
        'precio_costo',
        'precio_venta',
        'stock_minimo',
        'stock_maximo',
        'unidad_medida',
    ];

    protected $casts = [
        'precio_costo' => 'decimal:2',
        'precio_venta' => 'decimal:2',
        'stock_minimo' => 'integer',
        'stock_maximo' => 'integer',
        'creado_en' => 'datetime',
    ];

    public function categoria(): BelongsTo
    {
        return $this->belongsTo(Categoria::class, 'categoria_id');
    }

    public function moneda(): BelongsTo
    {
        return $this->belongsTo(Moneda::class, 'moneda_id');
    }

    public function existencias(): HasMany
    {
        return $this->hasMany(Existencia::class, 'producto_id');
    }

    public function movimientos(): HasMany
    {
        return $this->hasMany(MovimientoInventario::class, 'producto_id');
    }

    public function detallesOrdenCompra(): HasMany
    {
        return $this->hasMany(DetalleOrdenCompra::class, 'producto_id');
    }

    public function detallesOrdenVenta(): HasMany
    {
        return $this->hasMany(DetalleOrdenVenta::class, 'producto_id');
    }
}
