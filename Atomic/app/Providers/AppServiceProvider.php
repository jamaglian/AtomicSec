<?php

namespace App\Providers;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use App\View\Components\DashboardLayout;
use App\View\Components\AutenticacaoLayout;

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
        Blade::component('dashboard-layout', DashboardLayout::class);
        Blade::component('autenticacao-layout', AutenticacaoLayout::class);
    }
}
