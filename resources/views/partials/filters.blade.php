<div class="advanced-report-card">
    <strong class="advanced-report-card-title">{{ $explainTitle ?? 'Sobre este relatório' }}</strong>
    <p class="advanced-report-card-text">
        {{ $explainText ?? 'Os filtros abaixo permitem restringir os dados por ano letivo, instituição, escola e curso.' }}
    </p>
    @if(!empty($explainDictionary))
        <p class="advanced-report-card-text">
            <strong>Dicionário de termos (resumo):</strong> {!! $explainDictionary !!}
        </p>
    @endif
</div>

<form id="formcadastro" action="{{ $route }}" method="get">
    <table class="tablecadastro" width="100%" border="0" cellpadding="2" cellspacing="0" role="presentation">
        <tbody>
        <tr id="tr_nm_ano">
            <td class="formmdtd" valign="top">
                <span class="form">Ano</span>
                <span class="campo_obrigatorio">*</span>
            </td>
            <td class="formmdtd" valign="top">
                <select class="geral obrigatorio" name="ano" id="ano" style="width: 80px;">
                    <option value="">Selecione</option>
                    @foreach(($anosLetivos ?? []) as $item)
                        <option value="{{ data_get($item, 'id') }}" @if(old('ano', $ano ?? null) == data_get($item, 'id')) selected @endif>
                            {{ data_get($item, 'nome') }}
                        </option>
                    @endforeach
                </select>
            </td>
        </tr>
        <tr id="tr_nm_instituicao">
            <td class="formlttd" valign="top">
                <span class="form">Instituição</span>
                <span class="campo_obrigatorio">*</span>
            </td>
            <td class="formlttd" valign="top">
                @include('form.select-institution', ['obrigatorio' => true])
            </td>
        </tr>
        <tr id="tr_nm_escola">
            <td class="formmdtd" valign="top"><span class="form">Escola</span></td>
            <td class="formmdtd" valign="top">
                <select class="geral" name="ref_cod_escola" id="ref_cod_escola" style="width: 308px;">
                    <option value="">Selecione</option>
                    @foreach(($escolas ?? []) as $item)
                        <option value="{{ data_get($item, 'cod_escola') }}"
                                @selected((string) old('ref_cod_escola', $escolaId ?? request('ref_cod_escola')) === (string) data_get($item, 'cod_escola'))>
                            {{ data_get($item, 'nome') }}
                        </option>
                    @endforeach
                </select>
            </td>
        </tr>
        <tr id="tr_nm_curso">
            <td class="formlttd" valign="top"><span class="form">Curso</span></td>
            <td class="formlttd" valign="top">
                @if(!empty($requireCourse))
                    <span class="campo_obrigatorio">*</span>
                @endif
                <select class="geral {{ !empty($requireCourse) ? 'obrigatorio' : '' }}" name="ref_cod_curso" id="ref_cod_curso" style="width: 308px;">
                    <option value="">Selecione</option>
                    @foreach($cursos as $curso)
                        <option value="{{ $curso->cod_curso }}" @selected(($cursoId ?? null) == $curso->cod_curso)>
                            {{ $curso->nm_curso }}
                        </option>
                    @endforeach
                </select>
            </td>
        </tr>
        @if(!empty($withGrade))
            <tr id="tr_nm_serie">
                <td class="formmdtd" valign="top">
                    <span class="form">Série</span>
                    @if(!empty($requireSerie))
                        <span class="campo_obrigatorio">*</span>
                    @endif
                </td>
                <td class="formmdtd" valign="top">
                    <select class="geral {{ !empty($requireSerie) ? 'obrigatorio' : '' }}" name="ref_cod_serie" id="ref_cod_serie" style="width: 308px;">
                        <option value="">Selecione</option>
                        @foreach(($series ?? []) as $s)
                            <option value="{{ data_get($s, 'cod_serie') }}"
                                    @selected((string) old('ref_cod_serie', request('ref_cod_serie')) === (string) data_get($s, 'cod_serie'))>
                                {{ data_get($s, 'nm_serie') }}
                            </option>
                        @endforeach
                    </select>
                </td>
            </tr>
        @endif
        @if(!empty($withSchoolClass))
            <tr id="tr_nm_turma">
                <td class="formlttd" valign="top">
                    <span class="form">Turma</span>
                    @if(!empty($requireTurma))
                        <span class="campo_obrigatorio">*</span>
                    @endif
                </td>
                <td class="formlttd" valign="top">
                    <select class="geral {{ !empty($requireTurma) ? 'obrigatorio' : '' }}" name="ref_cod_turma" id="ref_cod_turma" style="width: 308px;">
                        <option value="">Selecione</option>
                        @foreach(($turmas ?? []) as $t)
                            @php($serieOk = empty(request('ref_cod_serie')) || (string) data_get($t, 'ref_ref_cod_serie') === (string) request('ref_cod_serie'))
                            @if($serieOk)
                                <option value="{{ data_get($t, 'cod_turma') }}"
                                        @selected((string) old('ref_cod_turma', request('ref_cod_turma')) === (string) data_get($t, 'cod_turma'))>
                                    {{ data_get($t, 'nm_turma') }}
                                </option>
                            @endif
                        @endforeach
                    </select>
                </td>
            </tr>
        @endif
        @if(!empty($withDates))
            <tr id="tr_data_inicial">
                <td class="formmdtd" valign="top">
                    <span class="form">Data inicial</span>
                    <span class="campo_obrigatorio">*</span>
                </td>
                <td class="formmdtd" valign="top">
                    <input class="geral obrigatorio" type="date" name="data_inicial" value="{{ request('data_inicial') }}" />
                </td>
            </tr>
            <tr id="tr_data_final">
                <td class="formlttd" valign="top">
                    <span class="form">Data final</span>
                    <span class="campo_obrigatorio">*</span>
                </td>
                <td class="formlttd" valign="top">
                    <input class="geral obrigatorio" type="date" name="data_final" value="{{ request('data_final') }}" />
                </td>
            </tr>
        @endif
        @if(!empty($withCharts))
            <tr id="tr_with_charts">
                <td class="formmdtd" valign="top">
                    <span class="form">Gráficos</span>
                </td>
                <td class="formmdtd" valign="top">
                    <label style="display:inline-flex;align-items:center;gap:4px;">
                        <input type="checkbox" name="with_charts" value="1" {{ request('with_charts') ? 'checked' : '' }}>
                        Incluir gráficos no PDF
                    </label>
                </td>
            </tr>
        @endif
        @if(!empty($extraRowsView))
            @include($extraRowsView)
        @endif
        <tr>
            <td class="formdktd" colspan="2"></td>
        </tr>
        </tbody>
    </table>

    @if(!empty($actionsView))
        @include($actionsView)
    @else
        <div style="text-align: center; margin-top: 16px;">
            <a href="{{ $route }}" class="btn">Limpar filtros</a>
            <button type="submit" class="btn-green" style="margin-left: 8px;">Filtrar</button>
        </div>
    @endif
