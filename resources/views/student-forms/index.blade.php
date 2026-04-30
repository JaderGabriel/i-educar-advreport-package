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
      'route' => ($type ?? '') === 'individual'
        ? route('advanced-reports.student-forms.individual.index')
        : route('advanced-reports.student-forms.enrollment.index'),
      'cursos' => $cursos,
      'cursoId' => $cursoId ?? null,
      'requireCourse' => true,
      'requireSchool' => true,
      'withGrade' => true,
      'withSchoolClass' => true,
      'withCharts' => false,
      'actionsView' => 'advanced-reports::student-forms._actions',
      'explainTitle' => 'Fichas (Documentos do aluno)',
      'explainText' => (($type ?? '') === 'individual'
        ? 'Ficha individual: permite emissão em lote e inclui assinaturas do(a) secretário(a) escolar e do(a) diretor(a) (quando configurados na escola).'
        : 'Ficha de matrícula: permite emissão em lote e inclui campo para assinatura do pai/mãe/responsável e do emissor do documento.')
        . ' Se selecionar matrículas, emite apenas as selecionadas; se não selecionar, emite em lote pelo filtro (limitado a 200).',
      'extraRowsView' => 'advanced-reports::student-forms._extra-filters-rows',
  ])
@endsection

@push('scripts')
  <script>
    (function () {
      const turmaSelect = document.getElementById('ref_cod_turma');
      const studentsSelect = document.getElementById('studentFormsStudentsSelect');
      const matriculaHidden = document.getElementById('studentFormsMatriculaId');
      const filterInput = document.querySelector('.js-student-forms-student-filter');
      const countEl = document.querySelector('.js-student-forms-selected-count');
      const clearBtn = document.querySelector('.js-student-forms-clear-selected');
      const emitBtn = document.querySelector('.js-student-forms-emit');
      const previewBtn = document.querySelector('.js-student-forms-preview');
      const pdfRoot = document.querySelector('.js-student-forms-preview-pdf');
      const modal = document.getElementById('advancedReportsStudentFormsPreviewModal');
      const closeBtn = document.querySelector('.js-student-forms-preview-close');
      const form = document.getElementById('formcadastro');
      const errorModal = document.getElementById('advancedReportsStudentFormsErrorModal');
      const errorText = document.querySelector('.js-student-forms-error-text');
      const errorClose = document.querySelector('.js-student-forms-error-close');

      if (!turmaSelect || !studentsSelect || !matriculaHidden || !form) return;

      const actionsRoot = document.getElementById('studentFormsActionsRoot');
      const pdfBase = actionsRoot ? actionsRoot.getAttribute('data-pdf-route') : null;

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

      function openError(message) {
        if (!errorModal || !errorText) {
          alert(message);
          return;
        }
        errorText.textContent = message;
        errorModal.style.display = 'block';
      }

      function closeError() {
        if (!errorModal) return;
        errorModal.style.display = 'none';
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

      function updateSelectedCount() {
        const selectedIds = Array.from(studentsSelect.selectedOptions || []).map(o => o.value).filter(Boolean);
        if (countEl) {
          const n = selectedIds.length;
          countEl.textContent = n + ' selecionado' + (n === 1 ? '' : 's');
        }
        matriculaHidden.value = selectedIds.length === 1 ? selectedIds[0] : '';
      }

      async function refreshStudents() {
        const turmaId = turmaSelect.value;
        studentsSelect.innerHTML = '';
        matriculaHidden.value = '';
        if (filterInput) filterInput.value = '';

        if (!turmaId) {
          studentsSelect.disabled = true;
          const opt = document.createElement('option');
          opt.value = '';
          opt.textContent = 'Selecione a turma para listar matrículas';
          studentsSelect.appendChild(opt);
          if (countEl) countEl.textContent = '0 selecionados';
          return;
        }

        studentsSelect.disabled = true;
        const loading = document.createElement('option');
        loading.value = '';
        loading.textContent = 'Carregando matrículas...';
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
        updateSelectedCount();
      }

      function buildPdfUrl(preview) {
        if (!pdfBase) return null;
        const params = new URLSearchParams(new FormData(form));
        if (preview) params.set('preview', '1');
        return pdfBase + "?" + params.toString();
      }

      function openPreview() {
        if (!modal || !pdfRoot) return;
        const msg = requiredMessage();
        if (msg) {
          openError(msg);
          return;
        }
        const url = buildPdfUrl(true);
        if (!url) return;
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

      function updateEmitState() {
        const disabled = !!requiredMessage();
        if (emitBtn) emitBtn.disabled = disabled;
        if (previewBtn) previewBtn.disabled = disabled;
      }

      turmaSelect.addEventListener('change', refreshStudents);
      const anoSelect = document.getElementById('ano');
      if (anoSelect) anoSelect.addEventListener('change', refreshStudents);
      refreshStudents();

      studentsSelect.addEventListener('change', updateSelectedCount);
      updateSelectedCount();

      if (filterInput) {
        filterInput.addEventListener('input', function () {
          const q = (filterInput.value || '').toLowerCase().trim();
          Array.from(studentsSelect.options || []).forEach(function (o) {
            if (!o.value) { o.hidden = false; return; }
            o.hidden = q.length > 0 && !(o.textContent || '').toLowerCase().includes(q);
          });
        });
      }

      if (clearBtn) {
        clearBtn.addEventListener('click', function () {
          Array.from(studentsSelect.options || []).forEach(function (o) { o.selected = false; });
          updateSelectedCount();
        });
      }

      if (previewBtn) previewBtn.addEventListener('click', function (e) { e.preventDefault(); openPreview(); });
      if (closeBtn) closeBtn.addEventListener('click', function (e) { e.preventDefault(); closePreview(); });
      if (modal) modal.addEventListener('click', function (e) { if (e.target === modal) closePreview(); });

      const reqEls = ['ano', 'ref_cod_instituicao', 'ref_cod_escola', 'ref_cod_curso'].map(id => document.getElementById(id)).filter(Boolean);
      reqEls.forEach(function (el) { el.addEventListener('change', updateEmitState); });
      updateEmitState();

      if (errorClose) errorClose.addEventListener('click', function (e) { e.preventDefault(); closeError(); });
      if (errorModal) errorModal.addEventListener('click', function (e) { if (e.target === errorModal) closeError(); });

      if (emitBtn) emitBtn.addEventListener('click', function (e) {
        e.preventDefault();
        const msg = requiredMessage();
        if (msg) {
          openError(msg);
          return;
        }
        const url = buildPdfUrl(false);
        if (!url) return;
        window.open(url, '_blank');
      });
    })();
  </script>
@endpush

