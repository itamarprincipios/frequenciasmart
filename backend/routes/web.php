<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;

// Redireciona raiz para login
Route::get('/', fn() => redirect('/login'));

// Auth Web
Route::get('/login',  [DashboardController::class, 'loginForm'])->name('login');
Route::post('/login', [DashboardController::class, 'loginPost'])->name('login.post');
Route::post('/logout',[DashboardController::class, 'logout'])->name('logout');

// Dashboard (protegido por session)
Route::middleware('auth.session.web')->group(function () {
    Route::get('/dashboard',     [DashboardController::class, 'dashboard'])->name('dashboard');
    Route::get('/orientadora',   [DashboardController::class, 'orientadora'])->name('orientadora');
    Route::get('/turmas',             [DashboardController::class, 'turmas'])->name('turmas');
    Route::get('/turmas/{id}/qrcode', [DashboardController::class, 'turmasQrcode'])->name('turmas.qrcode');
    Route::get('/usuarios',      [DashboardController::class, 'usuarios'])->name('usuarios');
    Route::get('/frequencias',   [DashboardController::class, 'frequencias'])->name('frequencias');
    Route::get('/frequencia/lancar',     [DashboardController::class, 'frequenciaLancar'])->name('frequencia.lancar');
    Route::post('/frequencia/registrar', [DashboardController::class, 'frequenciaRegistrar'])->name('frequencia.registrar');

    // Gestão de Alunos (CRUD completo)
    Route::get('/alunos',               [DashboardController::class, 'alunos'])->name('alunos');
    Route::get('/alunos/criar',         [DashboardController::class, 'alunosCriar'])->name('alunos.criar');
    Route::post('/alunos',              [DashboardController::class, 'alunosStore'])->name('alunos.store');
    Route::get('/alunos/{id}/editar',   [DashboardController::class, 'alunosEditar'])->name('alunos.editar');
    Route::post('/alunos/{id}',         [DashboardController::class, 'alunosUpdate'])->name('alunos.update');
    Route::post('/alunos/{id}/excluir', [DashboardController::class, 'alunosDestroy'])->name('alunos.destroy');
    Route::get('/alunos/{id}/qrcode',   [DashboardController::class, 'alunosQrcode'])->name('alunos.qrcode');
});

