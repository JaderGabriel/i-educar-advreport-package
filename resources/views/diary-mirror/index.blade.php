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
      'route' => route('advanced-reports.diary-mirror.index'),
      'cursos' => $cursos,
      'cursoId' => $cursoId ?? null,
      'withDates' => true,
      'withCharts' => false,
      'withGrade' => true,
      'withSchoolClass' => true,
      'requireSchool' => true,
      'requireCourse' => true,
      'requireSerie' => true,
      'requireTurma' => true,
      'actionsView' => 'advanced-reports::diary-mirror._actions',
      'explainTitle' => 'Espelho de diário (chamada)',
      'explainText' => 'Modelo de impressão do diário/chamada por turma e período. No preenchimento manual: presença = “•” e falta = “F”.',
      'explainDictionary' => 'Período (datas) = intervalo de dias exibidos no espelho. A grade é gerada com alunos ativos na turma e colunas para cada dia.',
  ])

  <div id="advancedReportsDiaryMirrorPreviewModal" class="ar-modal">
    <div class="ar-modal__dialog">
      <div class="ar-modal__header">
        <strong>Prévia (exemplo)</strong>
        <button type="button" class="btn js-diary-mirror-preview-close">Fechar</button>
      </div>
      <div class="js-diary-mirror-preview-pdf ar-modal__iframe ar-modal__pdfCanvasRoot" role="region" aria-label="Prévia do PDF"></div>
    </div>
  </div>

  @include('advanced-reports::partials._pdf_preview_runtime')
@endsection

@push('scripts')
  <script>
    (function () {
      const form = document.getElementById('formcadastro');
      const pdfBase = "{{ route('advanced-reports.diary-mirror.pdf') }}";

      function buildQuery() {
        if (!form) return '';
        return new URLSearchParams(new FormData(form)).toString();
      }

      function requiredMessage() {
        const ano = document.getElementById('ano');
        const inst = document.getElementById('ref_cod_instituicao');
        const escola = document.getElementById('ref_cod_escola');
        const curso = document.getElementById('ref_cod_curso');
        const serie = document.getElementById('ref_cod_serie');
        const turma = document.getElementById('ref_cod_turma');
        const di = document.querySelector('input[name=\"data_inicial\"]');
        const df = document.querySelector('input[name=\"data_final\"]');
        if (!ano || !ano.value) return 'Informe o ano letivo.';
        if (!inst || !inst.value) return 'Informe a instituição.';
        if (!escola || !escola.value) return 'Informe a escola.';
        if (!curso || !curso.value) return 'Informe o curso.';
        if (!serie || !serie.value) return 'Informe a série.';
        if (!turma || !turma.value) return 'Informe a turma.';
        if (!di || !di.value) return 'Informe a data inicial.';
        if (!df || !df.value) return 'Informe a data final.';
        return null;
      }

      const modal = document.getElementById('advancedReportsDiaryMirrorPreviewModal');
      const pdfRoot = modal ? modal.querySelector('.js-diary-mirror-preview-pdf') : null;
      const closeBtn = document.querySelector('.js-diary-mirror-preview-close');
      const errorModal = document.getElementById('advancedReportsDiaryMirrorErrorModal');
      const errorText = document.querySelector('.js-diary-mirror-error-text');
      const errorClose = document.querySelector('.js-diary-mirror-error-close');

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

      function closeModal() {
        if (!pdfRoot || !modal) return;
        if (window.AdvancedReportsPdfPreview) {
          window.AdvancedReportsPdfPreview.close(pdfRoot);
        }
        modal.style.display = 'none';
      }

      const emitBtn = document.querySelector('.js-diary-mirror-emit');
      if (emitBtn) {
        emitBtn.addEventListener('click', function (e) {
          e.preventDefault();
          const msg = requiredMessage();
          if (msg) return openError(msg);
          const q = buildQuery();
          if (!q) return;
          window.open(pdfBase + '?' + q, '_blank');
        });
      }

      const helpBtn = document.querySelector('.js-diary-mirror-help');
      if (helpBtn && form && modal && pdfRoot) {
        helpBtn.addEventListener('click', function (e) {
          e.preventDefault();
          const msg = requiredMessage();
          if (msg) return openError(msg);
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

