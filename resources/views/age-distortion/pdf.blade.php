@extends('advanced-reports::pdf.layout')

@section('doc_title', 'Distorção idade/série')
@section('doc_subtitle', 'Metodologia: ano - ano_nascimento (aprox.) • Idade ideal por série')
@section('doc_year', (string) $ano)

@section('content')
    <h1>Distorção idade/série — {{ $ano }}</h1>
    <p class="muted">
        Idade aproximada = ano - ano_nascimento (não considera mês/dia). Faixa considerada: 5 a 17 anos.
        “Idade ideal” vem do campo <code>pmieducar.serie.idade_ideal</code>.
    </p>

    @if(!empty($withCharts) && !empty($charts['distortion_pct_by_grade']))
        <div style="margin:6px 0 10px 0;">
            <img src="{{ $charts['distortion_pct_by_grade'] }}" style="width: 100%; height: auto;" alt="Gráfico distorção por série">
        </div>
    @endif

    <h2>Resumo</h2>
    <table>
        <tr><th>Indicador</th><th>Valor</th></tr>
        <tr><td>Total de alunos (recorte)</td><td>{{ $data['summary']['total_students'] ?? 0 }}</td></tr>
        <tr><td>Alunos em distorção</td><td>{{ $data['summary']['distortion_students'] ?? 0 }}</td></tr>
        <tr><td>% distorção</td><td>{{ $data['summary']['distortion_pct'] ?? 0 }}%</td></tr>
    </table>

    <h2>Por série</h2>
    <table>
        <thead>
        <tr>
            <th>Série</th>
            <th>Idade ideal</th>
            <th>Total</th>
            <th>Na idade ideal</th>
            <th>Distorção</th>
            <th>% distorção</th>
        </tr>
        </thead>
        <tbody>
        @foreach(($data['grades'] ?? []) as $g)
            <tr>
                <td>{{ $g['grade_name'] }}</td>
                <td>{{ $g['ideal_age'] }}</td>
                <td>{{ $g['total_students'] }}</td>
                <td>{{ $g['ideal_count'] }}</td>
                <td>{{ $g['distortion_count'] }}</td>
                <td>{{ $g['distortion_pct'] }}%</td>
            </tr>
        @endforeach
        </tbody>
    </table>
@endsection

