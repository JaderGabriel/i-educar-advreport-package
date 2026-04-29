<tr>
  <td class="formmdtd" valign="top"><span class="form">Situação</span></td>
  <td class="formmdtd" valign="top">
    <select class="geral" name="situacao" style="width: 320px;">
      <option value="">Todas</option>
      @foreach(($situacaoOptions ?? []) as $id => $label)
        <option value="{{ $id }}" @selected((string) request('situacao') === (string) $id)>{{ $label }}</option>
      @endforeach
    </select>
  </td>
</tr>
