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
      'route' => route('advanced-reports.school-history.index'),
      'cursos' => $cursos,
      'cursoId' => $cursoId ?? null,
      'templates' => $templates ?? [],
      'template' => $template ?? request('template', 'classic'),
      'withGrade' => true,
      'withSchoolClass' => true,
      'requireCourse' => true,
      'extraRowsView' => 'advanced-reports::school-history._extra-filters-rows',
      'actionsView' => 'advanced-reports::school-history._actions',
      'explainTitle' => 'Histórico escolar (PDF)',
      'explainText' => 'Selecione Ano → Instituição → Escola → Curso (obrigatórios). Série e turma refinam a lista. Na turma, escolha o aluno (somente quem já tem histórico nativo consolidado) e em seguida selecione o modelo visual para emitir o PDF validado.',
  ])
@endsection

@push('scripts')
  <script>
    (function () {
      const form = document.getElementById('formcadastro');
      const turmaSelect = document.getElementById('ref_cod_turma');
      const studentSelect = document.getElementById('schoolHistoryStudentSelect');
      const filterInput = document.querySelector('.js-school-history-student-filter');
      const bookInput = document.getElementById('schoolHistoryBook');
      const pageInput = document.getElementById('schoolHistoryPage');
      const recordInput = document.getElementById('schoolHistoryRecord');
      if (!turmaSelect || !studentSelect) return;

      async function loadStudentsByClass(turmaId) {
        const params = new URLSearchParams();
        params.set('turma_id', String(turmaId));
        const ano = document.getElementById('ano');
        if (ano && ano.value) params.set('ano', ano.value);
        params.set('only_with_history', '1');
        const url = "{{ route('advanced-reports.lookup.class-enrollments') }}" + "?" + params.toString();
        const res = await fetch(url, {headers: {'Accept': 'application/json'}});
        if (!res.ok) return [];
        return await res.json();
      }

      function clearMeta() {
        if (bookInput) bookInput.value = '';
        if (pageInput) pageInput.value = '';
        if (recordInput) recordInput.value = '';
      }

      async function loadMeta(alunoId) {
        clearMeta();
        if (!alunoId) return;
        const url = "{{ route('advanced-reports.lookup.school-history-meta') }}" + "?aluno_id=" + encodeURIComponent(String(alunoId));
        const res = await fetch(url, {headers: {'Accept': 'application/json'}});
        if (!res.ok) return;
        const json = await res.json();
        if (!json || !json.ok) return;
        if (bookInput) bookInput.value = json.book || '';
        if (pageInput) pageInput.value = json.page || '';
        if (recordInput) recordInput.value = json.record || '';
      }

      async function refreshStudents() {
        const turmaId = turmaSelect.value;
        studentSelect.innerHTML = '';
        studentSelect.value = '';
        clearMeta();

        if (!turmaId) {
          studentSelect.disabled = true;
          const opt = document.createElement('option');
          opt.value = '';
          opt.textContent = 'Selecione a turma para listar alunos com histórico nativo';
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

        const seen = {};
        (items || []).forEach(function (it) {
          const aid = String(it.aluno_id);
          if (seen[aid]) return;
          seen[aid] = true;
          const opt = document.createElement('option');
          opt.value = aid;
          opt.textContent = it.label || ('Aluno ' + aid);
          studentSelect.appendChild(opt);
        });

        studentSelect.disabled = false;
      }

      turmaSelect.addEventListener('change', refreshStudents);
      const anoSelect = document.getElementById('ano');
      if (anoSelect) anoSelect.addEventListener('change', refreshStudents);
      refreshStudents();

      if (filterInput) {
        filterInput.addEventListener('input', function () {
          const q = (filterInput.value || '').toLowerCase().trim();
          Array.from(studentSelect.options || []).forEach(function (o) {
            if (!o.value) {
              o.hidden = false;
              return;
            }
            o.hidden = q.length > 0 && !(o.textContent || '').toLowerCase().includes(q);
          });
        });
      }

      studentSelect.addEventListener('change', function () {
        loadMeta(studentSelect.value);
      });

      const modal = document.getElementById('advancedReportsSchoolHistoryPreviewModal');
      const iframe = document.querySelector('.js-school-history-preview-iframe');
      const openBtn = document.querySelector('.js-school-history-help');
      const closeBtn = document.querySelector('.js-school-history-preview-close');
      const emitBtn = document.querySelector('.js-school-history-emit');

      function buildPdfUrl() {
        if (!form) return null;
        const params = new URLSearchParams(new FormData(form));
        params.delete('preview');
        params.delete('preview[]');
        return "{{ route('advanced-reports.school-history.pdf') }}" + "?" + params.toString();
      }

      function openPreview() {
        if (!modal || !iframe || !form) return;
        const params = new URLSearchParams(new FormData(form));
        params.set('preview', '1');
        iframe.src = "{{ route('advanced-reports.school-history.pdf') }}" + "?" + params.toString();
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

      if (emitBtn) {
        emitBtn.addEventListener('click', function (e) {
          e.preventDefault();
          const url = buildPdfUrl();
          if (!url) return;
          window.open(url, '_blank');
        });
      }
    })();
  </script>
@endpush
