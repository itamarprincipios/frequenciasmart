<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UserController extends Controller
{
    /**
     * Lista todos os usuários (DIRETOR/VICE)
     */
    public function index()
    {
        $users = User::select('id', 'nome', 'email', 'role', 'ativo', 'created_at')
                     ->orderBy('nome')
                     ->get();

        return response()->json($users);
    }

    /**
     * Cria um novo usuário (apenas DIRETOR)
     */
    public function store(Request $request)
    {
        $request->validate([
            'nome'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
            'role'     => 'required|in:ASSISTENTE,ORIENTADORA,DIRETOR,VICE',
        ]);

        $user = User::create([
            'nome'     => $request->nome,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
            'role'     => $request->role,
            'ativo'    => true,
        ]);

        return response()->json([
            'message' => 'Usuário criado com sucesso.',
            'user'    => $user->only(['id', 'nome', 'email', 'role']),
        ], 201);
    }

    /**
     * Atualiza usuário (DIRETOR)
     */
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $request->validate([
            'nome'  => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:users,email,' . $id,
            'role'  => 'sometimes|in:ASSISTENTE,ORIENTADORA,DIRETOR,VICE',
            'ativo' => 'sometimes|boolean',
        ]);

        $user->update($request->only(['nome', 'email', 'role', 'ativo']));

        if ($request->filled('password')) {
            $user->update(['password' => Hash::make($request->password)]);
        }

        return response()->json([
            'message' => 'Usuário atualizado.',
            'user'    => $user->only(['id', 'nome', 'email', 'role', 'ativo']),
        ]);
    }

    /**
     * Remove usuário (apenas DIRETOR – não pode excluir a si mesmo)
     */
    public function destroy(Request $request, $id)
    {
        if ($request->user()->id == $id) {
            return response()->json(['message' => 'Você não pode excluir sua própria conta.'], 422);
        }

        $user = User::findOrFail($id);
        $user->delete();

        return response()->json(['message' => 'Usuário removido com sucesso.']);
    }
}
