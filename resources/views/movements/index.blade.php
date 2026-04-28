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

        <div style="display:flex;gap:8px;flex-wrap:wrap;align-items:center;">
            <select class="geral js-export-type" style="width: 180px;"
                    data-pdf="{{ route('advanced-reports.movements.pdf', array_merge(request()->all(), ['data_inicial' => request('data_inicial'), 'data_final' => request('data_final')])) }}"
                    data-excel="{{ route('advanced-reports.movements.excel', array_merge(request()->all(), ['data_inicial' => request('data_inicial'), 'data_final' => request('data_final')])) }}">
                <option value="pdf">Gerar PDF</option>
                <option value="excel">Exportar Excel</option>
            </select>
            <button type="button" class="btn-green js-export-run">Executar</button>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        (function () {
            const select = document.querySelector('.js-export-type');
            const btn = document.querySelector('.js-export-run');
            if (!select || !btn) return;

            btn.addEventListener('click', function () {
                const key = select.value === 'excel' ? 'excel' : 'pdf';
                const url = key === 'excel' ? select.dataset.excel : select.dataset.pdf;
                if (url) window.open(url, '_blank');
            });
        })();
    </script>
@endpush
