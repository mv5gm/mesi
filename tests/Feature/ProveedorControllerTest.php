<?php

namespace Tests\Feature;

use App\Models\OrdenCompra;
use App\Models\Proveedor;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ProveedorControllerTest extends TestCase
{
    use RefreshDatabase;

    private Role $adminRole;

    private Role $basicRole;

    protected function setUp(): void
    {
        parent::setUp();

        Permission::findOrCreate('gestionar proveedores');
        $this->adminRole = Role::findOrCreate('admin');
        $this->adminRole->givePermissionTo('gestionar proveedores');
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
        $this->get('/proveedores')
            ->assertRedirect('/login');
    }

    public function test_user_without_permission_is_forbidden(): void
    {
        $this->actingAs($this->basicUser())
            ->get('/proveedores')
            ->assertForbidden();
    }

    public function test_admin_can_view_proveedores_page(): void
    {
        $this->actingAs($this->adminUser())
            ->get('/proveedores')
            ->assertOk()
            ->assertViewIs('proveedores.index')
            ->assertSee('Proveedores');
    }

    public function test_data_endpoint_lists_proveedores(): void
    {
        $proveedor = Proveedor::factory()->create(['nombre_empresa' => 'Repuestos El Motor']);

        $this->actingAs($this->adminUser())
            ->get('/proveedores/datos?draw=1')
            ->assertOk()
            ->assertJson([
                'draw' => 1,
                'recordsTotal' => 1,
                'recordsFiltered' => 1,
            ])
            ->assertJsonFragment(['nombre_empresa' => 'Repuestos El Motor']);
    }

    public function test_valid_payload_creates_proveedor(): void
    {
        $this->actingAs($this->adminUser())
            ->post('/proveedores', [
                'nombre_empresa' => 'Autopartes C.A.',
                'nombre_contacto' => 'Roberto Díaz',
                'correo' => 'ventas@autopartes.com',
                'telefono' => '+58 212 555 0101',
                'direccion' => 'Av. Libertador, Caracas',
            ], ['Accept' => 'application/json'])
            ->assertOk()
            ->assertJson(['message' => 'Proveedor Autopartes C.A. creado correctamente.']);

        $this->assertDatabaseHas('proveedores', [
            'nombre_empresa' => 'Autopartes C.A.',
            'nombre_contacto' => 'Roberto Díaz',
            'correo' => 'ventas@autopartes.com',
        ]);
    }

    public function test_empty_payload_is_rejected(): void
    {
        $this->actingAs($this->adminUser())
            ->post('/proveedores', [], ['Accept' => 'application/json'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('nombre_empresa');

        $this->assertDatabaseCount('proveedores', 0);
    }

    public function test_invalid_correo_is_rejected(): void
    {
        $this->actingAs($this->adminUser())
            ->post('/proveedores', [
                'nombre_empresa' => 'AutoPartes del Sur',
                'correo' => 'no-es-un-correo',
            ], ['Accept' => 'application/json'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('correo');

        $this->assertDatabaseCount('proveedores', 0);
    }

    public function test_valid_payload_updates_proveedor(): void
    {
        $proveedor = Proveedor::factory()->create(['nombre_empresa' => 'Repuestos El Motor']);

        $this->actingAs($this->adminUser())
            ->put("/proveedores/{$proveedor->id}", [
                'nombre_empresa' => 'Repuestos El Motor Norte',
                'nombre_contacto' => null,
                'correo' => 'nuevo@elmotor.com',
                'telefono' => null,
                'direccion' => 'Nueva dirección',
            ], ['Accept' => 'application/json'])
            ->assertOk()
            ->assertJson(['message' => 'Proveedor Repuestos El Motor Norte actualizado correctamente.']);

        $this->assertSame('Repuestos El Motor Norte', $proveedor->fresh()->nombre_empresa);
        $this->assertSame('nuevo@elmotor.com', $proveedor->fresh()->correo);
        $this->assertNull($proveedor->fresh()->nombre_contacto);
    }

    public function test_proveedor_with_ordenes_cannot_be_deleted(): void
    {
        $proveedor = Proveedor::factory()->create();
        OrdenCompra::factory()->create(['proveedor_id' => $proveedor->id]);

        $this->actingAs($this->adminUser())
            ->delete("/proveedores/{$proveedor->id}", headers: ['Accept' => 'application/json'])
            ->assertUnprocessable();

        $this->assertDatabaseHas('proveedores', ['id' => $proveedor->id]);
    }

    public function test_empty_proveedor_is_deleted(): void
    {
        $proveedor = Proveedor::factory()->create(['nombre_empresa' => 'Taller El Turbo']);

        $this->actingAs($this->adminUser())
            ->delete("/proveedores/{$proveedor->id}", headers: ['Accept' => 'application/json'])
            ->assertOk()
            ->assertJson(['message' => 'Proveedor Taller El Turbo eliminado correctamente.']);

        $this->assertDatabaseMissing('proveedores', ['id' => $proveedor->id]);
    }
}
