<?php

namespace Database\Seeders;

use App\Models\Proveedor;
use Illuminate\Database\Seeder;

class ProveedorSeeder extends Seeder
{
    /**
     * Seed a sample directory of proveedores.
     */
    public function run(): void
    {
        $proveedores = [
            ['nombre_empresa' => 'Distribuidora La Providencia', 'nombre_contacto' => 'José Martínez', 'correo' => 'ventas@laprovidencia.com', 'telefono' => '+58 212 555 0101', 'direccion' => 'Av. Principal, Galpón 3'],
            ['nombre_empresa' => 'Alimentos del Sur C.A.', 'nombre_contacto' => 'Marta Jiménez', 'correo' => 'contacto@alimentossur.com', 'telefono' => '+58 212 555 0102', 'direccion' => 'Zona Industrial, Calle 2'],
            ['nombre_empresa' => 'ElectroImport S.R.L.', 'nombre_contacto' => 'Luis Rodríguez', 'correo' => 'pedidos@electroimport.com', 'telefono' => '+58 212 555 0103', 'direccion' => 'Cc. Industrial, Torre A'],
            ['nombre_empresa' => 'Bebidas y Más', 'nombre_contacto' => 'Ana González', 'correo' => 'info@bebidasymas.com', 'telefono' => '+58 212 555 0104', 'direccion' => 'Av. Las Américas, Local 7'],
        ];

        foreach ($proveedores as $proveedor) {
            Proveedor::updateOrCreate(['nombre_empresa' => $proveedor['nombre_empresa']], $proveedor);
        }
    }
}
