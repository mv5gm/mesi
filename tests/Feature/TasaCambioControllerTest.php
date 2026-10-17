<?php

namespace Tests\Feature;

use App\Models\Moneda;
use App\Models\TasaCambio;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class TasaCambioControllerTest extends TestCase
{
    use RefreshDatabase;

    private Role $adminRole;

    private Role $basicRole;

    protected function setUp(): void
    {
        parent::setUp();

        Permission::findOrCreate('gestionar tasas cambio');
        $this->adminRole = Role::findOrCreate('admin');
        $this->adminRole->givePermissionTo('gestionar tasas cambio');
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
        $this->get('/tasas-cambio')
            ->assertRedirect('/login');
    }

    public function test_user_without_permission_is_forbidden(): void
    {
        $this->actingAs($this->basicUser())
            ->get('/tasas-cambio')
            ->assertForbidden();
    }

    public function test_admin_can_view_tasas_page(): void
    {
        $this->actingAs($this->adminUser())
            ->get('/tasas-cambio')
            ->assertOk()
            ->assertViewIs('tasas-cambio.index')
            ->assertSee('Tasas de cambio');
    }

    public function test_data_endpoint_lists_tasas(): void
    {
        $moneda = Moneda::factory()->create(['codigo' => 'USD']);
        TasaCambio::factory()->create(['moneda_id' => $moneda->id, 'tasa' => 36.0000]);

        $this->actingAs($this->adminUser())
            ->get('/tasas-cambio/datos?draw=1')
            ->assertOk()
            ->assertJson([
                'draw' => 1,
                'recordsTotal' => 1,
                'recordsFiltered' => 1,
            ])
            ->assertJsonFragment(['tasa' => number_format(36.0000, 2)]);
    }

    public function test_valid_payload_creates_tasa(): void
    {
        $moneda = Moneda::factory()->create(['codigo' => 'USD']);

        $this->actingAs($this->adminUser())
            ->post('/tasas-cambio', [
                'moneda_id' => $moneda->id,
                'tasa' => 36.5000,
                'fecha_efectiva' => '2026-09-25T10:00',
            ], ['Accept' => 'application/json'])
            ->assertOk()
            ->assertJson(['message' => 'Tasa de USD creada correctamente.']);

        $this->assertDatabaseHas('tasas_cambio', [
            'moneda_id' => $moneda->id,
            'tasa' => 36.5000,
        ]);
    }

    public function test_empty_payload_is_rejected(): void
    {
        $this->actingAs($this->adminUser())
            ->post('/tasas-cambio', [], ['Accept' => 'application/json'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['moneda_id', 'tasa', 'fecha_efectiva']);

        $this->assertDatabaseCount('tasas_cambio', 0);
    }

    public function test_zero_tasa_is_rejected(): void
    {
        $moneda = Moneda::factory()->create();

        $this->actingAs($this->adminUser())
            ->post('/tasas-cambio', [
                'moneda_id' => $moneda->id,
                'tasa' => 0,
                'fecha_efectiva' => '2026-09-25T10:00',
            ], ['Accept' => 'application/json'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('tasa');
    }

    public function test_invalid_moneda_is_rejected(): void
    {
        $this->actingAs($this->adminUser())
            ->post('/tasas-cambio', [
                'moneda_id' => 9999,
                'tasa' => 36.5,
                'fecha_efectiva' => '2026-09-25T10:00',
            ], ['Accept' => 'application/json'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('moneda_id');
    }

    public function test_valid_payload_updates_tasa(): void
    {
        $moneda = Moneda::factory()->create(['codigo' => 'EUR']);
        $tasa = TasaCambio::factory()->create(['moneda_id' => $moneda->id, 'tasa' => 39.5000]);

        $this->actingAs($this->adminUser())
            ->put("/tasas-cambio/{$tasa->id}", [
                'moneda_id' => $moneda->id,
                'tasa' => 41.2500,
                'fecha_efectiva' => '2026-10-01T08:30',
            ], ['Accept' => 'application/json'])
            ->assertOk()
            ->assertJson(['message' => 'Tasa de EUR actualizada correctamente.']);

        $this->assertSame(41.2500, (float) $tasa->fresh()->tasa);
    }

    public function test_valid_payload_deletes_tasa(): void
    {
        $tasa = TasaCambio::factory()->create();

        $this->actingAs($this->adminUser())
            ->delete("/tasas-cambio/{$tasa->id}", headers: ['Accept' => 'application/json'])
            ->assertOk()
            ->assertJson(['message' => 'Tasa de cambio eliminada correctamente.']);

        $this->assertDatabaseMissing('tasas_cambio', ['id' => $tasa->id]);
    }
}
