<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class RolesAndUsersSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Crear Roles
        $adminRole = Role::create(['name' => 'Administrador']);
        $cajeroRole = Role::create(['name' => 'Cajero']);

        // 2. Crear los 7 Permisos Exactos de la Interfaz
        $permissions = [
            ['name' => 'Visualizar valor del inventario', 'slug' => 'view-inventory-value', 'module' => 'INVENTARIO'],
            ['name' => 'Visualizar reportes y gráficas', 'slug' => 'view-reports', 'module' => 'INVENTARIO'],

            ['name' => 'Gestionar categorías', 'slug' => 'manage-categories', 'module' => 'CATÁLOGO'],
            ['name' => 'Gestionar productos', 'slug' => 'manage-products', 'module' => 'CATÁLOGO'],
            ['name' => 'Eliminar del catálogo', 'slug' => 'delete-catalog', 'module' => 'CATÁLOGO'],

            ['name' => 'Registrar entradas y salidas', 'slug' => 'register-movements', 'module' => 'OPERACIONES'],

            ['name' => 'Gestionar usuarios', 'slug' => 'manage-users', 'module' => 'ADMINISTRACIÓN'],
        ];

        foreach ($permissions as $perm) {
            Permission::create($perm);
        }

        // 3. Crear Administrador (Acceso total)
        $admin = User::create([
            'name' => 'admin',
            'email' => 'admin@scgi.mx',
            'password' => Hash::make('password123'),
            'role_id' => $adminRole->id,
            'is_active' => true,
        ]);
        $admin->permissions()->attach(Permission::pluck('id'));

        // 4. Crear cajero (Acceso limitado)
        $operator = User::create([
            'name' => 'cajero1',
            'email' => 'cajero@scgi.mx',
            'password' => Hash::make('password123'),
            'role_id' => $cajeroRole->id,
            'is_active' => true,
        ]);
        
        // Solo asociamos registrar entradas y salidas como muestra inicial (1/7)
        $movPerm = Permission::where('slug', 'register-movements')->first();
        $operator->permissions()->attach($movPerm->id);
    }
}