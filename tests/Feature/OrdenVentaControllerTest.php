<?php

namespace Tests\Feature;

use App\Models\Cliente;
use App\Models\DetalleOrdenVenta;
use App\Models\Moneda;
use App\Models\OrdenVenta;
use App\Models\PagoOrdenVenta;
use App\Models\Producto;
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
        return [
            'cliente_id' => Cliente::factory()->create()->id,
            'moneda_id' => Moneda::factory()->create()->id,
            'tasa_cambio' => '36.0000',
            'fecha_orden' => '2026-09-24T14:30',
            'estado' => 'COMPLETADA',
            'estado_pago' => 'PAGADO',
            'detalles' => [
                ['producto_id' => Producto::factory()->create()->id, 'cantidad' => 2, 'precio_unitario' => 50.0],
                ['producto_id' => Producto::factory()->create()->id, 'cantidad' => 1, 'precio_unitario' => 120.0],
            ],
        ];
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
            'moneda_id' => $payload['moneda_id'],
            'tasa_cambio' => '36.0000',
            'monto_total' => '220.00',
            'monto_total_base' => '7920.00',
            'estado' => 'COMPLETADA',
            'estado_pago' => 'PAGADO',
        ]);

        $this->assertDatabaseCount('detalles_orden_venta', 2);
    }

    public function test_empty_payload_is_rejected(): void
    {
        $this->actingAs($this->adminUser())
            ->post('/ventas', [], ['Accept' => 'application/json'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['cliente_id', 'moneda_id', 'tasa_cambio', 'estado', 'detalles']);

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
            'monto_total_base' => '7920.00',
        ]);

        $this->assertDatabaseMissing('detalles_orden_venta', ['id' => $detalle->id]);
        $this->assertDatabaseCount('detalles_orden_venta', 2);
    }

    public function test_orden_with_pagos_cannot_be_deleted(): void
    {
        $orden = OrdenVenta::factory()->create();
        PagoOrdenVenta::factory()->create(['orden_venta_id' => $orden->id]);

        $this->actingAs($this->adminUser())
            ->delete("/ventas/{$orden->id}", headers: ['Accept' => 'application/json'])
            ->assertUnprocessable();

        $this->assertDatabaseHas('ordenes_venta', ['id' => $orden->id]);
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