</form>

@push('scripts')
    <script>
        (function () {
            const form = document.getElementById('formcadastro');
            const yearSelect = document.getElementById('ano');
            const instSelect = document.getElementById('ref_cod_instituicao');
            const escolaSelect = document.getElementById('ref_cod_escola');
            const cursoSelect = document.getElementById('ref_cod_curso');
            const serieSelect = document.getElementById('ref_cod_serie');
            const turmaSelect = document.getElementById('ref_cod_turma');

            if (!instSelect || !escolaSelect || !cursoSelect || !form || !yearSelect) {
                return;
            }

            function updateDisabled() {
                if (!instSelect.value) {
                    escolaSelect.disabled = true;
                    cursoSelect.disabled = true;
                    if (serieSelect) serieSelect.disabled = true;
                    if (turmaSelect) turmaSelect.disabled = true;
                } else {
                    escolaSelect.disabled = false;
                    if (!escolaSelect.value) {
                        cursoSelect.disabled = true;
                        if (serieSelect) serieSelect.disabled = true;
                        if (turmaSelect) turmaSelect.disabled = true;
                    } else {
                        cursoSelect.disabled = false;
                        if (serieSelect) serieSelect.disabled = false;
                        if (turmaSelect && serieSelect) {
                            turmaSelect.disabled = !serieSelect.value;
                        } else if (turmaSelect) {
                            turmaSelect.disabled = false;
                        }
                    }
                }
            }

            updateDisabled();

            yearSelect.addEventListener('change', function () {
                instSelect.value = '';
                escolaSelect.value = '';
                cursoSelect.value = '';
                if (serieSelect) serieSelect.value = '';
                if (turmaSelect) turmaSelect.value = '';
                updateDisabled();
                form.submit();
            });

            instSelect.addEventListener('change', function () {
                escolaSelect.value = '';
                cursoSelect.value = '';
                if (serieSelect) serieSelect.value = '';
                if (turmaSelect) turmaSelect.value = '';
                updateDisabled();
                form.submit();
            });

            escolaSelect.addEventListener('change', function () {
                cursoSelect.value = '';
                if (serieSelect) serieSelect.value = '';
                if (turmaSelect) turmaSelect.value = '';
                updateDisabled();
                form.submit();
            });

            cursoSelect.addEventListener('change', function () {
                if (serieSelect) serieSelect.value = '';
                if (turmaSelect) turmaSelect.value = '';
                updateDisabled();
                form.submit();
            });

            if (serieSelect && turmaSelect) {
                serieSelect.addEventListener('change', function () {
                    turmaSelect.value = '';
                    updateDisabled();
                    form.submit();
                });
            }
        })();
    </script>
@endpush
