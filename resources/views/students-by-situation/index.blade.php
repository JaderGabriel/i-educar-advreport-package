@extends('layout.default')

@section('content')
    @include('advanced-reports::partials.filters', [
        'action' => route('advanced-reports.students-by-situation.index'),
        'year' => $year ?? null,
        'years' => $years ?? [],
        'institutions' => $institutions ?? [],
        'schools' => $schools ?? [],
        'courses' => $courses ?? [],
        'grades' => $grades ?? [],
        'schoolClasses' => $schoolClasses ?? [],
        'institutionId' => $institutionId ?? null,
        'schoolId' => $schoolId ?? null,
        'courseId' => $courseId ?? null,
        'withGrade' => true,
        'withSchoolClass' => true,
        'explainTitle' => 'Alunos por situação',
        'explainText' => 'Lista e consolida alunos por situação de matrícula (cursando, transferido, reclassificado, abandono, falecido, etc.). Use os filtros para restringir por escola/curso/série/turma.',
    ])

    <div class="advanced-report-card" style="margin-top: 12px;">
        <strong class="advanced-report-card-title">Filtro adicional</strong>
        <p class="advanced-report-card-text">Opcionalmente restrinja a consulta por situação.</p>
        <form action="{{ route('advanced-reports.students-by-situation.index') }}" method="get">
            @foreach(request()->except(['situacao']) as $k => $v)
                <input type="hidden" name="{{ $k }}" value="{{ $v }}">
            @endforeach

            <table class="tablecadastro" width="100%" border="0" cellpadding="2" cellspacing="0" role="presentation">
                <tbody>
                <tr>
                    <td class="formmdtd" valign="top"><span class="form">Situação</span></td>
                    <td class="formmdtd" valign="top">
                        <select class="geral" name="situacao" style="width: 320px;">
                            <option value="">Todas</option>
                            @foreach(($situacaoOptions ?? []) as $id => $label)
                                <option value="{{ $id }}" @selected((string) request('situacao') === (string) $id)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </td>
                </tr>
                </tbody>
            </table>

            <div class="ar-actions">
                <div class="ar-actions__group">
                    <a href="{{ route('advanced-reports.students-by-situation.index') }}" class="btn ar-btn ar-btn--ghost">
                        <span class="ar-btn__icon" aria-hidden="true"></span>
                        Limpar
                    </a>
                    <button type="submit" class="btn-green ar-btn ar-btn--primary">
                        <span class="ar-btn__icon" aria-hidden="true"></span>
                        Filtrar
                    </button>
                </div>
            </div>
        </form>
    </div>

    @if(request('ano'))
        <div class="advanced-report-card" style="margin-top: 12px;">
            <strong class="advanced-report-card-title">Emissão</strong>
            <p class="advanced-report-card-text">Gere o documento em PDF (prévia no navegador) ou exporte em Excel.</p>
            <div class="ar-actions">
                <div class="ar-actions__group">
                    <span class="ar-actions__label">Saída</span>
                    <select class="geral ar-select js-export-type" style="width: 210px;"
                            data-pdf="{{ route('advanced-reports.students-by-situation.pdf') . '?' . http_build_query(request()->all()) }}"
                            data-excel="{{ route('advanced-reports.students-by-situation.excel') . '?' . http_build_query(request()->all()) }}">
                        <option value="pdf">PDF (prévia)</option>
                        <option value="excel">Excel</option>
                    </select>
                </div>
                <div class="ar-actions__group">
                    <button type="button" class="btn-green ar-btn ar-btn--secondary js-export-run">
                        <span class="ar-btn__icon" aria-hidden="true"></span>
                        Executar
                    </button>
                </div>
            </div>
        </div>
    @endif

    @if(!empty($data))
        @php($summary = $data['summary'] ?? [])
        @php($rows = $data['rows'] ?? collect())

        <div class="advanced-report-card" style="margin-top: 12px;">
            <strong class="advanced-report-card-title">Resumo</strong>
            <div style="overflow:auto;">
                <table class="tablelistagem" width="100%" cellspacing="1" cellpadding="4" border="0">
                    <tr>
                        <th class="formdktd">Situação</th>
                        <th class="formdktd" style="width: 120px;">Total</th>
                    </tr>
                    @forelse(($situacaoOptions ?? []) as $id => $label)
                        <tr>
                            <td class="formlttd">{{ $label }}</td>
                            <td class="formlttd">{{ (int) ($summary[$id] ?? 0) }}</td>
                        </tr>
                    @empty
                        <tr><td class="formlttd" colspan="2">Sem opções de situação.</td></tr>
                    @endforelse
                </table>
            </div>
        </div>

        <div class="advanced-report-card" style="margin-top: 12px;">
            <strong class="advanced-report-card-title">Alunos</strong>
            <p class="advanced-report-card-text">Lista limitada para melhor performance (use filtros para reduzir).</p>
            <div style="overflow:auto;">
                <table class="tablelistagem" width="100%" cellspacing="1" cellpadding="4" border="0">
                    <tr>
                        <th class="formdktd">Aluno(a)</th>
                        <th class="formdktd" style="width: 110px;">Matrícula</th>
                        <th class="formdktd" style="width: 170px;">Situação</th>
                        <th class="formdktd">Escola</th>
                        <th class="formdktd">Curso</th>
                        <th class="formdktd">Série</th>
                        <th class="formdktd">Turma</th>
                    </tr>
                    @forelse($rows as $r)
                        <tr>
                            <td class="formlttd">{{ $r['aluno'] ?? '' }}</td>
                            <td class="formlttd">{{ $r['matricula_id'] ?? '' }}</td>
                            <td class="formlttd">{{ $r['situacao'] ?? '' }}</td>
                            <td class="formlttd">{{ $r['escola'] ?? '' }}</td>
                            <td class="formlttd">{{ $r['curso'] ?? '' }}</td>
                            <td class="formlttd">{{ $r['serie'] ?? '' }}</td>
                            <td class="formlttd">{{ $r['turma'] ?? '' }}</td>
                        </tr>
                    @empty
                        <tr><td class="formlttd" colspan="7">Nenhum resultado para os filtros.</td></tr>
                    @endforelse
                </table>
            </div>
        </div>
    @endif

    <script>
        (function () {
            const typeSelect = document.querySelector('.js-export-type');
            const runBtn = document.querySelector('.js-export-run');
            if (!typeSelect || !runBtn) return;

            runBtn.addEventListener('click', function () {
                const v = typeSelect.value;
                const url = v === 'excel' ? typeSelect.dataset.excel : typeSelect.dataset.pdf;
                if (!url) return;
                window.open(url, '_blank');
            });
        })();
    </script>
@endsection

