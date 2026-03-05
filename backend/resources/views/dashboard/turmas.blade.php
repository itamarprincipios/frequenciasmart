@extends('layouts.app')
@section('titulo', 'Turmas')

@section('content')
<div class="table-wrap">
    <div class="table-head">
        <h3>🏫 Turmas Ativas</h3>
    </div>
    <table>
        <thead>
            <tr><th>Nome</th><th>Turno</th><th>Ano Letivo</th><th>Alunos</th><th>QR Code</th></tr>
        </thead>
        <tbody>
            @forelse($turmas as $turma)
            <tr>
                <td><strong>{{ $turma->nome }}</strong></td>
                <td>
                    @if($turma->turno === 'MANHA')
                        <span class="badge badge-blue">☀️ Manhã</span>
                    @elseif($turma->turno === 'TARDE')
                        <span class="badge badge-yellow">🌤️ Tarde</span>
                    @else
                        <span class="badge badge-gray">🌙 Noite</span>
                    @endif
                </td>
                <td>{{ $turma->ano_letivo }}</td>
                <td><span class="badge badge-green">{{ $turma->alunos_count }} alunos</span></td>
                <td>
                    <a href="/turmas/{{ $turma->id }}/qrcode" target="_blank" class="btn btn-outline" style="font-size:.75rem">
                        📱 Ver QR
                    </a>
                </td>
            </tr>
            @empty
            <tr><td colspan="5" style="text-align:center;color:#94a3b8;padding:1.5rem">Nenhuma turma cadastrada</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
