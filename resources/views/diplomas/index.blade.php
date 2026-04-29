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
      'route' => route('advanced-reports.diplomas.index'),
      'cursos' => $cursos,
      'cursoId' => $cursoId ?? null,
      'ano' => $ano ?? null,
      'instituicaoId' => $instituicaoId ?? null,
      'escolaId' => $escolaId ?? null,
      'series' => $series ?? [],
      'turmas' => $turmas ?? [],
      'withGrade' => true,
      'withSchoolClass' => true,
      'requireCourse' => true,
      'requireSerie' => true,
      'requireTurma' => true,
      'withCharts' => false,
      'extraRowsView' => 'advanced-reports::diplomas._extra-filters-rows',
      'actionsView' => 'advanced-reports::diplomas._actions',
      'explainTitle' => 'Diplomas e Certificados (modelos)',
      'explainText' => 'Selecione ano → instituição → escola → curso → série → turma (obrigatórios). Depois escolha o documento (diploma/certificado), o tipo e o lado e selecione um ou mais alunos. A prévia (?) usa dados fictícios; “Emitir PDF (final)” usa dados reais do cadastro.',
  ])
@endsection

@push('scripts')
    <script>
        (function () {
          const form = document.getElementById('formcadastro');
          const modal = document.getElementById('advancedReportsDiplomasPreviewModal');
          const iframe = document.querySelector('.js-diplomas-preview-iframe');
          const closeBtn = document.querySelector('.js-diplomas-preview-close');
          const helpBtn = document.querySelector('.js-diplomas-help');
          const emitBtn = document.querySelector('.js-diplomas-emit');
          const turmaSelect = document.getElementById('ref_cod_turma');
          const studentsSelect = document.getElementById('diplomasStudentsSelect');
          const filterInput = document.querySelector('.js-diplomas-student-filter');
          const docSelect = document.getElementById('diplomasDocument');
          const countEl = document.querySelector('.js-diplomas-selected-count');
          const clearBtn = document.querySelector('.js-diplomas-clear-selected');
          const countersEl = document.querySelector('.js-diplomas-class-counters');
          const errorModal = document.getElementById('advancedReportsDiplomasErrorModal');
          const errorText = document.querySelector('.js-diplomas-error-text');
          const errorClose = document.querySelector('.js-diplomas-error-close');
          if (!form || !modal || !iframe) return;

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
          if (errorClose) errorClose.addEventListener('click', function (e) { e.preventDefault(); closeError(); });
          if (errorModal) errorModal.addEventListener('click', function (e) { if (e.target === errorModal) closeError(); });

          function validationMessage() {
            const ano = document.getElementById('ano');
            const inst = document.getElementById('ref_cod_instituicao');
            const escola = document.getElementById('ref_cod_escola');
            const curso = document.getElementById('ref_cod_curso');
            const serie = document.getElementById('ref_cod_serie');
            const turma = document.getElementById('ref_cod_turma');
            if (!ano || !ano.value) return 'Informe o ano letivo.';
            if (!inst || !inst.value) return 'Informe a instituição.';
            if (!escola || !escola.value) return 'Informe a escola.';
            if (!curso || !curso.value) return 'Informe o curso.';
            if (!serie || !serie.value) return 'Informe a série.';
            if (!turma || !turma.value) return 'Informe a turma.';
            if (!studentsSelect) return null;
            const selected = Array.from(studentsSelect.selectedOptions || []).filter(function (o) { return !!o.value; }).length;
            if (selected < 1) return 'Selecione ao menos um aluno para emitir diploma ou certificado.';
            if (docSelect && docSelect.value === 'certificate' && selected > 1) {
              return 'Para certificado, selecione apenas um aluno.';
            }
            return null;
          }

          function previewUrl() {
            const params = new URLSearchParams(new FormData(form));
            params.set('preview', '1');
            return "{{ route('advanced-reports.diplomas.pdf') }}" + "?" + params.toString();
          }

          function emitUrl() {
            const params = new URLSearchParams(new FormData(form));
            params.delete('preview');
            params.delete('preview[]');
            return "{{ route('advanced-reports.diplomas.pdf') }}" + "?" + params.toString();
          }

          function openModal() {
            const msg = validationMessage();
            if (msg) {
              openError(msg);
              return;
            }
            iframe.src = previewUrl();
            modal.style.display = 'block';
          }

          function closeModal() {
            iframe.src = 'about:blank';
            modal.style.display = 'none';
          }

          if (helpBtn) helpBtn.addEventListener('click', function (e) { e.preventDefault(); openModal(); });
          if (closeBtn) closeBtn.addEventListener('click', function (e) { e.preventDefault(); closeModal(); });
          modal.addEventListener('click', function (e) { if (e.target === modal) closeModal(); });

          if (emitBtn) emitBtn.addEventListener('click', function (e) {
            e.preventDefault();
            const msg = validationMessage();
            if (msg) {
              openError(msg);
              return;
            }
            window.open(emitUrl(), '_blank');
          });

          async function loadStudentsByClass(turmaId) {
            const params = new URLSearchParams();
            params.set('turma_id', String(turmaId));
            const ano = document.getElementById('ano');
            if (ano && ano.value) params.set('ano', ano.value);
            if (docSelect && docSelect.value) params.set('document', docSelect.value);
            const url = "{{ route('advanced-reports.lookup.class-enrollments') }}" + "?" + params.toString();
            const res = await fetch(url, {headers: {'Accept': 'application/json'}});
            if (!res.ok) return [];
            return await res.json();
          }

          async function loadClassCounters(turmaId) {
            const params = new URLSearchParams();
            params.set('turma_id', String(turmaId));
            const ano = document.getElementById('ano');
            if (ano && ano.value) params.set('ano', ano.value);
            if (docSelect && docSelect.value) params.set('document', docSelect.value);
            const url = "{{ route('advanced-reports.lookup.class-enrollment-counters') }}" + "?" + params.toString();
            const res = await fetch(url, {headers: {'Accept': 'application/json'}});
            if (!res.ok) return null;
            return await res.json();
          }

          function renderCounters(data) {
            if (!countersEl) return;
            if (!data || typeof data.total === 'undefined') {
              countersEl.innerHTML = '<span style="color:#9ca3af;">Não foi possível carregar os contadores.</span>';
              return;
            }
            const total = Number(data.total || 0);
            const eligible = Number(data.eligible || 0);
            const ineligible = Number(data.ineligible || 0);
            countersEl.innerHTML =
              '<span><strong>Total:</strong> ' + total + '</span>' +
              '<span style="margin-left:10px;"><strong style="color:#166534;">Aptos:</strong> ' + eligible + '</span>' +
              '<span style="margin-left:10px;"><strong style="color:#991b1b;">Não aptos:</strong> ' + ineligible + '</span>' +
              '<span style="margin-left:10px;color:#9ca3af;">(por situação da matrícula)</span>';
          }

          async function refreshDiplomaStudents() {
            if (!turmaSelect || !studentsSelect) return;
            const turmaId = turmaSelect.value;
            studentsSelect.innerHTML = '';
            if (filterInput) filterInput.value = '';
            if (!turmaId) {
              studentsSelect.disabled = true;
              const opt = document.createElement('option');
              opt.value = '';
              opt.textContent = 'Selecione a turma para listar alunos';
              studentsSelect.appendChild(opt);
              if (countEl) countEl.textContent = '0 selecionados';
              if (countersEl) countersEl.innerHTML = '<span style="color:#9ca3af;">Selecione a turma para ver os contadores.</span>';
              return;
            }
            studentsSelect.disabled = true;
            const loading = document.createElement('option');
            loading.value = '';
            loading.textContent = 'Carregando alunos...';
            studentsSelect.appendChild(loading);
            renderCounters(await loadClassCounters(turmaId));
            const items = await loadStudentsByClass(turmaId);
            studentsSelect.innerHTML = '';
            (items || []).forEach(function (it) {
              const opt = document.createElement('option');
              opt.value = String(it.matricula_id);
              opt.textContent = it.label;
              studentsSelect.appendChild(opt);
            });
            studentsSelect.disabled = false;
            if (countEl) countEl.textContent = '0 selecionados';
          }

          if (turmaSelect) {
            turmaSelect.addEventListener('change', refreshDiplomaStudents);
            refreshDiplomaStudents();
          }
          if (docSelect) {
            docSelect.addEventListener('change', refreshDiplomaStudents);
          }

          if (filterInput && studentsSelect) {
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

          function updateSelectedCount() {
            if (!countEl || !studentsSelect) return;
            const selected = Array.from(studentsSelect.selectedOptions || []).filter(o => !!o.value).length;
            countEl.textContent = selected + ' selecionado' + (selected === 1 ? '' : 's');
          }

          if (studentsSelect) {
            studentsSelect.addEventListener('change', updateSelectedCount);
            updateSelectedCount();
          }

          if (clearBtn && studentsSelect) {
            clearBtn.addEventListener('click', function () {
              Array.from(studentsSelect.options || []).forEach(function (o) { o.selected = false; });
              updateSelectedCount();
            });
          }
        })();
    </script>
@endpush

