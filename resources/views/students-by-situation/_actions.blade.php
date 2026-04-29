@php($arClearRoute = $route ?? route('advanced-reports.students-by-situation.index'))
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
    <span class="ar-actions__label">Saída</span>
    <select class="geral ar-select js-export-type" style="width: 220px;"
            data-pdf="{{ route('advanced-reports.students-by-situation.pdf') . '?' . http_build_query(request()->all()) }}"
            data-excel="{{ route('advanced-reports.students-by-situation.excel') . '?' . http_build_query(request()->all()) }}">
      <option value="pdf">PDF (final)</option>
      <option value="excel">Excel</option>
    </select>
    <button type="button" class="btn-green ar-btn ar-btn--secondary js-export-run">
      <span class="ar-btn__icon ar-btn__icon--pdf" aria-hidden="true"></span>
      Emitir
    </button>
  </div>
</div>
