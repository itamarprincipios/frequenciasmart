@extends('layouts.app')
@section('titulo', 'Dashboard – Direção')

@section('content')
<!-- CARDS -->
<div class="cards">
    <div class="card red">
        <div class="card-label">Faltas no Mês</div>
        <div class="card-value">{{ $totalFaltasMes }}</div>
        <div class="card-sub">{{ now()->format('F Y') }}</div>
    </div>
    <div class="card yellow">
        <div class="card-label">Alertas Ativos</div>
        <div class="card-value">{{ $totalAlertasAtivos }}</div>
        <div class="card-sub">Este mês</div>
    </div>
    <div class="card green">
        <div class="card-label">Total de Alunos</div>
        <div class="card-value">{{ $totalAlunos }}</div>
        <div class="card-sub">Ativos</div>
    </div>
    <div class="card">
        <div class="card-label">Turmas</div>
        <div class="card-value">{{ $totalTurmas }}</div>
        <div class="card-sub">Ativas em {{ now()->year }}</div>
    </div>
</div>

<!-- GRID -->
<div class="grid-2">

    <!-- RANKING FALTAS -->
    <div class="table-wrap">
        <div class="table-head">
            <h3>🏆 Ranking de Faltas no Mês</h3>
        </div>
        <table>
            <thead>
                <tr><th>#</th><th>Aluno</th><th>Turma</th><th>Faltas</th></tr>
            </thead>
            <tbody>
                @forelse($rankingFaltas as $i => $item)
                <tr>
                    <td>{{ $i + 1 }}º</td>
                    <td>{{ $item->aluno->nome ?? '—' }}</td>
                    <td>{{ $item->aluno->turma->nome ?? '—' }}</td>
                    <td><span class="badge badge-red">{{ $item->total }}</span></td>
                </tr>
                @empty
                <tr><td colspan="4" style="text-align:center;color:#94a3b8;padding:1.5rem">Nenhuma falta registrada</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- GRÁFICO POR TURMA -->
    <div class="chart-wrap">
        <h3>📊 Faltas por Turma</h3>
        <canvas id="chartTurmas" height="200"></canvas>
    </div>

</div>

<!-- ALERTAS RECENTES -->
<div class="table-wrap">
    <div class="table-head">
        <h3>🔔 Alertas Recentes</h3>
        <a href="/orientadora" class="btn btn-outline" style="font-size:.75rem">Ver todos</a>
    </div>
    <table>
        <thead>
            <tr><th>Aluno</th><th>Turma</th><th>Tipo</th><th>Mês</th><th>Data</th></tr>
        </thead>
        <tbody>
            @forelse($alertasRecentes as $alerta)
            <tr>
                <td>{{ $alerta->aluno->nome ?? '—' }}</td>
                <td>{{ $alerta->aluno->turma->nome ?? '—' }}</td>
                <td>
                    @if($alerta->tipo === 'CONSECUTIVA')
                        <span class="badge badge-red">3 consecutivas</span>
                    @else
                        <span class="badge badge-yellow">10 mensais</span>
                    @endif
                </td>
                <td>{{ $alerta->mes_referencia }}</td>
                <td>{{ $alerta->created_at->format('d/m H:i') }}</td>
            </tr>
            @empty
            <tr><td colspan="5" style="text-align:center;color:#94a3b8;padding:1.5rem">Nenhum alerta gerado</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<script>
const labels  = @json($faltasPorTurma->map(fn($f) => $f->turma->nome ?? 'Sem nome'));
const valores = @json($faltasPorTurma->pluck('total'));
new Chart(document.getElementById('chartTurmas'), {
    type: 'bar',
    data: {
        labels,
        datasets: [{
            label: 'Faltas',
            data: valores,
            backgroundColor: '#6366f1',
            borderRadius: 6,
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { display: false } },
        scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } }
    }
});
</script>
@endsection
