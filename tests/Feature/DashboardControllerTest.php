<?php

namespace Tests\Feature;

use App\Models\DetalleOrdenVenta;
use App\Models\Existencia;
use App\Models\MetodoPago;
use App\Models\Moneda;
use App\Models\OrdenVenta;
use App\Models\PagoOrdenVenta;
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

    public function test_el_dashboard_desglosa_las_ventas_por_metodo_de_pago(): void
    {
        $dolar = Moneda::factory()->create(['codigo' => 'USD', 'simbolo' => '$', 'es_moneda_base' => true]);
        $bolivar = Moneda::factory()->create(['codigo' => 'VES', 'simbolo' => 'Bs.', 'es_moneda_base' => false]);

        $transferencia = MetodoPago::factory()->create(['nombre' => 'Transferencia', 'moneda_id' => $bolivar->id]);
        $divisa = MetodoPago::factory()->create(['nombre' => 'Divisa', 'moneda_id' => $dolar->id]);

        $orden = OrdenVenta::factory()->create(['estado' => 'COMPLETADA']);

        PagoOrdenVenta::factory()->create([
            'orden_venta_id' => $orden->id,
            'metodo_pago_id' => $transferencia->id,
            'monto' => 95000,
            'tasa_cambio' => 950,
            'monto_base' => 100,
        ]);

        PagoOrdenVenta::factory()->create([
            'orden_venta_id' => $orden->id,
            'metodo_pago_id' => $divisa->id,
            'monto' => 20,
            'tasa_cambio' => 1,
            'monto_base' => 20,
        ]);

        $this->actingAs(User::factory()->create())
            ->get('/')
            ->assertOk()
            ->assertViewHas('totalCobradoBase', 120.0)
            ->assertSee('Ventas por método de pago')
            ->assertSee('Transferencia')
            ->assertSee('Divisa')
            ->assertSee('Bs. 95.000,00')
            ->assertSee('$ 20,00');
    }

    public function test_el_desglose_por_metodo_de_pago_ignora_las_ordenes_canceladas(): void
    {
        $dolar = Moneda::factory()->create(['codigo' => 'USD', 'simbolo' => '$', 'es_moneda_base' => true]);
        $divisa = MetodoPago::factory()->create(['nombre' => 'Divisa', 'moneda_id' => $dolar->id]);

        $orden = OrdenVenta::factory()->create(['estado' => 'CANCELADA']);

        PagoOrdenVenta::factory()->create([
            'orden_venta_id' => $orden->id,
            'metodo_pago_id' => $divisa->id,
            'monto' => 20,
            'tasa_cambio' => 1,
            'monto_base' => 20,
        ]);

        $this->actingAs(User::factory()->create())
            ->get('/')
            ->assertOk()
            ->assertViewHas('pagosPorMetodo')
            ->assertViewHas('totalCobradoBase', 0.0)
            ->assertSee('Todavía no hay pagos registrados.');
    }
}
