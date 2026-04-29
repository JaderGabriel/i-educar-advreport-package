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
      const iframe = document.querySelector('.js-minutes-preview-iframe');
      const closeBtn = document.querySelector('.js-minutes-preview-close');
      const helpBtn = document.querySelector('.js-minutes-help');
      const emitBtn = document.querySelector('.js-minutes-emit');
      if (!form || !modal || !iframe || !closeBtn) return;

      function buildPdfUrl() {
        const params = new URLSearchParams(new FormData(form));
        params.delete('preview');
        params.delete('preview[]');
        return "{{ route('advanced-reports.minutes.pdf') }}" + "?" + params.toString();
      }

      function closeModal() {
        iframe.src = 'about:blank';
        modal.style.display = 'none';
      }

      if (helpBtn) {
        helpBtn.addEventListener('click', function (e) {
          e.preventDefault();
          const params = new URLSearchParams(new FormData(form));
          params.set('preview', '1');
          iframe.src = "{{ route('advanced-reports.minutes.pdf') }}" + "?" + params.toString();
          modal.style.display = 'block';
        });
      }

      closeBtn.addEventListener('click', function (e) { e.preventDefault(); closeModal(); });
      modal.addEventListener('click', function (e) { if (e.target === modal) closeModal(); });

      if (emitBtn) {
        emitBtn.addEventListener('click', function (e) {
          e.preventDefault();
          window.open(buildPdfUrl(), '_blank');
        });
      }
    })();
  </script>
@endpush
