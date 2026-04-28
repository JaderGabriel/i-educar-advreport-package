@extends('advanced-reports::pdf.layout')

@section('doc_title', 'Declaração de frequência')
@section('doc_subtitle', 'Documento oficial — declaração')
@section('doc_year', (string) ($matricula->ano_letivo ?? ''))
@section('formal_header', '1')

@section('content')
  <h1>DECLARAÇÃO DE FREQUÊNCIA</h1>

  <p class="muted">
    Percentual calculado pela função <code>modules.frequencia_da_matricula</code>.
  </p>

  <div class="box">
    <table>
      <tr><th>Aluno(a)</th><td>{{ $matricula->aluno_nome }}</td></tr>
      <tr><th>Matrícula (ID)</th><td>{{ $matricula->matricula_id }}</td></tr>
      <tr><th>Ano letivo</th><td>{{ $matricula->ano_letivo }}</td></tr>
      <tr><th>Escola</th><td>{{ $matricula->escola }}</td></tr>
      <tr><th>Curso/Série/Turma</th><td>{{ $matricula->curso }} — {{ $matricula->serie }} — {{ $matricula->turma }}</td></tr>
      <tr><th>% Frequência</th><td><strong>{{ $extra['frequencia_percentual'] ?? '-' }}%</strong></td></tr>
    </table>
  </div>

  <p>
    Declaramos, para os devidos fins, que o(a) aluno(a) acima identificado(a) possui frequência conforme percentual informado.
  </p>

  @include('advanced-reports::student-documents._footer')
@endsection

