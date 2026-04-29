@if(!empty($issuerName))
  <div style="margin-top: 14px; margin-bottom: 20px; text-align: center;">
    <div style="border-top: 1px solid #111827; width: 320px; padding-top: 4px; margin: 0 auto;">
      <strong style="display:block;">{{ $issuerName }}</strong>
    </div>
    @if(!empty($schoolInep))
      <div class="muted" style="font-size: 10px; margin-top: 2px;">
        INEP (escola): {{ $schoolInep }}
      </div>
    @endif
  </div>
@endif

