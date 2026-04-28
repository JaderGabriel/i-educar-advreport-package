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
      'route' => route('advanced-reports.student-documents.index'),
      'cursos' => $cursos,
      'cursoId' => $cursoId ?? null,
      'withGrade' => true,
      'withSchoolClass' => true,
      'withCharts' => false,
      'extraRowsView' => 'advanced-reports::student-documents._extra-filters-rows',
      'actionsView' => 'advanced-reports::student-documents._actions',
      'explainTitle' => 'Documentos oficiais do aluno',
      'explainText' => 'Selecione ano/instituição/escola/curso (obrigatórios). Série/turma são opcionais. Se selecionar alunos, emite para os selecionados; se não selecionar, emite em lote pelo filtro (limitado).',
  ])
@endsection

@push('scripts')
  <script>
    (function () {
      const turmaSelect = document.getElementById('ref_cod_turma');
      const studentsSelect = document.getElementById('studentDocumentsStudentsSelect');
      const matriculaHidden = document.getElementById('studentDocumentsMatriculaId');
      if (!turmaSelect || !studentsSelect || !matriculaHidden) return;

      async function loadStudentsByClass(turmaId) {
        const url = "{{ route('advanced-reports.lookup.class-enrollments') }}" + "?turma_id=" + encodeURIComponent(turmaId);
        const res = await fetch(url, {headers: {'Accept': 'application/json'}});
        if (!res.ok) return [];
        return await res.json();
      }

      async function refreshStudents() {
        const turmaId = turmaSelect.value;
        studentsSelect.innerHTML = '';
        matriculaHidden.value = '';

        if (!turmaId) {
          studentsSelect.disabled = true;
          const opt = document.createElement('option');
          opt.value = '';
          opt.textContent = 'Selecione a turma para listar alunos';
          studentsSelect.appendChild(opt);
          return;
        }

        studentsSelect.disabled = true;
        const loading = document.createElement('option');
        loading.value = '';
        loading.textContent = 'Carregando alunos...';
        studentsSelect.appendChild(loading);

        const items = await loadStudentsByClass(turmaId);
        studentsSelect.innerHTML = '';

        (items || []).forEach(function (it) {
          const opt = document.createElement('option');
          opt.value = String(it.matricula_id);
          opt.textContent = it.label;
          studentsSelect.appendChild(opt);
        });

        studentsSelect.disabled = false;
      }

      turmaSelect.addEventListener('change', refreshStudents);
      refreshStudents();

      studentsSelect.addEventListener('change', function () {
        const selected = Array.from(studentsSelect.selectedOptions || []).map(o => o.value).filter(Boolean);
        matriculaHidden.value = selected.length === 1 ? selected[0] : '';
      });

      // Ações (prévia/emissão)
      const modal = document.getElementById('advancedReportsStudentDocsPreviewModal');
      const iframe = document.querySelector('.js-student-docs-preview-iframe');
      const helpBtn = document.querySelector('.js-student-docs-help');
      const closeBtn = document.querySelector('.js-student-docs-preview-close');
      const emitBtn = document.querySelector('.js-student-docs-emit');
      const form = document.getElementById('formcadastro');

      function buildPdfUrl() {
        if (!form) return null;
        const params = new URLSearchParams(new FormData(form));
        return "{{ route('advanced-reports.student-documents.pdf') }}" + "?" + params.toString();
      }

      function openPreview() {
        if (!modal || !iframe) return;
        const params = new URLSearchParams(new FormData(form));
        params.set('preview', '1');
        const url = "{{ route('advanced-reports.student-documents.pdf') }}" + "?" + params.toString();
        if (!url || !modal || !iframe) return;
        iframe.src = url;
        modal.style.display = 'block';
      }

      function closePreview() {
        if (!modal || !iframe) return;
        iframe.src = 'about:blank';
        modal.style.display = 'none';
      }

      if (helpBtn) helpBtn.addEventListener('click', function (e) { e.preventDefault(); openPreview(); });
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

