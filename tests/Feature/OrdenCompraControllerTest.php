<?php

namespace Tests\Feature;

use App\Models\DetalleOrdenCompra;
use App\Models\Moneda;
use App\Models\OrdenCompra;
use App\Models\PagoOrdenCompra;
use App\Models\Producto;
use App\Models\Proveedor;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class OrdenCompraControllerTest extends TestCase
{
    use RefreshDatabase;

    private Role $adminRole;

    private Role $basicRole;

    protected function setUp(): void
    {
        parent::setUp();

        Permission::findOrCreate('gestionar compras');
        $this->adminRole = Role::findOrCreate('admin');
        $this->adminRole->givePermissionTo('gestionar compras');
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
            'proveedor_id' => Proveedor::factory()->create()->id,
            'moneda_id' => Moneda::factory()->create()->id,
            'tasa_cambio' => '40.5000',
            'fecha_orden' => '2026-09-24T10:00',
            'estado' => 'PENDIENTE',
            'estado_pago' => 'PENDIENTE',
            'detalles' => [
                ['producto_id' => Producto::factory()->create()->id, 'cantidad' => 2, 'costo_unitario' => 10.5],
                ['producto_id' => Producto::factory()->create()->id, 'cantidad' => 3, 'costo_unitario' => 20.0],
            ],
        ];
    }

    public function test_unauthenticated_request_redirects_to_login(): void
    {
        $this->get('/compras')
            ->assertRedirect('/login');
    }

    public function test_user_without_permission_is_forbidden(): void
    {
        $this->actingAs($this->basicUser())
            ->get('/compras')
            ->assertForbidden();
    }

    public function test_admin_can_view_compras_page(): void
    {
        $this->actingAs($this->adminUser())
            ->get('/compras')
            ->assertOk()
            ->assertViewIs('compras.index')
            ->assertSee('Compras');
    }

    public function test_data_endpoint_lists_ordenes(): void
    {
        $proveedor = Proveedor::factory()->create(['nombre_empresa' => 'Autopartes C.A.']);
        OrdenCompra::factory()->create(['proveedor_id' => $proveedor->id]);

        $this->actingAs($this->adminUser())
            ->get('/compras/datos?draw=1')
            ->assertOk()
            ->assertJson([
                'draw' => 1,
                'recordsTotal' => 1,
                'recordsFiltered' => 1,
            ])
            ->assertJsonFragment(['proveedor' => 'Autopartes C.A.']);
    }

    public function test_valid_payload_creates_orden_with_computed_totals(): void
    {
        $payload = $this->payloadValidado();
        $usuario = $this->adminUser();

        $this->actingAs($usuario)
            ->post('/compras', $payload, ['Accept' => 'application/json'])
            ->assertOk()
            ->assertJson(['message' => 'Orden de compra #1 creada correctamente.']);

        $this->assertDatabaseHas('ordenes_compra', [
            'proveedor_id' => $payload['proveedor_id'],
            'usuario_id' => $usuario->id,
            'moneda_id' => $payload['moneda_id'],
            'tasa_cambio' => '40.5000',
            'monto_total' => '81.00',
            'monto_total_base' => '2.00',
            'estado' => 'PENDIENTE',
            'estado_pago' => 'PENDIENTE',
        ]);

        $this->assertDatabaseCount('detalles_orden_compra', 2);
    }

    public function test_empty_payload_is_rejected(): void
    {
        $this->actingAs($this->adminUser())
            ->post('/compras', [], ['Accept' => 'application/json'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['proveedor_id', 'moneda_id', 'tasa_cambio', 'estado', 'detalles']);

        $this->assertDatabaseCount('ordenes_compra', 0);
        $this->assertDatabaseCount('detalles_orden_compra', 0);
    }

    public function test_duplicate_product_in_detalles_is_rejected(): void
    {
        $producto = Producto::factory()->create();
        $payload = $this->payloadValidado();
        $payload['detalles'] = [
            ['producto_id' => $producto->id, 'cantidad' => 1, 'costo_unitario' => 5],
            ['producto_id' => $producto->id, 'cantidad' => 2, 'costo_unitario' => 6],
        ];

        $this->actingAs($this->adminUser())
            ->post('/compras', $payload, ['Accept' => 'application/json'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('detalles.0.producto_id');

        $this->assertDatabaseCount('ordenes_compra', 0);
    }

    public function test_valid_payload_updates_orden_and_replaces_detalles(): void
    {
        $orden = OrdenCompra::factory()->create();
        $detalle = DetalleOrdenCompra::factory()->create(['orden_compra_id' => $orden->id]);

        $payload = $this->payloadValidado();

        $this->actingAs($this->adminUser())
            ->put("/compras/{$orden->id}", $payload, ['Accept' => 'application/json'])
            ->assertOk()
            ->assertJson(['message' => "Orden de compra #{$orden->id} actualizada correctamente."]);

        $this->assertDatabaseHas('ordenes_compra', [
            'id' => $orden->id,
            'monto_total' => '81.00',
            'monto_total_base' => '2.00',
        ]);

        $this->assertDatabaseMissing('detalles_orden_compra', ['id' => $detalle->id]);
        $this->assertDatabaseCount('detalles_orden_compra', 2);
    }

    public function test_orden_with_pagos_is_deleted_with_its_pagos_and_detalles(): void
    {
        $orden = OrdenCompra::factory()->create();
        DetalleOrdenCompra::factory()->create(['orden_compra_id' => $orden->id]);
        $pago = PagoOrdenCompra::factory()->create(['orden_compra_id' => $orden->id]);

        $this->actingAs($this->adminUser())
            ->delete("/compras/{$orden->id}", headers: ['Accept' => 'application/json'])
            ->assertOk()
            ->assertJson(['message' => "Orden de compra #{$orden->id} eliminada correctamente."]);

        $this->assertDatabaseMissing('ordenes_compra', ['id' => $orden->id]);
        $this->assertDatabaseMissing('pagos_orden_compra', ['id' => $pago->id]);
        $this->assertDatabaseCount('detalles_orden_compra', 0);
    }

    public function test_empty_orden_is_deleted_with_its_detalles(): void
    {
        $orden = OrdenCompra::factory()->create();
        DetalleOrdenCompra::factory()->create(['orden_compra_id' => $orden->id]);

        $this->actingAs($this->adminUser())
            ->delete("/compras/{$orden->id}", headers: ['Accept' => 'application/json'])
            ->assertOk()
            ->assertJson(['message' => "Orden de compra #{$orden->id} eliminada correctamente."]);

        $this->assertDatabaseMissing('ordenes_compra', ['id' => $orden->id]);
        $this->assertDatabaseMissing('detalles_orden_compra', ['orden_compra_id' => $orden->id]);
    }
}
