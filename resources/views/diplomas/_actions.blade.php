<div class="ar-actions">
  <div class="ar-actions__group">
    <a href="{{ $route }}" class="btn ar-btn ar-btn--ghost">
      <span class="ar-btn__icon ar-btn__icon--clear" aria-hidden="true"></span>
      Limpar
    </a>
  </div>

  <div class="ar-actions__group">
    <button type="button" class="btn-green ar-btn ar-btn--secondary js-diplomas-emit">
      <span class="ar-btn__icon ar-btn__icon--pdf" aria-hidden="true"></span>
      Emitir PDF (final)
    </button>
    <button type="button" class="btn ar-btn ar-btn--ghost js-diplomas-help" title="Prévia (exemplo)" aria-label="Prévia (exemplo)">?</button>
  </div>
</div>

<div id="advancedReportsDiplomasPreviewModal" class="ar-modal">
  <div class="ar-modal__dialog">
    <div class="ar-modal__header">
      <strong>Prévia (exemplo)</strong>
      <button type="button" class="btn js-diplomas-preview-close">Fechar</button>
    </div>
    <div class="js-diplomas-preview-pdf ar-modal__iframe ar-modal__pdfCanvasRoot" role="region" aria-label="Prévia do PDF"></div>
  </div>
</div>

@include('advanced-reports::partials._emit-error-modal', [
  'modalId' => 'advancedReportsDiplomasErrorModal',
  'closeClass' => 'js-diplomas-error-close',
  'textClass' => 'js-diplomas-error-text',
])

@include('advanced-reports::partials._pdf_preview_runtime')

