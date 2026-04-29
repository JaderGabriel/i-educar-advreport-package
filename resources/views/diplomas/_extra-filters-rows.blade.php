<tr>
  <td class="formmdtd"><span class="form">Documento</span></td>
  <td class="formmdtd">
    <select id="diplomasDocument" name="document" class="geral" style="width: 320px;">
      <option value="diploma" {{ request('document', 'diploma') === 'diploma' ? 'selected' : '' }}>Diploma</option>
      <option value="certificate" {{ request('document') === 'certificate' ? 'selected' : '' }}>Certificado</option>
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
    <div style="border:1px solid #e5e7eb;border-radius:10px;overflow:hidden;width:520px;background:#fff;">
      <div style="display:flex;align-items:center;gap:8px;padding:10px;border-bottom:1px solid #e5e7eb;background:#f9fafb;">
        <input type="text" class="geral js-diplomas-student-filter" placeholder="Buscar aluno na lista (sem Ctrl)" style="flex:1;min-width:220px;">
        <small class="js-diplomas-selected-count" style="color:#6b7280;white-space:nowrap;">0 selecionados</small>
        <button type="button" class="btn ar-btn ar-btn--ghost js-diplomas-clear-selected" title="Limpar seleção" aria-label="Limpar seleção">Limpar</button>
      </div>
      <select class="geral" id="diplomasStudentsSelect" name="matricula_ids[]" multiple size="9" style="width:100%;border:0;border-radius:0;" disabled>
        <option value="">Selecione a turma para listar alunos</option>
      </select>
      <div style="padding:10px;color:#6b7280;font-size:11px;line-height:1.35;">
        Nenhuma seleção = emite para todos da turma (limite de segurança). Uma seleção = um PDF. Várias = lote só dos selecionados.
      </div>
    </div>
  </td>
</tr>
