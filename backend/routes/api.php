<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\TurmaController;
use App\Http\Controllers\Api\AlunoController;
use App\Http\Controllers\Api\FrequenciaController;
use App\Http\Controllers\Api\AlertaController;
use App\Http\Controllers\Api\NotificacaoController;
use App\Http\Controllers\Api\UserController;

// ========================
// ROTAS PÚBLICAS
// ========================
Route::post('/login',  [AuthController::class, 'login']);

// ========================
// ROTAS AUTENTICADAS
// ========================
Route::middleware('auth:sanctum')->group(function () {

    // Auth
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me',      [AuthController::class, 'me']);

    // Turmas (todos autenticados podem listar, apenas DIRETOR/VICE criam/editam)
    Route::get('/turmas',              [TurmaController::class, 'index']);
    Route::get('/turmas/{id}',         [TurmaController::class, 'show']);
    Route::get('/turmas/{id}/qrcode',  [TurmaController::class, 'qrcode']);
    Route::post('/turmas',             [TurmaController::class, 'store'])->middleware('role:DIRETOR,VICE');
    Route::put('/turmas/{id}',         [TurmaController::class, 'update'])->middleware('role:DIRETOR,VICE');
    Route::delete('/turmas/{id}',      [TurmaController::class, 'destroy'])->middleware('role:DIRETOR');

    // Alunos
    Route::get('/alunos',         [AlunoController::class, 'index']);
    Route::get('/alunos/{id}',    [AlunoController::class, 'show']);
    Route::post('/alunos',        [AlunoController::class, 'store'])->middleware('role:DIRETOR,VICE');
    Route::put('/alunos/{id}',    [AlunoController::class, 'update'])->middleware('role:DIRETOR,VICE');
    Route::delete('/alunos/{id}', [AlunoController::class, 'destroy'])->middleware('role:DIRETOR');

    // Frequências (ASSISTENTE pode registrar, demais podem visualizar)
    Route::get('/frequencias',     [FrequenciaController::class, 'index']);
    Route::post('/frequencias',    [FrequenciaController::class, 'store'])->middleware('role:ASSISTENTE,DIRETOR,VICE,ORIENTADORA');
    Route::get('/frequencias/resumo', [FrequenciaController::class, 'resumo']);

    // Alertas (somente ORIENTADORA, DIRETOR, VICE visualizam)
    Route::get('/alertas', [AlertaController::class, 'index'])->middleware('role:ORIENTADORA,DIRETOR,VICE');

    // Notificações do usuário logado
    Route::get('/notificacoes',             [NotificacaoController::class, 'index']);
    Route::patch('/notificacoes/{id}/lida', [NotificacaoController::class, 'marcarLida']);

    // Gerenciamento de usuários (somente DIRETOR cria/edita/deleta)
    Route::get('/usuarios',          [UserController::class, 'index'])->middleware('role:DIRETOR,VICE');
    Route::post('/usuarios',         [UserController::class, 'store'])->middleware('role:DIRETOR');
    Route::put('/usuarios/{id}',     [UserController::class, 'update'])->middleware('role:DIRETOR');
    Route::delete('/usuarios/{id}',  [UserController::class, 'destroy'])->middleware('role:DIRETOR');
});
