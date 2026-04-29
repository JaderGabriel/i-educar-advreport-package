@extends('advanced-reports::pdf.layout')

@section('doc_title', 'Guia/Declaração de transferência')
@section('doc_subtitle', 'Documento oficial — guia/declaração')
@section('doc_year', (string) ($matricula->ano_letivo ?? ''))
@section('formal_header', '1')

@section('content')
  <h1>GUIA / DECLARAÇÃO DE TRANSFERÊNCIA</h1>

  <p class="muted">
    Modelo inicial. Pode ser ajustado para seguir política local (rede) e incluir campos adicionais.
  </p>

  <div class="box">
    <table>
      <tr><th>Aluno(a)</th><td>{{ $matricula->aluno_nome }}</td></tr>
      <tr><th>Matrícula (ID)</th><td>{{ $matricula->matricula_id }}</td></tr>
      <tr><th>Ano letivo</th><td>{{ $matricula->ano_letivo }}</td></tr>
      <tr><th>Escola de origem</th><td>{{ $matricula->escola }}</td></tr>
      <tr><th>Curso/Série/Turma</th><td>{{ $matricula->curso }} — {{ $matricula->serie }} — {{ $matricula->turma }}</td></tr>
    </table>
  </div>

  <p>
    Declaramos que o(a) aluno(a) acima identificado(a) está vinculado(a) à escola de origem indicada, para fins de
    transferência/continuidade dos estudos, conforme registros no i-Educar.
  </p>

  <p class="muted">
    Recomenda-se incluir: destino, data efetiva, motivo, responsáveis, e eventuais pendências/documentos anexos (por rede).
  </p>

  @include('advanced-reports::pdf._issuer-signature', [
    'issuerName' => $issuerName ?? null,
    'schoolInep' => $schoolInep ?? null,
  ])

  @include('advanced-reports::student-documents._footer')
@endsection

