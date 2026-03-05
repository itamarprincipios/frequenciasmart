@extends('layouts.app')
@section('titulo', 'Usuários')

@section('content')
<div class="table-wrap">
    <div class="table-head">
        <h3>👥 Usuários do Sistema</h3>
    </div>
    <table>
        <thead>
            <tr><th>Nome</th><th>E-mail</th><th>Perfil</th><th>Status</th><th>Cadastrado</th></tr>
        </thead>
        <tbody>
            @forelse($usuarios as $u)
            <tr>
                <td><strong>{{ $u->nome }}</strong></td>
                <td style="color:#64748b;font-size:.8rem">{{ $u->email }}</td>
                <td>
                    @php
                    $badge = match($u->role) {
                        'DIRETOR'    => 'badge-blue',
                        'VICE'       => 'badge-blue',
                        'ORIENTADORA'=> 'badge-green',
                        default      => 'badge-gray',
                    };
                    @endphp
                    <span class="badge {{ $badge }}">{{ $u->role }}</span>
                </td>
                <td>
                    @if($u->ativo)
                        <span class="badge badge-green">● Ativo</span>
                    @else
                        <span class="badge badge-red">● Inativo</span>
                    @endif
                </td>
                <td style="color:#94a3b8;font-size:.8rem">{{ $u->created_at->format('d/m/Y') }}</td>
            </tr>
            @empty
            <tr><td colspan="5" style="text-align:center;color:#94a3b8;padding:1.5rem">Nenhum usuário</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
