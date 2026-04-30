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
      'route' => route('advanced-reports.vacancies.index'),
      'cursos' => $cursos,
      'cursoId' => $cursoId ?? null,
      'withGrade' => true,
      'withSchoolClass' => true,
      'withCharts' => false,
      'actionsView' => 'advanced-reports::vacancies._actions',
      'explainTitle' => 'Vagas por turma',
      'explainText' => 'Selecione ano e escola (obrigatórios) e refine por curso/série/turma. Use «?» para prévia ilustrativa; emissão final gera PDF com validação.',
      'explainDictionary' => 'Capacidade = max_aluno da turma; Matriculados = enturmações ativas (matricula_turma.ativo=1) desconsiderando matrícula de dependência; Vagas = max( cap - matriculados, 0).'
  ])

  <div id="advancedReportsVacanciesPreviewModal" class="ar-modal">
    <div class="ar-modal__dialog">
      <div class="ar-modal__header">
        <strong>Prévia (exemplo)</strong>
        <button type="button" class="btn js-vacancies-preview-close">Fechar</button>
      </div>
      <div class="js-vacancies-preview-pdf ar-modal__iframe ar-modal__pdfCanvasRoot" role="region" aria-label="Prévia do PDF"></div>
    </div>
  </div>

  @include('advanced-reports::partials._pdf_preview_runtime')
@endsection

@push('scripts')
  <script>
    (function () {
      const form = document.getElementById('formcadastro');
      const pdfBase = "{{ route('advanced-reports.vacancies.pdf') }}";
      const excelBase = "{{ route('advanced-reports.vacancies.excel') }}";

      function buildQuery() {
        if (!form) return '';
        return new URLSearchParams(new FormData(form)).toString();
      }

      const errorModal = document.getElementById('advancedReportsVacanciesErrorModal');
      const errorText = document.querySelector('.js-vacancies-error-text');
      const errorClose = document.querySelector('.js-vacancies-error-close');

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

      function requiredVacanciesMessage() {
        const ano = document.getElementById('ano');
        const escola = document.getElementById('ref_cod_escola');
        if (!ano || !String(ano.value || '').trim()) return 'Informe o ano letivo.';
        if (!escola || !String(escola.value || '').trim()) return 'Informe a escola.';
        return null;
      }

      function openPreview() {
        const modal = document.getElementById('advancedReportsVacanciesPreviewModal');
        const pdfRoot = modal ? modal.querySelector('.js-vacancies-preview-pdf') : null;
        if (!modal || !pdfRoot || !form) return;
        const msg = requiredVacanciesMessage();
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
      }

      function closePreview() {
        const modal = document.getElementById('advancedReportsVacanciesPreviewModal');
        const pdfRoot = modal ? modal.querySelector('.js-vacancies-preview-pdf') : null;
        if (!modal || !pdfRoot) return;
        if (window.AdvancedReportsPdfPreview) {
          window.AdvancedReportsPdfPreview.close(pdfRoot);
        }
        modal.style.display = 'none';
      }

      const emitPdf = document.querySelector('.js-vacancies-emit-pdf');
      if (emitPdf) {
        emitPdf.addEventListener('click', function (e) {
          e.preventDefault();
          const msg = requiredVacanciesMessage();
          if (msg) {
            openError(msg);
            return;
          }
          const q = buildQuery();
          if (!q) return;
          window.open(pdfBase + '?' + q, '_blank');
        });
      }

      const emitXls = document.querySelector('.js-vacancies-excel');
      if (emitXls) {
        emitXls.addEventListener('click', function (e) {
          e.preventDefault();
          const msg = requiredVacanciesMessage();
          if (msg) {
            openError(msg);
            return;
          }
          const q = buildQuery();
          if (!q) return;
          window.open(excelBase + '?' + q, '_blank');
        });
      }

      const help = document.querySelector('.js-vacancies-help');
      const closeBtn = document.querySelector('.js-vacancies-preview-close');
      const modal = document.getElementById('advancedReportsVacanciesPreviewModal');
      if (help) help.addEventListener('click', function (e) { e.preventDefault(); openPreview(); });
      if (closeBtn) closeBtn.addEventListener('click', function (e) { e.preventDefault(); closePreview(); });
      if (modal) modal.addEventListener('click', function (e) { if (e.target === modal) closePreview(); });
    })();
  </script>
@endpush
