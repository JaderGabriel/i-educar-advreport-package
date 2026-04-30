<tr id="tr_student_forms_info">
  <td class="formmdtd" valign="top">
    <span class="form">Tipo</span>
  </td>
  <td class="formmdtd" valign="top">
    <strong>{{ ($type ?? '') === 'individual' ? 'Ficha individual' : 'Ficha de matrícula' }}</strong>
  </td>
</tr>

<tr id="tr_student_forms_matriculas">
  <td class="formlttd" valign="top">
    <span class="form">Matrículas (opcional)</span>
  </td>
  <td class="formlttd" valign="top">
    <input type="hidden" id="studentFormsMatriculaId" name="matricula_id" value="">

    <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;">
      <input type="text" class="geral js-student-forms-student-filter" placeholder="Filtrar na lista..." style="width: 220px;">
      <span class="muted js-student-forms-selected-count">0 selecionados</span>
      <a href="#" class="js-student-forms-clear-selected">Limpar seleção</a>
    </div>
    <select id="studentFormsStudentsSelect" name="matricula_ids[]" multiple size="10" style="width: 620px; margin-top: 6px;">
      <option value="">Selecione a turma para listar matrículas</option>
    </select>
    <p class="muted" style="margin-top:6px;">
      Dica: selecione múltiplos itens com Ctrl (Windows/Linux) ou Cmd (macOS).
    </p>
  </td>
</tr>

