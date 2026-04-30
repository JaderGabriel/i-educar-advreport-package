@php
  $pdfRoute = match ($type ?? '') {
    'individual' => route('advanced-reports.student-forms.individual.pdf'),
    'media_authorization' => route('advanced-reports.student-forms.media-authorization.pdf'),
    default => route('advanced-reports.student-forms.enrollment.pdf'),
  };
@endphp

<div class="ar-actions" id="studentFormsActionsRoot" data-pdf-route="{{ $pdfRoute }}">
  <div class="ar-actions__group">
    <a href="{{ $route }}" class="btn ar-btn ar-btn--ghost">
      <span class="ar-btn__icon ar-btn__icon--clear" aria-hidden="true"></span>
      Limpar
    </a>
  </div>

  <div class="ar-actions__group">
    <button type="button" class="btn-green ar-btn ar-btn--secondary js-student-forms-emit">
      <span class="ar-btn__icon ar-btn__icon--pdf" aria-hidden="true"></span>
      Emitir PDF (final)
    </button>
    <button type="button" class="btn ar-btn ar-btn--ghost js-student-forms-preview" title="Prévia (exemplo)" aria-label="Prévia (exemplo)">?</button>
  </div>
</div>

{{-- Modal de prévia (reusa o preview renderer do pacote) --}}
<div id="advancedReportsStudentFormsPreviewModal" class="ar-modal">
  <div class="ar-modal__dialog">
    <div class="ar-modal__header">
      <strong>Prévia (exemplo)</strong>
      <button type="button" class="btn js-student-forms-preview-close">Fechar</button>
    </div>
    <div class="js-student-forms-preview-pdf ar-modal__iframe ar-modal__pdfCanvasRoot" role="region" aria-label="Prévia do PDF"></div>
  </div>
</div>

@include('advanced-reports::partials._emit-error-modal', [
  'modalId' => 'advancedReportsStudentFormsErrorModal',
  'closeClass' => 'js-student-forms-error-close',
  'textClass' => 'js-student-forms-error-text',
])

@include('advanced-reports::partials._pdf_preview_runtime')

