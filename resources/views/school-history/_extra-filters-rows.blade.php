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
  <td class="formlttd"><span class="form">Livro/Folha/Registro</span></td>
  <td class="formlttd">
    <input class="geral" id="schoolHistoryBook" value="" style="width: 70px;" placeholder="Livro" readonly disabled>
    <input class="geral" id="schoolHistoryPage" value="" style="width: 70px;" placeholder="Folha" readonly disabled>
    <input class="geral" id="schoolHistoryRecord" value="" style="width: 90px;" placeholder="Registro" readonly disabled>
    <small style="display:block;color:#666;margin-top:4px;">
      Esses campos são carregados automaticamente do histórico gerado pela rotina nativa.
    </small>
  </td>
</tr>
<tr>
  <td class="formmdtd"><span class="form">Aluno</span> <span class="campo_obrigatorio">*</span></td>
  <td class="formmdtd">
    <select class="geral obrigatorio" id="schoolHistoryStudentSelect" name="aluno_id" size="8" style="width: 520px;" disabled>
      <option value="">Selecione a turma para listar alunos com histórico nativo</option>
    </select>
    <input type="text" class="geral js-school-history-student-filter" placeholder="Filtrar por nome (não precisa Ctrl)" style="width: 520px; margin-top: 6px;">
    <small style="display:block;color:#666;margin-top:4px;">
      A lista mostra somente alunos que já possuem <strong>histórico escolar consolidado</strong> (rotina nativa). O pacote apenas imprime em diferentes modelos e adiciona validação (QR/assinatura).
    </small>
  </td>
</tr>
