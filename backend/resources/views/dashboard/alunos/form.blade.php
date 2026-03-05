@extends('layouts.app')
@section('titulo', isset($aluno) ? 'Editar Aluno' : 'Novo Aluno')

@section('content')
<div style="max-width:560px">
    <div class="table-wrap">
        <div class="table-head">
            <h3>{{ isset($aluno) ? '✏️ Editar Aluno' : '➕ Cadastrar Novo Aluno' }}</h3>
            <a href="/alunos" class="btn btn-outline" style="font-size:.8rem">← Voltar</a>
        </div>
        <div style="padding:1.5rem 1.25rem">

            @if($errors->any())
            <div class="alert alert-error" style="margin-bottom:1rem">
                <ul style="margin:0;padding-left:1.25rem">
                    @foreach($errors->all() as $e) <li>{{ $e }}</li> @endforeach
                </ul>
            </div>
            @endif

            <form method="POST" action="{{ isset($aluno) ? '/alunos/'.$aluno->id : '/alunos' }}">
                @csrf

                <div class="form-group">
                    <label for="nome">Nome completo *</label>
                    <input type="text" id="nome" name="nome"
                           value="{{ old('nome', $aluno->nome ?? '') }}"
                           class="form-control" placeholder="Ex: João da Silva" required autofocus>
                </div>

                <div class="form-group">
                    <label for="matricula">Matrícula *</label>
                    <input type="text" id="matricula" name="matricula"
                           value="{{ old('matricula', $aluno->matricula ?? '') }}"
                           class="form-control" placeholder="Ex: 2026001" required>
                    <small style="color:#94a3b8;font-size:.75rem">Deve ser única no sistema</small>
                </div>

                <div class="form-group">
                    <label for="turma_id">Turma *</label>
                    <select id="turma_id" name="turma_id" class="form-control" required>
                        <option value="">Selecione a turma...</option>
                        @foreach($turmas as $t)
                        <option value="{{ $t->id }}"
                            {{ old('turma_id', $aluno->turma_id ?? '') == $t->id ? 'selected' : '' }}>
                            {{ $t->nome }} – {{ $t->turno }} ({{ $t->ano_letivo }})
                        </option>
                        @endforeach
                    </select>
                </div>

                @if(isset($aluno))
                <!-- Mostra o QR Code atual ao editar -->
                <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:1rem;margin-bottom:1rem;display:flex;align-items:center;gap:1rem">
                    <img src="{{ $aluno->qrUrl(80) }}" alt="QR Code" style="width:80px;height:80px;border-radius:6px">
                    <div>
                        <div style="font-size:.8rem;font-weight:600;color:#374151">QR Code atual</div>
                        <div style="font-family:monospace;font-size:.7rem;color:#94a3b8;word-break:break-all">{{ $aluno->qr_token }}</div>
                        <a href="/alunos/{{ $aluno->id }}/qrcode" target="_blank"
                           style="font-size:.75rem;color:#4f46e5;text-decoration:none">🖨️ Imprimir</a>
                    </div>
                </div>
                @endif

                <div style="display:flex;gap:.75rem">
                    <button type="submit" class="btn btn-primary">
                        {{ isset($aluno) ? '💾 Salvar alterações' : '✅ Cadastrar aluno' }}
                    </button>
                    <a href="/alunos" class="btn btn-outline">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
