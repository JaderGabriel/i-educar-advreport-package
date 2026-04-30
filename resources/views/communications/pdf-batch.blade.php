@extends('advanced-reports::pdf.layout')

@php($def = $definition ?? [])

@section('doc_title', $def['doc_title'] ?? 'COMUNICADO')
@section('doc_subtitle', 'Emissão em lote — ' . ($def['title'] ?? ''))
@section('doc_year', (string) (($context ?? [])['ano_letivo'] ?? ''))
@section('formal_header', '1')

@section('content')
  @foreach(($items ?? []) as $it)
    @include('advanced-reports::communications._pdf-body', [
      'item' => $it,
      'fields' => $fields ?? [],
      'context' => $context ?? [],
      'definition' => $def,
      'issuerName' => $issuerName ?? null,
      'schoolInep' => $schoolInep ?? null,
    ])

    @include('advanced-reports::student-documents._footer', [
      'issuedAt' => $issuedAt ?? now()->format('d/m/Y H:i'),
      'validationCode' => $validationCode ?? '',
      'validationUrl' => $validationUrl ?? '',
      'qrDataUri' => $qrDataUri ?? '',
      'issuerName' => $issuerName ?? null,
      'issuerRole' => $issuerRole ?? null,
      'cityUf' => $cityUf ?? null,
      'book' => null,
      'page' => null,
      'record' => null,
      'matriculaInternaAluno' => $it['matricula_id'] ?? null,
      'footerInline' => false,
    ])

    @if(!$loop->last)
      <div style="page-break-after: always;"></div>
    @endif
  @endforeach
@endsection
