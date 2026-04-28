@extends('advanced-reports::pdf.layout')

@section('doc_title', 'Declaração de escolaridade')
@section('doc_subtitle', 'Nada consta / vida escolar (resumo oficial)')
@section('doc_year', (string) (($matricula->ano_letivo ?? '') ?: ''))

@section('content')
  <h1>DECLARAÇÃO DE ESCOLARIDADE / NADA CONSTA</h1>

  <p>
    Declaramos, para os devidos fins, que <strong>{{ $matricula->aluno_nome }}</strong>,
    matrícula <strong>{{ $matricula->matricula_id }}</strong>, encontra-se vinculada à unidade
    <strong>{{ $matricula->escola }}</strong> ({{ $matricula->instituicao }}),
    no ano letivo de <strong>{{ $matricula->ano_letivo }}</strong>.
  </p>

  <p class="muted">
    Observação: esta declaração é um resumo oficial para fins administrativos. Informações sensíveis não são exibidas na validação pública.
  </p>

  @include('advanced-reports::student-documents._footer', [
    'issuedAt' => $issuedAt,
    'validationCode' => $validationCode,
    'validationUrl' => $validationUrl,
    'qrDataUri' => $qrDataUri,
    'issuerName' => $issuerName ?? null,
    'issuerRole' => $issuerRole ?? null,
    'cityUf' => $cityUf ?? null,
  ])
@endsection

