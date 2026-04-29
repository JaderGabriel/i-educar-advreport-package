@extends('advanced-reports::pdf.layout')

@section('doc_title', 'Declaração de conclusão')
@section('doc_subtitle', 'Documento oficial — declaração')
@section('doc_year', (string) ($matricula->ano_letivo ?? ''))
@section('formal_header', '1')

@section('content')
  <h1 style="text-align:center;">DECLARAÇÃO DE CONCLUSÃO</h1>

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
      @if(!empty($extra['historico_emissao_dias']))
        <tr><th>Prazo informado (histórico)</th><td><strong>{{ (int) $extra['historico_emissao_dias'] }} dia(s)</strong></td></tr>
      @endif
    </table>
  </div>

  <p>
    Declaramos, para os devidos fins, que o(a) aluno(a) acima identificado(a) concluiu a etapa/cursamento conforme registros
    desta unidade escolar no ano letivo informado, podendo fazer jus à emissão do histórico escolar e demais documentos legais.
  </p>

  @if(!empty($extra['historico_emissao_dias']))
    <p>
      <strong>Observação:</strong> foi informado prazo de <strong>{{ (int) $extra['historico_emissao_dias'] }} dia(s)</strong>
      para emissão/entrega do histórico escolar, conforme solicitado no momento da emissão desta declaração.
    </p>
  @endif

  @include('advanced-reports::student-documents._footer')
@endsection
