<?php

namespace Database\Seeders;

use App\Models\Categoria;
use App\Models\Moneda;
use App\Models\Producto;
use Illuminate\Database\Seeder;

class ProductoSeeder extends Seeder
{
    /**
     * Seed a sample catalog of productos.
     */
    public function run(): void
    {
        $monedaReferencia = Moneda::where('es_moneda_base', true)->first() ?? Moneda::first();

        if ($monedaReferencia === null) {
            return;
        }

        $productos = [
            ['sku' => 'MOT-001', 'nombre' => 'Filtro de Aceite', 'categoria' => 'Filtros', 'precio_costo' => 18.00, 'precio_venta' => 32.00, 'unidad_medida' => 'PZA'],
            ['sku' => 'MOT-002', 'nombre' => 'Filtro de Aire', 'categoria' => 'Filtros', 'precio_costo' => 15.00, 'precio_venta' => 28.00, 'unidad_medida' => 'PZA'],
            ['sku' => 'MOT-003', 'nombre' => 'Filtro de Combustible', 'categoria' => 'Filtros', 'precio_costo' => 20.00, 'precio_venta' => 36.00, 'unidad_medida' => 'PZA'],
            ['sku' => 'MOT-004', 'nombre' => 'Bujía de Iridio NGK', 'categoria' => 'Bujías', 'precio_costo' => 14.00, 'precio_venta' => 26.00, 'unidad_medida' => 'PZA'],
            ['sku' => 'MOT-005', 'nombre' => 'Correa de Distribución', 'categoria' => 'Correas', 'precio_costo' => 42.00, 'precio_venta' => 75.00, 'unidad_medida' => 'PZA'],
            ['sku' => 'MOT-006', 'nombre' => 'Bomba de Agua', 'categoria' => 'Bombas de agua', 'precio_costo' => 85.00, 'precio_venta' => 150.00, 'unidad_medida' => 'PZA'],
            ['sku' => 'FRE-001', 'nombre' => 'Pastillas de Freno Delanteras', 'categoria' => 'Pastillas', 'precio_costo' => 65.00, 'precio_venta' => 115.00, 'unidad_medida' => 'PZA'],
            ['sku' => 'FRE-002', 'nombre' => 'Pastillas de Freno Traseras', 'categoria' => 'Pastillas', 'precio_costo' => 58.00, 'precio_venta' => 105.00, 'unidad_medida' => 'PZA'],
            ['sku' => 'FRE-003', 'nombre' => 'Disco de Freno Ventilado', 'categoria' => 'Discos', 'precio_costo' => 110.00, 'precio_venta' => 195.00, 'unidad_medida' => 'PZA'],
            ['sku' => 'FRE-004', 'nombre' => 'Líquido de Frenos DOT 4', 'categoria' => 'Líquido de frenos', 'precio_costo' => 9.00, 'precio_venta' => 18.00, 'unidad_medida' => 'LT'],
            ['sku' => 'SUS-001', 'nombre' => 'Amortiguador Delantero', 'categoria' => 'Amortiguadores', 'precio_costo' => 95.00, 'precio_venta' => 170.00, 'unidad_medida' => 'PZA'],
            ['sku' => 'SUS-002', 'nombre' => 'Rótula de Suspensión', 'categoria' => 'Rótulas', 'precio_costo' => 28.00, 'precio_venta' => 55.00, 'unidad_medida' => 'PZA'],
            ['sku' => 'SUS-003', 'nombre' => 'Barra Estabilizadora', 'categoria' => 'Barras estabilizadoras', 'precio_costo' => 80.00, 'precio_venta' => 145.00, 'unidad_medida' => 'PZA'],
            ['sku' => 'ELE-001', 'nombre' => 'Batería 55Ah', 'categoria' => 'Baterías', 'precio_costo' => 240.00, 'precio_venta' => 420.00, 'unidad_medida' => 'PZA'],
            ['sku' => 'ELE-002', 'nombre' => 'Alternador 90A', 'categoria' => 'Alternadores', 'precio_costo' => 180.00, 'precio_venta' => 320.00, 'unidad_medida' => 'PZA'],
            ['sku' => 'ELE-003', 'nombre' => 'Sensor de Oxígeno', 'categoria' => 'Sensores', 'precio_costo' => 48.00, 'precio_venta' => 90.00, 'unidad_medida' => 'PZA'],
            ['sku' => 'LUB-001', 'nombre' => 'Aceite de Motor 5W-30', 'categoria' => 'Aceites de motor', 'precio_costo' => 12.00, 'precio_venta' => 22.00, 'unidad_medida' => 'LT'],
            ['sku' => 'LUB-002', 'nombre' => 'Aceite de Transmisión 75W-90', 'categoria' => 'Aceites de transmisión', 'precio_costo' => 15.00, 'precio_venta' => 28.00, 'unidad_medida' => 'LT'],
            ['sku' => 'LUB-003', 'nombre' => 'Refrigerante Anticongelante', 'categoria' => 'Refrigerantes', 'precio_costo' => 8.00, 'precio_venta' => 16.00, 'unidad_medida' => 'LT'],
            ['sku' => 'LLA-001', 'nombre' => 'Llanta 195/65 R15', 'categoria' => 'Llantas', 'precio_costo' => 220.00, 'precio_venta' => 390.00, 'unidad_medida' => 'PZA'],
            ['sku' => 'LLA-002', 'nombre' => 'Rin de Aleación 15"', 'categoria' => 'Rines', 'precio_costo' => 85.00, 'precio_venta' => 155.00, 'unidad_medida' => 'PZA'],
            ['sku' => 'LLA-003', 'nombre' => 'Válvula de Llanta', 'categoria' => 'Válvulas', 'precio_costo' => 2.50, 'precio_venta' => 6.00, 'unidad_medida' => 'PZA'],
            ['sku' => 'CAR-001', 'nombre' => 'Faro Delantero Derecho', 'categoria' => 'Luces', 'precio_costo' => 95.00, 'precio_venta' => 175.00, 'unidad_medida' => 'PZA'],
            ['sku' => 'CAR-002', 'nombre' => 'Retrovisor Lateral Conductor', 'categoria' => 'Retrovisores', 'precio_costo' => 45.00, 'precio_venta' => 85.00, 'unidad_medida' => 'PZA'],
            ['sku' => 'CAR-003', 'nombre' => 'Parabrisas Delantero', 'categoria' => 'Parabrisas', 'precio_costo' => 130.00, 'precio_venta' => 240.00, 'unidad_medida' => 'PZA'],
            ['sku' => 'TRA-001', 'nombre' => 'Kit de Embrague Completo', 'categoria' => 'Embragues', 'precio_costo' => 150.00, 'precio_venta' => 275.00, 'unidad_medida' => 'PZA'],
            ['sku' => 'TRA-002', 'nombre' => 'Sincronizador de 2ª Velocidad', 'categoria' => 'Sincronizadores', 'precio_costo' => 65.00, 'precio_venta' => 120.00, 'unidad_medida' => 'PZA'],
        ];

        foreach ($productos as $producto) {
            $categoria = Categoria::where('nombre', $producto['categoria'])->first() ?? Categoria::first();

            if ($categoria === null) {
                continue;
            }

            Producto::updateOrCreate(['sku' => $producto['sku']], [
                'categoria_id' => $categoria->id,
                'moneda_id' => $monedaReferencia->id,
                'codigo_barras' => null,
                'nombre' => $producto['nombre'],
                'descripcion' => "Repuesto {$producto['nombre']}",
                'precio_costo' => $producto['precio_costo'],
                'precio_venta' => $producto['precio_venta'],
                'stock_minimo' => 5,
                'stock_maximo' => 200,
                'unidad_medida' => $producto['unidad_medida'],
            ]);
        }
    }
}
