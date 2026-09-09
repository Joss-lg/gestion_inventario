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

        // 2. Crear los permisos del sistema, con create/edit/delete separados
        //    por módulo para poder ocultar acciones puntuales según el rol.
        $permissions = [
            // INVENTARIO (reportes / dashboard)
            ['name' => 'Visualizar reportes y gráficas', 'slug' => 'view-reports', 'module' => 'INVENTARIO'],
            ['name' => 'Visualizar valor del inventario', 'slug' => 'view-inventory-value', 'module' => 'INVENTARIO'],

            // CATÁLOGO → Categorías
            ['name' => 'Ver categorías', 'slug' => 'view-categories', 'module' => 'CATÁLOGO'],
            ['name' => 'Crear categorías', 'slug' => 'create-categories', 'module' => 'CATÁLOGO'],
            ['name' => 'Editar categorías', 'slug' => 'edit-categories', 'module' => 'CATÁLOGO'],
            ['name' => 'Eliminar categorías', 'slug' => 'delete-categories', 'module' => 'CATÁLOGO'],

            // CATÁLOGO → Productos
            ['name' => 'Ver productos', 'slug' => 'view-products', 'module' => 'CATÁLOGO'],
            ['name' => 'Crear productos', 'slug' => 'create-products', 'module' => 'CATÁLOGO'],
            ['name' => 'Editar productos', 'slug' => 'edit-products', 'module' => 'CATÁLOGO'],
            ['name' => 'Eliminar productos', 'slug' => 'delete-products', 'module' => 'CATÁLOGO'],

            // OPERACIONES → Stock (movimientos de inventario)
            ['name' => 'Ver movimientos de stock', 'slug' => 'view-stock', 'module' => 'OPERACIONES'],
            ['name' => 'Registrar movimientos de stock', 'slug' => 'create-stock', 'module' => 'OPERACIONES'],

            // OPERACIONES → Punto de Venta
            ['name' => 'Vender en el Punto de Venta', 'slug' => 'access-pos', 'module' => 'OPERACIONES'],

            // ADMINISTRACIÓN → Usuarios
            ['name' => 'Ver usuarios', 'slug' => 'view-users', 'module' => 'ADMINISTRACIÓN'],
            ['name' => 'Crear usuarios', 'slug' => 'create-users', 'module' => 'ADMINISTRACIÓN'],
            ['name' => 'Editar usuarios', 'slug' => 'edit-users', 'module' => 'ADMINISTRACIÓN'],
            ['name' => 'Eliminar usuarios', 'slug' => 'delete-users', 'module' => 'ADMINISTRACIÓN'],

            // ADMINISTRACIÓN → Roles y Permisos
            ['name' => 'Ver roles y permisos', 'slug' => 'view-roles', 'module' => 'ADMINISTRACIÓN'],
            ['name' => 'Crear roles', 'slug' => 'create-roles', 'module' => 'ADMINISTRACIÓN'],
            ['name' => 'Editar permisos de rol', 'slug' => 'edit-roles', 'module' => 'ADMINISTRACIÓN'],
            ['name' => 'Eliminar roles', 'slug' => 'delete-roles', 'module' => 'ADMINISTRACIÓN'],

            // ADMINISTRACIÓN → Caja
            ['name' => 'Ver historial de turnos de caja', 'slug' => 'view-caja-historial', 'module' => 'ADMINISTRACIÓN'],
        ];

        foreach ($permissions as $perm) {
            Permission::create($perm);
        }

        // 3. Asignar permisos por ROL (el Administrador tiene acceso total vía Gate::before,
        // pero igual se le sincronizan todos los permisos para que se vea completo en la vista de Roles)
        $adminRole->permissions()->sync(Permission::pluck('id'));

        // Al rol Cajero solo se le asigna "Vender en el Punto de Venta" como permiso inicial;
        // el resto (stock, catálogo, administración) se activa manualmente desde Roles y Permisos.
        $posPerm = Permission::where('slug', 'access-pos')->first();
        $cajeroRole->permissions()->sync([$posPerm->id]);

        // 4. Crear Administrador (Acceso total)
        User::create([
            'name' => 'admin',
            'email' => 'admin@scgi.mx',
            'password' => Hash::make('password123'),
            'role_id' => $adminRole->id,
            'is_active' => true,
        ]);

        // 5. Crear cajero (Acceso limitado, según los permisos de su rol)
        User::create([
            'name' => 'cajero1',
            'email' => 'cajero@scgi.mx',
            'password' => Hash::make('password123'),
            'role_id' => $cajeroRole->id,
            'is_active' => true,
        ]);
    }
}
