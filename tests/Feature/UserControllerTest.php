<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class UserControllerTest extends TestCase
{
    use RefreshDatabase;

    private Role $adminRole;

    private Role $basicRole;

    protected function setUp(): void
    {
        parent::setUp();

        Permission::findOrCreate('gestionar usuarios');
        $this->adminRole = Role::findOrCreate('admin');
        $this->adminRole->givePermissionTo('gestionar usuarios');
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
        $this->get('/usuarios')
            ->assertRedirect('/login');
    }

    public function test_user_without_permission_is_forbidden(): void
    {
        $this->actingAs($this->basicUser())
            ->get('/usuarios')
            ->assertForbidden();
    }

    public function test_admin_can_view_users_page(): void
    {
        $this->actingAs($this->adminUser())
            ->get('/usuarios')
            ->assertOk()
            ->assertViewIs('users.index')
            ->assertSee('Usuarios');
    }

    public function test_data_endpoint_lists_users(): void
    {
        $user = $this->basicUser();

        $this->actingAs($this->adminUser())
            ->get('/usuarios/datos?draw=1')
            ->assertOk()
            ->assertJson([
                'draw' => 1,
                'recordsTotal' => 2,
            ])
            ->assertJsonFragment(['username' => $user->username]);
    }

    public function test_valid_payload_creates_user_with_role(): void
    {
        $this->actingAs($this->adminUser())
            ->post('/usuarios', [
                'name' => 'Laura Pérez',
                'username' => 'lperez',
                'password' => 'clave123',
                'role' => 'usuario',
            ], ['Accept' => 'application/json'])
            ->assertOk()
            ->assertJson(['message' => 'Usuario creado correctamente.']);

        $created = User::where('username', 'lperez')->first();

        $this->assertNotNull($created);
        $this->assertSame('Laura Pérez', $created->name);
        $this->assertTrue(Hash::check('clave123', $created->password));
        $this->assertTrue($created->hasRole('usuario'));
    }

    public function test_empty_payload_is_rejected(): void
    {
        $this->actingAs($this->adminUser())
            ->post('/usuarios', [], ['Accept' => 'application/json'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['name', 'username', 'password', 'role']);

        $this->assertDatabaseCount('users', 1);
    }

    public function test_duplicate_username_is_rejected(): void
    {
        $existing = $this->basicUser();

        $this->actingAs($this->adminUser())
            ->post('/usuarios', [
                'name' => 'Otro Usuario',
                'username' => $existing->username,
                'password' => 'clave123',
                'role' => 'usuario',
            ], ['Accept' => 'application/json'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('username');

        $this->assertDatabaseCount('users', 2);
    }

    public function test_valid_payload_updates_user_and_syncs_role(): void
    {
        $user = $this->basicUser();

        $this->actingAs($this->adminUser())
            ->put("/usuarios/{$user->id}", [
                'name' => 'Nombre Nuevo',
                'username' => 'nuevoUsuario',
                'password' => '',
                'role' => 'admin',
            ], ['Accept' => 'application/json'])
            ->assertOk()
            ->assertJson(['message' => 'Usuario actualizado correctamente.']);

        $updated = $user->fresh();

        $this->assertSame('Nombre Nuevo', $updated->name);
        $this->assertSame('nuevoUsuario', $updated->username);
        $this->assertTrue($updated->hasRole('admin'));
        $this->assertFalse($updated->hasRole('usuario'));
    }

    public function test_blank_password_keeps_the_existing_password(): void
    {
        $user = $this->basicUser();
        $previousHash = $user->password;

        $this->actingAs($this->adminUser())
            ->put("/usuarios/{$user->id}", [
                'name' => $user->name,
                'username' => $user->username,
                'password' => '',
                'role' => 'usuario',
            ], ['Accept' => 'application/json'])
            ->assertOk();

        $this->assertSame($previousHash, $user->fresh()->password);
    }

    public function test_user_cannot_change_own_role(): void
    {
        $admin = $this->adminUser();

        $this->actingAs($admin)
            ->put("/usuarios/{$admin->id}", [
                'name' => $admin->name,
                'username' => $admin->username,
                'password' => '',
                'role' => 'usuario',
            ], ['Accept' => 'application/json'])
            ->assertOk();

        $this->assertTrue($admin->fresh()->hasRole('admin'));
    }

    public function test_valid_payload_deletes_user(): void
    {
        $target = $this->basicUser();

        $this->actingAs($this->adminUser())
            ->delete("/usuarios/{$target->id}", headers: ['Accept' => 'application/json'])
            ->assertOk()
            ->assertJson(['message' => 'Usuario eliminado correctamente.']);

        $this->assertDatabaseMissing('users', ['id' => $target->id]);
    }

    public function test_user_cannot_delete_himself(): void
    {
        $admin = $this->adminUser();

        $this->actingAs($admin)
            ->delete("/usuarios/{$admin->id}", headers: ['Accept' => 'application/json'])
            ->assertUnprocessable()
            ->assertJson(['message' => 'No puedes eliminar tu propio usuario.']);

        $this->assertDatabaseHas('users', ['id' => $admin->id]);
    }
}
