<tr>
  <td class="formmdtd"><span class="form">Documento</span></td>
  <td class="formmdtd">
    <select id="diplomasDocument" name="document" class="geral" style="width: 320px;">
      <option value="diploma" {{ request('document', 'diploma') === 'diploma' ? 'selected' : '' }}>Diploma</option>
      <option value="certificate" {{ request('document') === 'certificate' ? 'selected' : '' }}>Certificado</option>
      <option value="declaration" {{ request('document') === 'declaration' ? 'selected' : '' }}>Declaração</option>
    </select>
  </td>
</tr>
<tr>
  <td class="formlttd"><span class="form">Tipo</span></td>
  <td class="formlttd">
    <select id="diplomasTemplate" name="template" class="geral" style="width: 320px;">
      <option value="classic" {{ request('template','classic') === 'classic' ? 'selected' : '' }}>Clássico institucional</option>
      <option value="modern" {{ request('template') === 'modern' ? 'selected' : '' }}>Moderno minimalista</option>
      <option value="seal" {{ request('template') === 'seal' ? 'selected' : '' }}>Oficial com brasão</option>
      <option value="bilingual" {{ request('template') === 'bilingual' ? 'selected' : '' }}>Bilíngue</option>
    </select>
  </td>
</tr>
<tr>
  <td class="formmdtd"><span class="form">Lado</span></td>
  <td class="formmdtd">
    <select id="diplomasSide" name="side" class="geral" style="width: 320px;">
      <option value="both" @selected(!in_array((string) request('side'), ['front', 'back'], true))>Frente e verso (padrão)</option>
      <option value="front" {{ (string) request('side') === 'front' ? 'selected' : '' }}>Somente frente</option>
      <option value="back" {{ (string) request('side') === 'back' ? 'selected' : '' }}>Somente verso</option>
    </select>
  </td>
</tr>
<tr>
  <td class="formlttd"><span class="form">Alunos (opcional)</span></td>
  <td class="formlttd">
    <select class="geral" id="diplomasStudentsSelect" name="matricula_ids[]" multiple size="8" style="width: 520px;" disabled>
      <option value="">Selecione a turma para listar alunos</option>
    </select>
    <input type="text" class="geral js-diplomas-student-filter" placeholder="Filtrar por nome (não precisa Ctrl)" style="width: 520px; margin-top: 6px;">
    <small style="display:block;color:#666;margin-top:4px;">
      Se não selecionar nenhum aluno, o sistema emite para <strong>todos</strong> da turma (limite de segurança no PDF).
    </small>
  </td>
</tr>
