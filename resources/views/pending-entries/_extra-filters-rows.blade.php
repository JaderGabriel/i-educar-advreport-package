<tr id="tr_pending_entries_stage">
    <td class="formmdtd"><span class="form">Etapa</span></td>
    <td class="formmdtd">
        <input class="geral" name="etapa" value="{{ request('etapa') }}" style="width: 80px;" placeholder="Ex.: 1">
        <span class="form" style="margin-left: 8px; font-size: 11px;">(vazio = todas as etapas)</span>
    </td>
</tr>
<tr id="tr_pending_entries_check">
    <td class="formlttd"><span class="form">Verificar</span></td>
    <td class="formlttd">
        <label style="display:inline-flex;gap:6px;align-items:center;margin-right:12px;">
            <input type="checkbox" name="check_grades" value="1" {{ request()->boolean('check_grades', true) ? 'checked' : '' }}>
            Notas
        </label>
        <label style="display:inline-flex;gap:6px;align-items:center;">
            <input type="checkbox" name="check_frequency" value="1" {{ request()->boolean('check_frequency', true) ? 'checked' : '' }}>
            Frequência (faltas)
        </label>
    </td>
</tr>

