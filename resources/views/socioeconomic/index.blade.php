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
        @include('advanced-reports::partials._post-filter-export-bar', [
            'uid' => 'socioeconomic',
            'heading' => 'Resumo socioeconômico',
            'pdfRoute' => route('advanced-reports.socioeconomic.pdf'),
            'excelRoute' => route('advanced-reports.socioeconomic.excel'),
            'cardTitle' => 'Exportar relatório',
            'cardText' => 'Os dados abaixo refletem os filtros aplicados. Gere o PDF (marque “Incluir gráficos” nos filtros, se desejar) ou exporte em Excel.',
        ])
    @endif
@endsection
