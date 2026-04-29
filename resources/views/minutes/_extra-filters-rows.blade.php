<tr>
  <td class="formmdtd"><span class="form">Documento</span> <span class="campo_obrigatorio">*</span></td>
  <td class="formmdtd">
    <select class="geral obrigatorio" name="document" id="minutesDocument" style="width: 320px;">
      <option value="final_results" @selected(($document ?? request('document', 'final_results')) === 'final_results')>Ata de resultados finais</option>
      <option value="signatures" @selected(($document ?? request('document')) === 'signatures')>Lista de assinaturas (responsáveis)</option>
    </select>
  </td>
</tr>

<tr>
  <td class="formmdtd"><span class="form">Emissor</span></td>
  <td class="formmdtd">
    <input class="geral" value="{{ auth()->user()?->name }}" style="width: 320px;" disabled>
  </td>
</tr>

<tr>
  <td class="formmdtd"><span class="form">Detalhes</span></td>
  <td class="formmdtd">
    <label style="display:inline-flex;align-items:center;gap:6px;">
      <input type="checkbox" name="with_details" value="1" {{ request('with_details') ? 'checked' : '' }}>
      Incluir notas por componente/etapa e frequência (quando disponível)
    </label>
  </td>
</tr>

