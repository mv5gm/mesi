<?php

namespace Tests\Feature;

use App\Models\Almacen;
use App\Models\Existencia;
use App\Models\Producto;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ExistenciaControllerTest extends TestCase
{
    use RefreshDatabase;

    private Role $adminRole;

    private Role $basicRole;

    protected function setUp(): void
    {
        parent::setUp();

        Permission::findOrCreate('gestionar existencias');
        $this->adminRole = Role::findOrCreate('admin');
        $this->adminRole->givePermissionTo('gestionar existencias');
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
        $this->get('/existencias')
            ->assertRedirect('/login');
    }

    public function test_user_without_permission_is_forbidden(): void
    {
        $this->actingAs($this->basicUser())
            ->get('/existencias')
            ->assertForbidden();
    }

    public function test_admin_can_view_existencias_page(): void
    {
        $this->actingAs($this->adminUser())
            ->get('/existencias')
            ->assertOk()
            ->assertViewIs('existencias.index')
            ->assertSee('Existencias');
    }

    public function test_data_endpoint_lists_existencias(): void
    {
        $producto = Producto::factory()->create(['nombre' => 'Filtro de aceite']);
        $almacen = Almacen::factory()->create(['nombre' => 'Almacén Central']);
        Existencia::factory()->create([
            'producto_id' => $producto->id,
            'almacen_id' => $almacen->id,
            'cantidad' => 25,
        ]);

        $this->actingAs($this->adminUser())
            ->get('/existencias/datos?draw=1')
            ->assertOk()
            ->assertJson([
                'draw' => 1,
                'recordsTotal' => 1,
                'recordsFiltered' => 1,
            ])
            ->assertJsonFragment(['producto' => 'Filtro de aceite'])
            ->assertJsonFragment(['almacen' => 'Almacén Central']);
    }

    public function test_productos_endpoint_devuelve_productos_coincidentes_en_nombre_y_sku(): void
    {
        $porNombre = Producto::factory()->create(['sku' => 'ZZZ-9', 'nombre' => 'Filtro de aceite']);
        Producto::factory()->create(['sku' => 'ABC-1', 'nombre' => 'Aceite de motor']);
        Producto::factory()->create(['sku' => 'FILTRO-2', 'nombre' => 'Cualquier cosa']);

        $this->actingAs($this->adminUser())
            ->get('/existencias/productos?q=FILTRO')
            ->assertOk()
            ->assertJsonFragment(['id' => $porNombre->id, 'text' => 'ZZZ-9 — Filtro de aceite']);
    }

    public function test_productos_endpoint_limita_los_resultados(): void
    {
        Producto::factory()->count(25)->create();

        $this->actingAs($this->adminUser())
            ->get('/existencias/productos')
            ->assertOk()
            ->assertJsonCount(20);
    }

    public function test_productos_endpoint_requiere_autenticacion(): void
    {
        $this->get('/existencias/productos?q=test')
            ->assertRedirect('/login');
    }

    public function test_valid_payload_creates_existencia(): void
    {
        $producto = Producto::factory()->create();
        $almacen = Almacen::factory()->create();

        $this->actingAs($this->adminUser())
            ->post('/existencias', [
                'producto_id' => $producto->id,
                'almacen_id' => $almacen->id,
                'cantidad' => 15,
            ], ['Accept' => 'application/json'])
            ->assertOk()
            ->assertJson(['message' => 'Existencia registrada correctamente.']);

        $this->assertDatabaseHas('existencias', [
            'producto_id' => $producto->id,
            'almacen_id' => $almacen->id,
            'cantidad' => 15,
        ]);
    }

    public function test_empty_payload_is_rejected(): void
    {
        $this->actingAs($this->adminUser())
            ->post('/existencias', [], ['Accept' => 'application/json'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['producto_id', 'almacen_id', 'cantidad']);

        $this->assertDatabaseCount('existencias', 0);
    }

    public function test_un_registro_sin_almacen_es_rechazado_con_mensaje(): void
    {
        $producto = Producto::factory()->create();

        $this->actingAs($this->adminUser())
            ->post('/existencias', [
                'producto_id' => $producto->id,
                'cantidad' => 5,
            ], ['Accept' => 'application/json'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['almacen_id' => ['Debes seleccionar un almacén.']]);

        $this->assertDatabaseCount('existencias', 0);
    }

    public function test_el_formulario_tiene_los_contenedores_de_errores(): void
    {
        $this->actingAs($this->adminUser())
            ->get('/existencias')
            ->assertOk()
            ->assertSee('id="register-form"', false)
            ->assertSee('id="register-errors"', false)
            ->assertSee('id="edit-form"', false)
            ->assertSee('id="edit-errors"', false);
    }

    public function test_negative_cantidad_is_rejected(): void
    {
        $producto = Producto::factory()->create();
        $almacen = Almacen::factory()->create();

        $this->actingAs($this->adminUser())
            ->post('/existencias', [
                'producto_id' => $producto->id,
                'almacen_id' => $almacen->id,
                'cantidad' => -3,
            ], ['Accept' => 'application/json'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('cantidad');

        $this->assertDatabaseCount('existencias', 0);
    }

    public function test_duplicate_product_in_same_almacen_is_rejected(): void
    {
        $producto = Producto::factory()->create();
        $almacen = Almacen::factory()->create();
        Existencia::factory()->create([
            'producto_id' => $producto->id,
            'almacen_id' => $almacen->id,
            'cantidad' => 5,
        ]);

        $this->actingAs($this->adminUser())
            ->post('/existencias', [
                'producto_id' => $producto->id,
                'almacen_id' => $almacen->id,
                'cantidad' => 10,
            ], ['Accept' => 'application/json'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('producto_id');
    }

    public function test_valid_payload_updates_existencia(): void
    {
        $existencia = Existencia::factory()->create(['cantidad' => 10]);

        $this->actingAs($this->adminUser())
            ->put("/existencias/{$existencia->id}", [
                'producto_id' => $existencia->producto_id,
                'almacen_id' => $existencia->almacen_id,
                'cantidad' => 40,
            ], ['Accept' => 'application/json'])
            ->assertOk()
            ->assertJson(['message' => 'Existencia actualizada correctamente.']);

        $this->assertSame(40, $existencia->fresh()->cantidad);
    }

    public function test_update_cannot_collide_with_another_existencia(): void
    {
        $producto = Producto::factory()->create();
        $almacenA = Almacen::factory()->create();
        $almacenB = Almacen::factory()->create();
        $primera = Existencia::factory()->create([
            'producto_id' => $producto->id,
            'almacen_id' => $almacenA->id,
            'cantidad' => 1,
        ]);
        Existencia::factory()->create([
            'producto_id' => $producto->id,
            'almacen_id' => $almacenB->id,
            'cantidad' => 2,
        ]);

        $this->actingAs($this->adminUser())
            ->put("/existencias/{$primera->id}", [
                'producto_id' => $producto->id,
                'almacen_id' => $almacenB->id,
                'cantidad' => 3,
            ], ['Accept' => 'application/json'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('producto_id');
    }

    public function test_existencia_is_deleted(): void
    {
        $existencia = Existencia::factory()->create();

        $this->actingAs($this->adminUser())
            ->delete("/existencias/{$existencia->id}", headers: ['Accept' => 'application/json'])
            ->assertOk()
            ->assertJson(['message' => 'Existencia eliminada correctamente.']);

        $this->assertDatabaseMissing('existencias', ['id' => $existencia->id]);
    }
}
