<tr>
  <td class="formmdtd"><span class="form">Documento</span> <span class="campo_obrigatorio">*</span></td>
  <td class="formmdtd">
    <select class="geral obrigatorio" name="document" style="width: 320px;">
      <option value="declaration_enrollment" @selected(request('document', 'declaration_enrollment') === 'declaration_enrollment')>Declaração de matrícula</option>
      <option value="declaration_frequency" @selected(request('document') === 'declaration_frequency')>Declaração de frequência</option>
      <option value="transfer_guide" @selected(request('document') === 'transfer_guide')>Guia/Declaração de transferência</option>
      <option value="declaration_nada_consta" @selected(request('document') === 'declaration_nada_consta')>Declaração de escolaridade / Nada consta</option>
    </select>
  </td>
</tr>
<tr>
  <td class="formlttd"><span class="form">Aluno (na turma)</span> <span class="campo_obrigatorio">*</span></td>
  <td class="formlttd">
    <input type="hidden" name="matricula_id" id="studentDocumentsMatriculaId" value="{{ request('matricula_id') }}">
    <select class="geral obrigatorio" id="studentDocumentsStudentSelect" style="width: 520px;" disabled>
      <option value="">Selecione a turma para listar alunos</option>
    </select>
    <small style="display:block;color:#666;margin-top:4px;">
      Dica: use Escola → Série → Turma. Depois selecione o aluno para definir a matrícula automaticamente.
    </small>
  </td>
</tr>

