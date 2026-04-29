<tr>
  <td class="formmdtd"><span class="form">Etapa</span></td>
  <td class="formmdtd">
    <input class="geral" name="etapa" value="{{ request('etapa') }}" style="width: 80px;" placeholder="(opcional)">
    <span class="helper">Ex.: 1, 2, 3... ou Rc</span>
  </td>
</tr>
<tr>
  <td class="formlttd"><span class="form">Alunos (opcional)</span></td>
  <td class="formlttd">
    <select class="geral" id="boletimStudentSelect" name="matricula_ids[]" multiple size="8" style="width: 520px;" disabled>
      <option value="">Selecione a turma para listar alunos</option>
    </select>
    <input type="text" class="geral js-boletim-student-filter" placeholder="Filtrar por nome (não precisa Ctrl)" style="width: 520px; margin-top: 6px;">
    <small style="display:block;color:#666;margin-top:4px;">
      Nenhuma seleção = emite em lote pelo filtro (ano/escola/curso e opcionais). Uma seleção = um PDF. Várias = lote só dos selecionados.
    </small>
  </td>
</tr>

