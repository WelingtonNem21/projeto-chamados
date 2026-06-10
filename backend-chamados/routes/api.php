<?php

use App\Http\Controllers\Api\ChamadoController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

/*
 * Rotas da API REST — sem autenticação (sistema interno).
 *
 * Todos os endpoints retornam JSON.
 * Prefixo /api já é aplicado automaticamente pelo Laravel.
 *
 * GET    /api/chamados              → lista com filtros e busca
 * POST   /api/chamados              → criar chamado
 * GET    /api/chamados/{chamado}    → detalhes
 * PUT    /api/chamados/{chamado}    → editar
 * DELETE /api/chamados/{chamado}    → excluir
 * GET    /api/users                 → lista responsáveis (para selects)
 */

Route::apiResource('chamados', ChamadoController::class);
Route::get('users', [UserController::class, 'index']);
