@if(!empty($issuerName))
  {{-- Espaço mínimo ~1 cm entre o texto do documento e a linha de assinatura do emissor --}}
  <div style="margin-top: 1cm; margin-bottom: 20px; text-align: center;">
    <div style="border-top: 1px solid #111827; width: 320px; padding-top: 6px; margin: 0 auto;">
      <strong style="display:block;">{{ $issuerName }}</strong>
    </div>
    @if(!empty($schoolInep))
      <div class="muted" style="font-size: 10px; margin-top: 2px;">
        INEP (escola): {{ $schoolInep }}
      </div>
    @endif
  </div>
@endif

