@extends('advanced-reports::pdf.layout')

@section('doc_title', 'FICHA INDIVIDUAL')
@section('doc_subtitle', 'Documento oficial • QR Code para validação')
@section('doc_year', (string) ($matricula->ano_letivo ?? ''))
@section('formal_header', '1')

@section('content')
  @include('advanced-reports::student-forms._ficha-individual-inner')

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
    'matriculaInternaAluno' => $matricula->matricula_id ?? null,
    'footerInline' => false,
  ])
@endsection

