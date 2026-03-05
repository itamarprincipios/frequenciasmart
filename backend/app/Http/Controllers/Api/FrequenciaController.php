<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Frequencia;
use App\Models\Turma;
use App\Models\Aluno;
use App\Services\AlertaService;

class FrequenciaController extends Controller
{
    protected AlertaService $alertaService;

    public function __construct(AlertaService $alertaService)
    {
        $this->alertaService = $alertaService;
    }

    /**
     * Lista frequências com filtros opcionais
     */
    public function index(Request $request)
    {
        $query = Frequencia::with(['aluno', 'turma']);

        if ($request->has('turma_id'))  $query->where('turma_id', $request->turma_id);
        if ($request->has('aluno_id'))  $query->where('aluno_id', $request->aluno_id);
        if ($request->has('data'))      $query->where('data', $request->data);
        if ($request->has('mes')) {
            // Formato esperado: "2026-02"
            $query->whereRaw("DATE_FORMAT(data, '%Y-%m') = ?", [$request->mes]);
        }

        return response()->json($query->orderBy('data', 'desc')->get());
    }

    /**
     * Registra frequência de uma turma (via QR Code)
     */
    public function store(Request $request)
    {
        $request->validate([
            'turma_id'    => 'required|exists:turmas,id',
            'qr_token'    => 'required|string',
            'data'        => 'required|date',
            'frequencias' => 'required|array|min:1',
            'frequencias.*.aluno_id' => 'required|exists:alunos,id',
            'frequencias.*.status'   => 'required|in:PRESENTE,FALTA',
        ]);

        // Valida o QR Code
        $turma = Turma::findOrFail($request->turma_id);
        if ($turma->qr_token !== $request->qr_token) {
            return response()->json(['message' => 'QR Code inválido para esta turma.'], 422);
        }

        $registrados = [];
        $erros = [];

        foreach ($request->frequencias as $item) {
            try {
                $freq = Frequencia::updateOrCreate(
                    ['aluno_id' => $item['aluno_id'], 'data' => $request->data],
                    [
                        'turma_id'       => $request->turma_id,
                        'status'         => $item['status'],
                        'registrado_por' => $request->user()->id,
                    ]
                );
                $registrados[] = $freq;

                // Dispara verificação de alertas assincronamente
                if ($item['status'] === 'FALTA') {
                    $this->alertaService->verificar($item['aluno_id']);
                }
            } catch (\Exception $e) {
                $erros[] = ['aluno_id' => $item['aluno_id'], 'erro' => $e->getMessage()];
            }
        }

        return response()->json([
            'message'    => 'Frequência registrada com sucesso.',
            'registrados' => count($registrados),
            'erros'      => $erros,
        ], 201);
    }

    /**
     * Resumo de faltas por turma no mês
     */
    public function resumo(Request $request)
    {
        $mes = $request->get('mes', now()->format('Y-m'));

        $resumo = Frequencia::with('turma')
            ->whereRaw("DATE_FORMAT(data, '%Y-%m') = ?", [$mes])
            ->where('status', 'FALTA')
            ->selectRaw('turma_id, COUNT(*) as total_faltas')
            ->groupBy('turma_id')
            ->get();

        return response()->json($resumo);
    }
}
