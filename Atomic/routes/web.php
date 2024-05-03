<?php


use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Dashboard\DashboardController;
use App\Http\Controllers\Aplicacoes\AnalysisController;
use App\Http\Controllers\Aplicacoes\AplicacoesController;


Route::get('/', function () {
    return view('index');
});

Route::get('/dashboard', [DashboardController::class, 'index'])->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::prefix('aplicacoes')->group(function () {
        Route::get('/', [AplicacoesController::class, 'index'])->name('aplicacoes.index');
        Route::get('/cadastrar', [AplicacoesController::class, 'cadastrof'])->name('aplicacoes.cadastrarf');
        Route::post('/cadastrar', [AplicacoesController::class, 'cadastro'])->name('aplicacoes.cadastrar');
        Route::prefix('analises')->group(function () {
            Route::get('/', [AnalysisController::class, 'index'])->name('analysis.index');
            Route::get('/cadastrar', [AnalysisController::class, 'cadastrof'])->name('analysis.cadastrof');
            Route::post('/cadastrar', [AnalysisController::class, 'cadastro'])->name('analysis.cadastro');
            Route::get('/{id}', [AnalysisController::class, 'analise'])->name('analysis.analise');
        });
    });
});


if(config('app.debug')){
    Route::get('/mail', function () {
        return redirect()->away('http://localhost:8025');
    });
    Route::get('/phpmyadmin', function () {
        return redirect()->away('http://localhost:3307');
    });
}

require __DIR__.'/auth.php';
require __DIR__.'/gadmin.php';