@extends('advanced-reports::pdf.layout')

@section('doc_title', 'Comprovante de matrícula e declaração de transferência')
@section('doc_subtitle', 'Documento oficial — duas folhas')
@section('doc_year', (string) ($matricula->ano_letivo ?? ''))
@section('formal_header', '1')

@section('content')
  <h1>FOLHA DE MATRÍCULA / COMPROVANTE</h1>

  <p class="muted">
    Primeira folha: período de permanência na turma conforme registros do i-Educar.
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
      @if(!empty($matricula->data_entrada_turma_br))
        <tr><th>Início na turma</th><td>{{ $matricula->data_entrada_turma_br }}</td></tr>
      @endif
      @if(!empty($matricula->data_fim_turma_br))
        <tr><th>Permaneceu até</th><td>{{ $matricula->data_fim_turma_br }}</td></tr>
      @endif
    </table>
  </div>

  <p>
    Declaramos, para os devidos fins, que o(a) aluno(a) acima identificado(a) esteve regularmente matriculado(a) nesta unidade escolar,
    na turma indicada, no período de
    @if(!empty($matricula->data_entrada_turma_br) && !empty($matricula->data_fim_turma_br))
      <strong>{{ $matricula->data_entrada_turma_br }}</strong> a <strong>{{ $matricula->data_fim_turma_br }}</strong>,
    @elseif(!empty($matricula->data_entrada_turma_br))
      <strong>{{ $matricula->data_entrada_turma_br }}</strong> até a data de saída registrada no sistema,
    @else
      acordo com os registros constantes no sistema,
    @endif
    no ano letivo informado.
  </p>

  <div style="page-break-after: always;"></div>

  <h1>GUIA / DECLARAÇÃO DE TRANSFERÊNCIA</h1>

  <p class="muted">
    Segunda folha: declaração para fins de transferência / continuidade dos estudos.
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
    Recomenda-se incluir, quando exigido pela rede: destino, data efetiva, motivo e responsáveis.
  </p>

  @include('advanced-reports::pdf._issuer-signature', [
    'issuerName' => $issuerName ?? null,
    'schoolInep' => $schoolInep ?? null,
  ])

  @include('advanced-reports::student-documents._footer')
@endsection
