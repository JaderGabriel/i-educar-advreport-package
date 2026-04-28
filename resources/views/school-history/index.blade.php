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
  <h1>Histórico escolar (PDF)</h1>

  <div class="advanced-report-card">
    <strong class="advanced-report-card-title">Emissão</strong>
    <p class="advanced-report-card-text">
      Informe o código do aluno para gerar o histórico escolar consolidado com base em registros do i-Educar.
      O documento inclui QR Code e validação pública.
    </p>
  </div>

  <form method="get" action="{{ route('advanced-reports.school-history.pdf') }}" target="_blank" id="formcadastro">
    <table class="tablecadastro" width="100%" border="0" cellpadding="2" cellspacing="0" role="presentation">
      <tbody>
      <tr>
        <td class="formmdtd"><span class="form">Aluno</span> <span class="campo_obrigatorio">*</span></td>
        <td class="formmdtd">
          <input type="hidden" name="aluno_id" id="aluno_id" value="{{ $alunoId }}">
          <input class="geral obrigatorio" id="aluno_search" list="alunos_suggestions" value="{{ $alunoId }}" style="width: 520px;" placeholder="Digite o nome do aluno ou o ID">
          <datalist id="alunos_suggestions"></datalist>
        </td>
      </tr>
      <tr>
        <td class="formlttd"><span class="form">Modelo</span></td>
        <td class="formlttd">
          <select class="geral" name="template" id="template" style="width: 220px;">
            @foreach(($templates ?? ['classic' => 'Clássico (padrão)', 'modern' => 'Moderno (limpo)']) as $key => $label)
              <option value="{{ $key }}" @selected(($template ?? 'classic') === $key)>{{ $label }}</option>
            @endforeach
          </select>
        </td>
      </tr>
      <tr>
        <td class="formlttd"><span class="form">Livro/Folha/Registro</span></td>
        <td class="formlttd">
          <input class="geral" name="book" value="{{ request('book') }}" style="width: 70px;" placeholder="Livro">
          <input class="geral" name="page" value="{{ request('page') }}" style="width: 70px;" placeholder="Folha">
          <input class="geral" name="record" value="{{ request('record') }}" style="width: 90px;" placeholder="Registro">
        </td>
      </tr>
      </tbody>
    </table>

    <div style="text-align: center; margin-top: 16px;">
      <button type="button" class="btn js-history-preview-open">Ver prévia</button>
      <button type="submit" class="btn-green">Gerar PDF</button>
    </div>
  </form>

  <div id="advancedReportsHistoryPreviewModal" class="ar-modal">
    <div class="ar-modal__dialog">
      <div class="ar-modal__header">
        <strong>Prévia do histórico escolar</strong>
        <button type="button" class="btn js-history-preview-close">Fechar</button>
      </div>
      <iframe class="js-history-preview-iframe ar-modal__iframe"></iframe>
    </div>
  </div>
@endsection

@push('scripts')
  <script>
    (function () {
      const input = document.getElementById('aluno_search');
      const hidden = document.getElementById('aluno_id');
      const list = document.getElementById('alunos_suggestions');
      if (!input || !hidden || !list) return;

      function extractId(value) {
        const match = String(value || '').match(/^(\d+)\s+-\s+/);
        return match ? match[1] : (String(value || '').match(/^\d+$/) ? value : '');
      }

      async function loadSuggestions(q) {
        const url = "{{ route('advanced-reports.lookup.alunos') }}" + "?q=" + encodeURIComponent(q);
        const res = await fetch(url, {headers: {'Accept': 'application/json'}});
        if (!res.ok) return [];
        return await res.json();
      }

      let last = '';
      input.addEventListener('input', async function () {
        const raw = input.value || '';
        const id = extractId(raw);
        if (id) hidden.value = id;

        const q = raw.trim();
        if (q.length < 3 || q === last) return;
        last = q;

        const items = await loadSuggestions(q);
        list.innerHTML = '';
        (items || []).forEach(function (it) {
          const opt = document.createElement('option');
          opt.value = it.label;
          list.appendChild(opt);
        });
      });

      input.addEventListener('change', function () {
        const id = extractId(input.value);
        hidden.value = id || '';
      });
    })();
  </script>

  <script>
    (function () {
      const form = document.getElementById('formcadastro');
      const modal = document.getElementById('advancedReportsHistoryPreviewModal');
      const iframe = document.querySelector('.js-history-preview-iframe');
      const openBtn = document.querySelector('.js-history-preview-open');
      const closeBtn = document.querySelector('.js-history-preview-close');
      if (!form || !modal || !iframe || !openBtn || !closeBtn) return;

      function openModal() {
        const params = new URLSearchParams(new FormData(form));
        params.set('preview', '1');
        iframe.src = "{{ route('advanced-reports.school-history.pdf') }}" + "?" + params.toString();
        modal.style.display = 'block';
      }

      function closeModal() {
        iframe.src = 'about:blank';
        modal.style.display = 'none';
      }

      openBtn.addEventListener('click', function (e) { e.preventDefault(); openModal(); });
      closeBtn.addEventListener('click', function (e) { e.preventDefault(); closeModal(); });
      modal.addEventListener('click', function (e) { if (e.target === modal) closeModal(); });
    })();
  </script>
@endpush

