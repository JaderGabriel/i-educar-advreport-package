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
        'route' => route('advanced-reports.age-distortion.index'),
        'cursos' => $cursos,
        'cursoId' => $cursoId ?? null,
        'withCharts' => true,
        'explainTitle' => 'Distorção idade/série',
        'explainText' => 'Indicador de distorção idade/série baseado na idade ideal configurada na série e na idade aproximada (ano - ano de nascimento). Serve para gestão e planejamento pedagógico.',
        'explainDictionary' => 'Idade ideal = campo idade_ideal da série; Idade aproximada = ano - ano_nascimento (não considera mês/dia); Faixa usada: 5 a 17 anos.'
    ])

    @if(!empty($data))
        <div class="advanced-report-card" style="margin-top: 12px;">
            <strong class="advanced-report-card-title">Emissão</strong>
            <p class="advanced-report-card-text">
                Use os filtros e emita em PDF (opcionalmente com gráfico) ou exporte em Excel.
            </p>
            <div style="display:flex;gap:8px;flex-wrap:wrap;align-items:center;">
                <select class="geral js-export-type" style="width: 180px;"
                        data-pdf="{{ route('advanced-reports.age-distortion.pdf', array_merge(request()->all(), ['with_charts' => request('with_charts') ? 1 : 0])) }}"
                        data-excel="{{ route('advanced-reports.age-distortion.excel', request()->all()) }}">
                    <option value="pdf">Gerar PDF</option>
                    <option value="excel">Exportar Excel</option>
                </select>
                <button type="button" class="btn-green js-export-run">Executar</button>
            </div>
        </div>

        <h2 style="margin-top: 16px;">Resumo</h2>
        <table class="tablelistagem" style="width: 100%;" cellspacing="1" cellpadding="4" border="0">
            <tr>
                <td class="formdktd">Indicador</td>
                <td class="formdktd">Valor</td>
            </tr>
            <tr>
                <td class="formmdtd">Total de alunos (recorte)</td>
                <td class="formmdtd">{{ $data['summary']['total_students'] ?? 0 }}</td>
            </tr>
            <tr>
                <td class="formmdtd">Alunos em distorção</td>
                <td class="formmdtd">{{ $data['summary']['distortion_students'] ?? 0 }}</td>
            </tr>
            <tr>
                <td class="formmdtd">% distorção</td>
                <td class="formmdtd">{{ $data['summary']['distortion_pct'] ?? 0 }}%</td>
            </tr>
        </table>

        <h2 style="margin-top: 16px;">Por série</h2>
        <table class="tablelistagem" style="width: 100%;" cellspacing="1" cellpadding="4" border="0">
            <tr>
                <td class="formdktd">Série</td>
                <td class="formdktd">Idade ideal</td>
                <td class="formdktd">Total</td>
                <td class="formdktd">Na idade ideal</td>
                <td class="formdktd">Distorção</td>
                <td class="formdktd">% distorção</td>
            </tr>
            @foreach(($data['grades'] ?? []) as $g)
                <tr>
                    <td class="formmdtd">{{ $g['grade_name'] }}</td>
                    <td class="formmdtd">{{ $g['ideal_age'] }}</td>
                    <td class="formmdtd">{{ $g['total_students'] }}</td>
                    <td class="formmdtd">{{ $g['ideal_count'] }}</td>
                    <td class="formmdtd">{{ $g['distortion_count'] }}</td>
                    <td class="formmdtd">{{ $g['distortion_pct'] }}%</td>
                </tr>
            @endforeach
        </table>
    @endif
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

