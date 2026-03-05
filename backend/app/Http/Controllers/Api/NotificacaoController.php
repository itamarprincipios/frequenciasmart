<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Notificacao;

class NotificacaoController extends Controller
{
    public function index(Request $request)
    {
        $notificacoes = Notificacao::where('usuario_id', $request->user()->id)
                                   ->orderBy('created_at', 'desc')
                                   ->get();

        return response()->json($notificacoes);
    }

    public function marcarLida(Request $request, $id)
    {
        $notificacao = Notificacao::where('id', $id)
                                  ->where('usuario_id', $request->user()->id)
                                  ->firstOrFail();

        $notificacao->update(['lida' => true]);

        return response()->json(['message' => 'Notificação marcada como lida.']);
    }
}
