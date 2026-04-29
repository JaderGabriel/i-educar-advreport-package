<div class="ar-actions">
  <div class="ar-actions__group">
    <a href="{{ $route }}" class="btn ar-btn ar-btn--ghost">
      <span class="ar-btn__icon ar-btn__icon--clear" aria-hidden="true"></span>
      Limpar
    </a>
  </div>

  <div class="ar-actions__group">
    <button type="button" class="btn-green ar-btn ar-btn--secondary js-student-docs-emit">
      <span class="ar-btn__icon ar-btn__icon--pdf" aria-hidden="true"></span>
      Emitir PDF (final)
    </button>
    <button type="button" class="btn ar-btn ar-btn--ghost js-student-docs-help" title="Prévia (exemplo)" aria-label="Prévia (exemplo)">?</button>
  </div>
</div>

<div id="advancedReportsStudentDocsPreviewModal" class="ar-modal">
  <div class="ar-modal__dialog">
    <div class="ar-modal__header">
      <strong>Prévia (exemplo)</strong>
      <button type="button" class="btn js-student-docs-preview-close">Fechar</button>
    </div>
    <iframe class="js-student-docs-preview-iframe ar-modal__iframe"></iframe>
  </div>
</div>

@include('advanced-reports::partials._emit-error-modal', [
  'modalId' => 'advancedReportsStudentDocsErrorModal',
  'closeClass' => 'js-student-docs-error-close',
  'textClass' => 'js-student-docs-error-text',
])

