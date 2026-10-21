<?php

namespace Tests\Feature;

use App\Models\MetodoPago;
use App\Models\Moneda;
use App\Models\PagoOrdenCompra;
use App\Models\PagoOrdenVenta;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class MetodoPagoControllerTest extends TestCase
{
    use RefreshDatabase;

    private Role $adminRole;

    private Role $basicRole;

    protected function setUp(): void
    {
        parent::setUp();

        Permission::findOrCreate('gestionar metodos de pago');
        $this->adminRole = Role::findOrCreate('admin');
        $this->adminRole->givePermissionTo('gestionar metodos de pago');
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

    private function monedaId(): int
    {
        return Moneda::factory()->create()->id;
    }

    public function test_unauthenticated_request_redirects_to_login(): void
    {
        $this->get('/metodos-pago')
            ->assertRedirect('/login');
    }

    public function test_user_without_permission_is_forbidden(): void
    {
        $this->actingAs($this->basicUser())
            ->get('/metodos-pago')
            ->assertForbidden();
    }

    public function test_admin_can_view_metodos_pago_page(): void
    {
        $this->actingAs($this->adminUser())
            ->get('/metodos-pago')
            ->assertOk()
            ->assertViewIs('metodos-pago.index')
            ->assertSee('Métodos de pago');
    }

    public function test_data_endpoint_lists_metodos(): void
    {
        $metodo = MetodoPago::factory()->create(['nombre' => 'Pago Móvil']);

        $this->actingAs($this->adminUser())
            ->get('/metodos-pago/datos?draw=1')
            ->assertOk()
            ->assertJson([
                'draw' => 1,
                'recordsTotal' => 1,
                'recordsFiltered' => 1,
            ])
            ->assertJsonFragment(['nombre' => 'Pago Móvil']);
    }

    public function test_valid_payload_creates_metodo(): void
    {
        $monedaId = $this->monedaId();

        $this->actingAs($this->adminUser())
            ->post('/metodos-pago', [
                'nombre' => 'Zelle',
                'requiere_referencia' => '1',
                'moneda_id' => $monedaId,
            ], ['Accept' => 'application/json'])
            ->assertOk()
            ->assertJson(['message' => 'Método de pago Zelle creado correctamente.']);

        $this->assertDatabaseHas('metodos_pago', [
            'nombre' => 'Zelle',
            'requiere_referencia' => true,
            'moneda_id' => $monedaId,
        ]);
    }

    public function test_unchecked_requiere_referencia_defaults_false(): void
    {
        $this->actingAs($this->adminUser())
            ->post('/metodos-pago', [
                'nombre' => 'Efectivo',
                'moneda_id' => $this->monedaId(),
            ], ['Accept' => 'application/json'])
            ->assertOk();

        $this->assertDatabaseHas('metodos_pago', [
            'nombre' => 'Efectivo',
            'requiere_referencia' => false,
        ]);
    }

    public function test_empty_payload_is_rejected(): void
    {
        $this->actingAs($this->adminUser())
            ->post('/metodos-pago', [], ['Accept' => 'application/json'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['nombre', 'moneda_id']);

        $this->assertDatabaseCount('metodos_pago', 0);
    }

    public function test_invalid_moneda_is_rejected(): void
    {
        $this->actingAs($this->adminUser())
            ->post('/metodos-pago', [
                'nombre' => 'PayPal',
                'moneda_id' => 9999,
            ], ['Accept' => 'application/json'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('moneda_id');

        $this->assertDatabaseCount('metodos_pago', 0);
    }

    public function test_duplicate_nombre_is_rejected(): void
    {
        $existing = MetodoPago::factory()->create(['nombre' => 'Transferencia']);

        $this->actingAs($this->adminUser())
            ->post('/metodos-pago', [
                'nombre' => 'Transferencia',
                'moneda_id' => $this->monedaId(),
            ], ['Accept' => 'application/json'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('nombre');

        $this->assertDatabaseCount('metodos_pago', 1);
    }

    public function test_valid_payload_updates_metodo(): void
    {
        $metodo = MetodoPago::factory()->create(['nombre' => 'Efectivo']);
        $monedaId = $this->monedaId();

        $this->actingAs($this->adminUser())
            ->put("/metodos-pago/{$metodo->id}", [
                'nombre' => 'Efectivo USD',
                'requiere_referencia' => '0',
                'moneda_id' => $monedaId,
            ], ['Accept' => 'application/json'])
            ->assertOk()
            ->assertJson(['message' => 'Método de pago Efectivo USD actualizado correctamente.']);

        $this->assertSame('Efectivo USD', $metodo->fresh()->nombre);
        $this->assertFalse($metodo->fresh()->requiere_referencia);
        $this->assertSame($monedaId, $metodo->fresh()->moneda_id);
    }

    public function test_update_with_same_nombre_is_allowed(): void
    {
        $metodo = MetodoPago::factory()->create(['nombre' => 'Pago Móvil']);

        $this->actingAs($this->adminUser())
            ->put("/metodos-pago/{$metodo->id}", [
                'nombre' => 'Pago Móvil',
                'moneda_id' => $this->monedaId(),
            ], ['Accept' => 'application/json'])
            ->assertOk();

        $this->assertDatabaseCount('metodos_pago', 1);
    }

    public function test_metodo_with_pagos_orden_compra_cannot_be_deleted(): void
    {
        $metodo = MetodoPago::factory()->create();
        PagoOrdenCompra::factory()->create(['metodo_pago_id' => $metodo->id]);

        $this->actingAs($this->adminUser())
            ->delete("/metodos-pago/{$metodo->id}", headers: ['Accept' => 'application/json'])
            ->assertUnprocessable();

        $this->assertDatabaseHas('metodos_pago', ['id' => $metodo->id]);
    }

    public function test_metodo_with_pagos_orden_venta_cannot_be_deleted(): void
    {
        $metodo = MetodoPago::factory()->create();
        PagoOrdenVenta::factory()->create(['metodo_pago_id' => $metodo->id]);

        $this->actingAs($this->adminUser())
            ->delete("/metodos-pago/{$metodo->id}", headers: ['Accept' => 'application/json'])
            ->assertUnprocessable();

        $this->assertDatabaseHas('metodos_pago', ['id' => $metodo->id]);
    }

    public function test_empty_metodo_is_deleted(): void
    {
        $metodo = MetodoPago::factory()->create(['nombre' => 'Criptomoneda']);

        $this->actingAs($this->adminUser())
            ->delete("/metodos-pago/{$metodo->id}", headers: ['Accept' => 'application/json'])
            ->assertOk()
            ->assertJson(['message' => 'Método de pago Criptomoneda eliminado correctamente.']);

        $this->assertDatabaseMissing('metodos_pago', ['id' => $metodo->id]);
    }
}
