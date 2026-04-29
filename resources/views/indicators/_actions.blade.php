@php($arClearRoute = $route ?? url()->current())
<div class="ar-actions">
  <div class="ar-actions__group">
    <a href="{{ $arClearRoute }}" class="btn ar-btn ar-btn--ghost">Limpar</a>
  </div>
  <div class="ar-actions__group">
    <button type="button" class="btn-green ar-btn ar-btn--secondary" disabled>Emitir (em breve)</button>
    <button type="button" class="btn ar-btn ar-btn--ghost" title="Prévia (exemplo)" aria-label="Prévia (exemplo)">?</button>
  </div>
</div>

