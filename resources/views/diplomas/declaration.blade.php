@extends('advanced-reports::pdf.layout')

@section('doc_title', 'Declaração (modelo)')
@section('doc_subtitle', 'Documento oficial — modelo para impressão')
@section('doc_year', (string) ($year ?: date('Y')))
@section('formal_header', '1')
@section('doc_municipality', (string) ($municipality ?? ''))
@section('doc_school', (string) ($schoolName ?? ''))
@section('doc_contact', (string) ($contact ?? ''))

@section('content')
  <style>
    body { font-family: DejaVu Sans, Arial, sans-serif; color: #111827; }
    .page {
      border: 1px solid #e5e7eb;
      padding: 18px 18px;
      box-sizing: border-box;
    }
    h1 { font-size: 18px; margin: 0 0 10px; text-align: center; }
    .body { font-size: 12px; line-height: 1.6; text-align: justify; }
    .code { font-family: DejaVu Sans, Arial, sans-serif; }
  </style>

  <div class="page">
    <h1>DECLARAÇÃO</h1>

    <div class="body">
      <p>
        Declaramos, para os devidos fins, que <strong>{{ $studentName ?? 'ALUNO(A) EXEMPLO' }}</strong>, regularmente matriculado(a) no curso/etapa
        <strong>{{ $course ?: '__________' }}</strong>, turma <strong>{{ $class ?: '__________' }}</strong>, no ano letivo
        de <strong>{{ $year ?: '__________' }}</strong>, encontra-se em situação regular conforme registros desta unidade.
      </p>
      <p>
        A presente declaração é emitida a pedido do(a) interessado(a), para apresentação onde se fizer necessário.
      </p>
      @if(!empty($enrollment))
        <p>Matrícula selecionada: <strong>{{ $enrollment }}</strong>.</p>
      @endif
    </div>

    @include('advanced-reports::student-documents._authority-signatures', [
      'authorities' => [
        'secretary' => [
          'name' => $secretaryName ?? '',
          'inep' => $secretaryInep ?? null,
          'matricula_interna' => $secretaryMatriculaInterna ?? null,
        ],
        'director' => [
          'name' => $directorName ?? '',
          'inep' => $directorInep ?? null,
          'matricula_interna' => $directorMatriculaInterna ?? null,
        ],
      ],
    ])

    @include('advanced-reports::pdf._issuer-signature', [
      'issuerName' => $issuerName ?? null,
      'schoolInep' => $schoolInep ?? null,
    ])

    @include('advanced-reports::student-documents._footer', [
      'footerInline' => true,
      'issuedAt' => $issuedAt ?? date('d/m/Y H:i'),
      'validationCode' => $validationCode ?? '__________',
      'validationUrl' => $validationUrl ?? '',
      'qrDataUri' => $qrDataUri ?? null,
      'issuerName' => $issuerName ?? null,
      'issuerRole' => $issuerRole ?? null,
      'cityUf' => $cityUf ?? null,
      'book' => null,
      'page' => null,
      'record' => null,
      'matriculaInternaAluno' => $enrollment ?? null,
    ])
  </div>
@endsection

