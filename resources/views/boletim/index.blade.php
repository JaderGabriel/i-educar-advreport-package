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
      'route' => route('advanced-reports.boletim.index'),
      'cursos' => $cursos,
      'cursoId' => $cursoId ?? null,
      'withGrade' => true,
      'withSchoolClass' => true,
      'withCharts' => false,
      'extraRowsView' => 'advanced-reports::boletim._extra-filters-rows',
      'actionsView' => 'advanced-reports::boletim._actions',
      'explainTitle' => 'Boletim do aluno (PDF)',
      'explainText' => 'Selecione ano/instituição/escola/série/turma e então escolha o aluno. O documento inclui QR Code e código para validação pública.',
  ])
@endsection

@push('scripts')
  <script>
    (function () {
      const turmaSelect = document.getElementById('ref_cod_turma');
      const studentSelect = document.getElementById('boletimStudentSelect');
      const matriculaHidden = document.getElementById('boletimMatriculaId');
      if (!turmaSelect || !studentSelect || !matriculaHidden) return;

      async function loadStudentsByClass(turmaId) {
        const url = "{{ route('advanced-reports.lookup.class-enrollments') }}" + "?turma_id=" + encodeURIComponent(turmaId);
        const res = await fetch(url, {headers: {'Accept': 'application/json'}});
        if (!res.ok) return [];
        return await res.json();
      }

      async function refreshStudents() {
        const turmaId = turmaSelect.value;
        studentSelect.innerHTML = '';
        matriculaHidden.value = '';

        if (!turmaId) {
          studentSelect.disabled = true;
          const opt = document.createElement('option');
          opt.value = '';
          opt.textContent = 'Selecione a turma para listar alunos';
          studentSelect.appendChild(opt);
          return;
        }

        studentSelect.disabled = true;
        const loading = document.createElement('option');
        loading.value = '';
        loading.textContent = 'Carregando alunos...';
        studentSelect.appendChild(loading);

        const items = await loadStudentsByClass(turmaId);
        studentSelect.innerHTML = '';

        const first = document.createElement('option');
        first.value = '';
        first.textContent = 'Selecione o aluno';
        studentSelect.appendChild(first);

        (items || []).forEach(function (it) {
          const opt = document.createElement('option');
          opt.value = String(it.matricula_id);
          opt.textContent = it.label;
          studentSelect.appendChild(opt);
        });

        studentSelect.disabled = false;
      }

      turmaSelect.addEventListener('change', refreshStudents);
      refreshStudents();

      studentSelect.addEventListener('change', function () {
        matriculaHidden.value = studentSelect.value || '';
      });

      const modal = document.getElementById('advancedReportsBoletimPreviewModal');
      const iframe = document.querySelector('.js-boletim-preview-iframe');
      const openBtn = document.querySelector('.js-boletim-preview-open');
      const closeBtn = document.querySelector('.js-boletim-preview-close');
      const emitBtn = document.querySelector('.js-boletim-emit');
      const form = document.getElementById('formcadastro');

      function buildPdfUrl() {
        if (!form) return null;
        const params = new URLSearchParams(new FormData(form));
        if (!params.get('matricula_id')) return null;
        return "{{ route('advanced-reports.boletim.pdf') }}" + "?" + params.toString();
      }

      function openPreview() {
        const url = buildPdfUrl();
        if (!url || !modal || !iframe) return;
        iframe.src = url;
        modal.style.display = 'block';
      }

      function closePreview() {
        if (!modal || !iframe) return;
        iframe.src = 'about:blank';
        modal.style.display = 'none';
      }

      if (openBtn) openBtn.addEventListener('click', function (e) { e.preventDefault(); openPreview(); });
      if (closeBtn) closeBtn.addEventListener('click', function (e) { e.preventDefault(); closePreview(); });
      if (modal) modal.addEventListener('click', function (e) { if (e.target === modal) closePreview(); });
      if (emitBtn) emitBtn.addEventListener('click', function (e) {
        e.preventDefault();
        const url = buildPdfUrl();
        if (!url) return;
        window.open(url, '_blank');
      });
    })();
  </script>
@endpush

