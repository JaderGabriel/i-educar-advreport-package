<div class="ar-actions">
  <div class="ar-actions__group">
    <a href="{{ $route }}" class="btn ar-btn ar-btn--ghost">Limpar</a>
  </div>

  <div class="ar-actions__group">
    <button type="button" class="btn-green ar-btn ar-btn--secondary js-boletim-emit">Emitir PDF (final)</button>
    <button type="button" class="btn ar-btn ar-btn--ghost js-boletim-help" title="Ver prévia (exemplo)" aria-label="Ver prévia (exemplo)">?</button>
  </div>
</div>

<div id="advancedReportsBoletimPreviewModal" class="ar-modal">
  <div class="ar-modal__dialog">
    <div class="ar-modal__header">
      <strong>Prévia (exemplo)</strong>
      <button type="button" class="btn js-boletim-preview-close">Fechar</button>
    </div>
    <iframe class="js-boletim-preview-iframe ar-modal__iframe"></iframe>
  </div>
</div>

