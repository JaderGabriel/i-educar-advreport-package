@php($arClearRoute = $route ?? route('advanced-reports.pending-entries.index'))
<div class="ar-actions">
  <div class="ar-actions__group">
    <a href="{{ $arClearRoute }}" class="btn ar-btn ar-btn--ghost">Limpar</a>
    <button type="submit" form="formcadastro" class="btn-green ar-btn ar-btn--primary">Aplicar filtros</button>
  </div>
  @if(request('ref_cod_turma'))
    <div class="ar-actions__group">
      <button type="button" class="btn-green ar-btn ar-btn--secondary js-pending-emit-pdf">Emitir PDF (final)</button>
      <button type="button" class="btn ar-btn ar-btn--ghost js-pending-help" title="Ver prévia (exemplo)" aria-label="Ver prévia (exemplo)">?</button>
      <button type="button" class="btn ar-btn ar-btn--secondary js-pending-excel">Exportar Excel</button>
    </div>
  @endif
</div>
