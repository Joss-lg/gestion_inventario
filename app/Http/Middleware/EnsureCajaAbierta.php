<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureCajaAbierta
{
    /**
     * Maneja una solicitud entrante.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // Si no está autenticado, pasa a la siguiente capa
        if (! $user) {
            return $next($request);
        }

        if ($user->isAdmin()) {
            return $next($request);
        }

        if (! $user->hasPermission('access-pos')) {
            return $next($request);
        }

        // Los usuarios operativos necesitan una caja abierta para continuar.
        if (! $user->cajaActiva()) {

            // Si el bloqueo ocurre justo al entrar al dashboard (login),
            // lo mandamos a abrir caja SIN mostrar la alerta de error.
            if ($request->routeIs('dashboard')) {
                return redirect()->route('caja.index');
            }

            // En cualquier otra ruta protegida (POS, Stock, etc.) sí mostramos el aviso.
            return redirect()->route('caja.index')->with('error', 'Debes abrir el turno de caja para realizar movimientos u operaciones.');
        }

        return $next($request);
    }
}