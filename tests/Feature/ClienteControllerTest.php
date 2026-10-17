<?php

namespace Tests\Feature;

use App\Models\Cliente;
use App\Models\OrdenVenta;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ClienteControllerTest extends TestCase
{
    use RefreshDatabase;

    private Role $adminRole;

    private Role $basicRole;

    protected function setUp(): void
    {
        parent::setUp();

        Permission::findOrCreate('gestionar clientes');
        $this->adminRole = Role::findOrCreate('admin');
        $this->adminRole->givePermissionTo('gestionar clientes');
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

    public function test_unauthenticated_request_redirects_to_login(): void
    {
        $this->get('/clientes')
            ->assertRedirect('/login');
    }

    public function test_user_without_permission_is_forbidden(): void
    {
        $this->actingAs($this->basicUser())
            ->get('/clientes')
            ->assertForbidden();
    }

    public function test_admin_can_view_clientes_page(): void
    {
        $this->actingAs($this->adminUser())
            ->get('/clientes')
            ->assertOk()
            ->assertViewIs('clientes.index')
            ->assertSee('Clientes');
    }

    public function test_data_endpoint_lists_clientes(): void
    {
        $cliente = Cliente::factory()->create(['nombre' => 'María Pérez']);

        $this->actingAs($this->adminUser())
            ->get('/clientes/datos?draw=1')
            ->assertOk()
            ->assertJson([
                'draw' => 1,
                'recordsTotal' => 1,
                'recordsFiltered' => 1,
            ])
            ->assertJsonFragment(['nombre' => 'María Pérez']);
    }

    public function test_valid_payload_creates_cliente(): void
    {
        $this->actingAs($this->adminUser())
            ->post('/clientes', [
                'nombre' => 'Carlos Rodríguez',
                'correo' => 'carlos@example.com',
                'telefono' => '+58 412 123 4567',
                'direccion' => 'Calle 5, Caracas',
            ], ['Accept' => 'application/json'])
            ->assertOk()
            ->assertJson(['message' => 'Cliente Carlos Rodríguez creado correctamente.']);

        $this->assertDatabaseHas('clientes', [
            'nombre' => 'Carlos Rodríguez',
            'correo' => 'carlos@example.com',
            'telefono' => '+58 412 123 4567',
            'direccion' => 'Calle 5, Caracas',
        ]);
    }

    public function test_empty_payload_is_rejected(): void
    {
        $this->actingAs($this->adminUser())
            ->post('/clientes', [], ['Accept' => 'application/json'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('nombre');

        $this->assertDatabaseCount('clientes', 0);
    }

    public function test_invalid_correo_is_rejected(): void
    {
        $this->actingAs($this->adminUser())
            ->post('/clientes', [
                'nombre' => 'Juan López',
                'correo' => 'no-es-un-correo',
            ], ['Accept' => 'application/json'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('correo');

        $this->assertDatabaseCount('clientes', 0);
    }

    public function test_valid_payload_updates_cliente(): void
    {
        $cliente = Cliente::factory()->create(['nombre' => 'María Pérez']);

        $this->actingAs($this->adminUser())
            ->put("/clientes/{$cliente->id}", [
                'nombre' => 'María Pérez Gómez',
                'correo' => 'maria.nueva@example.com',
                'telefono' => null,
                'direccion' => 'Nueva dirección',
            ], ['Accept' => 'application/json'])
            ->assertOk()
            ->assertJson(['message' => 'Cliente María Pérez Gómez actualizado correctamente.']);

        $this->assertSame('María Pérez Gómez', $cliente->fresh()->nombre);
        $this->assertSame('maria.nueva@example.com', $cliente->fresh()->correo);
        $this->assertNull($cliente->fresh()->telefono);
    }

    public function test_cliente_with_ordenes_cannot_be_deleted(): void
    {
        $cliente = Cliente::factory()->create();
        OrdenVenta::factory()->create(['cliente_id' => $cliente->id]);

        $this->actingAs($this->adminUser())
            ->delete("/clientes/{$cliente->id}", headers: ['Accept' => 'application/json'])
            ->assertUnprocessable();

        $this->assertDatabaseHas('clientes', ['id' => $cliente->id]);
    }

    public function test_empty_cliente_is_deleted(): void
    {
        $cliente = Cliente::factory()->create(['nombre' => 'Pedro Infante']);

        $this->actingAs($this->adminUser())
            ->delete("/clientes/{$cliente->id}", headers: ['Accept' => 'application/json'])
            ->assertOk()
            ->assertJson(['message' => 'Cliente Pedro Infante eliminado correctamente.']);

        $this->assertDatabaseMissing('clientes', ['id' => $cliente->id]);
    }
}
