<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Auth;
use App\Models\Mensaje;

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
        // Share unread messages count with all views
        View::composer('*', function ($view) {
            if (Auth::check()) {
                $usuario = Auth::user();
                $unreadMessages = Mensaje::whereHas('conversacion', function ($query) use ($usuario) {
                    $query->whereHas('usuarios', function ($q) use ($usuario) {
                        $q->where('usuarios.id_usuario', $usuario->id_usuario);
                    });
                })
                ->where('id_usuario', '!=', $usuario->id_usuario)
                ->where('leido', false)
                ->count();

                $view->with('unreadMessages', $unreadMessages);
            }
        });
    }
}
