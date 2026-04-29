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
      'explainText' => 'Selecione ano → instituição → escola → curso → série → turma (obrigatórios). Depois escolha o documento (diploma/certificado), o tipo e o lado e, se quiser, selecione um ou mais alunos. A prévia (?) usa dados fictícios; “Emitir PDF (final)” usa dados reais do cadastro.',
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
          const countEl = document.querySelector('.js-diplomas-selected-count');
          const clearBtn = document.querySelector('.js-diplomas-clear-selected');
          if (!form || !modal || !iframe) return;

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
            window.open(emitUrl(), '_blank');
          });

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
            if (countEl) countEl.textContent = '0 selecionados';
          }

          if (turmaSelect) {
            turmaSelect.addEventListener('change', refreshDiplomaStudents);
            refreshDiplomaStudents();
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

