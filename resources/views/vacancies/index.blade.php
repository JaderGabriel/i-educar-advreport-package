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
      'explainTitle' => 'Vagas por turma',
      'explainText' => 'Relatório de gestão para responder rapidamente: “quantas vagas tenho por turma?”. Selecione ano e escola; opcionalmente refine por curso/série/turma.',
      'explainDictionary' => 'Capacidade = max_aluno da turma; Matriculados = enturmações ativas (matricula_turma.ativo=1) desconsiderando matrícula de dependência; Vagas = max( cap - matriculados, 0).'
  ])

  @if(!empty($data))
    <div class="advanced-report-card" style="margin-top: 12px;">
      <strong class="advanced-report-card-title">Emissão</strong>
      <p class="advanced-report-card-text">Gere o PDF para impressão/arquivo.</p>
      <div class="ar-actions">
        <div class="ar-actions__group">
          <button type="button" class="btn ar-btn ar-btn--secondary js-vacancies-preview-open">
            <span class="ar-btn__icon" aria-hidden="true"></span>
            Prévia (PDF)
          </button>
        </div>
        <div class="ar-actions__group">
          <span class="ar-actions__label">Saída</span>
          <select class="geral ar-select js-export-type" style="width: 210px;"
                  data-pdf="{{ route('advanced-reports.vacancies.pdf', request()->all()) }}"
                  data-excel="{{ route('advanced-reports.vacancies.excel', request()->all()) }}">
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

    @php($s = $data['summary'] ?? [])
    <h2 style="margin-top: 16px;">Resumo</h2>
    <table class="tablelistagem" style="width: 100%;" cellspacing="1" cellpadding="4" border="0">
      <tr>
        <td class="formdktd">Turmas</td>
        <td class="formdktd">Capacidade</td>
        <td class="formdktd">Matriculados</td>
        <td class="formdktd">Vagas</td>
      </tr>
      <tr>
        <td class="formmdtd">{{ (int) ($s['turmas'] ?? 0) }}</td>
        <td class="formmdtd">{{ (int) ($s['capacidade'] ?? 0) }}</td>
        <td class="formmdtd">{{ (int) ($s['matriculados'] ?? 0) }}</td>
        <td class="formmdtd"><strong>{{ (int) ($s['vagas'] ?? 0) }}</strong></td>
      </tr>
    </table>

    <h2 style="margin-top: 16px;">Por turma</h2>
    <table class="tablelistagem" style="width: 100%;" cellspacing="1" cellpadding="4" border="0">
      <tr>
        <td class="formdktd">Escola</td>
        <td class="formdktd">Turma</td>
        <td class="formdktd">Turno</td>
        <td class="formdktd">Curso</td>
        <td class="formdktd">Série</td>
        <td class="formdktd">Capacidade</td>
        <td class="formdktd">Matriculados</td>
        <td class="formdktd">Vagas</td>
      </tr>
      @foreach(($data['items'] ?? []) as $row)
        <tr>
          <td class="formmdtd">{{ $row->escola }}</td>
          <td class="formmdtd">{{ $row->turma }}</td>
          <td class="formmdtd">{{ $row->turno }}</td>
          <td class="formmdtd">{{ $row->curso }}</td>
          <td class="formmdtd">{{ $row->serie }}</td>
          <td class="formmdtd">{{ (int) $row->capacidade }}</td>
          <td class="formmdtd">{{ (int) $row->matriculados }}</td>
          <td class="formmdtd"><strong>{{ (int) $row->vagas }}</strong></td>
        </tr>
      @endforeach
    </table>
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
        const modal = document.getElementById('advancedReportsVacanciesPreviewModal');
        const iframe = document.querySelector('.js-vacancies-preview-iframe');
        if (!modal || !iframe) return;
        iframe.src = url;
        modal.style.display = 'block';
      });

      const previewOpen = document.querySelector('.js-vacancies-preview-open');
      const previewClose = document.querySelector('.js-vacancies-preview-close');
      const modal = document.getElementById('advancedReportsVacanciesPreviewModal');
      const iframe = document.querySelector('.js-vacancies-preview-iframe');
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

<div id="advancedReportsVacanciesPreviewModal" class="ar-modal">
  <div class="ar-modal__dialog">
    <div class="ar-modal__header">
      <strong>Prévia (PDF)</strong>
      <button type="button" class="btn js-vacancies-preview-close">Fechar</button>
    </div>
    <iframe class="js-vacancies-preview-iframe ar-modal__iframe"></iframe>
  </div>
</div>

