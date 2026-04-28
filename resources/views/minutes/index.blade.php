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
    <strong class="advanced-report-card-title">Atas e registros formais</strong>
    <p class="advanced-report-card-text">
      Emissão de atas em PDF com validação pública (QR Code). Selecione o tipo e informe a turma (ano/escola/série/turma).
    </p>
    <p class="advanced-report-card-text">
      Use <strong>“Ata de resultados finais”</strong> para registrar o quadro de situação; use <strong>“Lista de assinaturas”</strong> para colher confirmação dos responsáveis.
    </p>
  </div>

  <form method="get" action="{{ route('advanced-reports.minutes.pdf') }}" target="_blank" id="minutesForm">
    <table class="tablecadastro" width="100%" border="0" cellpadding="2" cellspacing="0" role="presentation">
      <tbody>
      <tr>
        <td class="formmdtd"><span class="form">Documento</span> <span class="campo_obrigatorio">*</span></td>
        <td class="formmdtd">
          <select class="geral obrigatorio" name="document" style="width: 320px;">
            <option value="final_results" @selected(($document ?? '') === 'final_results')>Ata de resultados finais</option>
            <option value="signatures" @selected(($document ?? '') === 'signatures')>Lista de assinaturas (responsáveis)</option>
          </select>
        </td>
      </tr>
      <tr>
        <td class="formlttd"><span class="form">Ano</span> <span class="campo_obrigatorio">*</span></td>
        <td class="formlttd">@include('form.select-year')</td>
      </tr>
      <tr>
        <td class="formmdtd"><span class="form">Instituição</span></td>
        <td class="formmdtd">@include('form.select-institution')</td>
      </tr>
      <tr>
        <td class="formlttd"><span class="form">Escola</span> <span class="campo_obrigatorio">*</span></td>
        <td class="formlttd">@include('form.select-school')</td>
      </tr>
      <tr>
        <td class="formmdtd"><span class="form">Série</span> <span class="campo_obrigatorio">*</span></td>
        <td class="formmdtd">@include('form.select-grade')</td>
      </tr>
      <tr>
        <td class="formlttd"><span class="form">Turma</span> <span class="campo_obrigatorio">*</span></td>
        <td class="formlttd">@include('form.select-school-class')</td>
      </tr>
      <tr>
        <td class="formmdtd"><span class="form">Emissor</span></td>
        <td class="formmdtd"><input class="geral" name="issuer_name" value="{{ request('issuer_name') }}" style="width: 320px;" placeholder="Nome do responsável"></td>
      </tr>
      <tr>
        <td class="formlttd"><span class="form">Cargo</span></td>
        <td class="formlttd"><input class="geral" name="issuer_role" value="{{ request('issuer_role') }}" style="width: 320px;" placeholder="Ex.: Secretaria Escolar"></td>
      </tr>
      <tr>
        <td class="formmdtd"><span class="form">Cidade/UF</span></td>
        <td class="formmdtd"><input class="geral" name="city_uf" value="{{ request('city_uf') }}" style="width: 160px;" placeholder="Ex.: Saubara/BA"></td>
      </tr>
      <tr>
        <td class="formmdtd"><span class="form">Detalhes</span></td>
        <td class="formmdtd">
          <label style="display:inline-flex;align-items:center;gap:6px;">
            <input type="checkbox" name="with_details" value="1" {{ request('with_details') ? 'checked' : '' }}>
            Incluir notas por componente/etapa e frequência (quando disponível)
          </label>
        </td>
      </tr>
      </tbody>
    </table>

    <div style="text-align:center;margin-top: 16px;">
      <button type="button" class="btn js-minutes-preview-open">Ver prévia</button>
      <button type="submit" class="btn-green">Gerar PDF</button>
    </div>
  </form>

  <div id="advancedReportsMinutesPreviewModal" class="ar-modal">
    <div class="ar-modal__dialog">
      <div class="ar-modal__header">
        <strong>Prévia</strong>
        <button type="button" class="btn js-minutes-preview-close">Fechar</button>
      </div>
      <iframe class="js-minutes-preview-iframe ar-modal__iframe"></iframe>
    </div>
  </div>
@endsection

@push('scripts')
  <script>
    (function () {
      const form = document.getElementById('minutesForm');
      const modal = document.getElementById('advancedReportsMinutesPreviewModal');
      const iframe = document.querySelector('.js-minutes-preview-iframe');
      const openBtn = document.querySelector('.js-minutes-preview-open');
      const closeBtn = document.querySelector('.js-minutes-preview-close');
      if (!form || !modal || !iframe || !openBtn || !closeBtn) return;

      function openModal() {
        const params = new URLSearchParams(new FormData(form));
        params.set('preview', '1');
        iframe.src = "{{ route('advanced-reports.minutes.pdf') }}" + "?" + params.toString();
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

