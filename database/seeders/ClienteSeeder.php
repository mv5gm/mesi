<?php

namespace Database\Seeders;

use App\Models\Cliente;
use Illuminate\Database\Seeder;

class ClienteSeeder extends Seeder
{
    /**
     * Seed a sample directory of clientes.
     */
    public function run(): void
    {
        $clientes = [
            ['nombre' => 'Carlos Pérez', 'correo' => 'carlos.perez@example.com', 'telefono' => '+58 414 555 0101', 'direccion' => 'Urbanización Las Flores, Casa 12'],
            ['nombre' => 'Distribuidora El Progreso', 'correo' => 'compras@elprogreso.com', 'telefono' => '+58 426 555 0102', 'direccion' => 'Av. Intercomunal, Local 5'],
            ['nombre' => 'María Fernández', 'correo' => 'maria.fernandez@example.com', 'telefono' => '+58 412 555 0103', 'direccion' => 'Conjunto Residencial La Colina, Edif. B'],
            ['nombre' => 'Supermercado El Ahorro', 'correo' => 'mercado@elahorro.com', 'telefono' => '+58 424 555 0104', 'direccion' => 'Cc. Bicentenario, Sotano 1'],
            ['nombre' => 'Pedro Sánchez', 'correo' => 'pedro.sanchez@example.com', 'telefono' => '+58 416 555 0105', 'direccion' => 'Barrio El Carmen, Calle 4'],
        ];

        foreach ($clientes as $cliente) {
            Cliente::updateOrCreate(['nombre' => $cliente['nombre']], $cliente);
        }
    }
}
