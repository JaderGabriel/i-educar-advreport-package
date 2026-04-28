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

      <div style="display:flex;gap:8px;flex-wrap:wrap;align-items:center;">
        <select class="geral js-export-type" style="width: 180px;"
                data-pdf="{{ route('advanced-reports.pending-entries.pdf', request()->all()) }}"
                data-excel="{{ route('advanced-reports.pending-entries.excel', request()->all()) }}">
          <option value="pdf">Gerar PDF</option>
          <option value="excel">Exportar Excel</option>
        </select>
        <button type="button" class="btn-green js-export-run">Executar</button>
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
        if (url) window.open(url, '_blank');
      });
    })();
  </script>
@endpush

