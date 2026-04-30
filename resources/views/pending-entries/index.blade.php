@extends('layout.default')

@push('styles')
  @if (class_exists('Asset'))
    <link rel="stylesheet" type="text/css" href="{{ Asset::get('css/ieducar.css') }}"/>
    <link rel="stylesheet" type="text/css" href="{{ Asset::get('css/advanced-reports.css') }}"/>
  @else
    <link rel="stylesheet" type="text/css" href="{{ asset('css/advanced-reports.css') }}"/>
  @endif
@endpush

@section('content')
  @include('advanced-reports::partials.filters', [
      'route' => route('advanced-reports.pending-entries.index'),
      'cursos' => $cursos,
      'cursoId' => $cursoId ?? null,
      'withCharts' => false,
      'withGrade' => true,
      'withSchoolClass' => true,
      'extraRowsView' => 'advanced-reports::pending-entries._extra-filters-rows',
      'actionsView' => 'advanced-reports::pending-entries._actions',
      'explainTitle' => 'Pendências de lançamento (notas/frequência)',
      'explainText' => 'Use este relatório para identificar, por turma, quais matrículas ainda possuem pendências de lançamento de notas e/ou frequência por componente e etapa. Selecione a turma e use «?» para prévia ilustrativa.',
      'explainDictionary' => 'Pendência de nota = ausência de nota lançada; Pendência de frequência = ausência de faltas lançadas conforme regra de presença.'
  ])

  <div id="advancedReportsPendingPreviewModal" class="ar-modal">
    <div class="ar-modal__dialog">
      <div class="ar-modal__header">
        <strong>Prévia (exemplo)</strong>
        <button type="button" class="btn js-pending-preview-close">Fechar</button>
      </div>
      <div class="js-pending-preview-pdf ar-modal__iframe ar-modal__pdfCanvasRoot" role="region" aria-label="Prévia do PDF"></div>
    </div>
  </div>

  @include('advanced-reports::partials._pdf_preview_runtime')
@endsection

@push('scripts')
  <script>
    (function () {
      const form = document.getElementById('formcadastro');
      const pdfBase = "{{ route('advanced-reports.pending-entries.pdf') }}";
      const excelBase = "{{ route('advanced-reports.pending-entries.excel') }}";

      function buildQuery() {
        if (!form) return '';
        return new URLSearchParams(new FormData(form)).toString();
      }

      const modal = document.getElementById('advancedReportsPendingPreviewModal');
      const pdfRoot = modal ? modal.querySelector('.js-pending-preview-pdf') : null;
      const closeBtn = document.querySelector('.js-pending-preview-close');
      const errorModal = document.getElementById('advancedReportsPendingErrorModal');
      const errorText = document.querySelector('.js-pending-error-text');
      const errorClose = document.querySelector('.js-pending-error-close');

      function openError(message) {
        if (!errorModal || !errorText) {
          window.alert(message);
          return;
        }
        errorText.textContent = message;
        errorModal.style.display = 'block';
      }
      function closeError() {
        if (!errorModal) return;
        errorModal.style.display = 'none';
      }
      if (errorClose) errorClose.addEventListener('click', function (e) { e.preventDefault(); closeError(); });
      if (errorModal) errorModal.addEventListener('click', function (e) { if (e.target === errorModal) closeError(); });

      function requiredMessage() {
        const ano = document.getElementById('ano');
        const inst = document.getElementById('ref_cod_instituicao');
        const escola = document.getElementById('ref_cod_escola');
        const curso = document.getElementById('ref_cod_curso');
        const serie = document.getElementById('ref_cod_serie');
        const turma = document.getElementById('ref_cod_turma');
        if (!ano || !ano.value) return 'Informe o ano letivo.';
        if (!inst || !inst.value) return 'Informe a instituição.';
        if (!escola || !escola.value) return 'Informe a escola.';
        if (!curso || !curso.value) return 'Informe o curso.';
        if (!serie || !serie.value) return 'Informe a série.';
        if (!turma || !turma.value) return 'Informe a turma.';
        return null;
      }

      function closeModal() {
        if (!pdfRoot || !modal) return;
        if (window.AdvancedReportsPdfPreview) {
          window.AdvancedReportsPdfPreview.close(pdfRoot);
        }
        modal.style.display = 'none';
      }

      const emitPdf = document.querySelector('.js-pending-emit-pdf');
      if (emitPdf) {
        emitPdf.addEventListener('click', function (e) {
          e.preventDefault();
          const msg = requiredMessage();
          if (msg) {
            openError(msg);
            return;
          }
          const q = buildQuery();
          if (!q) return;
          window.open(pdfBase + '?' + q, '_blank');
        });
      }

      const excelBtn = document.querySelector('.js-pending-excel');
      if (excelBtn) {
        excelBtn.addEventListener('click', function (e) {
          e.preventDefault();
          const msg = requiredMessage();
          if (msg) {
            openError(msg);
            return;
          }
          const q = buildQuery();
          if (!q) return;
          window.open(excelBase + '?' + q, '_blank');
        });
      }

      const helpBtn = document.querySelector('.js-pending-help');
      if (helpBtn && form && modal && pdfRoot) {
        helpBtn.addEventListener('click', function (e) {
          e.preventDefault();
          const msg = requiredMessage();
          if (msg) {
            openError(msg);
            return;
          }
          const params = new URLSearchParams(new FormData(form));
          params.set('preview', '1');
          const url = pdfBase + '?' + params.toString();
          modal.style.display = 'block';
          if (window.AdvancedReportsPdfPreview) {
            window.AdvancedReportsPdfPreview.open(pdfRoot, url);
          }
        });
      }

      if (closeBtn) closeBtn.addEventListener('click', function (e) { e.preventDefault(); closeModal(); });
      if (modal) modal.addEventListener('click', function (e) { if (e.target === modal) closeModal(); });
    })();
  </script>
@endpush
