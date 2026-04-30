@if(!empty($issuerName))
  @php($variant = $variant ?? null)
  @if($variant === 'diploma')
    <div class="diploma-issuer" style="margin-top: 0.6cm; margin-bottom: 12px; text-align: center;">
      <div class="line" style="border-top: 1px solid #111827; width: 320px; padding-top: 6px; margin: 0 auto;">
        <strong style="display:block;">{{ $issuerName }}</strong>
        @include('advanced-reports::pdf._issuer-person-lines')
      </div>
      @if(!empty($schoolInep))
        <div class="inep" style="font-size: 10px; color: #6b7280; margin-top: 4px;">
          INEP (escola): {{ $schoolInep }}
        </div>
      @endif
    </div>
  @else
    {{-- Espaço mínimo ~1 cm entre o texto do documento e a linha de assinatura do emissor --}}
    <div style="margin-top: 1cm; margin-bottom: 20px; text-align: center;">
      <div style="border-top: 1px solid #111827; width: 320px; padding-top: 6px; margin: 0 auto;">
        <strong style="display:block;">{{ $issuerName }}</strong>
        @include('advanced-reports::pdf._issuer-person-lines')
      </div>
      @if(!empty($schoolInep))
        <div class="muted" style="font-size: 10px; margin-top: 6px;">
          INEP (escola): {{ $schoolInep }}
        </div>
      @endif
    </div>
  @endif
@endif
