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

  <div class="box">
    <strong>Frequência mensal</strong>
    <p class="muted" style="margin-top: 4px;">
      Observação: a frequência mensal depende de dados de diário/chamada por data. Quando não disponível, o valor pode aparecer como “—”.
    </p>
    <table style="margin-top: 8px;">
      <thead>
      <tr>
        <th style="width: 220px;">Mês</th>
        <th style="width: 160px;">% Frequência</th>
      </tr>
      </thead>
      <tbody>
      @foreach(($extra['frequencia_mensal'] ?? []) as $row)
        <tr>
          <td>{{ $row['label'] ?? '' }}</td>
          <td><strong>{{ isset($row['percent']) && $row['percent'] !== null ? ($row['percent'] . '%') : '—' }}</strong></td>
        </tr>
      @endforeach
      </tbody>
    </table>
  </div>

  <p>
    Declaramos, para os devidos fins, que o(a) aluno(a) acima identificado(a) possui frequência conforme percentual informado.
  </p>

  @include('advanced-reports::student-documents._footer')
@endsection

