@extends('advanced-reports::pdf.layout')

@section('doc_title', 'Guia/Declaração de transferência')
@section('doc_subtitle', 'Documento oficial — guia/declaração')
@section('doc_year', (string) ($matricula->ano_letivo ?? ''))
@section('formal_header', '1')

@section('content')
  <h1>GUIA / DECLARAÇÃO DE TRANSFERÊNCIA</h1>

  <p class="muted">
    Modelo para fins de transferência / continuidade dos estudos, conforme registros do i-Educar.
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
    Recomenda-se incluir: destino, data efetiva, motivo, responsáveis, e eventuais pendências/documentos anexos (por rede).
  </p>

  @include('advanced-reports::student-documents._authority-signatures')

  @include('advanced-reports::student-documents._footer', [
    'matriculaInternaAluno' => $matricula->matricula_id,
  ])
@endsection
