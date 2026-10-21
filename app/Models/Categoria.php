<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

class Categoria extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'categoria_padre_id',
        'nombre',
        'descripcion',
    ];

    public function categoriaPadre(): BelongsTo
    {
        return $this->belongsTo(Categoria::class, 'categoria_padre_id');
    }

    public function subcategorias(): HasMany
    {
        return $this->hasMany(Categoria::class, 'categoria_padre_id');
    }

    public function productos(): HasMany
    {
        return $this->hasMany(Producto::class, 'categoria_id');
    }

    /**
     * Listado de categorías indentado para los selectores: id => "-- Nombre".
     *
     * @return Collection<int, string>
     */
    public static function paraSelect(): Collection
    {
        $todas = self::orderBy('nombre')->get(['id', 'categoria_padre_id', 'nombre']);
        $hijos = $todas->groupBy('categoria_padre_id');
        $resultado = [];

        $agregar = function (?int $padreId, int $nivel) use (&$agregar, $hijos, &$resultado): void {
            foreach ($hijos->get($padreId, collect()) as $categoria) {
                $resultado[$categoria->id] = str_repeat('— ', $nivel).$categoria->nombre;
                $agregar($categoria->id, $nivel + 1);
            }
        };

        $agregar(null, 0);

        return collect($resultado);
    }
}
