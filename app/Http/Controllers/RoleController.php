<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class RoleController extends Controller
{
    /**
     * Nombres de roles protegidos que no se pueden borrar ni editar sus permisos
     * (el Administrador siempre tiene acceso total vía Gate::before).
     */
    private const ROLES_PROTEGIDOS = ['administrador', 'admin'];

    public function index()
    {
        $roles = Role::withCount('users')->with('permissions')->get();
        $permissions = Permission::orderBy('module')->orderBy('name')->get();
        $permissionsByModule = $permissions->groupBy('module');

        return view('roles.index', compact('roles', 'permissions', 'permissionsByModule'));
    }

    /**
     * Registra un nuevo rol y sincroniza sus permisos.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|unique:roles,name|max:255',
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,id',
        ], [
            'name.unique' => '¡El rol "'.$request->name.'" ya existe!',
            'name.required' => 'El nombre del rol es obligatorio.',
        ]);

        if ($validator->fails()) {
            return redirect()->route('roles.index')->with('error', $validator->errors()->first());
        }

        $role = Role::create([
            'name' => $request->name,
        ]);

        $role->permissions()->sync($request->input('permissions', []));

        return redirect()->route('roles.index')->with('success', '¡Rol "'.$role->name.'" creado exitosamente!');
    }

    /**
     * Actualiza el nombre y los permisos asignados a un rol existente.
     */
    public function update(Request $request, $id)
    {
        $role = Role::find($id);

        if (! $role) {
            return redirect()->route('roles.index')->with('error', 'El rol que intentas editar ya no existe en la base de datos.');
        }

        if (in_array(strtolower($role->name), self::ROLES_PROTEGIDOS)) {
            return redirect()->route('roles.index')->with('error', 'No se pueden modificar los permisos del rol principal del sistema.');
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:roles,name,'.$role->id,
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,id',
        ], [
            'name.unique' => '¡El rol "'.$request->name.'" ya existe!',
            'name.required' => 'El nombre del rol es obligatorio.',
        ]);

        if ($validator->fails()) {
            return redirect()->route('roles.index')->with('error', $validator->errors()->first());
        }

        $role->update([
            'name' => $request->name,
        ]);

        $role->permissions()->sync($request->input('permissions', []));

        return redirect()->route('roles.index')->with('success', 'Rol "'.$role->name.'" actualizado con éxito.');
    }

    /**
     * Elimina un rol del sistema de forma segura.
     */
    public function destroy($id)
    {
        $role = Role::find($id);

        if (! $role) {
            return redirect()->route('roles.index')->with('error', 'El rol que intentas eliminar ya no existe en la base de datos.');
        }

        // 1. Proteger roles críticos para que no sean borrados por error
        if (in_array(strtolower($role->name), self::ROLES_PROTEGIDOS)) {
            return redirect()->route('roles.index')->with('error', 'No se puede eliminar el rol principal del sistema.');
        }

        // 2. Verificar si hay usuarios utilizando este rol para evitar romper relaciones
        if ($role->users()->count() > 0) {
            return redirect()->route('roles.index')->with('error', 'No se puede eliminar el rol porque hay usuarios asignados a él.');
        }

        $role->delete();

        return redirect()->route('roles.index')->with('success', 'Rol eliminado con éxito.');
    }
}
