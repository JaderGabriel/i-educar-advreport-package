@php($arClearRoute = $route ?? route('advanced-reports.vacancies.index'))
<div class="ar-actions">
  <div class="ar-actions__group">
    <a href="{{ $arClearRoute }}" class="btn ar-btn ar-btn--ghost">
      <span class="ar-btn__icon ar-btn__icon--clear" aria-hidden="true"></span>
      Limpar
    </a>
    <button type="submit" form="formcadastro" class="btn-green ar-btn ar-btn--primary">
      <span class="ar-btn__icon" aria-hidden="true"></span>
      Aplicar filtros
    </button>
  </div>

  <div class="ar-actions__group">
    <button type="button" class="btn-green ar-btn ar-btn--secondary js-vacancies-emit-pdf">
      <span class="ar-btn__icon ar-btn__icon--pdf" aria-hidden="true"></span>
      Emitir PDF (final)
    </button>
    <button type="button" class="btn ar-btn ar-btn--ghost js-vacancies-help" title="Ver prévia (exemplo)" aria-label="Ver prévia (exemplo)">?</button>
    <button type="button" class="btn ar-btn ar-btn--secondary js-vacancies-excel">
      <span class="ar-btn__icon" aria-hidden="true"></span>
      Exportar Excel
    </button>
  </div>
</div>

@include('advanced-reports::partials._emit-error-modal', [
  'modalId' => 'advancedReportsVacanciesErrorModal',
  'closeClass' => 'js-vacancies-error-close',
  'textClass' => 'js-vacancies-error-text',
])
