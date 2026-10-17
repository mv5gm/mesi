<?php

namespace Database\Seeders;

use App\Models\Almacen;
use Illuminate\Database\Seeder;

class AlmacenSeeder extends Seeder
{
    /**
     * Seed the warehouses.
     */
    public function run(): void
    {
        $almacenes = [
            ['nombre' => 'Almacén Principal', 'ubicacion' => 'Zona Industrial, Plant 1', 'esta_activo' => true],
            ['nombre' => 'Sucursal Centro', 'ubicacion' => 'Calle 5 de Julio, Local 2', 'esta_activo' => true],
        ];

        foreach ($almacenes as $almacen) {
            Almacen::updateOrCreate(['nombre' => $almacen['nombre']], $almacen);
        }
    }
}
