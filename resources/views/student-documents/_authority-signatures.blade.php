{{-- Assinaturas institucionais (Secretário e Diretor), usadas em transferências e pacotes de conclusão. --}}
@php($authorities = $authorities ?? [])
@php($sec = $authorities['secretary'] ?? [])
@php($dir = $authorities['director'] ?? [])
<div style="margin-top: 1.1cm; margin-bottom: 8px;">
  <table style="width: 100%; border-collapse: collapse;">
    <tr>
      <td style="width: 48%; text-align: center; vertical-align: top; border: 0; padding: 0 10px;">
        <div style="border-top: 1px solid #111827; padding-top: 8px;">
          <strong>Secretário(a) Escolar</strong>
          @if(!empty($sec['name']))
            <div style="margin-top: 2px; font-size: 11px; color: #111827;">{{ $sec['name'] }}</div>
          @endif
          @if(!empty($sec['inep']) || !empty($sec['matricula_interna']))
            <div class="muted" style="margin-top: 2px; font-size: 9px;">
              @if(!empty($sec['inep']))INEP: {{ $sec['inep'] }}@endif
              @if(!empty($sec['inep']) && !empty($sec['matricula_interna'])) • @endif
              @if(!empty($sec['matricula_interna']))Matrícula interna: {{ $sec['matricula_interna'] }}@endif
            </div>
          @endif
        </div>
      </td>
      <td style="width: 48%; text-align: center; vertical-align: top; border: 0; padding: 0 10px;">
        <div style="border-top: 1px solid #111827; padding-top: 8px;">
          <strong>Diretor(a)</strong>
          @if(!empty($dir['name']))
            <div style="margin-top: 2px; font-size: 11px; color: #111827;">{{ $dir['name'] }}</div>
          @endif
          @if(!empty($dir['inep']) || !empty($dir['matricula_interna']))
            <div class="muted" style="margin-top: 2px; font-size: 9px;">
              @if(!empty($dir['inep']))INEP: {{ $dir['inep'] }}@endif
              @if(!empty($dir['inep']) && !empty($dir['matricula_interna'])) • @endif
              @if(!empty($dir['matricula_interna']))Matrícula interna: {{ $dir['matricula_interna'] }}@endif
            </div>
          @endif
        </div>
      </td>
    </tr>
  </table>
</div>
