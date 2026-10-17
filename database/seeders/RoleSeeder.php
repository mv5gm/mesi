<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            'ver dashboard',
            'gestionar usuarios',
            'gestionar roles',
            'gestionar categorias',
            'gestionar productos',
            'gestionar monedas',
            'gestionar tasas cambio',
            'gestionar almacenes',
            'gestionar clientes',
            'gestionar metodos de pago',
            'gestionar proveedores',
            'gestionar existencias',
            'gestionar compras',
            'gestionar ventas',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        $admin = Role::firstOrCreate(['name' => 'admin']);
        $admin->syncPermissions($permissions);

        Role::firstOrCreate(['name' => 'usuario']);
    }
}
