@php
  $pdfRoute = route('advanced-reports.communications.pdf', ['slug' => $slug ?? '']);
@endphp

<div class="ar-actions" id="communicationsActionsRoot" data-pdf-route="{{ $pdfRoute }}">
  <div class="ar-actions__group">
    <a href="{{ $route }}" class="btn ar-btn ar-btn--ghost">
      <span class="ar-btn__icon ar-btn__icon--clear" aria-hidden="true"></span>
      Limpar
    </a>
  </div>
  <div class="ar-actions__group">
    <button type="button" class="btn-green ar-btn ar-btn--secondary js-communications-emit">
      <span class="ar-btn__icon ar-btn__icon--pdf" aria-hidden="true"></span>
      Emitir PDF (final)
    </button>
    <button type="button" class="btn ar-btn ar-btn--ghost js-communications-preview" title="Prévia (exemplo)" aria-label="Prévia (exemplo)">?</button>
  </div>
</div>

<div id="communicationsPreviewModal" class="ar-modal">
  <div class="ar-modal__dialog">
    <div class="ar-modal__header">
      <strong>Prévia (exemplo)</strong>
      <button type="button" class="btn js-communications-preview-close">Fechar</button>
    </div>
    <div class="js-communications-preview-pdf ar-modal__iframe ar-modal__pdfCanvasRoot" role="region" aria-label="Prévia do PDF"></div>
  </div>
</div>

@include('advanced-reports::partials._emit-error-modal', [
  'modalId' => 'communicationsErrorModal',
  'closeClass' => 'js-communications-error-close',
  'textClass' => 'js-communications-error-text',
])

@include('advanced-reports::partials._pdf_preview_runtime')
