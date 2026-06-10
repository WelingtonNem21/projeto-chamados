<?php

namespace App\Http\Controllers\Api;

use App\Enums\StatusChamado;
use App\Http\Controllers\Controller;
use App\Models\Chamado;
use App\Services\DistribuicaoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ChamadoController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $chamados = Chamado::with('responsavel')
            ->when($request->status,         fn($q) => $q->where('status', $request->status))
            ->when($request->prioridade,     fn($q) => $q->where('prioridade', $request->prioridade))
            ->when($request->responsavel_id, fn($q) => $q->where('responsavel_id', $request->responsavel_id))
            ->when($request->busca,          fn($q) => $q->where('titulo', 'like', "%{$request->busca}%"))
            ->orderByRaw("CASE prioridade WHEN 'alta' THEN 1 WHEN 'media' THEN 2 ELSE 3 END")
            ->orderBy('aberto_em', 'desc')
            ->paginate(15);

        return response()->json($chamados);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'titulo'                   => 'required|string|max:255',
            'descricao'                => 'nullable|string',
            'prioridade'               => 'required|in:baixa,media,alta',
            'responsavel_id'           => 'nullable|exists:users,id',
            'atribuir_automaticamente' => 'boolean',
        ]);

        if ($request->boolean('atribuir_automaticamente')) {
            $responsavel = app(DistribuicaoService::class)->atribuirResponsavel();
            $data['responsavel_id'] = $responsavel?->id;
        }

        unset($data['atribuir_automaticamente']);

        $chamado = Chamado::create($data);

        return response()->json($chamado->load('responsavel'), 201);
    }

    public function show(Chamado $chamado): JsonResponse
    {
        return response()->json($chamado->load('responsavel'));
    }

    public function update(Request $request, Chamado $chamado): JsonResponse
    {
        if ($chamado->status === StatusChamado::Fechado) {
            return response()->json(['message' => 'Chamado fechado não pode ser editado.'], 422);
        }

        $data = $request->validate([
            'titulo'         => 'sometimes|required|string|max:255',
            'descricao'      => 'nullable|string',
            'prioridade'     => 'sometimes|required|in:baixa,media,alta',
            'status'         => 'sometimes|required|in:aberto,em_andamento,resolvido,fechado',
            'responsavel_id' => 'nullable|exists:users,id',
        ]);

        $chamado->update($data);

        return response()->json($chamado->load('responsavel'));
    }

    public function destroy(Chamado $chamado): JsonResponse
    {
        $chamado->delete();

        return response()->json(null, 204);
    }
}
