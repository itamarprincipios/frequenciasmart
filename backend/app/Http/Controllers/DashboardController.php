<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Frequencia;
use App\Models\Alerta;
use App\Models\Turma;
use App\Models\Aluno;
use Carbon\Carbon;

class DashboardController extends Controller
{
    // ==========================
    // AUTH WEB (via Session)
    // ==========================

    public function loginForm()
    {
        if (session('usuario')) return redirect('/dashboard');
        return view('auth.login');
    }

    public function loginPost(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->where('ativo', true)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return back()->with('error', 'Email ou senha incorretos.');
        }

        session(['usuario' => [
            'id'    => $user->id,
            'nome'  => $user->nome,
            'email' => $user->email,
            'role'  => $user->role,
        ]]);

        // Redireciona por role
        return match($user->role) {
            'ORIENTADORA' => redirect('/orientadora'),
            default       => redirect('/dashboard'),
        };
    }

    public function logout(Request $request)
    {
        $request->session()->flush();
        return redirect('/login');
    }

    // ==========================
    // DASHBOARD DIREÇÃO / VICE
    // ==========================

    public function dashboard()
    {
        $usuario = session('usuario');

        // Segurança: somente DIRETOR e VICE
        if (!in_array($usuario['role'], ['DIRETOR', 'VICE'])) {
            return redirect('/orientadora');
        }

        $mes = now()->format('Y-m');

        // Cards do dashboard
        $totalFaltasMes   = Frequencia::where('status', 'FALTA')
                                ->whereRaw("DATE_FORMAT(data, '%Y-%m') = ?", [$mes])
                                ->count();

        $totalAlertasAtivos = Alerta::where('mes_referencia', $mes)->count();
        $totalAlunos        = Aluno::where('ativo', true)->count();
        $totalTurmas        = Turma::where('ativa', true)->count();

        // Ranking: top 10 alunos com mais faltas no mês
        $rankingFaltas = Frequencia::with('aluno')
            ->where('status', 'FALTA')
            ->whereRaw("DATE_FORMAT(data, '%Y-%m') = ?", [$mes])
            ->selectRaw('aluno_id, COUNT(*) as total')
            ->groupBy('aluno_id')
            ->orderByDesc('total')
            ->limit(10)
            ->get();

        // Faltas por turma (para gráfico)
        $faltasPorTurma = Frequencia::with('turma')
            ->where('status', 'FALTA')
            ->whereRaw("DATE_FORMAT(data, '%Y-%m') = ?", [$mes])
            ->selectRaw('turma_id, COUNT(*) as total')
            ->groupBy('turma_id')
            ->get();

        // Alertas recentes
        $alertasRecentes = Alerta::with('aluno.turma')
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        return view('dashboard.index', compact(
            'usuario', 'totalFaltasMes', 'totalAlertasAtivos',
            'totalAlunos', 'totalTurmas', 'rankingFaltas',
            'faltasPorTurma', 'alertasRecentes', 'mes'
        ));
    }

    // ==========================
    // PAINEL ORIENTADORA
    // ==========================

    public function orientadora()
    {
        $usuario = session('usuario');
        $mes     = request('mes', now()->format('Y-m'));
        $turmaId = request('turma_id');

        $alertas = Alerta::with('aluno.turma')
            ->when($turmaId, fn($q) => $q->whereHas('aluno', fn($a) => $a->where('turma_id', $turmaId)))
            ->where('mes_referencia', $mes)
            ->orderByDesc('created_at')
            ->get();

        $turmas = Turma::where('ativa', true)->orderBy('nome')->get();

        return view('dashboard.orientadora', compact('usuario', 'alertas', 'turmas', 'mes', 'turmaId'));
    }

    // ==========================
    // GESTÃO DE TURMAS
    // ==========================

    public function turmas()
    {
        $usuario = session('usuario');
        $turmas  = Turma::withCount('alunos')->where('ativa', true)->get();
        return view('dashboard.turmas', compact('usuario', 'turmas'));
    }

    public function turmasQrcode($id)
    {
        $usuario = session('usuario');
        $turma   = Turma::findOrFail($id);
        return view('dashboard.turmas.qrcode', compact('usuario', 'turma'));
    }

    // ==========================
    // GESTÃO DE USUÁRIOS
    // ==========================

    public function usuarios()
    {
        $usuario  = session('usuario');
        if ($usuario['role'] !== 'DIRETOR') abort(403);
        $usuarios = User::orderBy('nome')->get();
        return view('dashboard.usuarios', compact('usuario', 'usuarios'));
    }

    // ==========================
    // FREQUÊNCIAS
    // ==========================

    public function frequencias()
    {
        $usuario = session('usuario');
        $turmaId = request('turma_id');
        $data    = request('data', now()->toDateString());

        $frequencias = Frequencia::with(['aluno', 'turma', 'registrador'])
            ->when($turmaId, fn($q) => $q->where('turma_id', $turmaId))
            ->where('data', $data)
            ->orderBy('created_at', 'desc')
            ->get();

        $turmas = Turma::where('ativa', true)->get();

        return view('dashboard.frequencias', compact('usuario', 'frequencias', 'turmas', 'turmaId', 'data'));
    }

    /**
     * Tela de lançamento de frequência via QR Code
     */
    public function frequenciaLancar()
    {
        $usuario = session('usuario');
        $turmas  = Turma::where('ativa', true)->orderBy('nome')->get();

        // Pré-carregar todos os alunos agrupados por turma (para usar no JS)
        $alunosPorTurma = [];
        foreach ($turmas as $turma) {
            $alunosPorTurma[$turma->id] = Aluno::where('turma_id', $turma->id)
                ->where('ativo', true)
                ->orderBy('nome')
                ->get(['id', 'nome', 'matricula', 'qr_token'])
                ->toArray();
        }

        return view('dashboard.frequencia.lancar', compact('usuario', 'turmas', 'alunosPorTurma'));
    }

    /**
     * Processa o formulário de frequência (POST)
     */
    public function frequenciaRegistrar(Request $request)
    {
        $request->validate([
            'turma_id' => 'required|exists:turmas,id',
            'data'     => 'required|date',
        ]);

        $turmaId   = $request->turma_id;
        $data      = $request->data;
        $presentes = $request->input('presentes', []);
        $usuarioId = session('usuario.id');

        // Buscar todos alunos ativos da turma
        $todosAlunos = Aluno::where('turma_id', $turmaId)->where('ativo', true)->pluck('id');

        $alertaService = app(\App\Services\AlertaService::class);

        foreach ($todosAlunos as $alunoId) {
            $status = in_array($alunoId, $presentes) ? 'PRESENTE' : 'FALTA';

            Frequencia::updateOrCreate(
                ['aluno_id' => $alunoId, 'data' => $data],
                [
                    'turma_id'       => $turmaId,
                    'status'         => $status,
                    'registrado_por' => $usuarioId,
                ]
            );

            if ($status === 'FALTA') {
                $alertaService->verificar($alunoId);
            }
        }

        return redirect('/frequencias?turma_id=' . $turmaId . '&data=' . $data)
            ->with('success', 'Frequência registrada com sucesso! ' . count($presentes) . ' presentes, ' . ($todosAlunos->count() - count($presentes)) . ' faltas.');
    }

    // ==========================
    // GESTÃO DE ALUNOS (CRUD)
    // ==========================

    public function alunos()
    {
        $usuario = session('usuario');
        $turmaId = request('turma_id');
        $busca   = request('busca');

        $query = Aluno::with('turma')->where('ativo', true);

        if ($turmaId) $query->where('turma_id', $turmaId);
        if ($busca)   $query->where(function($q) use ($busca) {
            $q->where('nome', 'like', "%$busca%")
              ->orWhere('matricula', 'like', "%$busca%");
        });

        $alunos = $query->orderBy('nome')->get();
        $turmas = Turma::where('ativa', true)->orderBy('nome')->get();

        return view('dashboard.alunos.index', compact('usuario', 'alunos', 'turmas', 'turmaId', 'busca'));
    }

    public function alunosCriar()
    {
        $usuario = session('usuario');
        $turmas  = Turma::where('ativa', true)->orderBy('nome')->get();
        return view('dashboard.alunos.form', compact('usuario', 'turmas'));
    }

    public function alunosStore(Request $request)
    {
        $request->validate([
            'nome'      => 'required|string|max:255',
            'matricula' => 'required|string|max:50|unique:alunos,matricula',
            'turma_id'  => 'required|exists:turmas,id',
        ]);

        Aluno::create([
            'nome'      => $request->nome,
            'matricula' => $request->matricula,
            'turma_id'  => $request->turma_id,
            'qr_token'  => 'ALU_' . strtoupper(\Illuminate\Support\Str::random(10)),
            'ativo'     => true,
        ]);

        return redirect('/alunos')->with('success', 'Aluno cadastrado com sucesso!');
    }

    public function alunosEditar($id)
    {
        $usuario = session('usuario');
        $aluno   = Aluno::findOrFail($id);
        $turmas  = Turma::where('ativa', true)->orderBy('nome')->get();
        return view('dashboard.alunos.form', compact('usuario', 'aluno', 'turmas'));
    }

    public function alunosUpdate(Request $request, $id)
    {
        $aluno = Aluno::findOrFail($id);

        $request->validate([
            'nome'      => 'required|string|max:255',
            'matricula' => 'required|string|max:50|unique:alunos,matricula,' . $id,
            'turma_id'  => 'required|exists:turmas,id',
        ]);

        $aluno->update($request->only(['nome', 'matricula', 'turma_id']));

        return redirect('/alunos')->with('success', 'Aluno atualizado com sucesso!');
    }

    public function alunosDestroy($id)
    {
        $aluno = Aluno::findOrFail($id);
        $aluno->update(['ativo' => false]); // soft-delete por campo ativo
        return redirect('/alunos')->with('success', 'Aluno excluído com sucesso.');
    }

    public function alunosQrcode($id)
    {
        $usuario = session('usuario');
        $aluno   = Aluno::with('turma')->findOrFail($id);
        return view('dashboard.alunos.qrcode', compact('usuario', 'aluno'));
    }
}
