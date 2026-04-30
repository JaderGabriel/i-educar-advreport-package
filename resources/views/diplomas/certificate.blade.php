@extends('advanced-reports::pdf.diploma-shell')

@php($pageTitle = 'Certificado — ' . ($year ?? date('Y')))

@push('styles')
  <style>
    @page { size: A4 landscape; margin: 8mm; }
    * { box-sizing: border-box; }
    html, body {
      margin: 0;
      padding: 0;
      font-family: "DejaVu Serif", "Times New Roman", Times, serif;
      color: #1a1a1a;
      -webkit-print-color-adjust: exact;
      print-color-adjust: exact;
    }
    .cert-sheet {
      width: 100%;
      margin: 0;
      padding: 0;
      position: relative;
      page-break-inside: avoid;
    }
    .cert-frame {
      position: relative;
      padding: 8mm 10mm;
      border: 3px double #6b4c1b;
      background: #fffef8;
    }
    .cert-entity {
      text-align: center;
      font-family: DejaVu Sans, sans-serif;
      font-size: 11px;
      color: #4a3728;
      line-height: 1.35;
      margin-bottom: 4mm;
      max-width: 100%;
      word-wrap: break-word;
    }
    .cert-title {
      text-align: center;
      font-size: 32px;
      font-weight: 700;
      letter-spacing: 0.06em;
      margin: 2mm 0 2mm;
      color: #2c1810;
      text-transform: uppercase;
    }
    .cert-body {
      margin-top: 5mm;
      font-size: 16px;
      line-height: 1.8;
      text-align: justify;
      max-width: 100%;
      word-wrap: break-word;
      overflow-wrap: break-word;
    }
    .cert-body p { margin: 0 0 4mm 0; }
    .cert-signatures {
      margin-top: 8mm;
      width: 100%;
      display: table;
      table-layout: fixed;
    }
    .cert-signatures .cell {
      display: table-cell;
      width: 50%;
      vertical-align: bottom;
      text-align: center;
      padding: 0 4mm;
    }
    .cert-signatures .line {
      border-top: 2px solid #2c1810;
      padding-top: 3mm;
      margin-top: 16mm;
      font-size: 12px;
    }
    .cert-signatures .role { font-weight: 700; font-size: 13px; margin-bottom: 1mm; }
    .cert-signatures .name { font-size: 12px; color: #374151; min-height: 14px; }
    .cert-footer-inline { margin-top: 10mm; }
  </style>
@endpush

@section('content')
  <div class="cert-sheet">
    <div class="cert-frame">
      @if(!empty($municipality) || !empty($schoolName))
        <div class="cert-entity">
          @if(!empty($municipality))<div>{{ $municipality }}</div>@endif
          @if(!empty($schoolName))<div><strong>{{ $schoolName }}</strong></div>@endif
          @if(!empty($contact))<div>{{ $contact }}</div>@endif
        </div>
      @endif

      <div class="cert-title">Certificado</div>

      <div class="cert-body">
        <p>
          Certificamos que <strong>{{ $studentName ?? 'ALUNO(A) EXEMPLO' }}</strong> concluiu, no ano letivo de
          <strong>{{ $year ?: '__________' }}</strong>, o curso/etapa <strong>{{ $course ?: '__________' }}</strong>,
          atendendo às exigências legais e regimentais aplicáveis.
        </p>
        <p>
          Este certificado é emitido para fins de comprovação de escolaridade, em conformidade com a legislação vigente.
        </p>
        @if(!empty($enrollment))
          <p>Registro de matrícula (i-Educar): <strong>{{ $enrollment }}</strong>.</p>
        @endif
      </div>

      <div class="cert-signatures">
        <div class="cell">
          <div class="line">
            <div class="role">Secretário(a) escolar</div>
            <div class="name">{{ $secretaryName ?: ' ' }}</div>
            @if(!empty($secretaryInep) || !empty($secretaryMatriculaInterna))
              <div class="muted" style="margin-top: 2px; font-size: 9px;">
                @if(!empty($secretaryInep))INEP: {{ $secretaryInep }}@endif
                @if(!empty($secretaryInep) && !empty($secretaryMatriculaInterna)) • @endif
                @if(!empty($secretaryMatriculaInterna))Matrícula interna: {{ $secretaryMatriculaInterna }}@endif
              </div>
            @endif
          </div>
        </div>
        <div class="cell">
          <div class="line">
            <div class="role">Diretor(a)</div>
            <div class="name">{{ $directorName ?: ' ' }}</div>
            @if(!empty($directorInep) || !empty($directorMatriculaInterna))
              <div class="muted" style="margin-top: 2px; font-size: 9px;">
                @if(!empty($directorInep))INEP: {{ $directorInep }}@endif
                @if(!empty($directorInep) && !empty($directorMatriculaInterna)) • @endif
                @if(!empty($directorMatriculaInterna))Matrícula interna: {{ $directorMatriculaInterna }}@endif
              </div>
            @endif
          </div>
        </div>
      </div>

      <div class="cert-footer-inline">
        @include('advanced-reports::student-documents._footer', [
          'footerInline' => true,
          'issuedAt' => $issuedAt ?? date('d/m/Y H:i'),
          'validationCode' => $validationCode ?? '',
          'validationUrl' => $validationUrl ?? '',
          'qrDataUri' => $qrDataUri ?? null,
          'issuerName' => $issuerName ?? null,
          'issuerRole' => null,
          'cityUf' => null,
          'book' => null,
          'page' => null,
          'record' => null,
          'matriculaInternaAluno' => $enrollment ?? null,
        ])
      </div>
    </div>
  </div>
@endsection
