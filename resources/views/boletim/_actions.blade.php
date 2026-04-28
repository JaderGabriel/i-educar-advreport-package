<div class="ar-actions">
  <div class="ar-actions__group">
    <a href="{{ $route }}" class="btn ar-btn ar-btn--ghost">Limpar</a>
    <button type="submit" class="btn-green ar-btn ar-btn--primary" style="margin-left: 8px;">Filtrar</button>
  </div>

  <div class="ar-actions__group">
    <button type="button" class="btn ar-btn ar-btn--secondary js-boletim-preview-open">Prévia (PDF)</button>
    <button type="button" class="btn-green ar-btn ar-btn--secondary js-boletim-emit">Emitir PDF (final)</button>
  </div>
</div>

<div id="advancedReportsBoletimPreviewModal" class="ar-modal">
  <div class="ar-modal__dialog">
    <div class="ar-modal__header">
      <strong>Prévia (PDF)</strong>
      <button type="button" class="btn js-boletim-preview-close">Fechar</button>
    </div>
    <iframe class="js-boletim-preview-iframe ar-modal__iframe"></iframe>
  </div>
</div>

