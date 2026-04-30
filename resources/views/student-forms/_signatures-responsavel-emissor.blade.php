{{-- Duas assinaturas na mesma linha: responsável (esq.) e emissor (dir.), padrão de tabela como demais documentos do pacote. --}}
@php($respNome = $responsavelExibicao ?? null)
<div style="margin-top: 10px; margin-bottom: 4px;">
  <table style="width: 100%; border-collapse: collapse;">
    <tr>
      <td style="width: 48%; text-align: center; vertical-align: top; border: 0; padding: 0 10px;">
        <div style="border-top: 1px solid #111827; padding-top: 8px;">
          <strong>Pai/mãe/responsável legal</strong>
          @if(!empty($respNome))
            <div style="margin-top: 4px; font-size: 11px; color: #111827;">{{ $respNome }}</div>
          @endif
          <div class="muted" style="margin-top: 4px; font-size: 9px;">Assinatura</div>
        </div>
      </td>
      <td style="width: 48%; text-align: center; vertical-align: top; border: 0; padding: 0 10px;">
        <div style="border-top: 1px solid #111827; padding-top: 8px;">
          <strong>Emissor do documento</strong>
          @if(!empty($issuerName))
            <div style="margin-top: 4px; font-size: 11px; color: #111827;">{{ $issuerName }}</div>
          @endif
          @if(!empty($schoolInep))
            <div class="muted" style="margin-top: 4px; font-size: 9px;">INEP da escola: {{ $schoolInep }}</div>
          @endif
          <div class="muted" style="margin-top: 4px; font-size: 9px;">Assinatura e carimbo (quando couber)</div>
        </div>
      </td>
    </tr>
  </table>
</div>
