@extends('layouts.app')
@section('titulo', 'Frequências')

@section('content')
<!-- FILTROS -->
<div class="table-wrap" style="margin-bottom:1.5rem">
    <div class="table-head"><h3>🔍 Filtrar Frequências</h3></div>
    <div style="padding:1rem 1.25rem">
        <form method="GET" action="/frequencias" style="display:flex;gap:1rem;flex-wrap:wrap;align-items:flex-end">
            <div class="form-group" style="margin:0;flex:1;min-width:160px">
                <label>Data</label>
                <input type="date" name="data" value="{{ $data }}" class="form-control">
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
            <a href="/frequencias" class="btn btn-outline">Hoje</a>
        </form>
    </div>
</div>

<!-- RESUMO -->
@php
    $presentes = $frequencias->where('status','PRESENTE')->count();
    $faltas    = $frequencias->where('status','FALTA')->count();
    $total     = $frequencias->count();
@endphp
@if($total > 0)
<div class="cards" style="margin-bottom:1.5rem">
    <div class="card green">
        <div class="card-label">Presentes</div>
        <div class="card-value">{{ $presentes }}</div>
        <div class="card-sub">{{ $total > 0 ? round(($presentes/$total)*100) : 0 }}% da turma</div>
    </div>
    <div class="card red">
        <div class="card-label">Faltas</div>
        <div class="card-value">{{ $faltas }}</div>
    </div>
    <div class="card">
        <div class="card-label">Total registrado</div>
        <div class="card-value">{{ $total }}</div>
    </div>
</div>
@endif

<!-- TABELA -->
<div class="table-wrap">
    <div class="table-head">
        <h3>📋 Registros de {{ \Carbon\Carbon::parse($data)->format('d/m/Y') }}</h3>
        <span style="font-size:.8rem;color:#64748b">{{ $total }} registro(s)</span>
    </div>
    <table>
        <thead>
            <tr><th>Aluno</th><th>Turma</th><th>Status</th><th>Registrado por</th><th>Hora</th></tr>
        </thead>
        <tbody>
            @forelse($frequencias as $f)
            <tr>
                <td><strong>{{ $f->aluno->nome ?? '—' }}</strong></td>
                <td>{{ $f->turma->nome ?? '—' }}</td>
                <td>
                    @if($f->status === 'PRESENTE')
                        <span class="badge badge-green">✓ Presente</span>
                    @else
                        <span class="badge badge-red">✗ Falta</span>
                    @endif
                </td>
                <td style="color:#64748b;font-size:.8rem">{{ $f->registrador->nome ?? '—' }}</td>
                <td style="color:#94a3b8;font-size:.8rem">{{ $f->created_at->format('H:i') }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="5" style="text-align:center;color:#94a3b8;padding:2rem">
                    Nenhuma frequência registrada para este período
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
