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
      'explainTitle' => 'Pendências de lançamento (notas/frequência)',
      'explainText' => 'Use este relatório para identificar, por turma, quais matrículas ainda possuem pendências de lançamento de notas e/ou frequência por componente e etapa.',
      'explainDictionary' => 'Pendência de nota = ausência de nota lançada; Pendência de frequência = ausência de faltas lançadas conforme regra de presença.'
  ])

  <div class="advanced-report-card" style="margin-top: 12px;">
    <strong class="advanced-report-card-title">Filtros adicionais</strong>
    <p class="advanced-report-card-text">Opcionalmente restrinja por etapa e pelo tipo de pendência.</p>

    <form action="{{ route('advanced-reports.pending-entries.index') }}" method="get">
      @foreach(request()->except(['etapa','check_grades','check_frequency']) as $k => $v)
        <input type="hidden" name="{{ $k }}" value="{{ $v }}">
      @endforeach

      <table class="tablecadastro" width="100%" border="0" cellpadding="2" cellspacing="0" role="presentation">
        <tbody>
        <tr>
          <td class="formmdtd"><span class="form">Etapa</span></td>
          <td class="formmdtd">
            <input class="geral" name="etapa" value="{{ request('etapa') }}" style="width: 80px;" placeholder="Ex.: 1">
            <span class="form" style="margin-left: 8px; font-size: 11px;">(vazio = todas as etapas)</span>
          </td>
        </tr>
        <tr>
          <td class="formlttd"><span class="form">Verificar</span></td>
          <td class="formlttd">
            <label style="display:inline-flex;gap:6px;align-items:center;margin-right:12px;">
              <input type="checkbox" name="check_grades" value="1" {{ request()->boolean('check_grades', true) ? 'checked' : '' }}>
              Notas
            </label>
            <label style="display:inline-flex;gap:6px;align-items:center;">
              <input type="checkbox" name="check_frequency" value="1" {{ request()->boolean('check_frequency', true) ? 'checked' : '' }}>
              Frequência (faltas)
            </label>
          </td>
        </tr>
        </tbody>
      </table>

      <div style="text-align: center; margin-top: 12px;">
        <button type="submit" class="btn-green">Aplicar</button>
      </div>
    </form>
  </div>

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

