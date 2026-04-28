@extends('advanced-reports::pdf.layout')

@section('doc_title', 'Declaração de matrícula')
@section('doc_subtitle', 'Documento oficial — declaração')
@section('doc_year', (string) ($matricula->ano_letivo ?? ''))

@section('content')
  <h1>DECLARAÇÃO DE MATRÍCULA</h1>

  <p class="muted">
    Emitida com base nos registros do i-Educar para fins de comprovação.
  </p>

  <div class="box">
    <table>
      <tr><th>Aluno(a)</th><td>{{ $matricula->aluno_nome }}</td></tr>
      <tr><th>Matrícula (ID)</th><td>{{ $matricula->matricula_id }}</td></tr>
      <tr><th>Ano letivo</th><td>{{ $matricula->ano_letivo }}</td></tr>
      <tr><th>Instituição</th><td>{{ $matricula->instituicao }}</td></tr>
      <tr><th>Escola</th><td>{{ $matricula->escola }}</td></tr>
      <tr><th>Curso</th><td>{{ $matricula->curso }}</td></tr>
      <tr><th>Série</th><td>{{ $matricula->serie }}</td></tr>
      <tr><th>Turma</th><td>{{ $matricula->turma }}</td></tr>
    </table>
  </div>

  <p>
    Declaramos, para os devidos fins, que o(a) aluno(a) acima identificado(a) encontra-se regularmente matriculado(a)
    nesta unidade escolar no ano letivo informado.
  </p>

  @include('advanced-reports::student-documents._footer')
@endsection

