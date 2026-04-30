@extends('advanced-reports::pdf.layout')

@section('doc_title', 'Declaração de matrícula')
@section('doc_subtitle', 'Documento oficial — declaração')
@section('doc_year', (string) ($matricula->ano_letivo ?? ''))
@section('formal_header', '1')

@section('content')
  <h1>DECLARAÇÃO DE MATRÍCULA</h1>

  <p class="muted">
    Emitida com base nos registros do i-Educar para fins de comprovação.
  </p>

  @include('advanced-reports::student-documents._matricula-data-box', ['matricula' => $matricula])

  <p>
    Declaramos, para os devidos fins, que o(a) aluno(a) acima identificado(a) encontra-se regularmente matriculado(a)
    nesta unidade escolar no ano letivo informado.
  </p>

  @include('advanced-reports::pdf._issuer-signature', [
    'issuerName' => $issuerName ?? null,
    'schoolInep' => $schoolInep ?? null,
  ])

  @include('advanced-reports::student-documents._footer')
@endsection

