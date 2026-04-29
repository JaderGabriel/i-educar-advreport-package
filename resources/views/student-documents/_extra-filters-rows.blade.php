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
    <div style="border:1px solid #e5e7eb;border-radius:10px;overflow:hidden;width:520px;background:#fff;">
      <div style="display:flex;align-items:center;gap:8px;padding:10px;border-bottom:1px solid #e5e7eb;background:#f9fafb;">
        <input type="text" class="geral js-student-docs-student-filter" placeholder="Buscar aluno na lista (sem Ctrl)" style="flex:1;min-width:220px;">
        <small class="js-student-docs-selected-count" style="color:#6b7280;white-space:nowrap;">0 selecionados</small>
        <button type="button" class="btn ar-btn ar-btn--ghost js-student-docs-clear-selected" title="Limpar seleção" aria-label="Limpar seleção">Limpar</button>
      </div>
      <div class="js-student-docs-class-counters" style="display:none;padding:8px 10px;border-bottom:1px solid #e5e7eb;background:#fff;color:#6b7280;font-size:12px;">
        <span style="color:#9ca3af;">Contadores da turma (documento restrito).</span>
      </div>
      <select class="geral" id="studentDocumentsStudentsSelect" name="matricula_ids[]" multiple size="9" style="width:100%;border:0;border-radius:0;" disabled>
        <option value="">Selecione a turma para listar alunos</option>
      </select>
      <div style="padding:10px;color:#6b7280;font-size:11px;line-height:1.35;">
        Para <strong>Declaração de conclusão</strong>, apenas matrículas <strong>concluídas/aprovadas/finalizadas</strong> aparecem na lista.
        Nenhuma seleção = emite em lote pelo filtro (limitado). Uma seleção = um PDF. Várias = lote só dos selecionados.
      </div>
    </div>
  </td>
</tr>

