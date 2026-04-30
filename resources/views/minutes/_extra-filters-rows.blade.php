<tr>
  <td class="formmdtd"><span class="form">Documento</span> <span class="campo_obrigatorio">*</span></td>
  <td class="formmdtd">
    <select class="geral obrigatorio" name="document" id="minutesDocument" style="width: 320px;">
      <option value="final_results" @selected(($document ?? request('document', 'final_results')) === 'final_results')>Ata de resultados finais</option>
      <option value="delivery_results" @selected(($document ?? request('document')) === 'delivery_results')>Ata de entrega de resultados</option>
      <option value="council_class" @selected(($document ?? request('document')) === 'council_class')>Ata de conselho de classe</option>
      <option value="signatures" @selected(($document ?? request('document')) === 'signatures')>Lista de assinaturas (responsáveis)</option>
    </select>
  </td>
</tr>

<tr id="minutesEtapasRow">
  <td class="formmdtd" valign="top"><span class="form">Períodos avaliativos</span> <span class="campo_obrigatorio" id="minutesEtapasRequired" style="display:none;">*</span></td>
  <td class="formmdtd" valign="top">
    <input class="geral" type="text" name="etapas" id="minutesEtapas" style="width: 320px;"
           value="{{ old('etapas', $etapas ?? request('etapas')) }}"
           placeholder="Ex.: 1, 2 ou 1, 3, 4 (números das etapas do diário)">
    <div class="formhint" style="margin-top:4px;font-size:11px;color:#555;">
      Obrigatório para <strong>Ata de entrega de resultados</strong> e <strong>Ata de conselho de classe</strong>: etapas separadas por vírgula (bimestres, trimestres etc., conforme a regra da turma).
    </div>
  </td>
</tr>

<tr id="minutesCouncilTurmasRow" style="display: none;">
  <td class="formmdtd" valign="top"><span class="form">Turmas na ata</span></td>
  <td class="formmdtd" valign="top">
    <select class="geral" name="turma_ids[]" id="minutesCouncilTurmas" multiple size="8" style="width: 340px;"></select>
    <div class="formhint" style="margin-top:4px;font-size:11px;color:#555;">
      Selecione <strong>uma ou mais turmas</strong> (Ctrl+clique) dentre as da série. Se nada for selecionado aqui, será usada apenas a <strong>turma principal</strong> do filtro acima — o PDF terá <strong>uma seção por turma</strong> e quebra de página entre elas.
    </div>
  </td>
</tr>

<tr>
  <td class="formmdtd"><span class="form">Emissor</span></td>
  <td class="formmdtd">
    <input class="geral" value="{{ auth()->user()?->name }}" style="width: 320px;" disabled>
  </td>
</tr>

<tr id="minutesWithDetailsRow">
  <td class="formmdtd"><span class="form">Detalhes</span></td>
  <td class="formmdtd">
    <label style="display:inline-flex;align-items:center;gap:6px;">
      <input type="checkbox" name="with_details" value="1" {{ request('with_details') ? 'checked' : '' }}>
      Incluir notas por componente/etapa e frequência (quando disponível — ata de resultados finais)
    </label>
  </td>
</tr>

