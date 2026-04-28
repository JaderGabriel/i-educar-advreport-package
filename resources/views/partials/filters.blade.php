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
                        <option value="{{ $item->id }}" @if(old('ano', $ano ?? null) == $item->id) selected @endif>
                            {{ $item->nome }}
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
                @include('form.select-school')
            </td>
        </tr>
        <tr id="tr_nm_curso">
            <td class="formlttd" valign="top"><span class="form">Curso</span></td>
            <td class="formlttd" valign="top">
                <select class="geral" name="ref_cod_curso" id="ref_cod_curso" style="width: 308px;">
                    <option value="">Selecione</option>
                    @foreach($cursos as $curso)
                        <option value="{{ $curso->cod_curso }}" @selected(($cursoId ?? null) == $curso->cod_curso)>
                            {{ $curso->nm_curso }}
                        </option>
                    @endforeach
                </select>
            </td>
        </tr>
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
        <tr>
            <td class="formdktd" colspan="2"></td>
        </tr>
        </tbody>
    </table>

    <div style="text-align: center; margin-top: 16px;">
        <a href="{{ $route }}" class="btn">Limpar filtros</a>
        <button type="submit" class="btn-green" style="margin-left: 8px;">Filtrar</button>
    </div>
</form>

@push('scripts')
    <script>
        (function () {
            const form = document.getElementById('formcadastro');
            const instSelect = document.getElementById('ref_cod_instituicao');
            const escolaSelect = document.getElementById('ref_cod_escola');
            const cursoSelect = document.getElementById('ref_cod_curso');

            if (!instSelect || !escolaSelect || !cursoSelect || !form) {
                return;
            }

            function updateDisabled() {
                if (!instSelect.value) {
                    escolaSelect.disabled = true;
                    cursoSelect.disabled = true;
                } else {
                    escolaSelect.disabled = false;
                    if (!escolaSelect.value) {
                        cursoSelect.disabled = true;
                    } else {
                        cursoSelect.disabled = false;
                    }
                }
            }

            updateDisabled();

            instSelect.addEventListener('change', function () {
                escolaSelect.value = '';
                cursoSelect.value = '';
                updateDisabled();
                form.submit();
            });

            escolaSelect.addEventListener('change', function () {
                cursoSelect.value = '';
                updateDisabled();
                form.submit();
            });
        })();
    </script>
@endpush
