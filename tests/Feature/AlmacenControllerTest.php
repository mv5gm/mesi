<?php

namespace Tests\Feature;

use App\Enums\TipoMovimiento;
use App\Models\Almacen;
use App\Models\Existencia;
use App\Models\MovimientoInventario;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AlmacenControllerTest extends TestCase
{
    use RefreshDatabase;

    private Role $adminRole;

    private Role $basicRole;

    protected function setUp(): void
    {
        parent::setUp();

        Permission::findOrCreate('gestionar almacenes');
        $this->adminRole = Role::findOrCreate('admin');
        $this->adminRole->givePermissionTo('gestionar almacenes');
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
        $this->get('/almacenes')
            ->assertRedirect('/login');
    }

    public function test_user_without_permission_is_forbidden(): void
    {
        $this->actingAs($this->basicUser())
            ->get('/almacenes')
            ->assertForbidden();
    }

    public function test_admin_can_view_almacenes_page(): void
    {
        $this->actingAs($this->adminUser())
            ->get('/almacenes')
            ->assertOk()
            ->assertViewIs('almacenes.index')
            ->assertSee('Almacenes');
    }

    public function test_data_endpoint_lists_almacenes(): void
    {
        $almacen = Almacen::factory()->create(['nombre' => 'Almacén Central']);

        $this->actingAs($this->adminUser())
            ->get('/almacenes/datos?draw=1')
            ->assertOk()
            ->assertJson([
                'draw' => 1,
                'recordsTotal' => 1,
                'recordsFiltered' => 1,
            ])
            ->assertJsonFragment(['nombre' => 'Almacén Central']);
    }

    public function test_valid_payload_creates_almacen(): void
    {
        $this->actingAs($this->adminUser())
            ->post('/almacenes', [
                'nombre' => 'Sucursal El Centro',
                'ubicacion' => 'Av. Bolívar 123',
                'esta_activo' => '1',
            ], ['Accept' => 'application/json'])
            ->assertOk()
            ->assertJson(['message' => 'Almacén Sucursal El Centro creado correctamente.']);

        $this->assertDatabaseHas('almacenes', [
            'nombre' => 'Sucursal El Centro',
            'ubicacion' => 'Av. Bolívar 123',
            'esta_activo' => true,
        ]);
    }

    public function test_unchecked_activo_creates_inactive_almacen(): void
    {
        $this->actingAs($this->adminUser())
            ->post('/almacenes', [
                'nombre' => 'Almacén Inactivo',
            ], ['Accept' => 'application/json'])
            ->assertOk();

        $this->assertDatabaseHas('almacenes', [
            'nombre' => 'Almacén Inactivo',
            'esta_activo' => false,
        ]);
    }

    public function test_empty_payload_is_rejected(): void
    {
        $this->actingAs($this->adminUser())
            ->post('/almacenes', [], ['Accept' => 'application/json'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('nombre');

        $this->assertDatabaseCount('almacenes', 0);
    }

    public function test_duplicate_nombre_is_rejected(): void
    {
        $existing = Almacen::factory()->create(['nombre' => 'Almacén Central']);

        $this->actingAs($this->adminUser())
            ->post('/almacenes', [
                'nombre' => 'Almacén Central',
            ], ['Accept' => 'application/json'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('nombre');

        $this->assertDatabaseCount('almacenes', 1);
    }

    public function test_valid_payload_updates_almacen(): void
    {
        $almacen = Almacen::factory()->create(['nombre' => 'Almacén Central']);

        $this->actingAs($this->adminUser())
            ->put("/almacenes/{$almacen->id}", [
                'nombre' => 'Almacén Norte',
                'ubicacion' => 'Zona Industrial',
                'esta_activo' => '0',
            ], ['Accept' => 'application/json'])
            ->assertOk()
            ->assertJson(['message' => 'Almacén Almacén Norte actualizado correctamente.']);

        $this->assertSame('Almacén Norte', $almacen->fresh()->nombre);
        $this->assertFalse($almacen->fresh()->esta_activo);
    }

    public function test_update_with_same_nombre_is_allowed(): void
    {
        $almacen = Almacen::factory()->create(['nombre' => 'Almacén Central']);

        $this->actingAs($this->adminUser())
            ->put("/almacenes/{$almacen->id}", [
                'nombre' => 'Almacén Central',
            ], ['Accept' => 'application/json'])
            ->assertOk();

        $this->assertDatabaseCount('almacenes', 1);
    }

    public function test_almacen_with_existencias_cannot_be_deleted(): void
    {
        $almacen = Almacen::factory()->create();
        Existencia::factory()->create(['almacen_id' => $almacen->id]);

        $this->actingAs($this->adminUser())
            ->delete("/almacenes/{$almacen->id}", headers: ['Accept' => 'application/json'])
            ->assertUnprocessable();

        $this->assertDatabaseHas('almacenes', ['id' => $almacen->id]);
    }

    public function test_almacen_with_movimientos_cannot_be_deleted(): void
    {
        $almacen = Almacen::factory()->create();
        MovimientoInventario::factory()->create([
            'almacen_id' => $almacen->id,
            'tipo_movimiento' => TipoMovimiento::Entrada,
        ]);

        $this->actingAs($this->adminUser())
            ->delete("/almacenes/{$almacen->id}", headers: ['Accept' => 'application/json'])
            ->assertUnprocessable();

        $this->assertDatabaseHas('almacenes', ['id' => $almacen->id]);
    }

    public function test_empty_almacen_is_deleted(): void
    {
        $almacen = Almacen::factory()->create(['nombre' => 'Almacén Temporal']);

        $this->actingAs($this->adminUser())
            ->delete("/almacenes/{$almacen->id}", headers: ['Accept' => 'application/json'])
            ->assertOk()
            ->assertJson(['message' => 'Almacén Almacén Temporal eliminado correctamente.']);

        $this->assertDatabaseMissing('almacenes', ['id' => $almacen->id]);
    }
}
