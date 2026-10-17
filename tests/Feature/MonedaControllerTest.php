<?php

namespace Tests\Feature;

use App\Models\Moneda;
use App\Models\Producto;
use App\Models\TasaCambio;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class MonedaControllerTest extends TestCase
{
    use RefreshDatabase;

    private Role $adminRole;

    private Role $basicRole;

    protected function setUp(): void
    {
        parent::setUp();

        Permission::findOrCreate('gestionar monedas');
        $this->adminRole = Role::findOrCreate('admin');
        $this->adminRole->givePermissionTo('gestionar monedas');
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
        $this->get('/monedas')
            ->assertRedirect('/login');
    }

    public function test_user_without_permission_is_forbidden(): void
    {
        $this->actingAs($this->basicUser())
            ->get('/monedas')
            ->assertForbidden();
    }

    public function test_admin_can_view_monedas_page(): void
    {
        $this->actingAs($this->adminUser())
            ->get('/monedas')
            ->assertOk()
            ->assertViewIs('monedas.index')
            ->assertSee('Monedas');
    }

    public function test_data_endpoint_lists_monedas(): void
    {
        $moneda = Moneda::factory()->create(['codigo' => 'USD']);

        $this->actingAs($this->adminUser())
            ->get('/monedas/datos?draw=1')
            ->assertOk()
            ->assertJson([
                'draw' => 1,
                'recordsTotal' => 1,
                'recordsFiltered' => 1,
            ])
            ->assertJsonFragment(['codigo' => 'USD']);
    }

    public function test_valid_payload_creates_moneda(): void
    {
        $this->actingAs($this->adminUser())
            ->post('/monedas', [
                'codigo' => 'MXN',
                'nombre' => 'Peso Mexicano',
                'simbolo' => 'MXN',
            ], ['Accept' => 'application/json'])
            ->assertOk()
            ->assertJson(['message' => 'Moneda MXN creada correctamente.']);

        $this->assertDatabaseHas('monedas', [
            'codigo' => 'MXN',
            'es_moneda_base' => false,
        ]);
    }

    public function test_creating_base_moneda_unflags_previous_base(): void
    {
        $anterior = Moneda::factory()->create(['codigo' => 'VES', 'es_moneda_base' => true]);

        $this->actingAs($this->adminUser())
            ->post('/monedas', [
                'codigo' => 'MXN',
                'nombre' => 'Peso Mexicano',
                'simbolo' => 'MXN',
                'es_moneda_base' => '1',
            ], ['Accept' => 'application/json'])
            ->assertOk();

        $this->assertFalse($anterior->fresh()->es_moneda_base);
        $this->assertDatabaseHas('monedas', ['codigo' => 'MXN', 'es_moneda_base' => true]);
    }

    public function test_empty_payload_is_rejected(): void
    {
        $this->actingAs($this->adminUser())
            ->post('/monedas', [], ['Accept' => 'application/json'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['codigo', 'nombre', 'simbolo']);

        $this->assertDatabaseCount('monedas', 0);
    }

    public function test_duplicate_codigo_is_rejected(): void
    {
        $existing = Moneda::factory()->create(['codigo' => 'EUR']);

        $this->actingAs($this->adminUser())
            ->post('/monedas', [
                'codigo' => 'EUR',
                'nombre' => 'Euro Duplicado',
                'simbolo' => '€',
            ], ['Accept' => 'application/json'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('codigo');

        $this->assertDatabaseCount('monedas', 1);
    }

    public function test_valid_payload_updates_moneda(): void
    {
        $moneda = Moneda::factory()->create(['codigo' => 'USD']);

        $this->actingAs($this->adminUser())
            ->put("/monedas/{$moneda->id}", [
                'codigo' => 'USDC',
                'nombre' => 'Dólar Canadiense',
                'simbolo' => 'C$',
            ], ['Accept' => 'application/json'])
            ->assertOk()
            ->assertJson(['message' => 'Moneda USDC actualizada correctamente.']);

        $this->assertSame('USDC', $moneda->fresh()->codigo);
        $this->assertSame('Dólar Canadiense', $moneda->fresh()->nombre);
    }

    public function test_updating_base_moneda_unflags_previous_base(): void
    {
        $anterior = Moneda::factory()->create(['codigo' => 'VES', 'es_moneda_base' => true]);
        $nueva = Moneda::factory()->create(['codigo' => 'USD']);

        $this->actingAs($this->adminUser())
            ->put("/monedas/{$nueva->id}", [
                'codigo' => 'USD',
                'nombre' => $nueva->nombre,
                'simbolo' => '$',
                'es_moneda_base' => '1',
            ], ['Accept' => 'application/json'])
            ->assertOk();

        $this->assertFalse($anterior->fresh()->es_moneda_base);
        $this->assertTrue($nueva->fresh()->es_moneda_base);
    }

    public function test_moneda_with_tasas_cannot_be_deleted(): void
    {
        $moneda = Moneda::factory()->create();
        TasaCambio::factory()->create(['moneda_id' => $moneda->id]);

        $this->actingAs($this->adminUser())
            ->delete("/monedas/{$moneda->id}", headers: ['Accept' => 'application/json'])
            ->assertUnprocessable();

        $this->assertDatabaseHas('monedas', ['id' => $moneda->id]);
    }

    public function test_moneda_with_productos_cannot_be_deleted(): void
    {
        $moneda = Moneda::factory()->create();
        Producto::factory()->create(['moneda_id' => $moneda->id]);

        $this->actingAs($this->adminUser())
            ->delete("/monedas/{$moneda->id}", headers: ['Accept' => 'application/json'])
            ->assertUnprocessable();

        $this->assertDatabaseHas('monedas', ['id' => $moneda->id]);
    }

    public function test_empty_moneda_is_deleted(): void
    {
        $moneda = Moneda::factory()->create(['codigo' => 'MXN']);

        $this->actingAs($this->adminUser())
            ->delete("/monedas/{$moneda->id}", headers: ['Accept' => 'application/json'])
            ->assertOk()
            ->assertJson(['message' => 'Moneda MXN eliminada correctamente.']);

        $this->assertDatabaseMissing('monedas', ['id' => $moneda->id]);
    }
}
