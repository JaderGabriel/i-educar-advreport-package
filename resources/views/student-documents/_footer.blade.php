<div class="ar-official-footer" style="border-top: 1px dashed #cbd5e1; padding-top: 10px; font-size: 10px; color: #374151; background: #fff;">
  <table style="width:100%; border-collapse: collapse; margin:0;">
    <tr>
      <td style="vertical-align: top; padding: 0; border: 0;">
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

        @if(!empty($issuerName))
          <div style="margin-top: 10px;">
            <div style="border-top: 1px solid #94a3b8; width: 280px; padding-top: 4px;">
              {{ $issuerName }}
              @if(!empty($issuerRole))
                <span class="muted">— {{ $issuerRole }}</span>
              @endif
            </div>
          </div>
        @endif
      </td>

      <td style="vertical-align: top; padding: 0; border: 0; width: 96px; text-align: right;">
        @if(!empty($qrDataUri))
          <img src="{{ $qrDataUri }}" alt="QR Code validação" style="width: 78px; height: 78px; border: 1px solid #e5e7eb; padding: 4px; background: #fff;">
        @endif
      </td>
    </tr>
  </table>
</div>

