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

    /**
     * Descuenta unidades de las existencias del producto, tomado de los almacenes con stock.
     *
     * @return array<int, array{almacen_id: int, cantidad: int}>
     */
    public static function descontar(int $productoId, int $cantidad): array
    {
        $movimientos = [];
        $restante = $cantidad;

        $existencias = static::query()
            ->where('producto_id', $productoId)
            ->where('cantidad', '>', 0)
            ->orderBy('almacen_id')
            ->get();

        foreach ($existencias as $existencia) {
            if ($restante <= 0) {
                break;
            }

            $tomado = min($existencia->cantidad, $restante);

            $existencia->update(['cantidad' => $existencia->cantidad - $tomado]);

            $movimientos[] = ['almacen_id' => $existencia->almacen_id, 'cantidad' => $tomado];
            $restante -= $tomado;
        }

        return $movimientos;
    }

    /**
     * Devuelve unidades a las existencias del producto en el almacén indicado.
     */
    public static function devolver(int $productoId, int $almacenId, int $cantidad): void
    {
        if ($cantidad <= 0) {
            return;
        }

        $existencia = static::query()
            ->where('producto_id', $productoId)
            ->where('almacen_id', $almacenId)
            ->first();

        if ($existencia === null) {
            static::create([
                'producto_id' => $productoId,
                'almacen_id' => $almacenId,
                'cantidad' => $cantidad,
            ]);

            return;
        }

        $existencia->update(['cantidad' => $existencia->cantidad + $cantidad]);
    }

    /**
     * Suma unidades a las existencias del producto, agregándolas al almacén con stock existente
     * (o creando una nueva existencia en el primer almacén si no hay ninguna).
     */
    public static function sumar(int $productoId, int $cantidad): void
    {
        if ($cantidad <= 0) {
            return;
        }

        $existencia = static::query()
            ->where('producto_id', $productoId)
            ->orderBy('almacen_id')
            ->first();

        if ($existencia === null) {
            $almacenId = Almacen::query()->orderBy('id')->value('id');

            if ($almacenId === null) {
                return;
            }

            static::create([
                'producto_id' => $productoId,
                'almacen_id' => $almacenId,
                'cantidad' => $cantidad,
            ]);

            return;
        }

        $existencia->update(['cantidad' => $existencia->cantidad + $cantidad]);
    }
}
