@php
  $modalId = $modalId ?? 'advancedReportsEmitErrorModal';
  $closeClass = $closeClass ?? 'js-ar-emit-error-close';
  $textClass = $textClass ?? 'js-ar-emit-error-text';
  $modalTitle = $modalTitle ?? 'Não foi possível emitir';
@endphp
<div id="{{ $modalId }}" class="ar-modal" style="display: none;" role="dialog" aria-modal="true" aria-labelledby="{{ $modalId }}_title">
  <div class="ar-modal__dialog">
    <div class="ar-modal__header">
      <strong id="{{ $modalId }}_title">{{ $modalTitle }}</strong>
      <button type="button" class="btn {{ $closeClass }}">Fechar</button>
    </div>
    <div style="padding: 12px 14px;">
      <p class="{{ $textClass }}" style="margin: 0;"></p>
    </div>
  </div>
</div>
