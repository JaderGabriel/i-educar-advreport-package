<tr>
  <td class="formmdtd"><span class="form">Documento</span></td>
  <td class="formmdtd">
    <select id="document" name="document" class="geral" style="width: 320px;">
      <option value="diploma" {{ request('document', 'diploma') === 'diploma' ? 'selected' : '' }}>Diploma</option>
      <option value="certificate" {{ request('document') === 'certificate' ? 'selected' : '' }}>Certificado</option>
      <option value="declaration" {{ request('document') === 'declaration' ? 'selected' : '' }}>Declaração</option>
    </select>
  </td>
</tr>
<tr>
  <td class="formlttd"><span class="form">Tipo</span></td>
  <td class="formlttd">
    <select id="template" name="template" class="geral" style="width: 320px;">
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
    <select id="side" name="side" class="geral" style="width: 320px;">
      <option value="front" {{ request('side','front') === 'front' ? 'selected' : '' }}>Frente</option>
      <option value="back" {{ request('side') === 'back' ? 'selected' : '' }}>Verso</option>
      <option value="both" {{ request('side') === 'both' ? 'selected' : '' }}>Frente e verso</option>
    </select>
  </td>
</tr>

