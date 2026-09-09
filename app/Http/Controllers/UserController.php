<?php

namespace App\Http\Controllers;

use App\Models\CajaMovimiento;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index()
    {
        // Cargamos los usuarios con su rol (y los permisos de ese rol) y sus movimientos de caja activos
        $users = User::with(['role.permissions', 'cajaMovimientos' => function ($query) {
            $query->where('estado', 'abierta');
        }])->get();

        $roles = Role::all();

        // Total de permisos existentes en el sistema, para mostrar "X / total" por rol
        $totalPermissions = Permission::count();

        // Métricas dinámicas para el dashboard
        $totalUsers = $users->count();
        $totalAdmins = $users->filter(fn ($u) => optional($u->role)->name === 'Administrador')->count();
        // "Personal operativo": cualquier usuario que no sea Administrador (antes comparaba
        // contra un rol llamado "Operador" que nunca se siembra, así que siempre daba 0).
        $totalOperators = $totalUsers - $totalAdmins;
        $activeUsers = $users->where('is_active', true)->count();
        $cajasAbiertasHoy = $users->filter(fn ($u) => $u->cajaMovimientos->isNotEmpty())->count();

        return view('users.index', compact(
            'users', 'roles', 'totalPermissions',
            'totalUsers', 'totalAdmins', 'totalOperators', 'activeUsers', 'cajasAbiertasHoy'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
            'role_id' => 'required|exists:roles,id',
        ]);

        $isActive = filter_var($request->input('is_active', true), FILTER_VALIDATE_BOOLEAN);

        // Pasamos la contraseña limpia porque el modelo User tiene 'password' => 'hashed' en $casts
        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => $request->password,
            'role_id' => $request->role_id,
            'is_active' => $isActive,
        ]);

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

            return redirect()->route('users.index')->with('success', 'Perfil del Super Administrador actualizado con éxito.');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,'.$user->id,
            'role_id' => 'required|exists:roles,id',
            'password' => 'nullable|string|min:8',
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