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
          <input class="geral obrigatorio" name="aluno_id" value="{{ $alunoId }}" style="width: 120px;" placeholder="ID">
        </td>
      </tr>
      </tbody>
    </table>

    <div style="text-align: center; margin-top: 16px;">
      <button type="submit" class="btn-green">Gerar PDF</button>
    </div>
  </form>
@endsection

