@extends('advanced-reports::pdf.layout')

@section('doc_title', 'Declaração de conclusão')
@section('doc_subtitle', 'Documento oficial — declaração')
@section('doc_year', (string) ($matricula->ano_letivo ?? ''))
@section('formal_header', '1')

@section('content')
  @include('advanced-reports::student-documents._declaration-conclusion-inner', [
    'matricula' => $matricula,
    'extra' => $extra ?? [],
    'issuerName' => $issuerName ?? null,
    'schoolInep' => $schoolInep ?? null,
  ])

  @include('advanced-reports::student-documents._footer', [
    'matriculaInternaAluno' => $matricula->matricula_id,
  ])
@endsection
