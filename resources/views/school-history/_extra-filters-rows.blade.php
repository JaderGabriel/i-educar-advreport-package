<tr>
  <td class="formmdtd"><span class="form">Modelo</span></td>
  <td class="formmdtd">
    <select class="geral" name="template" id="template" style="width: 420px;">
      @foreach(($templates ?? []) as $key => $label)
        <option value="{{ $key }}" @selected(($template ?? 'classic') === $key)>{{ $label }}</option>
      @endforeach
    </select>
  </td>
</tr>
<tr>
  <td class="formmdtd" valign="top">
    <span class="form">Históricos prontos</span> <span class="campo_obrigatorio">*</span>
  </td>
  <td class="formmdtd">
    <div style="width: 820px; max-width: 100%;">
      <div style="display:flex; align-items:center; gap:12px; margin-bottom:6px;">
        <label style="display:flex; align-items:center; gap:6px; margin:0;">
          <input type="checkbox" class="js-school-history-select-all">
          Selecionar todos
        </label>
        <span class="js-school-history-selected-count" style="color:#666;">0 selecionado(s)</span>
      </div>

      <div style="border: 1px solid #e5e7eb; border-radius: 10px; overflow: hidden; background:#fff;">
        <table class="tablelistagem" style="width:100%; border-collapse: collapse; margin:0;">
          <thead>
          <tr style="background:#f8fafc;">
            <th style="width:34px; padding:8px; text-align:center;">&nbsp;</th>
            <th style="padding:8px; text-align:left;">Aluno</th>
            <th style="width:70px; padding:8px; text-align:left;">Ano</th>
            <th style="padding:8px; text-align:left;">Curso</th>
            <th style="padding:8px; text-align:left;">Série</th>
            <th style="width:90px; padding:8px; text-align:left;">Registro</th>
            <th style="width:70px; padding:8px; text-align:left;">Livro</th>
            <th style="width:70px; padding:8px; text-align:left;">Folha</th>
          </tr>
          </thead>
          <tbody class="js-school-history-tbody">
          <tr>
            <td colspan="8" style="padding:10px; color:#666;">
              Selecione a turma para carregar os alunos com histórico nativo consolidado.
            </td>
          </tr>
          </tbody>
        </table>
      </div>

      <small style="display:block;color:#666;margin-top:6px;">
        A tabela lista apenas alunos que já possuem <strong>histórico escolar gerado pela rotina nativa</strong>. O pacote apenas imprime (em diferentes modelos) e adiciona validação (QR/assinatura).
      </small>
    </div>
  </td>
</tr>
