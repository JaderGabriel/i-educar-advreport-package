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
  <h1>Documentos oficiais do aluno</h1>

  <div class="advanced-report-card">
    <strong class="advanced-report-card-title">Emissão</strong>
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
          </select>
        </td>
      </tr>
      <tr>
        <td class="formlttd"><span class="form">Matrícula</span> <span class="campo_obrigatorio">*</span></td>
        <td class="formlttd">
          <input class="geral obrigatorio" name="matricula_id" value="{{ $matriculaId }}" style="width: 120px;" placeholder="ID">
        </td>
      </tr>
      <tr>
        <td class="formmdtd"><span class="form">Emissor</span></td>
        <td class="formmdtd">
          <input class="geral" name="issuer_name" value="{{ request('issuer_name') }}" style="width: 320px;" placeholder="Nome do responsável">
        </td>
      </tr>
      <tr>
        <td class="formlttd"><span class="form">Cargo</span></td>
        <td class="formlttd">
          <input class="geral" name="issuer_role" value="{{ request('issuer_role') }}" style="width: 320px;" placeholder="Ex.: Secretaria Escolar">
        </td>
      </tr>
      <tr>
        <td class="formmdtd"><span class="form">Cidade/UF</span></td>
        <td class="formmdtd">
          <input class="geral" name="city_uf" value="{{ request('city_uf') }}" style="width: 160px;" placeholder="Ex.: Saubara/BA">
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
      <button type="submit" class="btn-green">Gerar PDF</button>
    </div>
  </form>
@endsection

