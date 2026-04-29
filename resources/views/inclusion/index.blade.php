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
        'route' => route('advanced-reports.inclusion.index'),
        'cursos' => $cursos,
        'cursoId' => $cursoId ?? null,
        'withCharts' => true,
        'explainTitle' => 'Indicadores de Inclusão (NEE)',
        'explainText' => 'Este relatório consolida indicadores de inclusão com base em cadastros disponíveis no i-Educar: deficiências (cadastro físico), NIS e benefícios. É um recorte de gestão (não substitui laudos ou avaliações clínicas).',
        'explainDictionary' => 'Deficiências = cadastro em pessoa física; NIS = identificador social quando informado; Benefícios = benefícios/programas associados ao aluno.'
    ])

    @if(!empty($data))
        @include('advanced-reports::partials._post-filter-export-bar', [
            'uid' => 'inclusion',
            'heading' => 'Indicadores de inclusão',
            'pdfRoute' => route('advanced-reports.inclusion.pdf'),
            'excelRoute' => route('advanced-reports.inclusion.excel'),
            'cardTitle' => 'Exportar relatório',
            'cardText' => 'Os totais abaixo refletem os filtros aplicados. Gere o PDF (use “Incluir gráficos” nos filtros, se desejar) ou exporte em Excel.',
        ])

        <h2 style="margin-top: 16px;">Resumo</h2>
        <table class="tablelistagem" style="width: 100%;" cellspacing="1" cellpadding="4" border="0">
            <tr>
                <td class="formdktd">Indicador</td>
                <td class="formdktd">Total</td>
            </tr>
            <tr>
                <td class="formmdtd">Total de alunos (recorte)</td>
                <td class="formmdtd">{{ $data['summary']['total_students'] ?? 0 }}</td>
            </tr>
            <tr>
                <td class="formmdtd">Alunos com deficiência (cadastro)</td>
                <td class="formmdtd">{{ $data['summary']['with_disabilities'] ?? 0 }}</td>
            </tr>
            <tr>
                <td class="formmdtd">Alunos com NIS</td>
                <td class="formmdtd">{{ $data['summary']['with_nis'] ?? 0 }}</td>
            </tr>
            <tr>
                <td class="formmdtd">Alunos com benefícios</td>
                <td class="formmdtd">{{ $data['summary']['with_benefits'] ?? 0 }}</td>
            </tr>
        </table>
    @endif
@endsection

