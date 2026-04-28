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
      'route' => route('advanced-reports.diplomas.index'),
      'cursos' => $cursos,
      'cursoId' => $cursoId ?? null,
      'withGrade' => false,
      'withSchoolClass' => false,
      'withCharts' => false,
      'extraRowsView' => 'advanced-reports::diplomas._extra-filters-rows',
      'actionsView' => 'advanced-reports::diplomas._actions',
      'explainTitle' => 'Diplomas/Certificados/Declarações (modelos)',
      'explainText' => 'Escolha ano/instituição/escola/curso (para buscar dados oficiais) e selecione apenas Documento/Tipo/Lado. A prévia é um exemplo (dados fictícios).',
  ])
@endsection

@push('scripts')
    <script>
        (function () {
          const form = document.getElementById('formcadastro');
          const modal = document.getElementById('advancedReportsDiplomasPreviewModal');
          const iframe = document.querySelector('.js-diplomas-preview-iframe');
          const closeBtn = document.querySelector('.js-diplomas-preview-close');
          const helpBtn = document.querySelector('.js-diplomas-help');
          const emitBtn = document.querySelector('.js-diplomas-emit');
          if (!form || !modal || !iframe) return;

          function previewUrl() {
            const params = new URLSearchParams(new FormData(form));
            params.set('preview', '1');
            return "{{ route('advanced-reports.diplomas.pdf') }}" + "?" + params.toString();
          }

          function emitUrl() {
            const params = new URLSearchParams(new FormData(form));
            params.delete('preview');
            return "{{ route('advanced-reports.diplomas.pdf') }}" + "?" + params.toString();
          }

          function openModal() {
            iframe.src = previewUrl();
            modal.style.display = 'block';
          }

          function closeModal() {
            iframe.src = 'about:blank';
            modal.style.display = 'none';
          }

          if (helpBtn) helpBtn.addEventListener('click', function (e) { e.preventDefault(); openModal(); });
          if (closeBtn) closeBtn.addEventListener('click', function (e) { e.preventDefault(); closeModal(); });
          modal.addEventListener('click', function (e) { if (e.target === modal) closeModal(); });

          if (emitBtn) emitBtn.addEventListener('click', function (e) {
            e.preventDefault();
            window.open(emitUrl(), '_blank');
          });
        })();
    </script>
@endpush

