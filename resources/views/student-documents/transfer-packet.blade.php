@extends('advanced-reports::pdf.layout')

@section('doc_title', 'Comprovante de matrícula e declaração de transferência')
@section('doc_subtitle', 'Documento oficial — dois documentos')
@section('doc_year', (string) ($matricula->ano_letivo ?? ''))
@section('formal_header', '1')

@section('content')
  {{-- Documento 1: comprovante / folha de matrícula (folha completa com assinaturas e rodapé) --}}
  <h1>FOLHA DE MATRÍCULA / COMPROVANTE</h1>

  <p class="muted">
    Comprovante de permanência na turma conforme registros do i-Educar.
  </p>

  @include('advanced-reports::student-documents._matricula-data-box', [
    'matricula' => $matricula,
    'showInstituicao' => true,
    'entradaLabel' => 'Início na turma',
    'saidaLabel' => 'Permaneceu até',
  ])

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

  @include('advanced-reports::student-documents._authority-signatures')

  @include('advanced-reports::student-documents._footer', [
    'matriculaInternaAluno' => $matricula->matricula_id,
    'footerInline' => true,
  ])

  <div style="page-break-after: always;"></div>

  {{-- Documento 2: guia de transferência (nova folha completa) --}}
  <h1>GUIA / DECLARAÇÃO DE TRANSFERÊNCIA</h1>

  <p class="muted">
    Declaração para fins de transferência / continuidade dos estudos.
  </p>

  @include('advanced-reports::student-documents._matricula-data-box', [
    'matricula' => $matricula,
    'showInstituicao' => false,
    'entradaLabel' => 'Início na turma',
    'saidaLabel' => 'Permaneceu até',
  ])

  <p>
    Declaramos que o(a) aluno(a) acima identificado(a) está vinculado(a) à escola de origem indicada, para fins de
    transferência/continuidade dos estudos, conforme registros no i-Educar.
  </p>

  @include('advanced-reports::student-documents._transfer-documentos-adicionais-observacao')

  <p class="muted">
    Recomenda-se incluir, quando exigido pela rede: destino, data efetiva, motivo e responsáveis.
  </p>

  @include('advanced-reports::student-documents._authority-signatures')

  @include('advanced-reports::student-documents._footer', [
    'matriculaInternaAluno' => $matricula->matricula_id,
    'footerInline' => true,
  ])
@endsection
