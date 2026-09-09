<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // =========================================================================
        // SUPER ADMINISTRADOR (Acceso total automático sin restricciones)
        // =========================================================================
        // Las rutas se protegen con el middleware 'permission' (CheckPermission) y
        // las vistas consultan directamente $user->hasPermission('slug'), que ya
        // hace este mismo bypass para administradores. Este Gate::before se deja
        // como respaldo por si en el futuro se usa Gate/@can/authorize() en algún
        // punto del código (policies, form requests, etc.).
        Gate::before(function (User $user) {
            if ($user->isAdmin()) {
                return true;
            }
        });
    }
}
