<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        if (! auth()->check()) {
            return redirect()->route('login');
        }

        $user = auth()->user();

        // El Administrador siempre pasa sin importar sus permisos asignados
        if ($user->role && $user->role->name === 'Administrador') {
            return $next($request);
        }

        // Operadores u otros roles evalúan sus permisos asignados
        // Permisos pueden venir como lista separada por '|' (OR lógico)
        $permissions = explode('|', $permission);
        foreach ($permissions as $perm) {
            $perm = trim($perm);
            if ($perm === '') {
                continue;
            }
            if ($user->hasPermissionTo($perm)) {
                return $next($request);
            }
        }

        return redirect()->route('dashboard')->with('error', 'No cuentas con los permisos requeridos para acceder.');
    }
}
