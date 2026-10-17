<?php

namespace App\Models;

use App\Enums\TipoMovimiento;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MovimientoInventario extends Model
{
    use HasFactory;

    protected $table = 'movimientos_inventario';

    public const CREATED_AT = 'creado_en';

    public const UPDATED_AT = null;

    protected $fillable = [
        'producto_id',
        'almacen_id',
        'usuario_id',
        'tipo_movimiento',
        'cantidad',
        'tipo_referencia',
        'referencia_id',
        'notas',
    ];

    protected $casts = [
        'tipo_movimiento' => TipoMovimiento::class,
        'cantidad' => 'integer',
        'creado_en' => 'datetime',
    ];

    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class, 'producto_id');
    }

    public function almacen(): BelongsTo
    {
        return $this->belongsTo(Almacen::class, 'almacen_id');
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }
}
