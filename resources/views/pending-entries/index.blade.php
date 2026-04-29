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
      <iframe class="js-pending-preview-iframe ar-modal__iframe"></iframe>
    </div>
  </div>
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
      const iframe = document.querySelector('.js-pending-preview-iframe');
      const closeBtn = document.querySelector('.js-pending-preview-close');

      function closeModal() {
        if (!iframe || !modal) return;
        iframe.src = 'about:blank';
        modal.style.display = 'none';
      }

      const emitPdf = document.querySelector('.js-pending-emit-pdf');
      if (emitPdf) {
        emitPdf.addEventListener('click', function (e) {
          e.preventDefault();
          const q = buildQuery();
          if (!q) return;
          window.open(pdfBase + '?' + q, '_blank');
        });
      }

      const excelBtn = document.querySelector('.js-pending-excel');
      if (excelBtn) {
        excelBtn.addEventListener('click', function (e) {
          e.preventDefault();
          const q = buildQuery();
          if (!q) return;
          window.open(excelBase + '?' + q, '_blank');
        });
      }

      const helpBtn = document.querySelector('.js-pending-help');
      if (helpBtn && form && modal && iframe) {
        helpBtn.addEventListener('click', function (e) {
          e.preventDefault();
          const params = new URLSearchParams(new FormData(form));
          params.set('preview', '1');
          iframe.src = pdfBase + '?' + params.toString();
          modal.style.display = 'block';
        });
      }

      if (closeBtn) closeBtn.addEventListener('click', function (e) { e.preventDefault(); closeModal(); });
      if (modal) modal.addEventListener('click', function (e) { if (e.target === modal) closeModal(); });
    })();
  </script>
@endpush
