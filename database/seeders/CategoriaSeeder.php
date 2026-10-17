<?php

namespace Database\Seeders;

use App\Models\Categoria;
use Illuminate\Database\Seeder;

class CategoriaSeeder extends Seeder
{
    /**
     * Seed the category hierarchy.
     */
    public function run(): void
    {
        $jerarquia = [
            'Motor' => ['Filtros', 'Bujías', 'Correas', 'Bombas de agua'],
            'Frenos' => ['Pastillas', 'Discos', 'Líquido de frenos'],
            'Suspensión y dirección' => ['Amortiguadores', 'Rótulas', 'Barras estabilizadoras'],
            'Sistema eléctrico' => ['Baterías', 'Alternadores', 'Sensores'],
            'Lubricantes y fluidos' => ['Aceites de motor', 'Aceites de transmisión', 'Refrigerantes'],
            'Llantas y ruedas' => ['Llantas', 'Rines', 'Válvulas'],
            'Carrocería y accesorios' => ['Luces', 'Retrovisores', 'Parabrisas'],
            'Transmisión' => ['Embragues', 'Sincronizadores'],
        ];

        foreach ($jerarquia as $padre => $subcategorias) {
            $categoriaPadre = Categoria::updateOrCreate(
                ['nombre' => $padre],
                ['descripcion' => "Repuestos de {$padre}"]
            );

            foreach ($subcategorias as $subcategoria) {
                Categoria::updateOrCreate(
                    ['nombre' => $subcategoria],
                    ['categoria_padre_id' => $categoriaPadre->id, 'descripcion' => "Subcategoría {$subcategoria}"]
                );
            }
        }
    }
}
