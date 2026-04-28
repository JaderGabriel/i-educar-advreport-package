<div class="ar-actions">
  <div class="ar-actions__group">
    <a href="{{ $route }}" class="btn ar-btn ar-btn--ghost">
      <span class="ar-btn__icon" aria-hidden="true"></span>
      Limpar
    </a>
    <button type="submit" class="btn-green ar-btn ar-btn--primary">
      <span class="ar-btn__icon" aria-hidden="true"></span>
      Filtrar
    </button>
  </div>

  <div class="ar-actions__group">
    <button type="button" class="btn ar-btn ar-btn--secondary js-student-docs-preview-open">
      <span class="ar-btn__icon" aria-hidden="true"></span>
      Prévia (PDF)
    </button>
    <button type="button" class="btn-green ar-btn ar-btn--secondary js-student-docs-emit">
      <span class="ar-btn__icon" aria-hidden="true"></span>
      Emitir PDF (final)
    </button>
  </div>
</div>

<div id="advancedReportsStudentDocsPreviewModal" class="ar-modal">
  <div class="ar-modal__dialog">
    <div class="ar-modal__header">
      <strong>Prévia (PDF)</strong>
      <button type="button" class="btn js-student-docs-preview-close">Fechar</button>
    </div>
    <iframe class="js-student-docs-preview-iframe ar-modal__iframe"></iframe>
  </div>
</div>

