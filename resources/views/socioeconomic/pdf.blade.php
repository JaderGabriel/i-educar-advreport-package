@extends('advanced-reports::pdf.layout')

@section('doc_title', 'Relatório Socioeconômico')
@section('doc_subtitle', 'Distribuições: raça/cor • gênero • benefícios • alunos por escola')
@section('doc_year', (string) $ano)
@section('formal_header', '1')

@section('content')
    @php($instituicoesCol = collect($instituicoes ?? []))
    @php($escolasCol = collect($escolas ?? []))
    @php($cursosCol = collect($cursos ?? []))
    @php($instRow = ($instituicaoId ?? null) ? $instituicoesCol->firstWhere('cod_instituicao', $instituicaoId) : null)
    @php($escRow = ($escolaId ?? null) ? $escolasCol->firstWhere('cod_escola', $escolaId) : null)
    @php($curRow = ($cursoId ?? null) ? $cursosCol->firstWhere('cod_curso', $cursoId) : null)

    <h1>Relatório Socioeconômico — {{ $ano }}</h1>

    <div class="box">
        <strong>Filtros do recorte</strong>
        <table style="margin-top: 6px; font-size: 9px;">
            <tr><th style="width: 28%;">Ano letivo</th><td>{{ $ano }}</td></tr>
            <tr><th>Instituição</th><td>{{ $instRow ? $instRow->nm_instituicao : (($instituicaoId ?? null) ? ('ID ' . $instituicaoId) : 'Todas') }}</td></tr>
            <tr><th>Escola</th><td>{{ $escRow ? $escRow->nome : (($escolaId ?? null) ? ('ID ' . $escolaId) : 'Todas') }}</td></tr>
            <tr><th>Curso</th><td>{{ $curRow ? $curRow->nm_curso : (($cursoId ?? null) ? ('ID ' . $cursoId) : 'Todos') }}</td></tr>
            <tr><th>Incluir gráficos</th><td>{{ !empty($withCharts) ? 'Sim' : 'Não' }}</td></tr>
        </table>
    </div>

    <p class="muted" style="font-size:9px;">Legenda: Raça/Cor e gênero conforme cadastro físico; benefícios conforme programas sociais no aluno; alunos por escola = concentração no recorte.</p>


    <h2>Distribuição por raça/cor</h2>
    @if(!empty($withCharts) && !empty($charts['race']))
        <div style="margin:6px 0 10px 0;">
            <img src="{{ $charts['race'] }}" style="width: 100%; height: auto;" alt="Gráfico raça/cor">
        </div>
    @endif
    <table>
        <thead>
        <tr>
            <th>Raça/Cor</th>
            <th>Total de alunos</th>
        </tr>
        </thead>
        <tbody>
        @foreach($data['race'] as $row)
            <tr>
                <td>{{ $row->raca_label ?? ($row->raca ?? 'Não informada') }}</td>
                <td>{{ $row->total }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>

    <h2>Distribuição por gênero</h2>
    @if(!empty($withCharts) && !empty($charts['gender']))
        <div style="margin:6px 0 10px 0;">
            <img src="{{ $charts['gender'] }}" style="width: 100%; height: auto;" alt="Gráfico gênero">
        </div>
    @endif
    <table>
        <thead>
        <tr>
            <th>Gênero</th>
            <th>Total de alunos</th>
        </tr>
        </thead>
        <tbody>
        @foreach($data['gender'] as $row)
            <tr>
                <td>{{ $row->sexo_label ?? ($row->sexo ?? 'Não informado') }}</td>
                <td>{{ $row->total }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>

    <h2>Benefícios / Programas sociais</h2>
    @if(!empty($withCharts) && !empty($charts['benefits']))
        <div style="margin:6px 0 10px 0;">
            <img src="{{ $charts['benefits'] }}" style="width: 100%; height: auto;" alt="Gráfico benefícios">
        </div>
    @endif
    <table>
        <thead>
        <tr>
            <th>Benefício</th>
            <th>Total de alunos</th>
        </tr>
        </thead>
        <tbody>
        @foreach($data['benefits'] as $row)
            <tr>
                <td>{{ $row->beneficio }}</td>
                <td>{{ $row->total }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>

    <h2>Alunos por escola</h2>
    @if(!empty($withCharts) && !empty($charts['schools']))
        <div style="margin:6px 0 10px 0;">
            <img src="{{ $charts['schools'] }}" style="width: 100%; height: auto;" alt="Gráfico escolas">
        </div>
    @endif
    <table>
        <thead>
        <tr>
            <th>Escola</th>
            <th>Total de alunos</th>
        </tr>
        </thead>
        <tbody>
        @foreach($data['schools'] as $row)
            <tr>
                <td>{{ $row->nome }}</td>
                <td>{{ $row->total }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>

  @include('advanced-reports::student-documents._footer', [
    'issuedAt' => $issuedAt ?? now()->format('d/m/Y H:i'),
    'validationCode' => $validationCode ?? '',
    'validationUrl' => $validationUrl ?? '',
    'qrDataUri' => $qrDataUri ?? '',
    'issuerName' => $issuerName ?? null,
    'issuerRole' => $issuerRole ?? null,
    'cityUf' => $cityUf ?? null,
    'book' => null,
    'page' => null,
    'record' => null,
    'matriculaInternaAluno' => null,
    'footerInline' => true,
  ])
@endsection
