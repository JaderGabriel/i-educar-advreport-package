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
      'explainTitle' => 'Pendências de lançamento (notas/frequência)',
      'explainText' => 'Use este relatório para identificar, por turma, quais matrículas ainda possuem pendências de lançamento de notas e/ou frequência por componente e etapa.',
      'explainDictionary' => 'Pendência de nota = ausência de nota lançada; Pendência de frequência = ausência de faltas lançadas conforme regra de presença.'
  ])

  @if(request('ref_cod_turma'))
    <div class="advanced-report-card" style="margin-top: 12px;">
      <strong class="advanced-report-card-title">Emissão</strong>
      <p class="advanced-report-card-text">Gere o relatório em PDF ou exporte em Excel.</p>

      <div class="ar-actions">
        <div class="ar-actions__group">
          <button type="button" class="btn ar-btn ar-btn--secondary js-pending-preview-open">
            <span class="ar-btn__icon" aria-hidden="true"></span>
            Prévia (PDF)
          </button>
        </div>
        <div class="ar-actions__group">
          <span class="ar-actions__label">Saída</span>
          <select class="geral ar-select js-export-type" style="width: 210px;"
                  data-pdf="{{ route('advanced-reports.pending-entries.pdf', request()->all()) }}"
                  data-excel="{{ route('advanced-reports.pending-entries.excel', request()->all()) }}">
            <option value="pdf">PDF (prévia)</option>
            <option value="excel">Excel</option>
          </select>
          <button type="button" class="btn-green ar-btn ar-btn--secondary js-export-run">
            <span class="ar-btn__icon" aria-hidden="true"></span>
            Executar
          </button>
        </div>
      </div>
    </div>
  @endif
@endsection

@push('scripts')
  <script>
    (function () {
      const select = document.querySelector('.js-export-type');
      const btn = document.querySelector('.js-export-run');
      if (!select || !btn) return;
      btn.addEventListener('click', function () {
        const key = select.value === 'excel' ? 'excel' : 'pdf';
        const url = key === 'excel' ? select.dataset.excel : select.dataset.pdf;
        if (!url) return;
        if (key === 'excel') {
          window.open(url, '_blank');
          return;
        }
        const modal = document.getElementById('advancedReportsPendingPreviewModal');
        const iframe = document.querySelector('.js-pending-preview-iframe');
        if (!modal || !iframe) return;
        iframe.src = url;
        modal.style.display = 'block';
      });

      const previewOpen = document.querySelector('.js-pending-preview-open');
      const previewClose = document.querySelector('.js-pending-preview-close');
      const modal = document.getElementById('advancedReportsPendingPreviewModal');
      const iframe = document.querySelector('.js-pending-preview-iframe');
      if (previewOpen && previewClose && modal && iframe) {
        function closeModal() {
          iframe.src = 'about:blank';
          modal.style.display = 'none';
        }
        previewOpen.addEventListener('click', function (e) {
          e.preventDefault();
          const url = select ? select.dataset.pdf : null;
          if (!url) return;
          iframe.src = url;
          modal.style.display = 'block';
        });
        previewClose.addEventListener('click', function (e) { e.preventDefault(); closeModal(); });
        modal.addEventListener('click', function (e) { if (e.target === modal) closeModal(); });
      }
    })();
  </script>
@endpush

<div id="advancedReportsPendingPreviewModal" class="ar-modal">
  <div class="ar-modal__dialog">
    <div class="ar-modal__header">
      <strong>Prévia (PDF)</strong>
      <button type="button" class="btn js-pending-preview-close">Fechar</button>
    </div>
    <iframe class="js-pending-preview-iframe ar-modal__iframe"></iframe>
  </div>
</div>

