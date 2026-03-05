@extends('layouts.app')
@section('titulo', 'Alertas de Frequência')

@section('content')
<!-- FILTROS -->
<div class="table-wrap" style="margin-bottom:1.5rem">
    <div class="table-head"><h3>🔍 Filtros</h3></div>
    <div style="padding:1rem 1.25rem">
        <form method="GET" action="/orientadora" style="display:flex;gap:1rem;flex-wrap:wrap;align-items:flex-end">
            <div class="form-group" style="margin:0;flex:1;min-width:160px">
                <label>Mês</label>
                <input type="month" name="mes" value="{{ $mes }}" class="form-control">
            </div>
            <div class="form-group" style="margin:0;flex:1;min-width:160px">
                <label>Turma</label>
                <select name="turma_id" class="form-control">
                    <option value="">Todas</option>
                    @foreach($turmas as $t)
                    <option value="{{ $t->id }}" {{ $turmaId == $t->id ? 'selected' : '' }}>{{ $t->nome }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Filtrar</button>
            <a href="/orientadora" class="btn btn-outline">Limpar</a>
        </form>
    </div>
</div>

<!-- CARDS RESUMO -->
<div class="cards" style="margin-bottom:1.5rem">
    <div class="card red">
        <div class="card-label">Alertas no período</div>
        <div class="card-value">{{ $alertas->count() }}</div>
    </div>
    <div class="card yellow">
        <div class="card-label">Faltas consecutivas</div>
        <div class="card-value">{{ $alertas->where('tipo','CONSECUTIVA')->count() }}</div>
    </div>
    <div class="card">
        <div class="card-label">Faltas mensais (10+)</div>
        <div class="card-value">{{ $alertas->where('tipo','INTERCALADA')->count() }}</div>
    </div>
</div>

<!-- TABELA ALERTAS -->
<div class="table-wrap">
    <div class="table-head">
        <h3>🔔 Lista de Alertas</h3>
        <span style="font-size:.8rem;color:#64748b">{{ $alertas->count() }} resultado(s)</span>
    </div>
    <table>
        <thead>
            <tr><th>Aluno</th><th>Matrícula</th><th>Turma</th><th>Tipo de Alerta</th><th>Mês</th><th>Gerado em</th></tr>
        </thead>
        <tbody>
            @forelse($alertas as $alerta)
            <tr>
                <td><strong>{{ $alerta->aluno->nome ?? '—' }}</strong></td>
                <td style="color:#64748b;font-size:.8rem">{{ $alerta->aluno->matricula ?? '—' }}</td>
                <td>{{ $alerta->aluno->turma->nome ?? '—' }}</td>
                <td>
                    @if($alerta->tipo === 'CONSECUTIVA')
                        <span class="badge badge-red">⚠️ 3 Consecutivas</span>
                    @else
                        <span class="badge badge-yellow">📊 10 Mensais</span>
                    @endif
                </td>
                <td>{{ $alerta->mes_referencia }}</td>
                <td style="color:#64748b;font-size:.8rem">{{ $alerta->created_at->format('d/m/Y H:i') }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="6" style="text-align:center;color:#94a3b8;padding:2rem">
                    ✅ Nenhum alerta para este período
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
