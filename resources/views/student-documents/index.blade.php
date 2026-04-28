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
  <div class="advanced-report-card">
    <strong class="advanced-report-card-title">Documentos oficiais do aluno</strong>
    <p class="advanced-report-card-text">
      Informe a matrícula e selecione o documento. O PDF incluirá QR Code e código para validação pública.
    </p>
  </div>

  <form method="get" action="{{ route('advanced-reports.student-documents.pdf') }}" target="_blank" id="formcadastro">
    <table class="tablecadastro" width="100%" border="0" cellpadding="2" cellspacing="0" role="presentation">
      <tbody>
      <tr>
        <td class="formmdtd"><span class="form">Documento</span> <span class="campo_obrigatorio">*</span></td>
        <td class="formmdtd">
          <select class="geral obrigatorio" name="document" style="width: 320px;">
            <option value="declaration_enrollment" @selected(($document ?? '') === 'declaration_enrollment')>Declaração de matrícula</option>
            <option value="declaration_frequency" @selected(($document ?? '') === 'declaration_frequency')>Declaração de frequência</option>
            <option value="transfer_guide" @selected(($document ?? '') === 'transfer_guide')>Guia/Declaração de transferência</option>
            <option value="declaration_nada_consta" @selected(($document ?? '') === 'declaration_nada_consta')>Declaração de escolaridade / Nada consta</option>
          </select>
        </td>
      </tr>
      <tr>
        <td class="formlttd"><span class="form">Matrícula</span> <span class="campo_obrigatorio">*</span></td>
        <td class="formlttd">
          <input type="hidden" name="matricula_id" id="matricula_id" value="{{ $matriculaId }}">
          <input class="geral obrigatorio" id="matricula_search" list="matriculas_suggestions" value="{{ $matriculaId }}" style="width: 520px;" placeholder="Digite o nome do aluno ou o ID da matrícula">
          <datalist id="matriculas_suggestions"></datalist>
        </td>
      </tr>
      <tr>
        <td class="formmdtd"><span class="form">Emissor</span></td>
        <td class="formmdtd">
          <input class="geral" value="{{ auth()->user()?->name }}" style="width: 320px;" disabled>
        </td>
      </tr>
      <tr>
        <td class="formlttd"><span class="form">Cargo</span></td>
        <td class="formlttd">
          <input class="geral" value="(automático)" style="width: 320px;" disabled>
        </td>
      </tr>
      <tr>
        <td class="formmdtd"><span class="form">Cidade/UF</span></td>
        <td class="formmdtd">
          <input class="geral" value="(automático)" style="width: 160px;" disabled>
        </td>
      </tr>
      <tr>
      </tbody>
    </table>

    <div style="text-align: center; margin-top: 16px;">
      <button type="submit" class="btn-green">Gerar PDF</button>
    </div>
  </form>
@endsection

@push('scripts')
  <script>
    (function () {
      const input = document.getElementById('matricula_search');
      const hidden = document.getElementById('matricula_id');
      const list = document.getElementById('matriculas_suggestions');
      if (!input || !hidden || !list) return;

      function extractId(value) {
        const match = String(value || '').match(/^(\d+)\s+-\s+/);
        return match ? match[1] : (String(value || '').match(/^\d+$/) ? value : '');
      }

      async function loadSuggestions(q) {
        const url = "{{ route('advanced-reports.lookup.matriculas') }}" + "?q=" + encodeURIComponent(q);
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
@endpush

