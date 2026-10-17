<?php

namespace Tests\Feature;

use App\Models\Categoria;
use App\Models\Producto;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class CategoriaControllerTest extends TestCase
{
    use RefreshDatabase;

    private Role $adminRole;

    private Role $basicRole;

    protected function setUp(): void
    {
        parent::setUp();

        Permission::findOrCreate('gestionar categorias');
        $this->adminRole = Role::findOrCreate('admin');
        $this->adminRole->givePermissionTo('gestionar categorias');
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
        $this->get('/categorias')
            ->assertRedirect('/login');
    }

    public function test_user_without_permission_is_forbidden(): void
    {
        $this->actingAs($this->basicUser())
            ->get('/categorias')
            ->assertForbidden();
    }

    public function test_admin_can_view_categories_page(): void
    {
        $this->actingAs($this->adminUser())
            ->get('/categorias')
            ->assertOk()
            ->assertViewIs('categorias.index')
            ->assertSee('Categorías');
    }

    public function test_data_endpoint_lists_categories(): void
    {
        $categoria = Categoria::factory()->create(['nombre' => 'Bebidas']);

        $this->actingAs($this->adminUser())
            ->get('/categorias/datos?draw=1')
            ->assertOk()
            ->assertJson([
                'draw' => 1,
                'recordsTotal' => 1,
                'recordsFiltered' => 1,
            ])
            ->assertJsonFragment(['nombre' => 'Bebidas']);
    }

    public function test_valid_payload_creates_category_with_parent(): void
    {
        $padre = Categoria::factory()->create(['nombre' => 'Alimentos']);

        $this->actingAs($this->adminUser())
            ->post('/categorias', [
                'nombre' => 'Cereales',
                'categoria_padre_id' => $padre->id,
                'descripcion' => 'Desayunos',
            ], ['Accept' => 'application/json'])
            ->assertOk()
            ->assertJson(['message' => 'Categoría Cereales creada correctamente.']);

        $this->assertDatabaseHas('categorias', [
            'nombre' => 'Cereales',
            'categoria_padre_id' => $padre->id,
        ]);
    }

    public function test_empty_payload_is_rejected(): void
    {
        $this->actingAs($this->adminUser())
            ->post('/categorias', [], ['Accept' => 'application/json'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['nombre']);

        $this->assertDatabaseCount('categorias', 0);
    }

    public function test_category_cannot_be_its_own_parent(): void
    {
        $categoria = Categoria::factory()->create();

        $this->actingAs($this->adminUser())
            ->put("/categorias/{$categoria->id}", [
                'nombre' => $categoria->nombre,
                'categoria_padre_id' => $categoria->id,
            ], ['Accept' => 'application/json'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['categoria_padre_id']);
    }

    public function test_valid_payload_updates_category(): void
    {
        $categoria = Categoria::factory()->create(['nombre' => 'Antigua']);
        $nuevaPadre = Categoria::factory()->create(['nombre' => 'Nueva Padre']);

        $this->actingAs($this->adminUser())
            ->put("/categorias/{$categoria->id}", [
                'nombre' => 'Nueva',
                'categoria_padre_id' => $nuevaPadre->id,
                'descripcion' => 'Actualizada',
            ], ['Accept' => 'application/json'])
            ->assertOk()
            ->assertJson(['message' => 'Categoría actualizada correctamente.']);

        $fresh = $categoria->fresh();

        $this->assertSame('Nueva', $fresh->nombre);
        $this->assertSame($nuevaPadre->id, $fresh->categoria_padre_id);
    }

    public function test_category_with_products_cannot_be_deleted(): void
    {
        $categoria = Categoria::factory()->create();
        Producto::factory()->create(['categoria_id' => $categoria->id]);

        $this->actingAs($this->adminUser())
            ->delete("/categorias/{$categoria->id}", headers: ['Accept' => 'application/json'])
            ->assertUnprocessable();

        $this->assertDatabaseHas('categorias', ['id' => $categoria->id]);
    }

    public function test_category_with_subcategories_cannot_be_deleted(): void
    {
        $padre = Categoria::factory()->create();
        Categoria::factory()->create(['categoria_padre_id' => $padre->id]);

        $this->actingAs($this->adminUser())
            ->delete("/categorias/{$padre->id}", headers: ['Accept' => 'application/json'])
            ->assertUnprocessable();

        $this->assertDatabaseHas('categorias', ['id' => $padre->id]);
    }

    public function test_empty_category_is_deleted(): void
    {
        $categoria = Categoria::factory()->create();

        $this->actingAs($this->adminUser())
            ->delete("/categorias/{$categoria->id}", headers: ['Accept' => 'application/json'])
            ->assertOk()
            ->assertJson(['message' => 'Categoría eliminada correctamente.']);

        $this->assertDatabaseMissing('categorias', ['id' => $categoria->id]);
    }
}
