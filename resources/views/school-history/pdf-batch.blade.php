@extends('advanced-reports::pdf.layout')

@section('doc_title', 'Histórico escolar')
@section('doc_subtitle', ($templateLabel ?? 'Emissão em lote • QR Code para validação'))
@section('doc_year', '')

@section('content')
  @foreach(($items ?? []) as $it)
    @php($data = $it['data'] ?? [])

    @if(($template ?? 'classic') === 'modern')
      @include('advanced-reports::school-history._pdf-modern-body')
    @else
      @include('advanced-reports::school-history._pdf-classic-body')
    @endif

    @include('advanced-reports::student-documents._footer', [
      'issuedAt' => $issuedAt,
      'validationCode' => $it['validationCode'] ?? '',
      'validationUrl' => $it['validationUrl'] ?? '',
      'qrDataUri' => $it['qrDataUri'] ?? null,
      'issuerName' => null,
      'issuerRole' => null,
      'cityUf' => null,
      'book' => $book ?? null,
      'page' => $page ?? null,
      'record' => $record ?? null,
    ])

    @if(!$loop->last)
      <div style="page-break-after: always;"></div>
    @endif
  @endforeach
@endsection
