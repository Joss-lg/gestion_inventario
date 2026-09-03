<?php

namespace App\Http\Controllers;

use App\Models\CajaMovimiento;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    public function index()
    {
        // Cargamos los usuarios con sus roles, permisos y además sus movimientos de caja activos
        $users = User::with(['role', 'permissions', 'cajaMovimientos' => function ($query) {
            $query->where('estado', 'abierta');
        }])->get();

        $roles = Role::all();
        $permissions = Permission::all();

        // Métricas dinámicas para el dashboard
        $totalUsers = $users->count();
        $totalAdmins = $users->filter(fn ($u) => optional($u->role)->name === 'Administrador')->count();
        $totalOperators = $users->filter(fn ($u) => optional($u->role)->name === 'Operador')->count();
        $activeUsers = $users->where('is_active', true)->count();
        $cajasAbiertasHoy = $users->filter(fn ($u) => $u->cajaMovimientos->isNotEmpty())->count();

        return view('users.index', compact(
            'users', 'roles', 'permissions',
            'totalUsers', 'totalAdmins', 'totalOperators', 'activeUsers', 'cajasAbiertasHoy'
        ));
    }

    /**
     * 🟢 Registra un nuevo rol y sincroniza sus permisos.
     */
    public function storeRole(Request $request)
    {
        // Validamos manualmente para capturar la duplicidad
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|unique:roles,name|max:255',
            'permissions' => 'nullable|array',
        ], [
            'name.unique' => '¡El rol "' . $request->name . '" ya existe!',
            'name.required' => 'El nombre del rol es obligatorio.',
        ]);

        // Si el rol ya existe o no pasa la validación, envía el error como alerta flotante
        if ($validator->fails()) {
            return redirect()->back()->with('error', $validator->errors()->first());
        }

        // 1. Crear el rol en la base de datos
        $role = Role::create([
            'name' => $request->name,
        ]);

        return redirect()->route('users.index')->with('success', '¡Rol "'.$role->name.'" creado exitosamente!');
    }

    /**
     * 🟢 NUEVO MÉTODO: Elimina un rol del sistema de forma segura.
     * Corregido para recibir el ID manualmente y evitar errores 404 si el registro ya no existe.
     */
    public function destroyRole($id)
    {
        // Buscamos el rol de forma manual para evitar el 404 automático si ya no existe
        $role = Role::find($id);

        if (!$role) {
            return redirect()->route('users.index')->with('error', 'El rol que intentas eliminar ya no existe en la base de datos.');
        }

        // 1. Opcional: Proteger roles críticos para que no sean borrados por error
        if (in_array(strtolower($role->name), ['administrador', 'admin'])) {
            return redirect()->route('users.index')->with('error', 'No se puede eliminar el rol principal del sistema.');
        }

        // 2. Verificar si hay usuarios utilizando este rol para evitar romper relaciones
        if ($role->users()->count() > 0) {
            return redirect()->route('users.index')->with('error', 'No se puede eliminar el rol porque hay usuarios asignados a él.');
        }

        // 3. Eliminar el rol
        $role->delete();

        return redirect()->route('users.index')->with('success', 'Rol eliminado con éxito.');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
            'role_id' => 'required|exists:roles,id',
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        $isActive = filter_var($request->input('is_active', true), FILTER_VALIDATE_BOOLEAN);

        // Pasamos la contraseña limpia porque el modelo User tiene 'password' => 'hashed' en $casts
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => $request->password,
            'role_id' => $request->role_id,
            'is_active' => $isActive,
        ]);

        // Sincronizamos permisos (vacío por defecto si no hay selección)
        $user->permissions()->sync($request->input('permissions', []));

        return redirect()->route('users.index')->with('success', 'Usuario creado exitosamente.');
    }

    public function update(Request $request, User $user)
    {
        // Protección explícita para el Super Administrador de la plataforma
        if ($user->isAdmin()) {
            $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email,'.$user->id,
                'password' => 'nullable|string|min:8',
            ]);

            $data = [
                'name' => $request->name,
                'email' => $request->email,
                'role_id' => $user->role_id ?? 1,
                'is_active' => true, // El Super Admin jamás se desactiva
            ];

            if ($request->filled('password')) {
                $data['password'] = $request->password;
            }

            $user->update($data);

            if (class_exists(Permission::class)) {
                $user->permissions()->sync(Permission::pluck('id')->toArray());
            }

            return redirect()->route('users.index')->with('success', 'Perfil del Super Administrador actualizado con éxito.');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,'.$user->id,
            'role_id' => 'required|exists:roles,id',
            'password' => 'nullable|string|min:8',
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        $isActive = filter_var($request->input('is_active', true), FILTER_VALIDATE_BOOLEAN);

        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'role_id' => $request->role_id,
            'is_active' => $isActive,
        ];

        if ($request->filled('password')) {
            $data['password'] = $request->password;
        }

        $user->update($data);

        // Sincronizamos correctamente los permisos editados
        $user->permissions()->sync($request->input('permissions', []));

        return redirect()->route('users.index')->with('success', 'Usuario actualizado con éxito.');
    }

    public function destroy(User $user)
    {
        if ($user->isAdmin()) {
            return redirect()->route('users.index')->with('error', 'Acción no permitida. El Super Administrador principal no puede ser eliminado.');
        }

        $tieneCajaAbierta = CajaMovimiento::where('user_id', $user->id)
            ->where('estado', 'abierta')
            ->exists();

        if ($tieneCajaAbierta) {
            return redirect()->route('users.index')->with('error', 'No se puede eliminar al usuario porque tiene una sesión de caja abierta activa.');
        }

        $user->delete();

        return redirect()->route('users.index')->with('success', 'Usuario eliminado con éxito.');
    }
}