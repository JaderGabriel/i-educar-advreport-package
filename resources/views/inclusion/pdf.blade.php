@extends('advanced-reports::pdf.layout')

@section('doc_title', 'Indicadores de Inclusão (NEE)')
@section('doc_subtitle', 'Indicadores de gestão (não substitui laudos/avaliações clínicas)')
@section('doc_year', (string) $ano)

@section('content')
    <h1>Indicadores de Inclusão (NEE) — {{ $ano }}</h1>
    <p class="muted">
        Baseado em cadastros do i-Educar (deficiências em pessoa física, NIS e benefícios).
        Não substitui laudos/avaliações clínicas; serve para gestão e planejamento.
    </p>

    @if(!empty($withCharts) && !empty($charts['overview']))
        <div style="margin:6px 0 10px 0;">
            <img src="{{ $charts['overview'] }}" style="width: 100%; height: auto;" alt="Gráfico visão geral">
        </div>
    @endif

    <h2>Resumo</h2>
    <table>
        <thead>
        <tr>
            <th>Indicador</th>
            <th>Total</th>
        </tr>
        </thead>
        <tbody>
        <tr>
            <td>Total de alunos (recorte)</td>
            <td>{{ $data['summary']['total_students'] ?? 0 }}</td>
        </tr>
        <tr>
            <td>Alunos com deficiência (cadastro)</td>
            <td>{{ $data['summary']['with_disabilities'] ?? 0 }}</td>
        </tr>
        <tr>
            <td>Alunos com NIS</td>
            <td>{{ $data['summary']['with_nis'] ?? 0 }}</td>
        </tr>
        <tr>
            <td>Alunos com benefícios</td>
            <td>{{ $data['summary']['with_benefits'] ?? 0 }}</td>
        </tr>
        </tbody>
    </table>

    <h2>Deficiências (top)</h2>
    @if(!empty($withCharts) && !empty($charts['types']))
        <div style="margin:6px 0 10px 0;">
            <img src="{{ $charts['types'] }}" style="width: 100%; height: auto;" alt="Gráfico deficiências">
        </div>
    @endif
    <table>
        <thead>
        <tr>
            <th>Deficiência</th>
            <th>Total de alunos</th>
        </tr>
        </thead>
        <tbody>
        @foreach(($data['disability_by_type'] ?? []) as $row)
            <tr>
                <td>{{ $row->deficiencia }}</td>
                <td>{{ $row->total }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>

    <h2>Top escolas (recorte)</h2>
    <table>
        <thead>
        <tr>
            <th>Escola</th>
            <th>Total</th>
            <th>Com deficiência</th>
            <th>Com NIS</th>
        </tr>
        </thead>
        <tbody>
        @foreach(($data['by_school'] ?? []) as $row)
            <tr>
                <td>{{ $row->escola }}</td>
                <td>{{ $row->total }}</td>
                <td>{{ $row->com_deficiencia }}</td>
                <td>{{ $row->com_nis }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
@endsection

