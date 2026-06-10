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
    /**
     * Lista chamados com filtros opcionais e paginação.
     *
     * Query params aceitos:
     *   ?status=aberto
     *   ?prioridade=alta
     *   ?responsavel_id=2
     *   ?busca=impressora
     *
     * Ordenação padrão: prioridade (alta→media→baixa) e depois data de abertura (mais recente).
     */
    public function index(Request $request): JsonResponse
    {
        $chamados = Chamado::with('responsavel')
            // cada `when` só aplica o filtro se o parâmetro vier preenchido na request
            ->when($request->status,         fn($q) => $q->where('status', $request->status))
            ->when($request->prioridade,     fn($q) => $q->where('prioridade', $request->prioridade))
            ->when($request->responsavel_id, fn($q) => $q->where('responsavel_id', $request->responsavel_id))
            ->when($request->busca,          fn($q) => $q->where('titulo', 'like', "%{$request->busca}%"))
            // ordena por prioridade de forma semântica (não alfabética)
            ->orderByRaw("CASE prioridade WHEN 'alta' THEN 1 WHEN 'media' THEN 2 ELSE 3 END")
            ->orderBy('aberto_em', 'desc')
            ->paginate(15);

        return response()->json($chamados);
    }

    /**
     * Cria um novo chamado.
     *
     * Se `atribuir_automaticamente` for true, ignora `responsavel_id` e
     * delega a escolha ao DistribuicaoService.
     */
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
            // o service encapsula os critérios de escolha (menor carga, desempate por id)
            $responsavel = app(DistribuicaoService::class)->atribuirResponsavel();
            $data['responsavel_id'] = $responsavel?->id;
        }

        // remove o campo auxiliar antes de persistir (não existe na tabela)
        unset($data['atribuir_automaticamente']);

        $chamado = Chamado::create($data);

        // retorna 201 Created com o chamado completo e responsável carregado
        return response()->json($chamado->load('responsavel'), 201);
    }

    /**
     * Retorna os detalhes de um chamado específico.
     * O Laravel resolve automaticamente o modelo pelo ID na rota (Route Model Binding).
     */
    public function show(Chamado $chamado): JsonResponse
    {
        return response()->json($chamado->load('responsavel'));
    }

    /**
     * Atualiza um chamado existente.
     *
     * Regra: chamados com status `fechado` são imutáveis.
     * `sometimes` nas validações permite atualização parcial (PATCH-style via PUT).
     */
    public function update(Request $request, Chamado $chamado): JsonResponse
    {
        // chamado fechado não pode ser editado — retorna 422 com mensagem clara
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

    /**
     * Remove um chamado do banco de dados.
     * Retorna 204 No Content (sem corpo na resposta) por convenção REST.
     */
    public function destroy(Chamado $chamado): JsonResponse
    {
        $chamado->delete();

        return response()->json(null, 204);
    }
}
