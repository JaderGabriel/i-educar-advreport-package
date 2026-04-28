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
  <h1>Boletim do aluno (PDF)</h1>

  <div class="advanced-report-card">
    <strong class="advanced-report-card-title">Emissão</strong>
    <p class="advanced-report-card-text">
      Informe a matrícula para gerar o boletim. O documento inclui código e QR Code para validação pública.
    </p>
  </div>

  <form method="get" action="{{ route('advanced-reports.boletim.pdf') }}" target="_blank" id="formcadastro">
    <table class="tablecadastro" width="100%" border="0" cellpadding="2" cellspacing="0" role="presentation">
      <tbody>
      <tr>
        <td class="formmdtd"><span class="form">Matrícula</span> <span class="campo_obrigatorio">*</span></td>
        <td class="formmdtd">
          <input class="geral obrigatorio" name="matricula_id" value="{{ $matriculaId }}" style="width: 120px;" placeholder="ID">
        </td>
      </tr>
      <tr>
        <td class="formlttd"><span class="form">Etapa</span></td>
        <td class="formlttd">
          <input class="geral" name="etapa" value="{{ $etapa }}" style="width: 80px;" placeholder="(opcional)">
          <span class="helper">Ex.: 1, 2, 3... ou Rc</span>
        </td>
      </tr>
      </tbody>
    </table>

    <div style="text-align: center; margin-top: 16px;">
      <button type="submit" class="btn-green">Gerar PDF</button>
    </div>
  </form>
@endsection

