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

    <div class="advanced-report-card" style="margin-top: 12px;">
        <strong class="advanced-report-card-title">Emissão</strong>
        <p class="advanced-report-card-text">
            Use os filtros acima e em seguida emita em PDF ou Excel.
        </p>

        <div style="display:flex;gap:8px;flex-wrap:wrap;">
            <a class="btn-green" target="_blank"
               href="{{ route('advanced-reports.movements.pdf', array_merge(request()->all(), ['data_inicial' => request('data_inicial'), 'data_final' => request('data_final')])) }}">
                Emitir PDF
            </a>
            <a class="btn" target="_blank"
               href="{{ route('advanced-reports.movements.excel', array_merge(request()->all(), ['data_inicial' => request('data_inicial'), 'data_final' => request('data_final')])) }}">
                Exportar Excel
            </a>
        </div>
    </div>
@endsection
