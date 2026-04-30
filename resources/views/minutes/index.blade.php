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
      'route' => route('advanced-reports.minutes.index'),
      'cursos' => $cursos,
      'cursoId' => $cursoId ?? null,
      'withGrade' => true,
      'withSchoolClass' => true,
      'requireSchool' => true,
      'requireCourse' => true,
      'requireSerie' => true,
      'requireTurma' => true,
      'withCharts' => false,
      'extraRowsView' => 'advanced-reports::minutes._extra-filters-rows',
      'actionsView' => 'advanced-reports::minutes._actions',
      'explainTitle' => 'Atas escolares (PDF)',
      'explainText' => 'Emissão de atas em PDF com validação pública (QR Code). Selecione o documento e informe ano, instituição, escola, curso, série e turma.',
  ])
@endsection

@push('scripts')
  <script>
    (function () {
      const form = document.getElementById('formcadastro');
      const modal = document.getElementById('advancedReportsMinutesPreviewModal');
      const pdfRoot = modal ? modal.querySelector('.js-minutes-preview-pdf') : null;
      const closeBtn = document.querySelector('.js-minutes-preview-close');
      const helpBtn = document.querySelector('.js-minutes-help');
      const emitBtn = document.querySelector('.js-minutes-emit');
      const errorModal = document.getElementById('advancedReportsMinutesErrorModal');
      const errorText = document.querySelector('.js-minutes-error-text');
      const errorClose = document.querySelector('.js-minutes-error-close');
      if (!form || !modal || !pdfRoot || !closeBtn) return;

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

      function buildPdfUrl() {
        const params = new URLSearchParams(new FormData(form));
        params.delete('preview');
        params.delete('preview[]');
        return "{{ route('advanced-reports.minutes.pdf') }}" + "?" + params.toString();
      }

      function closeModal() {
        if (window.AdvancedReportsPdfPreview) {
          window.AdvancedReportsPdfPreview.close(pdfRoot);
        }
        modal.style.display = 'none';
      }

      if (helpBtn) {
        helpBtn.addEventListener('click', function (e) {
          e.preventDefault();
          const msg = requiredMessage();
          if (msg) {
            openError(msg);
            return;
          }
          const params = new URLSearchParams(new FormData(form));
          params.set('preview', '1');
          const url = "{{ route('advanced-reports.minutes.pdf') }}" + "?" + params.toString();
          modal.style.display = 'block';
          if (window.AdvancedReportsPdfPreview) {
            window.AdvancedReportsPdfPreview.open(pdfRoot, url);
          }
        });
      }

      closeBtn.addEventListener('click', function (e) { e.preventDefault(); closeModal(); });
      modal.addEventListener('click', function (e) { if (e.target === modal) closeModal(); });

      if (emitBtn) {
        emitBtn.addEventListener('click', function (e) {
          e.preventDefault();
          const msg = requiredMessage();
          if (msg) {
            openError(msg);
            return;
          }
          window.open(buildPdfUrl(), '_blank');
        });
      }
    })();
  </script>
@endpush
