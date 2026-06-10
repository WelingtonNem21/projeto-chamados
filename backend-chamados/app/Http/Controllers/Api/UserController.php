<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;

class UserController extends Controller
{
    /**
     * Retorna a lista de responsáveis disponíveis.
     *
     * Usado pelo frontend para popular os selects de "Responsável"
     * nas telas de criação e edição de chamados.
     *
     * Retorna apenas id e name — dados suficientes para o select,
     * sem expor password ou outros campos sensíveis.
     */
    public function index(): JsonResponse
    {
        $users = User::select('id', 'name')
            ->orderBy('name')
            ->get();

        return response()->json($users);
    }
}
