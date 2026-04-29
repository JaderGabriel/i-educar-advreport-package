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
      'requireCourse' => true,
      'requireSchool' => true,
      'withGrade' => true,
      'withSchoolClass' => true,
      'withCharts' => false,
      'extraRowsView' => 'advanced-reports::student-documents._extra-filters-rows',
      'actionsView' => 'advanced-reports::student-documents._actions',
      'explainTitle' => 'Documentos oficiais do aluno',
      'explainText' => 'Selecione ano, instituição, escola e curso (obrigatórios). Série/turma são opcionais. Se selecionar alunos, emite para os selecionados; se não selecionar, emite em lote pelo filtro (limitado).',
  ])
@endsection

@push('scripts')
  <script>
    (function () {
      const turmaSelect = document.getElementById('ref_cod_turma');
      const studentsSelect = document.getElementById('studentDocumentsStudentsSelect');
      const matriculaHidden = document.getElementById('studentDocumentsMatriculaId');
      const documentSelect = document.getElementById('studentDocumentsDocument');
      const historicoRow = document.getElementById('studentDocumentsHistoricoRow');
      const filterInput = document.querySelector('.js-student-filter');
      if (!turmaSelect || !studentsSelect || !matriculaHidden) return;

      function syncHistoricoRow() {
        if (!documentSelect || !historicoRow) return;
        historicoRow.style.display = documentSelect.value === 'declaration_conclusion' ? '' : 'none';
      }

      async function loadStudentsByClass(turmaId) {
        const params = new URLSearchParams();
        params.set('turma_id', String(turmaId));
        if (documentSelect && documentSelect.value) params.set('document', documentSelect.value);
        const ano = document.getElementById('ano');
        if (ano && ano.value) params.set('ano', ano.value);
        const url = "{{ route('advanced-reports.lookup.class-enrollments') }}" + "?" + params.toString();
        const res = await fetch(url, {headers: {'Accept': 'application/json'}});
        if (!res.ok) return [];
        return await res.json();
      }

      async function refreshStudents() {
        syncHistoricoRow();
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
      if (documentSelect) documentSelect.addEventListener('change', refreshStudents);
      const anoSelect = document.getElementById('ano');
      if (anoSelect) anoSelect.addEventListener('change', refreshStudents);
      refreshStudents();
      syncHistoricoRow();

      if (filterInput) {
        filterInput.addEventListener('input', function () {
          const q = (filterInput.value || '').toLowerCase().trim();
          Array.from(studentsSelect.options || []).forEach(function (o) {
            if (!o.value) {
              o.hidden = false;
              return;
            }
            o.hidden = q.length > 0 && !(o.textContent || '').toLowerCase().includes(q);
          });
        });
      }

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
      const errorModal = document.getElementById('advancedReportsStudentDocsErrorModal');
      const errorText = document.querySelector('.js-student-docs-error-text');
      const errorClose = document.querySelector('.js-student-docs-error-close');

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

      function buildPdfUrl() {
        if (!form) return null;
        const params = new URLSearchParams(new FormData(form));
        return "{{ route('advanced-reports.student-documents.pdf') }}" + "?" + params.toString();
      }

      function openPreview() {
        if (!modal || !iframe) return;
        const msg = requiredMessage();
        if (msg) {
          openError(msg);
          return;
        }
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

      function updateEmitState() {
        if (!emitBtn) return;
        emitBtn.disabled = !!requiredMessage();
      }

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
        const url = buildPdfUrl();
        if (!url) return;
        window.open(url, '_blank');
      });
    })();
  </script>
@endpush

<div id="advancedReportsStudentDocsErrorModal" class="ar-modal">
  <div class="ar-modal__dialog">
    <div class="ar-modal__header">
      <strong>Não foi possível emitir</strong>
      <button type="button" class="btn js-student-docs-error-close">Fechar</button>
    </div>
    <div style="padding: 12px 14px;">
      <p class="js-student-docs-error-text" style="margin: 0;"></p>
    </div>
  </div>
</div>

