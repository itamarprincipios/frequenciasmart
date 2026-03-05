<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Turma;
use Illuminate\Support\Str;
// QR Code: composer require simplesoftwareio/simple-qrcode
// use SimpleSoftwareIO\QrCode\Facades\QrCode;

class TurmaController extends Controller
{
    public function index()
    {
        return response()->json(Turma::where('ativa', true)->get());
    }

    public function show($id)
    {
        return response()->json(Turma::with('alunos')->findOrFail($id));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nome'       => 'required|string|max:100',
            'turno'      => 'required|in:MANHA,TARDE,NOITE',
            'ano_letivo' => 'required|integer|min:2020|max:2100',
        ]);

        $turma = Turma::create([
            'nome'       => $request->nome,
            'turno'      => $request->turno,
            'ano_letivo' => $request->ano_letivo,
            'qr_token'   => 'TURMA_' . Str::upper(Str::random(8)) . '_' . $request->ano_letivo,
            'ativa'      => true,
        ]);

        return response()->json(['message' => 'Turma criada.', 'turma' => $turma], 201);
    }

    public function update(Request $request, $id)
    {
        $turma = Turma::findOrFail($id);
        $turma->update($request->only(['nome', 'turno', 'ano_letivo', 'ativa']));
        return response()->json(['message' => 'Turma atualizada.', 'turma' => $turma]);
    }

    public function destroy($id)
    {
        Turma::findOrFail($id)->delete();
        return response()->json(['message' => 'Turma removida.']);
    }

    /**
     * Retorna o QR Code da turma como URL de imagem (Google Charts) e o payload JSON.
     * O app mobile lerá o token e validará contra o backend.
     */
    public function qrcode($id)
    {
        $turma = Turma::findOrFail($id);

        $payload = json_encode([
            'turma_id'   => $turma->id,
            'qr_token'   => $turma->qr_token,
            'ano_letivo' => $turma->ano_letivo,
        ]);

        // URL do QR Code via API do Google Charts (não precisa de biblioteca)
        $qrUrl = 'https://chart.googleapis.com/chart?chs=300x300&cht=qr&chl='
                 . urlencode($payload) . '&choe=UTF-8';

        return response()->json([
            'turma'    => $turma->nome,
            'turno'    => $turma->turno,
            'qr_url'   => $qrUrl,
            'payload'  => json_decode($payload),
        ]);
    }
}
