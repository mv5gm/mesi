<?php

namespace Tests\Feature;

use App\Models\DetalleOrdenVenta;
use App\Models\Existencia;
use App\Models\Moneda;
use App\Models\OrdenVenta;
use App\Models\Producto;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_el_dashboard_requiere_iniciar_sesion(): void
    {
        $this->get('/')
            ->assertRedirect('/login');
    }

    public function test_el_dashboard_muestra_las_estadisticas(): void
    {
        $this->actingAs(User::factory()->create())
            ->get('/')
            ->assertOk()
            ->assertViewIs('dashboard')
            ->assertViewHas('invertido')
            ->assertViewHas('ventas')
            ->assertViewHas('unidadesExistencias')
            ->assertSee('Invertido, ganancias y ventas')
            ->assertSee('Ganancias (venta − costo)');
    }

    public function test_el_dashboard_calcula_el_invertido_y_lo_vendido(): void
    {
        $moneda = Moneda::factory()->create(['codigo' => 'USD', 'es_moneda_base' => true]);
        $producto = Producto::factory()->create([
            'moneda_id' => $moneda->id,
            'precio_costo' => 10,
            'precio_venta' => 20,
        ]);

        Existencia::factory()->create([
            'producto_id' => $producto->id,
            'cantidad' => 5,
        ]);

        $orden = OrdenVenta::factory()->create(['estado' => 'COMPLETADA']);
        DetalleOrdenVenta::factory()->create([
            'orden_venta_id' => $orden->id,
            'producto_id' => $producto->id,
            'cantidad' => 2,
        ]);

        $this->actingAs(User::factory()->create())
            ->get('/')
            ->assertOk()
            // 5 existencias x 10 de costo + 2 vendidos x 10 de costo = 70
            ->assertViewHas('invertido', 70.0)
            ->assertViewHas('ventas', 40.0)
            ->assertViewHas('ganancias', 20.0)
            ->assertViewHas('unidadesExistencias', 5);
    }

    public function test_no_cuenta_las_ordenes_canceladas(): void
    {
        $moneda = Moneda::factory()->create(['codigo' => 'USD', 'es_moneda_base' => true]);
        $producto = Producto::factory()->create([
            'moneda_id' => $moneda->id,
            'precio_costo' => 10,
            'precio_venta' => 20,
        ]);

        $orden = OrdenVenta::factory()->create(['estado' => 'CANCELADA']);
        DetalleOrdenVenta::factory()->create([
            'orden_venta_id' => $orden->id,
            'producto_id' => $producto->id,
            'cantidad' => 2,
        ]);

        $this->actingAs(User::factory()->create())
            ->get('/')
            ->assertOk()
            ->assertViewHas('ventas', 0.0)
            ->assertViewHas('ganancias', 0.0);
    }
}
