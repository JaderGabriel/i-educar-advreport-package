@extends('layout.default')

@push('styles')
    @if (class_exists('Asset'))
        <link rel="stylesheet" type="text/css" href="{{ Asset::get('css/ieducar.css') }}"/>
        <link rel="stylesheet" type="text/css" href="{{ Asset::get('css/advanced-reports.css') }}"/>
    @else
        <link rel="stylesheet" type="text/css" href="{{ asset('css/advanced-reports.css') }}"/>
    @endif
@endpush

@section('content')
    @include('advanced-reports::partials.filters', [
        'route' => route('advanced-reports.socioeconomic.index'),
        'cursos' => $cursos,
        'cursoId' => $cursoId ?? null,
        'withCharts' => true,
        'explainTitle' => 'Sobre este relatório socioeconômico',
        'explainText' => 'Este relatório consolida dados de raça/cor, gênero, benefícios/programas sociais e distribuição de alunos por escola. Os filtros controlam o recorte da análise (rede inteira, por instituição, por escola ou curso).',
        'explainDictionary' => 'Raça/Cor = informação do cadastro físico; Benefícios = programas sociais cadastrados no aluno; Gênero = sexo biológico informado; Escola = unidade escolar de oferta; Curso = etapa/modalidade do curso.'
    ])

    @if(!empty($data))
        <h2>Resumo socioeconômico</h2>
        <div class="advanced-report-card" style="margin-top: 12px;">
            <strong class="advanced-report-card-title">Emissão</strong>
            <p class="advanced-report-card-text">
                Use os filtros e em seguida emita em PDF (opcionalmente com gráficos) ou exporte em Excel.
            </p>

            <div style="display:flex;gap:8px;flex-wrap:wrap;">
                <a class="btn-green" target="_blank"
                   href="{{ route('advanced-reports.socioeconomic.pdf', array_merge(request()->all(), ['with_charts' => request('with_charts') ? 1 : 0])) }}">
                    Emitir PDF
                </a>
                <a class="btn" target="_blank"
                   href="{{ route('advanced-reports.socioeconomic.excel', request()->all()) }}">
                    Exportar Excel
                </a>
            </div>
        </div>
    @endif
@endsection
