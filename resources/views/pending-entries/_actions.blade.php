@php($arClearRoute = $route ?? route('advanced-reports.pending-entries.index'))
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
  @if(request('ref_cod_turma'))
    <div class="ar-actions__group">
      <button type="button" class="btn-green ar-btn ar-btn--secondary js-pending-emit-pdf">
        <span class="ar-btn__icon ar-btn__icon--pdf" aria-hidden="true"></span>
        Emitir PDF (final)
      </button>
      <button type="button" class="btn ar-btn ar-btn--ghost js-pending-help" title="Ver prévia (exemplo)" aria-label="Ver prévia (exemplo)">?</button>
      <button type="button" class="btn ar-btn ar-btn--secondary js-pending-excel">
        <span class="ar-btn__icon" aria-hidden="true"></span>
        Exportar Excel
      </button>
    </div>
  @endif
</div>

@include('advanced-reports::partials._emit-error-modal', [
  'modalId' => 'advancedReportsPendingErrorModal',
  'closeClass' => 'js-pending-error-close',
  'textClass' => 'js-pending-error-text',
])
