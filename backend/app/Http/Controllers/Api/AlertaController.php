<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Alerta;

class AlertaController extends Controller
{
    public function index(Request $request)
    {
        $query = Alerta::with('aluno.turma')->orderBy('created_at', 'desc');

        if ($request->has('tipo'))    $query->where('tipo', $request->tipo);
        if ($request->has('mes'))     $query->where('mes_referencia', $request->mes);
        if ($request->has('aluno_id')) $query->where('aluno_id', $request->aluno_id);

        return response()->json($query->get());
    }
}
