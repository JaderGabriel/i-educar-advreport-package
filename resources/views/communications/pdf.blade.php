@extends('advanced-reports::pdf.layout')

@php($def = $definition ?? [])
@php($item0 = ($items ?? [])[0] ?? [])

@section('doc_title', $def['doc_title'] ?? 'COMUNICADO')
@section('doc_subtitle', $def['doc_subtitle'] ?? '')
@section('doc_year', (string) (($context ?? [])['ano_letivo'] ?? ''))
@section('formal_header', '1')

@section('content')
  @include('advanced-reports::communications._pdf-body', [
    'item' => $item0,
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
    'matriculaInternaAluno' => $item0['matricula_id'] ?? null,
    'footerInline' => false,
  ])
@endsection
