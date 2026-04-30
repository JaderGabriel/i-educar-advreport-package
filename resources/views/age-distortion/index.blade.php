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
        'requireCourse' => true,
        'explainTitle' => 'Distorção idade/série',
        'explainText' => 'Indicador de distorção idade/série baseado na idade ideal configurada na série e na idade aproximada (ano - ano de nascimento). Serve para gestão e planejamento pedagógico.',
        'explainDictionary' => 'Idade ideal = campo idade_ideal da série; Idade aproximada = ano - ano_nascimento (não considera mês/dia); Faixa usada: 5 a 17 anos.'
    ])

    @if(!empty($data))
        @include('advanced-reports::partials._post-filter-export-bar', [
            'uid' => 'age-distortion',
            'heading' => 'Distorção idade/série',
            'pdfRoute' => route('advanced-reports.age-distortion.pdf'),
            'excelRoute' => route('advanced-reports.age-distortion.excel'),
            'requiredFields' => ['ano', 'ref_cod_instituicao', 'ref_cod_escola', 'ref_cod_curso'],
            'requiredFieldMessages' => [
                'ano' => 'Informe o ano letivo antes de exportar.',
                'ref_cod_instituicao' => 'Informe a instituição antes de exportar.',
                'ref_cod_escola' => 'Informe a escola antes de exportar.',
                'ref_cod_curso' => 'Informe o curso (obrigatório neste relatório) antes de exportar.',
            ],
            'cardTitle' => 'Exportar relatório',
            'cardText' => 'Os dados abaixo refletem os filtros aplicados. Gere o PDF ou exporte em Excel.',
        ])

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

