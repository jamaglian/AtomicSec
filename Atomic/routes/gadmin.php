<?php

use Illuminate\Support\Facades\Route;
use App\Http\Middleware\CheckGlobalAdmin;
use App\Http\Controllers\Companies\CompaniesController;


Route::middleware([CheckGlobalAdmin::class, 'auth'])->group(function () {
    Route::prefix('gadmin')->group(function () {
        Route::get('/empresas', [CompaniesController::class, 'view_gadmin'])->name('gadmin_companies.list');
        Route::get('/empresas/cadastrar', [CompaniesController::class, 'cadastrof_gadmin'])->name('gadmin_companies.registerf');
        Route::post('/empresas/cadastrar', [CompaniesController::class, 'cadastro_gadmin'])->name('gadmin_companies.register');
    });
});