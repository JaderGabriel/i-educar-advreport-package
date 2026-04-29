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
      const tbody = document.querySelector('.js-school-history-tbody');
      const selectAll = document.querySelector('.js-school-history-select-all');
      const selectedCount = document.querySelector('.js-school-history-selected-count');
      if (!turmaSelect || !tbody) return;

      function openError(message) {
        const modal = document.getElementById('advancedReportsSchoolHistoryErrorModal');
        const text = document.querySelector('.js-school-history-error-text');
        if (!modal || !text) {
          alert(message);
          return;
        }
        text.textContent = message;
        modal.style.display = 'block';
      }

      function closeError() {
        const modal = document.getElementById('advancedReportsSchoolHistoryErrorModal');
        if (!modal) return;
        modal.style.display = 'none';
      }

      async function loadReadyHistories(turmaId) {
        const params = new URLSearchParams();
        params.set('turma_id', String(turmaId));
        const ano = document.getElementById('ano');
        if (ano && ano.value) params.set('ano', ano.value);
        const url = "{{ route('advanced-reports.lookup.ready-school-histories') }}" + "?" + params.toString();
        const res = await fetch(url, {headers: {'Accept': 'application/json'}});
        if (!res.ok) return [];
        return await res.json();
      }

      function currentSelectedAlunoIds() {
        return Array.from(document.querySelectorAll('input[name="aluno_ids[]"]:checked') || [])
          .map(el => parseInt(String(el.value || '0'), 10))
          .filter(n => Number.isFinite(n) && n > 0);
      }

      function syncSelectedCount() {
        if (!selectedCount) return;
        const n = currentSelectedAlunoIds().length;
        selectedCount.textContent = n + ' selecionado(s)';
      }

      function syncSelectAllState() {
        if (!selectAll) return;
        const checks = Array.from(document.querySelectorAll('input[name="aluno_ids[]"]') || []);
        const checked = checks.filter(c => c.checked);
        selectAll.checked = checks.length > 0 && checked.length === checks.length;
        selectAll.indeterminate = checked.length > 0 && checked.length < checks.length;
      }

      function renderEmpty(message) {
        tbody.innerHTML = '';
        const tr = document.createElement('tr');
        const td = document.createElement('td');
        td.colSpan = 8;
        td.style.padding = '10px';
        td.style.color = '#666';
        td.textContent = message;
        tr.appendChild(td);
        tbody.appendChild(tr);
      }

      function row(it) {
        const tr = document.createElement('tr');
        tr.style.borderTop = '1px solid #f1f5f9';

        const tdCheck = document.createElement('td');
        tdCheck.style.padding = '8px';
        tdCheck.style.textAlign = 'center';
        const chk = document.createElement('input');
        chk.type = 'checkbox';
        chk.name = 'aluno_ids[]';
        chk.value = String(it.aluno_id || '');
        chk.addEventListener('change', function () {
          syncSelectedCount();
          syncSelectAllState();
        });
        tdCheck.appendChild(chk);
        tr.appendChild(tdCheck);

        function td(text) {
          const t = document.createElement('td');
          t.style.padding = '8px';
          t.textContent = text || '';
          return t;
        }

        tr.appendChild(td(it.aluno_nome || ''));
        tr.appendChild(td(String(it.ano || '')));
        tr.appendChild(td(it.curso || ''));
        tr.appendChild(td(it.serie || ''));
        tr.appendChild(td(it.registro || ''));
        tr.appendChild(td(it.livro || ''));
        tr.appendChild(td(it.folha || ''));
        return tr;
      }

      async function refreshTable() {
        const turmaId = turmaSelect.value;
        syncSelectedCount();
        syncSelectAllState();

        if (!turmaId) {
          renderEmpty('Selecione a turma para carregar os alunos com histórico nativo consolidado.');
          return;
        }

        renderEmpty('Carregando...');
        const items = await loadReadyHistories(turmaId);
        tbody.innerHTML = '';

        if (!items || items.length === 0) {
          renderEmpty('Nenhum aluno com histórico nativo pronto foi encontrado para esta turma.');
          return;
        }

        (items || []).forEach(function (it) {
          tbody.appendChild(row(it));
        });

        syncSelectedCount();
        syncSelectAllState();
      }

      turmaSelect.addEventListener('change', refreshTable);
      const anoSelect = document.getElementById('ano');
      if (anoSelect) anoSelect.addEventListener('change', refreshTable);
      refreshTable();

      if (selectAll) {
        selectAll.addEventListener('change', function () {
          const checks = Array.from(document.querySelectorAll('input[name="aluno_ids[]"]') || []);
          checks.forEach(function (c) { c.checked = !!selectAll.checked; });
          syncSelectedCount();
          syncSelectAllState();
        });
      }

      const modal = document.getElementById('advancedReportsSchoolHistoryPreviewModal');
      const iframe = document.querySelector('.js-school-history-preview-iframe');
      const openBtn = document.querySelector('.js-school-history-help');
      const closeBtn = document.querySelector('.js-school-history-preview-close');
      const emitBtn = document.querySelector('.js-school-history-emit');
      const errorClose = document.querySelector('.js-school-history-error-close');
      const errorModal = document.getElementById('advancedReportsSchoolHistoryErrorModal');

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
          const ano = document.getElementById('ano');
          const inst = document.getElementById('ref_cod_instituicao');
          const escola = document.getElementById('ref_cod_escola');
          const curso = document.getElementById('ref_cod_curso');
          if (!ano || !inst || !escola || !curso) return;

          if (!ano.value) return openError('Informe o ano letivo.');
          if (!inst.value) return openError('Informe a instituição.');
          if (!escola.value) return openError('Informe a escola.');
          if (!curso.value) return openError('Informe o curso.');

          const selected = currentSelectedAlunoIds();
          if (selected.length === 0) return openError('Selecione ao menos um aluno para impressão.');

          const template = document.getElementById('template');
          const tpl = template ? String(template.value || '') : 'classic';
          if (selected.length > 1 && (tpl !== 'classic' && tpl !== 'modern')) {
            return openError('Para emissão em lote, selecione um modelo compatível (Clássico ou Moderno).');
          }

          const url = buildPdfUrl();
          if (!url) return;
          window.open(url, '_blank');
        });
      }

      if (errorClose) errorClose.addEventListener('click', function (e) { e.preventDefault(); closeError(); });
      if (errorModal) errorModal.addEventListener('click', function (e) { if (e.target === errorModal) closeError(); });
    })();
  </script>
@endpush

<div id="advancedReportsSchoolHistoryErrorModal" class="ar-modal">
  <div class="ar-modal__dialog">
    <div class="ar-modal__header">
      <strong>Não foi possível emitir</strong>
      <button type="button" class="btn js-school-history-error-close">Fechar</button>
    </div>
    <div style="padding: 12px 14px;">
      <p class="js-school-history-error-text" style="margin: 0;"></p>
    </div>
  </div>
</div>
