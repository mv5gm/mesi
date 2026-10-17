<?php

namespace Tests\Feature;

use App\Models\Categoria;
use App\Models\Existencia;
use App\Models\Moneda;
use App\Models\Producto;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ProductoControllerTest extends TestCase
{
    use RefreshDatabase;

    private Role $adminRole;

    private Role $basicRole;

    protected function setUp(): void
    {
        parent::setUp();

        Permission::findOrCreate('gestionar productos');
        $this->adminRole = Role::findOrCreate('admin');
        $this->adminRole->givePermissionTo('gestionar productos');
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
        $this->get('/productos')
            ->assertRedirect('/login');
    }

    public function test_user_without_permission_is_forbidden(): void
    {
        $this->actingAs($this->basicUser())
            ->get('/productos')
            ->assertForbidden();
    }

    public function test_admin_can_view_products_page(): void
    {
        $this->actingAs($this->adminUser())
            ->get('/productos')
            ->assertOk()
            ->assertViewIs('productos.index')
            ->assertSee('Productos');
    }

    public function test_data_endpoint_lists_products(): void
    {
        $producto = Producto::factory()->create(['nombre' => 'Harina de Maíz']);

        $this->actingAs($this->adminUser())
            ->get('/productos/datos?draw=1')
            ->assertOk()
            ->assertJson([
                'draw' => 1,
                'recordsTotal' => 1,
                'recordsFiltered' => 1,
            ])
            ->assertJsonFragment(['sku' => $producto->sku]);
    }

    public function test_valid_payload_creates_product(): void
    {
        $categoria = Categoria::factory()->create();
        $moneda = Moneda::factory()->create();

        $this->actingAs($this->adminUser())
            ->post('/productos', [
                'sku' => 'PRD-001',
                'codigo_barras' => '7501000012345',
                'nombre' => 'Harina de Maíz',
                'descripcion' => 'Bolsa de 1kg',
                'categoria_id' => $categoria->id,
                'moneda_id' => $moneda->id,
                'precio_costo' => 2.50,
                'precio_venta' => 4.00,
                'stock_minimo' => 5,
                'stock_maximo' => 100,
                'unidad_medida' => 'PZA',
            ], ['Accept' => 'application/json'])
            ->assertOk()
            ->assertJson(['message' => 'Producto Harina de Maíz creado correctamente.']);

        $this->assertDatabaseHas('productos', [
            'sku' => 'PRD-001',
            'nombre' => 'Harina de Maíz',
            'stock_minimo' => 5,
        ]);
    }

    public function test_empty_payload_is_rejected(): void
    {
        $this->actingAs($this->adminUser())
            ->post('/productos', [], ['Accept' => 'application/json'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['sku', 'nombre', 'categoria_id', 'moneda_id', 'precio_costo', 'precio_venta', 'unidad_medida']);

        $this->assertDatabaseCount('productos', 0);
    }

    public function test_duplicate_sku_is_rejected(): void
    {
        $existing = Producto::factory()->create();

        $this->actingAs($this->adminUser())
            ->post('/productos', [
                'sku' => $existing->sku,
                'nombre' => 'Otro producto',
                'categoria_id' => Categoria::factory()->create()->id,
                'moneda_id' => Moneda::factory()->create()->id,
                'precio_costo' => 1.00,
                'precio_venta' => 2.00,
                'unidad_medida' => 'PZA',
            ], ['Accept' => 'application/json'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['sku']);

        $this->assertDatabaseCount('productos', 1);
    }

    public function test_stock_maximum_cannot_be_lower_than_minimum(): void
    {
        $this->actingAs($this->adminUser())
            ->post('/productos', [
                'sku' => 'PRD-002',
                'nombre' => 'Producto de prueba',
                'categoria_id' => Categoria::factory()->create()->id,
                'moneda_id' => Moneda::factory()->create()->id,
                'precio_costo' => 1.00,
                'precio_venta' => 2.00,
                'stock_minimo' => 50,
                'stock_maximo' => 10,
                'unidad_medida' => 'PZA',
            ], ['Accept' => 'application/json'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['stock_maximo']);
    }

    public function test_valid_payload_updates_product(): void
    {
        $producto = Producto::factory()->create(['nombre' => 'Nombre Antiguo']);
        $nuevaCategoria = Categoria::factory()->create();
        $moneda = Moneda::factory()->create();

        $this->actingAs($this->adminUser())
            ->put("/productos/{$producto->id}", [
                'sku' => $producto->sku,
                'nombre' => 'Nombre Nuevo',
                'categoria_id' => $nuevaCategoria->id,
                'moneda_id' => $moneda->id,
                'precio_costo' => 3.00,
                'precio_venta' => 5.50,
                'stock_minimo' => 2,
                'unidad_medida' => 'CAJA',
            ], ['Accept' => 'application/json'])
            ->assertOk()
            ->assertJson(['message' => 'Producto actualizado correctamente.']);

        $fresh = $producto->fresh();

        $this->assertSame('Nombre Nuevo', $fresh->nombre);
        $this->assertSame(3.00, (float) $fresh->precio_costo);
        $this->assertSame($nuevaCategoria->id, $fresh->categoria_id);
    }

    public function test_product_with_existencias_cannot_be_deleted(): void
    {
        $producto = Producto::factory()->create();
        Existencia::factory()->create(['producto_id' => $producto->id]);

        $this->actingAs($this->adminUser())
            ->delete("/productos/{$producto->id}", headers: ['Accept' => 'application/json'])
            ->assertUnprocessable();

        $this->assertDatabaseHas('productos', ['id' => $producto->id]);
    }

    public function test_empty_product_is_deleted(): void
    {
        $producto = Producto::factory()->create();

        $this->actingAs($this->adminUser())
            ->delete("/productos/{$producto->id}", headers: ['Accept' => 'application/json'])
            ->assertOk()
            ->assertJson(['message' => 'Producto eliminado correctamente.']);

        $this->assertDatabaseMissing('productos', ['id' => $producto->id]);
    }
}
