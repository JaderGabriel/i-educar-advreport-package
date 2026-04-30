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
        'route' => route('advanced-reports.movements.index'),
        'cursos' => $cursos,
        'cursoId' => $cursoId ?? null,
        'withDates' => true,
        'withCharts' => false,
        'explainTitle' => 'Relatório de Movimentações',
        'explainText' => 'Este relatório permitirá analisar movimentações de matrícula (entradas, saídas, transferências) por ano, instituição, escola e curso.',
        'explainDictionary' => 'Movimentações = alterações de vínculo de matrícula; Escola = unidade escolar de oferta; Curso = etapa/modalidade do curso.'
    ])

    @include('advanced-reports::partials._post-filter-export-bar', [
        'uid' => 'movements',
        'heading' => 'Movimentações de matrícula',
        'pdfRoute' => route('advanced-reports.movements.pdf'),
        'excelRoute' => route('advanced-reports.movements.excel'),
        'requiredFields' => ['ano', 'ref_cod_instituicao', 'data_inicial', 'data_final'],
        'requiredFieldMessages' => [
            'ano' => 'Informe o ano letivo antes de exportar.',
            'ref_cod_instituicao' => 'Informe a instituição antes de exportar.',
            'data_inicial' => 'Informe a data inicial do período.',
            'data_final' => 'Informe a data final do período.',
        ],
        'cardTitle' => 'Exportar relatório',
        'cardText' => 'Informe ano, instituição e o período (datas) nos filtros acima. Em seguida gere o PDF ou exporte em Excel.',
    ])
@endsection
