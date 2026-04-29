<tr>
  <td class="formmdtd"><span class="form">Documento</span> <span class="campo_obrigatorio">*</span></td>
  <td class="formmdtd">
    <select class="geral obrigatorio" name="document" id="studentDocumentsDocument" style="width: 320px;">
      <option value="declaration_enrollment" @selected(request('document', 'declaration_enrollment') === 'declaration_enrollment')>Declaração de matrícula</option>
      <option value="declaration_frequency" @selected(request('document') === 'declaration_frequency')>Declaração de frequência</option>
      <option value="transfer_guide" @selected(request('document') === 'transfer_guide')>Guia/Declaração de transferência</option>
      <option value="declaration_conclusion" @selected(request('document') === 'declaration_conclusion')>Declaração de conclusão</option>
      <option value="declaration_nada_consta" @selected(request('document') === 'declaration_nada_consta')>Declaração de escolaridade / Nada consta</option>
    </select>
  </td>
</tr>
<tr id="studentDocumentsHistoricoRow" style="display: none;">
  <td class="formmdtd"><span class="form">Prazo (dias) p/ histórico</span></td>
  <td class="formmdtd">
    <input class="geral" type="number" min="0" step="1" name="historico_emissao_dias" value="{{ request('historico_emissao_dias') }}" style="width: 160px;">
    <small style="display:block;color:#666;margin-top:4px;">Usado na <strong>Declaração de conclusão</strong> (texto do documento).</small>
  </td>
</tr>
<tr>
  <td class="formlttd"><span class="form">Alunos (opcional)</span></td>
  <td class="formlttd">
    <input type="hidden" name="matricula_id" id="studentDocumentsMatriculaId" value="{{ request('matricula_id') }}">
    <select class="geral" id="studentDocumentsStudentsSelect" name="matricula_ids[]" multiple size="8" style="width: 520px;" disabled>
      <option value="">Selecione a turma para listar alunos</option>
    </select>
    <input type="text" class="geral js-student-filter" placeholder="Filtrar por nome (não precisa Ctrl)" style="width: 520px; margin-top: 6px;">
    <small style="display:block;color:#666;margin-top:4px;">
      Dica: use Escola → Série → Turma. Selecione um ou mais alunos (clique em várias linhas). Se não selecionar nenhum, o sistema emite em lote pelo filtro (limitado).
    </small>
  </td>
</tr>

