<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Aluno;

class AlunoController extends Controller
{
    public function index(Request $request)
    {
        $query = Aluno::with('turma')->where('ativo', true);

        if ($request->has('turma_id')) {
            $query->where('turma_id', $request->turma_id);
        }

        return response()->json($query->orderBy('nome')->get());
    }

    public function show($id)
    {
        return response()->json(Aluno::with(['turma', 'frequencias'])->findOrFail($id));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nome'      => 'required|string|max:255',
            'matricula' => 'required|string|unique:alunos,matricula',
            'turma_id'  => 'required|exists:turmas,id',
        ]);

        $aluno = Aluno::create([
            'nome'      => $request->nome,
            'matricula' => $request->matricula,
            'turma_id'  => $request->turma_id,
            'ativo'     => true,
        ]);

        return response()->json(['message' => 'Aluno criado.', 'aluno' => $aluno], 201);
    }

    public function update(Request $request, $id)
    {
        $aluno = Aluno::findOrFail($id);

        $request->validate([
            'nome'      => 'sometimes|string|max:255',
            'matricula' => 'sometimes|string|unique:alunos,matricula,' . $id,
            'turma_id'  => 'sometimes|exists:turmas,id',
            'ativo'     => 'sometimes|boolean',
        ]);

        $aluno->update($request->only(['nome', 'matricula', 'turma_id', 'ativo']));

        return response()->json(['message' => 'Aluno atualizado.', 'aluno' => $aluno]);
    }

    public function destroy($id)
    {
        Aluno::findOrFail($id)->delete();
        return response()->json(['message' => 'Aluno removido.']);
    }
}
