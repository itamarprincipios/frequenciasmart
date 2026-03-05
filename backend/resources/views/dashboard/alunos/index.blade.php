@extends('layouts.app')
@section('titulo', 'Gestão de Alunos')

@section('content')
<!-- FILTROS -->
<div class="table-wrap" style="margin-bottom:1.5rem">
    <div class="table-head"><h3>🔍 Filtros</h3></div>
    <div style="padding:1rem 1.25rem">
        <form method="GET" action="/alunos" style="display:flex;gap:1rem;flex-wrap:wrap;align-items:flex-end">
            <div class="form-group" style="margin:0;flex:2;min-width:200px">
                <label>Buscar aluno</label>
                <input type="text" name="busca" value="{{ $busca }}" class="form-control" placeholder="Nome ou matrícula...">
            </div>
            <div class="form-group" style="margin:0;flex:1;min-width:180px">
                <label>Turma</label>
                <select name="turma_id" class="form-control">
                    <option value="">Todas as turmas</option>
                    @foreach($turmas as $t)
                    <option value="{{ $t->id }}" {{ $turmaId == $t->id ? 'selected' : '' }}>{{ $t->nome }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Filtrar</button>
            <a href="/alunos" class="btn btn-outline">Limpar</a>
        </form>
    </div>
</div>

<!-- TABELA -->
<div class="table-wrap">
    <div class="table-head">
        <h3>🎓 Alunos ({{ $alunos->count() }})</h3>
        <a href="/alunos/criar" class="btn btn-primary">
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M12 4v16m8-8H4"/></svg>
            Novo Aluno
        </a>
    </div>
    <table>
        <thead>
            <tr>
                <th>Nome</th>
                <th>Matrícula</th>
                <th>Turma</th>
                <th>QR Token</th>
                <th style="text-align:center">Ações</th>
            </tr>
        </thead>
        <tbody>
            @forelse($alunos as $aluno)
            <tr>
                <td>
                    <strong>{{ $aluno->nome }}</strong>
                </td>
                <td>
                    <span style="font-family:monospace;font-size:.8rem;color:#475569">{{ $aluno->matricula }}</span>
                </td>
                <td>
                    @if($aluno->turma)
                        <span class="badge badge-blue">{{ $aluno->turma->nome }}</span>
                        <span style="font-size:.7rem;color:#94a3b8;margin-left:.3rem">{{ $aluno->turma->turno }}</span>
                    @else
                        <span class="badge badge-gray">Sem turma</span>
                    @endif
                </td>
                <td>
                    <span style="font-family:monospace;font-size:.7rem;color:#94a3b8">{{ substr($aluno->qr_token, 0, 12) }}…</span>
                </td>
                <td>
                    <div style="display:flex;gap:.5rem;justify-content:center;flex-wrap:wrap">
                        <!-- QR Code -->
                        <a href="/alunos/{{ $aluno->id }}/qrcode" target="_blank"
                           class="btn btn-outline" style="font-size:.75rem;padding:.35rem .7rem"
                           title="Imprimir QR Code">
                            📱 QR
                        </a>
                        <!-- Editar -->
                        <a href="/alunos/{{ $aluno->id }}/editar"
                           class="btn btn-primary" style="font-size:.75rem;padding:.35rem .7rem"
                           title="Editar aluno">
                            ✏️ Editar
                        </a>
                        <!-- Excluir -->
                        <form method="POST" action="/alunos/{{ $aluno->id }}/excluir"
                              onsubmit="return confirm('Excluir {{ $aluno->nome }}?')">
                            @csrf
                            <button type="submit" class="btn btn-danger"
                                    style="font-size:.75rem;padding:.35rem .7rem" title="Excluir">
                                🗑️
                            </button>
                        </form>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="5" style="text-align:center;color:#94a3b8;padding:2rem">
                    Nenhum aluno encontrado. <a href="/alunos/criar" style="color:#4f46e5">+ Cadastrar primeiro aluno</a>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
