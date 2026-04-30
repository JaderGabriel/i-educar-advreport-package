@extends('advanced-reports::pdf.layout')

@section('doc_title', 'Histórico escolar')
@section('doc_subtitle', ($templateLabel ?? 'Documento oficial — histórico consolidado (modelo moderno)'))
@section('doc_year', '')
@section('formal_header', '1')

@section('content')
  @include('advanced-reports::school-history._pdf-modern-body')

  @include('advanced-reports::pdf._issuer-signature', [
    'issuerName' => $issuerName ?? null,
    'schoolInep' => $schoolInep ?? null,
  ])

  @include('advanced-reports::student-documents._authority-signatures', [
    'authorities' => $authorities ?? [],
  ])

  @include('advanced-reports::student-documents._footer', [
    'issuedAt' => $issuedAt,
    'validationCode' => $validationCode,
    'validationUrl' => $validationUrl,
    'qrDataUri' => $qrDataUri,
    'issuerName' => $issuerName ?? null,
    'issuerRole' => null,
    'cityUf' => null,
    'book' => $book ?? null,
    'page' => $page ?? null,
    'record' => $record ?? null,
  ])
@endsection
