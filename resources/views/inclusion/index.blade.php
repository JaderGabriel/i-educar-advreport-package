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
        <div class="advanced-report-card" style="margin-top: 12px;">
            <strong class="advanced-report-card-title">Emissão</strong>
            <p class="advanced-report-card-text">
                Use os filtros e em seguida emita em PDF (opcionalmente com gráficos) ou exporte em Excel.
            </p>

            <div style="display:flex;gap:8px;flex-wrap:wrap;">
                <a class="btn-green" target="_blank"
                   href="{{ route('advanced-reports.inclusion.pdf', array_merge(request()->all(), ['with_charts' => request('with_charts') ? 1 : 0])) }}">
                    Emitir PDF
                </a>
                <a class="btn" target="_blank"
                   href="{{ route('advanced-reports.inclusion.excel', request()->all()) }}">
                    Exportar Excel
                </a>
            </div>
        </div>

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

