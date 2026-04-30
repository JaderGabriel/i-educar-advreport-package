@php($def = $definition ?? [])
<tr id="tr_comm_ref">
  <td class="formmdtd" valign="top"><span class="form">Referência do documento</span></td>
  <td class="formmdtd" valign="top">
    <input class="geral" type="text" name="ref_documento" id="comm_ref_documento" style="width: 200px;"
           placeholder="Ex.: 012/2026"
           value="{{ request('ref_documento') }}">
    <span class="muted" style="margin-left:8px;">Opcional — número interno do comunicado</span>
  </td>
</tr>
<tr id="tr_comm_data_doc">
  <td class="formlttd" valign="top"><span class="form">Data do comunicado</span></td>
  <td class="formlttd" valign="top">
    <input class="geral" type="date" name="data_documento" id="comm_data_documento" style="width: 160px;"
           value="{{ request('data_documento', date('Y-m-d')) }}">
  </td>
</tr>
<tr id="tr_comm_assunto">
  <td class="formmdtd" valign="top"><span class="form">Assunto</span></td>
  <td class="formmdtd" valign="top">
    <input class="geral" type="text" name="assunto" id="comm_assunto" style="width: 620px;"
           value="{{ request('assunto', $def['default_assunto'] ?? '') }}">
  </td>
</tr>
<tr id="tr_comm_local">
  <td class="formlttd" valign="top"><span class="form">Local do evento / atendimento</span></td>
  <td class="formlttd" valign="top">
    <input class="geral" type="text" name="local_evento" id="comm_local_evento" style="width: 620px;"
           placeholder="Ex.: Auditório da escola / Sala de reuniões / plataforma on-line"
           value="{{ request('local_evento') }}">
  </td>
</tr>
<tr id="tr_comm_data_hora">
  <td class="formmdtd" valign="top"><span class="form">Data e horário</span></td>
  <td class="formmdtd" valign="top">
    <input class="geral" type="date" name="data_evento" id="comm_data_evento" style="width: 160px;" value="{{ request('data_evento') }}">
    <input class="geral" type="time" name="hora_evento" id="comm_hora_evento" style="width: 120px; margin-left: 12px;" value="{{ request('hora_evento') }}">
  </td>
</tr>
@if(!empty($def['show_pauta']))
  <tr id="tr_comm_pauta">
    <td class="formlttd" valign="top"><span class="form">Pauta / ordem do dia</span></td>
    <td class="formlttd" valign="top">
      <textarea class="geral" name="pauta" id="comm_pauta" rows="4" style="width: 620px;" placeholder="Itens a tratar na reunião ou assembleia...">{{ request('pauta') }}</textarea>
    </td>
  </tr>
@endif
@if(!empty($def['show_prazo_resposta']))
  <tr id="tr_comm_prazo">
    <td class="formmdtd" valign="top"><span class="form">Prazo para comparecimento / resposta</span></td>
    <td class="formmdtd" valign="top">
      <input class="geral" type="text" name="prazo_resposta" id="comm_prazo_resposta" style="width: 620px;"
             placeholder="Ex.: até 5 dias úteis / dia __/__/____"
             value="{{ request('prazo_resposta') }}">
    </td>
  </tr>
@endif
<tr id="tr_comm_corpo">
  <td class="formlttd" valign="top"><span class="form">Texto do comunicado</span></td>
  <td class="formlttd" valign="top">
    <textarea class="geral" name="corpo" id="comm_corpo" rows="12" style="width: 620px;">{{ request('corpo', $def['default_corpo'] ?? '') }}</textarea>
    <p class="muted" style="margin-top:6px;">Texto sugerido pela rede (editável). Quebras de linha são preservadas no PDF.</p>
  </td>
</tr>
<tr id="tr_comm_matriculas">
  <td class="formmdtd" valign="top">
    <span class="form">Destinatários (opcional)</span>
  </td>
  <td class="formmdtd" valign="top">
    <input type="hidden" id="communicationsMatriculaId" name="matricula_id" value="">
    <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;">
      <input type="text" class="geral js-communications-student-filter" placeholder="Filtrar na lista..." style="width: 220px;">
      <span class="muted js-communications-selected-count">0 selecionados</span>
      <a href="#" class="js-communications-clear-selected">Limpar seleção</a>
    </div>
    <select id="communicationsStudentsSelect" name="matricula_ids[]" multiple size="10" style="width: 620px; margin-top: 6px;">
      <option value="">Selecione a turma para listar matrículas</option>
    </select>
    <p class="muted" style="margin-top:6px;">
      <strong>Emissão em lote:</strong> selecione matrículas para um comunicado por estudante (nome no destinatário).
      Se não selecionar, será emitido <strong>um único comunicado coletivo</strong> conforme turma/curso dos filtros (até 200 matrículas por lote).
    </p>
  </td>
</tr>
