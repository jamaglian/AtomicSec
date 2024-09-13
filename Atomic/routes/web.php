<?php


use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Aplicacoes\AttacksController;
use App\Http\Controllers\Dashboard\DashboardController;
use App\Http\Controllers\Aplicacoes\AnalysisController;
use App\Http\Controllers\Aplicacoes\AplicacoesController;

Route::get('/', function () {
    return view('index');
});

Route::get('/dashboard', [DashboardController::class, 'index'])->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    /*
    |--------------------------------------------------------------------------
    | Rotas de Browser
    |--------------------------------------------------------------------------
    */
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::prefix('aplicacoes')->group(function () {
        Route::get('/', [AplicacoesController::class, 'index'])->name('aplicacoes.index');
        Route::get('/cadastrar', [AplicacoesController::class, 'cadastrof'])->name('aplicacoes.cadastrarf');
        Route::post('/cadastrar', [AplicacoesController::class, 'cadastro'])->name('aplicacoes.cadastrar');
        Route::delete('/delete/{id}', [AplicacoesController::class, 'delete'])->name('aplicacoes.delete');
        Route::prefix('analises')->group(function () {
            Route::get('/', [AnalysisController::class, 'index'])->name('analysis.index');
            Route::delete('/delete/{id}', [AnalysisController::class, 'delete'])->name('analysis.delete');
            Route::get('/cadastrar', [AnalysisController::class, 'cadastrof'])->name('analysis.cadastrof');
            Route::post('/cadastrar', [AnalysisController::class, 'cadastro'])->name('analysis.cadastro');
            Route::get('/{id}', [AnalysisController::class, 'analise'])->name('analysis.analise');
        });
        Route::prefix('ataques')->group(function () {
            Route::delete('/delete/{id}', [AttacksController::class, 'delete'])->name('ataques.delete');
            Route::get('/http-keep-alive', [AttacksController::class, 'http_keep_alive_index'])->name('ataques.http-keep-alive');
            Route::get('/http-keep-alive/cadastrar', [AttacksController::class, 'http_keep_alive_cadastrof'])->name('ataques.http-keep-alive.cadratrof');
            Route::post('/http-keep-alive/cadastrar', [AttacksController::class, 'http_keep_alive_cadastro'])->name('ataques.http-keep-alive.cadastro');
            Route::get('/http-keep-alive/{id}', [AttacksController::class, 'http_keep_alive_attack'])->name('ataques.http-keep-alive.ataque');
            Route::get('/http-slow-post', [AttacksController::class, 'http_slow_post_index'])->name('ataques.http-slow-post');
            Route::get('/http-slow-post/cadastrar', [AttacksController::class, 'http_slow_post_cadastrof'])->name('ataques.http-slow-post.cadratrof');
            Route::post('/http-slow-post/cadastrar', [AttacksController::class, 'http_slow_post_cadastro'])->name('ataques.http-slow-post.cadastro');
            Route::get('/http-slow-post/{id}', [AttacksController::class, 'http_slow_post_attack'])->name('ataques.http-slow-post.ataque');
        });  
    });
    /*
    |--------------------------------------------------------------------------
    | Rotas de AJAX
    |--------------------------------------------------------------------------
    */
    Route::prefix('ajax')->group(function () {
        Route::prefix('aplicacoes')->group(function () {
            Route::get('/{id}/ultima-analise', [AnalysisController::class, 'getLatestAnalysis'])->name('aplicacoes.ajax-ultima-analise');
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