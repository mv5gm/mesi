<?php

namespace Tests\Feature;

use App\Models\Cliente;
use App\Models\DetalleOrdenVenta;
use App\Models\Existencia;
use App\Models\MetodoPago;
use App\Models\Moneda;
use App\Models\OrdenVenta;
use App\Models\PagoOrdenVenta;
use App\Models\Producto;
use App\Models\TasaCambio;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class OrdenVentaControllerTest extends TestCase
{
    use RefreshDatabase;

    private Role $adminRole;

    private Role $basicRole;

    protected function setUp(): void
    {
        parent::setUp();

        Permission::findOrCreate('gestionar ventas');
        $this->adminRole = Role::findOrCreate('admin');
        $this->adminRole->givePermissionTo('gestionar ventas');
        $this->basicRole = Role::findOrCreate('usuario');
    }

    private function adminUser(): User
    {
        $user = User::factory()->create();

        $user->assignRole($this->adminRole);

        return $user;
    }

    private function basicUser(): User
    {
        return tap(User::factory()->create(), fn (User $user) => $user->assignRole($this->basicRole));
    }

    /**
     * @return array<string, mixed>
     */
    private function payloadValidado(): array
    {
        $moneda = Moneda::factory()->create();
        $metodoPago = MetodoPago::factory()->create(['moneda_id' => $moneda->id]);

        return [
            'cliente_id' => Cliente::factory()->create()->id,
            'tasa_cambio' => '950.0000',
            'fecha_orden' => '2026-09-24T14:30',
            'estado' => 'COMPLETADA',
            'estado_pago' => 'PAGADO',
            'detalles' => [
                ['producto_id' => $this->productoConExistencias()->id, 'cantidad' => 2, 'precio_unitario' => 50.0],
                ['producto_id' => $this->productoConExistencias()->id, 'cantidad' => 1, 'precio_unitario' => 120.0],
            ],
            'pagos' => [
                [
                    'metodo_pago_id' => $metodoPago->id,
                    'monto' => '220.00',
                    'tasa_cambio' => '950.0000',
                    'numero_referencia' => 'REF-001',
                    'fecha_pago' => '2026-09-24T14:30',
                ],
            ],
        ];
    }

    /**
     * Producto con existencias registradas para poder venderlo.
     */
    private function productoConExistencias(int $cantidad = 50): Producto
    {
        $producto = Producto::factory()->create();

        Existencia::factory()->create([
            'producto_id' => $producto->id,
            'cantidad' => $cantidad,
        ]);

        return $producto;
    }

    public function test_unauthenticated_request_redirects_to_login(): void
    {
        $this->get('/ventas')
            ->assertRedirect('/login');
    }

    public function test_user_without_permission_is_forbidden(): void
    {
        $this->actingAs($this->basicUser())
            ->get('/ventas')
            ->assertForbidden();
    }

    public function test_admin_can_view_ventas_page(): void
    {
        $this->actingAs($this->adminUser())
            ->get('/ventas')
            ->assertOk()
            ->assertViewIs('ventas.index')
            ->assertSee('Ventas');
    }

    public function test_data_endpoint_lists_ordenes(): void
    {
        $cliente = Cliente::factory()->create(['nombre' => 'Taller El Turbo']);
        OrdenVenta::factory()->create(['cliente_id' => $cliente->id]);

        $this->actingAs($this->adminUser())
            ->get('/ventas/datos?draw=1')
            ->assertOk()
            ->assertJson([
                'draw' => 1,
                'recordsTotal' => 1,
                'recordsFiltered' => 1,
            ])
            ->assertJsonFragment(['cliente' => 'Taller El Turbo']);
    }

    public function test_valid_payload_creates_orden_with_computed_totals(): void
    {
        $payload = $this->payloadValidado();
        $usuario = $this->adminUser();

        $this->actingAs($usuario)
            ->post('/ventas', $payload, ['Accept' => 'application/json'])
            ->assertOk()
            ->assertJson(['message' => 'Orden de venta #1 creada correctamente.']);

        $this->assertDatabaseHas('ordenes_venta', [
            'cliente_id' => $payload['cliente_id'],
            'usuario_id' => $usuario->id,
            'tasa_cambio' => '950.0000',
            'monto_total' => '220.00',
            'monto_total_base' => '0.23',
            'estado' => 'COMPLETADA',
            'estado_pago' => 'PAGADO',
        ]);

        $this->assertDatabaseCount('detalles_orden_venta', 2);
    }

    public function test_la_orden_hereda_la_moneda_del_pago(): void
    {
        $monedaDolar = Moneda::factory()->create(['codigo' => 'USD']);
        $metodoPago = MetodoPago::factory()->create(['nombre' => 'Zelle', 'moneda_id' => $monedaDolar->id]);

        $payload = $this->payloadValidado();
        $payload['pagos'][0]['metodo_pago_id'] = $metodoPago->id;

        $this->actingAs($this->adminUser())
            ->post('/ventas', $payload, ['Accept' => 'application/json'])
            ->assertOk();

        $this->assertDatabaseHas('ordenes_venta', [
            'moneda_id' => $monedaDolar->id,
        ]);
    }

    public function test_pago_hereda_la_moneda_del_metodo_de_pago(): void
    {
        $monedaDolar = Moneda::factory()->create(['codigo' => 'USD']);
        $metodoPago = MetodoPago::factory()->create(['nombre' => 'Zelle', 'moneda_id' => $monedaDolar->id]);

        $payload = $this->payloadValidado();
        $payload['pagos'][0]['metodo_pago_id'] = $metodoPago->id;
        $payload['pagos'][0]['tasa_cambio'] = '36.5000';

        $this->actingAs($this->adminUser())
            ->post('/ventas', $payload, ['Accept' => 'application/json'])
            ->assertOk();

        $pago = PagoOrdenVenta::first();

        $this->assertSame($monedaDolar->id, $pago->metodoPago->moneda_id);
        $this->assertSame('6.03', $pago->monto_base);
        $this->assertArrayNotHasKey('moneda_id', $pago->getAttributes());
    }

    public function test_el_precio_del_producto_se_convierte_a_la_moneda_de_la_orden(): void
    {
        $dolar = Moneda::factory()->create(['codigo' => 'USD', 'es_moneda_base' => true]);
        $bolivar = Moneda::factory()->create(['codigo' => 'VES', 'es_moneda_base' => false]);
        TasaCambio::factory()->create(['moneda_id' => $bolivar->id, 'tasa' => 950]);
        $producto = Producto::factory()->create(['moneda_id' => $dolar->id, 'precio_venta' => 10]);

        $this->actingAs($this->adminUser())
            ->get("/ventas/producto-precio?producto_id={$producto->id}&moneda_id={$bolivar->id}")
            ->assertOk()
            ->assertJson([
                'precio_venta' => 10.0,
                'precio_orden' => 9500.0,
                'tasa' => 950.0,
            ]);
    }

    public function test_el_precio_del_producto_se_mantiene_en_dolares(): void
    {
        $dolar = Moneda::factory()->create(['codigo' => 'USD', 'es_moneda_base' => true]);
        $producto = Producto::factory()->create(['moneda_id' => $dolar->id, 'precio_venta' => 10]);

        $this->actingAs($this->adminUser())
            ->get("/ventas/producto-precio?producto_id={$producto->id}&moneda_id={$dolar->id}")
            ->assertOk()
            ->assertJson([
                'precio_venta' => 10.0,
                'precio_orden' => 10.0,
                'tasa' => 1.0,
            ]);
    }

    public function test_la_suma_de_los_pagos_debe_cubrir_el_total_de_la_orden(): void
    {
        $payload = $this->payloadValidado();
        $payload['pagos'][0]['monto'] = '100.00';

        $this->actingAs($this->adminUser())
            ->post('/ventas', $payload, ['Accept' => 'application/json'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors([
                'pagos' => ['La suma de los pagos (100,00) no puede ser menor al total de la orden (220,00).'],
            ]);

        $this->assertDatabaseCount('ordenes_venta', 0);
    }

    public function test_una_orden_parcial_puede_tener_pagos_menores_al_total(): void
    {
        $payload = $this->payloadValidado();
        $payload['estado_pago'] = 'PARCIAL';
        $payload['pagos'][0]['monto'] = '100.00';

        $this->actingAs($this->adminUser())
            ->post('/ventas', $payload, ['Accept' => 'application/json'])
            ->assertOk();

        $this->assertDatabaseHas('ordenes_venta', [
            'estado_pago' => 'PARCIAL',
            'monto_total' => '220.00',
        ]);
    }

    public function test_la_suma_de_los_pagos_puede_superar_el_total(): void
    {
        $payload = $this->payloadValidado();
        $payload['pagos'][0]['monto'] = '500.00';

        $this->actingAs($this->adminUser())
            ->post('/ventas', $payload, ['Accept' => 'application/json'])
            ->assertOk();

        $this->assertDatabaseHas('ordenes_venta', [
            'monto_total' => '220.00',
        ]);
    }

    public function test_se_puede_ver_el_detalle_de_una_venta(): void
    {
        $orden = OrdenVenta::factory()->create();
        DetalleOrdenVenta::factory()->create(['orden_venta_id' => $orden->id]);
        PagoOrdenVenta::factory()->create(['orden_venta_id' => $orden->id]);

        $this->actingAs($this->adminUser())
            ->get("/ventas/{$orden->id}")
            ->assertOk()
            ->assertJson([
                'id' => $orden->id,
            ])
            ->assertJsonCount(1, 'detalles')
            ->assertJsonCount(1, 'pagos')
            ->assertJsonStructure([
                'id',
                'cliente',
                'fecha',
                'moneda',
                'total',
                'total_base',
                'estado',
                'estado_pago',
                'detalles' => [['sku', 'producto', 'cantidad', 'precio_unitario', 'subtotal']],
                'pagos' => [['metodo', 'moneda', 'monto', 'tasa_cambio', 'monto_base', 'numero_referencia', 'fecha_pago']],
            ]);
    }

    public function test_no_se_puede_vender_un_producto_sin_existencias(): void
    {
        $payload = $this->payloadValidado();
        $producto = Producto::factory()->create(['nombre' => 'Bujía de Iridio']);
        $payload['detalles'][0]['producto_id'] = $producto->id;

        $this->actingAs($this->adminUser())
            ->post('/ventas', $payload, ['Accept' => 'application/json'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors([
                'detalles' => ['El producto Bujía de Iridio no tiene existencias suficientes (disponible: 0, solicitado: 2).'],
            ]);

        $this->assertDatabaseCount('ordenes_venta', 0);
    }

    public function test_no_se_puede_vender_mas_unidades_de_las_existencias(): void
    {
        $payload = $this->payloadValidado();
        $payload['detalles'][0]['cantidad'] = 999;

        $this->actingAs($this->adminUser())
            ->post('/ventas', $payload, ['Accept' => 'application/json'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('detalles');

        $this->assertDatabaseCount('ordenes_venta', 0);
    }

    public function test_las_existencias_se_suman_los_almacenes(): void
    {
        $producto = Producto::factory()->create();
        Existencia::factory()->create(['producto_id' => $producto->id, 'cantidad' => 6]);
        Existencia::factory()->create(['producto_id' => $producto->id, 'cantidad' => 5]);

        $payload = $this->payloadValidado();
        $payload['detalles'] = [
            ['producto_id' => $producto->id, 'cantidad' => 11, 'precio_unitario' => 10.0],
        ];
        $payload['pagos'][0]['monto'] = '110.00';

        $this->actingAs($this->adminUser())
            ->post('/ventas', $payload, ['Accept' => 'application/json'])
            ->assertOk();

        $payload['detalles'][0]['cantidad'] = 12;
        $payload['pagos'][0]['monto'] = '120.00';

        $this->actingAs($this->adminUser())
            ->post('/ventas', $payload, ['Accept' => 'application/json'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('detalles');
    }

    public function test_el_selector_de_productos_muestra_las_existencias_disponibles(): void
    {
        $conStock = $this->productoConExistencias(7);
        $sinStock = Producto::factory()->create(['nombre' => 'Producto sin existencias']);

        $response = $this->actingAs($this->adminUser())
            ->get('/ventas/productos-select2?q=')
            ->assertOk();

        $response->assertJsonFragment(['id' => $conStock->id, 'stock' => 7]);
        $response->assertJsonFragment(['id' => $sinStock->id, 'stock' => 0]);
    }

    public function test_al_registrar_una_venta_se_descuenta_el_stock(): void
    {
        $producto = $this->productoConExistencias(4);

        $payload = $this->payloadValidado();
        $payload['detalles'] = [
            ['producto_id' => $producto->id, 'cantidad' => 3, 'precio_unitario' => 50.0],
        ];
        $payload['pagos'][0]['monto'] = '150.00';

        $this->actingAs($this->adminUser())
            ->post('/ventas', $payload, ['Accept' => 'application/json'])
            ->assertOk();

        $this->assertDatabaseHas('existencias', [
            'producto_id' => $producto->id,
            'cantidad' => 1,
        ]);

        $this->assertDatabaseHas('movimientos_inventario', [
            'producto_id' => $producto->id,
            'tipo_movimiento' => 'SALIDA',
            'cantidad' => 3,
            'tipo_referencia' => 'ORDEN_VENTA',
        ]);
    }

    public function test_al_eliminar_una_venta_se_devuelve_el_stock(): void
    {
        $producto = $this->productoConExistencias(4);

        $payload = $this->payloadValidado();
        $payload['detalles'] = [
            ['producto_id' => $producto->id, 'cantidad' => 3, 'precio_unitario' => 50.0],
        ];
        $payload['pagos'][0]['monto'] = '150.00';

        $this->actingAs($this->adminUser())
            ->post('/ventas', $payload, ['Accept' => 'application/json'])
            ->assertOk();

        $orden = OrdenVenta::first();

        $this->actingAs($this->adminUser())
            ->delete("/ventas/{$orden->id}", headers: ['Accept' => 'application/json'])
            ->assertOk();

        $this->assertDatabaseHas('existencias', [
            'producto_id' => $producto->id,
            'cantidad' => 4,
        ]);

        $this->assertDatabaseMissing('movimientos_inventario', [
            'tipo_referencia' => 'ORDEN_VENTA',
            'referencia_id' => $orden->id,
        ]);
    }

    public function test_al_editar_una_venta_se_ajusta_el_stock(): void
    {
        $producto = $this->productoConExistencias(10);

        $payload = $this->payloadValidado();
        $payload['detalles'] = [
            ['producto_id' => $producto->id, 'cantidad' => 3, 'precio_unitario' => 50.0],
        ];
        $payload['pagos'][0]['monto'] = '150.00';

        $this->actingAs($this->adminUser())
            ->post('/ventas', $payload, ['Accept' => 'application/json'])
            ->assertOk();

        $orden = OrdenVenta::first();

        $payload['detalles'][0]['cantidad'] = 5;
        $payload['pagos'][0]['monto'] = '250.00';

        $this->actingAs($this->adminUser())
            ->put("/ventas/{$orden->id}", $payload, ['Accept' => 'application/json'])
            ->assertOk();

        $this->assertDatabaseHas('existencias', [
            'producto_id' => $producto->id,
            'cantidad' => 5,
        ]);
    }

    public function test_una_orden_cancelada_no_descuenta_stock(): void
    {
        $producto = $this->productoConExistencias(4);

        $payload = $this->payloadValidado();
        $payload['estado'] = 'CANCELADA';
        $payload['detalles'] = [
            ['producto_id' => $producto->id, 'cantidad' => 3, 'precio_unitario' => 50.0],
        ];
        $payload['pagos'][0]['monto'] = '150.00';

        $this->actingAs($this->adminUser())
            ->post('/ventas', $payload, ['Accept' => 'application/json'])
            ->assertOk();

        $this->assertDatabaseHas('existencias', [
            'producto_id' => $producto->id,
            'cantidad' => 4,
        ]);
    }

    public function test_empty_payload_is_rejected(): void
    {
        $this->actingAs($this->adminUser())
            ->post('/ventas', [], ['Accept' => 'application/json'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['cliente_id', 'tasa_cambio', 'estado', 'detalles', 'pagos']);

        $this->assertDatabaseCount('ordenes_venta', 0);
        $this->assertDatabaseCount('detalles_orden_venta', 0);
    }

    public function test_duplicate_product_in_detalles_is_rejected(): void
    {
        $producto = Producto::factory()->create();
        $payload = $this->payloadValidado();
        $payload['detalles'] = [
            ['producto_id' => $producto->id, 'cantidad' => 1, 'precio_unitario' => 5],
            ['producto_id' => $producto->id, 'cantidad' => 2, 'precio_unitario' => 6],
        ];

        $this->actingAs($this->adminUser())
            ->post('/ventas', $payload, ['Accept' => 'application/json'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('detalles.0.producto_id');

        $this->assertDatabaseCount('ordenes_venta', 0);
    }

    public function test_valid_payload_updates_orden_and_replaces_detalles(): void
    {
        $orden = OrdenVenta::factory()->create();
        $detalle = DetalleOrdenVenta::factory()->create(['orden_venta_id' => $orden->id]);

        $payload = $this->payloadValidado();

        $this->actingAs($this->adminUser())
            ->put("/ventas/{$orden->id}", $payload, ['Accept' => 'application/json'])
            ->assertOk()
            ->assertJson(['message' => "Orden de venta #{$orden->id} actualizada correctamente."]);

        $this->assertDatabaseHas('ordenes_venta', [
            'id' => $orden->id,
            'monto_total' => '220.00',
            'monto_total_base' => '0.23',
        ]);

        $this->assertDatabaseMissing('detalles_orden_venta', ['id' => $detalle->id]);
        $this->assertDatabaseCount('detalles_orden_venta', 2);
    }

    public function test_orden_with_pagos_is_deleted_with_its_pagos_and_detalles(): void
    {
        $orden = OrdenVenta::factory()->create();
        DetalleOrdenVenta::factory()->create(['orden_venta_id' => $orden->id]);
        $pago = PagoOrdenVenta::factory()->create(['orden_venta_id' => $orden->id]);

        $this->actingAs($this->adminUser())
            ->delete("/ventas/{$orden->id}", headers: ['Accept' => 'application/json'])
            ->assertOk()
            ->assertJson(['message' => "Orden de venta #{$orden->id} eliminada correctamente."]);

        $this->assertDatabaseMissing('ordenes_venta', ['id' => $orden->id]);
        $this->assertDatabaseMissing('pagos_orden_venta', ['id' => $pago->id]);
        $this->assertDatabaseCount('detalles_orden_venta', 0);
    }

    public function test_empty_orden_is_deleted_with_its_detalles(): void
    {
        $orden = OrdenVenta::factory()->create();
        DetalleOrdenVenta::factory()->create(['orden_venta_id' => $orden->id]);

        $this->actingAs($this->adminUser())
            ->delete("/ventas/{$orden->id}", headers: ['Accept' => 'application/json'])
            ->assertOk()
            ->assertJson(['message' => "Orden de venta #{$orden->id} eliminada correctamente."]);

        $this->assertDatabaseMissing('ordenes_venta', ['id' => $orden->id]);
        $this->assertDatabaseMissing('detalles_orden_venta', ['orden_venta_id' => $orden->id]);
    }
}
