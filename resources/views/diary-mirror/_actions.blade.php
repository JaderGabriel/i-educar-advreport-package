<div class="ar-actions">
  <div class="ar-actions__group">
    <a href="{{ $route }}" class="btn ar-btn ar-btn--ghost">
      <span class="ar-btn__icon ar-btn__icon--clear" aria-hidden="true"></span>
      Limpar
    </a>
  </div>

  <div class="ar-actions__group">
    <button type="button" class="btn-green ar-btn ar-btn--secondary js-diary-mirror-emit">
      <span class="ar-btn__icon ar-btn__icon--pdf" aria-hidden="true"></span>
      Emitir PDF (final)
    </button>
    <button type="button" class="btn ar-btn ar-btn--ghost js-diary-mirror-help" title="Prévia (exemplo)" aria-label="Prévia (exemplo)">?</button>
  </div>
</div>

@include('advanced-reports::partials._emit-error-modal', [
  'modalId' => 'advancedReportsDiaryMirrorErrorModal',
  'closeClass' => 'js-diary-mirror-error-close',
  'textClass' => 'js-diary-mirror-error-text',
])

