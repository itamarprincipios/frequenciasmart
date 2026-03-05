<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckRole
{
    /**
     * Verifica se o usuário autenticado tem um dos roles permitidos.
     *
     * Uso na rota: ->middleware('role:DIRETOR,VICE')
     */
    public function handle(Request $request, Closure $next, string ...$roles): mixed
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['message' => 'Não autenticado.'], 401);
        }

        if (!in_array($user->role, $roles)) {
            return response()->json([
                'message' => 'Acesso negado. Você não tem permissão para este recurso.',
                'seu_role' => $user->role,
                'roles_necessarios' => $roles,
            ], 403);
        }

        return $next($request);
    }
}
