<tr>
  <td class="formmdtd"><span class="form">Etapa</span></td>
  <td class="formmdtd">
    <input class="geral" name="etapa" value="{{ request('etapa') }}" style="width: 80px;" placeholder="(opcional)">
    <span class="helper">Ex.: 1, 2, 3... ou Rc</span>
  </td>
</tr>
<tr>
  <td class="formlttd"><span class="form">Aluno (opcional)</span></td>
  <td class="formlttd">
    <input type="hidden" name="matricula_id" id="boletimMatriculaId" value="{{ request('matricula_id') }}">
    <select class="geral" id="boletimStudentSelect" style="width: 520px;" disabled>
      <option value="">Selecione a turma para listar alunos</option>
    </select>
  </td>
</tr>

