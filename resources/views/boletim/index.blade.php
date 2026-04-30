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
      'explainText' => 'Selecione Ano → Instituição → Escola → Curso (obrigatórios). Série/turma são opcionais. Na turma, você pode filtrar por nome e selecionar nenhum, um ou vários alunos (sem Ctrl).',
  ])
@endsection

@push('scripts')
  <script>
    (function () {
      const form = document.getElementById('formcadastro');
      const turmaSelect = document.getElementById('ref_cod_turma');
      const studentSelect = document.getElementById('boletimStudentSelect');
      const filterInput = document.querySelector('.js-boletim-student-filter');
      const countEl = document.querySelector('.js-boletim-selected-count');
      const clearBtn = document.querySelector('.js-boletim-clear-selected');
      if (!turmaSelect || !studentSelect) return;

      async function loadStudentsByClass(turmaId) {
        const params = new URLSearchParams();
        params.set('turma_id', String(turmaId));
        const ano = document.getElementById('ano');
        if (ano && ano.value) params.set('ano', ano.value);
        const url = "{{ route('advanced-reports.lookup.class-enrollments') }}" + "?" + params.toString();
        const res = await fetch(url, {headers: {'Accept': 'application/json'}});
        if (!res.ok) return [];
        return await res.json();
      }

      async function refreshStudents() {
        const turmaId = turmaSelect.value;
        studentSelect.innerHTML = '';
        if (filterInput) filterInput.value = '';

        if (!turmaId) {
          studentSelect.disabled = true;
          const opt = document.createElement('option');
          opt.value = '';
          opt.textContent = 'Selecione a turma para listar alunos';
          studentSelect.appendChild(opt);
          if (countEl) countEl.textContent = '0 selecionados';
          return;
        }

        studentSelect.disabled = true;
        const loading = document.createElement('option');
        loading.value = '';
        loading.textContent = 'Carregando alunos...';
        studentSelect.appendChild(loading);

        const items = await loadStudentsByClass(turmaId);
        studentSelect.innerHTML = '';

        (items || []).forEach(function (it) {
          const opt = document.createElement('option');
          opt.value = String(it.matricula_id);
          opt.textContent = it.label;
          studentSelect.appendChild(opt);
        });

        studentSelect.disabled = false;
        if (countEl) countEl.textContent = '0 selecionados';
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

      function updateSelectedCount() {
        if (!countEl) return;
        const selected = Array.from(studentSelect.selectedOptions || []).filter(o => !!o.value).length;
        countEl.textContent = selected + ' selecionado' + (selected === 1 ? '' : 's');
      }

      studentSelect.addEventListener('change', updateSelectedCount);
      updateSelectedCount();

      if (clearBtn) {
        clearBtn.addEventListener('click', function () {
          Array.from(studentSelect.options || []).forEach(function (o) { o.selected = false; });
          updateSelectedCount();
        });
      }

      const modal = document.getElementById('advancedReportsBoletimPreviewModal');
      const pdfRoot = modal ? modal.querySelector('.js-boletim-preview-pdf') : null;
      const closeBtn = document.querySelector('.js-boletim-preview-close');
      const emitBtn = document.querySelector('.js-boletim-emit');
      const helpBtn = document.querySelector('.js-boletim-help');
      const errorModal = document.getElementById('advancedReportsBoletimErrorModal');
      const errorText = document.querySelector('.js-boletim-error-text');
      const errorClose = document.querySelector('.js-boletim-error-close');

      function openError(message) {
        if (!errorModal || !errorText) {
          window.alert(message);
          return;
        }
        errorText.textContent = message;
        errorModal.style.display = 'block';
      }
      function closeError() {
        if (!errorModal) return;
        errorModal.style.display = 'none';
      }

      function buildPdfUrl() {
        if (!form) return null;
        const params = new URLSearchParams(new FormData(form));
        params.delete('preview');
        params.delete('preview[]');
        return "{{ route('advanced-reports.boletim.pdf') }}" + "?" + params.toString();
      }

      function requiredMessage() {
        const ano = document.getElementById('ano');
        const inst = document.getElementById('ref_cod_instituicao');
        const escola = document.getElementById('ref_cod_escola');
        const curso = document.getElementById('ref_cod_curso');
        if (!ano || !inst || !escola || !curso) return null;
        if (!ano.value) return 'Informe o ano letivo.';
        if (!inst.value) return 'Informe a instituição.';
        if (!escola.value) return 'Informe a escola.';
        if (!curso.value) return 'Informe o curso.';
        return null;
      }

      function openPreview() {
        if (!modal || !pdfRoot || !form) return;
        const msg = requiredMessage();
        if (msg) {
          openError(msg);
          return;
        }
        const params = new URLSearchParams(new FormData(form));
        params.set('preview', '1');
        const url = "{{ route('advanced-reports.boletim.pdf') }}" + "?" + params.toString();
        modal.style.display = 'block';
        if (window.AdvancedReportsPdfPreview) {
          window.AdvancedReportsPdfPreview.open(pdfRoot, url);
        }
      }

      function closePreview() {
        if (!modal || !pdfRoot) return;
        if (window.AdvancedReportsPdfPreview) {
          window.AdvancedReportsPdfPreview.close(pdfRoot);
        }
        modal.style.display = 'none';
      }

      if (helpBtn) helpBtn.addEventListener('click', function (e) { e.preventDefault(); openPreview(); });
      if (closeBtn) closeBtn.addEventListener('click', function (e) { e.preventDefault(); closePreview(); });
      if (modal) modal.addEventListener('click', function (e) { if (e.target === modal) closePreview(); });
      if (errorClose) errorClose.addEventListener('click', function (e) { e.preventDefault(); closeError(); });
      if (errorModal) errorModal.addEventListener('click', function (e) { if (e.target === errorModal) closeError(); });

      if (emitBtn) emitBtn.addEventListener('click', function (e) {
        e.preventDefault();
        const msg = requiredMessage();
        if (msg) {
          openError(msg);
          return;
        }
        const url = buildPdfUrl();
        if (!url) return;
        window.open(url, '_blank');
      });
    })();
  </script>
@endpush

