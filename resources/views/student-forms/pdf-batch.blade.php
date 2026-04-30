@extends('advanced-reports::pdf.layout')

@section('doc_title', $title ?? 'Ficha')
@section('doc_subtitle', 'Emissão em lote • QR Code para validação')
@section('doc_year', (string) (data_get($items, '0.matricula.ano_letivo') ?? ''))
@section('formal_header', '1')

@section('content')
  @foreach(($items ?? []) as $it)
    @php($m = data_get($it, 'matricula'))
    @php($extra = data_get($it, 'extra', []))

    @if(($type ?? '') === 'enrollment')
      @include('advanced-reports::student-forms._ficha-matricula-inner', [
        'matricula' => $m,
        'extra' => $extra,
        'issuerName' => $issuerName ?? null,
        'schoolInep' => $schoolInep ?? null,
      ])
    @else
      @include('advanced-reports::student-forms._ficha-individual-inner', [
        'matricula' => $m,
        'extra' => $extra,
        'authorities' => $authorities ?? null,
      ])
    @endif

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
      'matriculaInternaAluno' => $m->matricula_id ?? null,
      'footerInline' => false,
    ])

    @if(!$loop->last)
      <div style="page-break-after: always;"></div>
    @endif
  @endforeach
@endsection

