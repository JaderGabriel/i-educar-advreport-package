<div class="ar-official-footer" style="border-top: 1px dashed #cbd5e1; padding-top: 10px; font-size: 10px; color: #374151; display:flex; justify-content: space-between; gap: 12px; flex-wrap: nowrap; align-items: center; background: #fff;">
  <div style="flex: 1; min-width: 240px;">
    <div><strong>Emissão</strong>: {{ $issuedAt }}</div>
    @if(!empty($issuerName) || !empty($issuerRole))
      <div><strong>Emissor</strong>: {{ trim(($issuerName ?? '') . (!empty($issuerRole) ? (' (' . $issuerRole . ')') : '')) }}</div>
    @endif
    @if(!empty($cityUf))
      <div><strong>Cidade/UF</strong>: {{ $cityUf }}</div>
    @endif
    @if(!empty($book) || !empty($page) || !empty($record))
      <div><strong>Livro/Folha/Registro</strong>: {{ $book ?: '-' }} / {{ $page ?: '-' }} / {{ $record ?: '-' }}</div>
    @endif
    <div><strong>Código</strong>: {{ $validationCode }}</div>
    <div><strong>Validação</strong>: {{ $validationUrl }}</div>
  </div>
  <div style="min-width: 92px; text-align: right;">
    @if(!empty($qrDataUri))
      <img src="{{ $qrDataUri }}" alt="QR Code validação" style="width: 78px; height: 78px; border: 1px solid #e5e7eb; padding: 4px; background: #fff;">
    @endif
  </div>
</div>

