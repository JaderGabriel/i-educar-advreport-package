@extends('advanced-reports::pdf.layout-landscape')

@section('doc_title', 'Certificado (modelo)')
@section('doc_subtitle', 'Documento oficial — modelo para impressão')
@section('doc_year', (string) ($year ?: date('Y')))
@section('formal_header', '1')
@section('doc_municipality', (string) ($municipality ?? ''))
@section('doc_school', (string) ($schoolName ?? ''))
@section('doc_contact', (string) ($contact ?? ''))

@section('content')
  <style>
    body { font-family: "Times New Roman", serif; color: #111827; }
    .page {
      border: 1px solid #ddd;
      padding: 18px 18px;
      box-sizing: border-box;
      position: relative;
      min-height: 100%;
    }
    .title { text-align: center; margin-top: 18px; }
    .title h1 { font-size: 30px; margin: 0; }
    .title p { margin: 6px 0 0; font-size: 14px; color: #4b5563; }
    .body { margin-top: 24px; font-size: 14px; line-height: 1.7; text-align: justify; }
    .signatures { position: absolute; left: 18px; right: 18px; bottom: 120px; display: table; width: calc(100% - 36px); }
    .sig { display: table-cell; width: 50%; text-align: center; font-size: 12px; vertical-align: bottom; }
    .sig + .sig { padding-left: 18px; }
    .line { border-top: 1px solid #111827; margin-top: 42px; padding-top: 4px; }
    .doc-footer {
      position: absolute;
      left: 18px;
      right: 18px;
      bottom: 16px;
      font-size: 10px;
      color: #374151;
      display: flex;
      justify-content: space-between;
      gap: 12px;
      background: #fff;
    }
    .code { font-family: DejaVu Sans, Arial, sans-serif; font-size: 10px; }
    .qr { width: 78px; height: 78px; border: 1px solid #e5e7eb; padding: 4px; background: #fff; }
  </style>

  <div class="page">
    <div class="title">
      <h1>Certificado</h1>
    </div>

    <div class="body">
      <p>
        Certificamos que <strong>{{ $studentName ?? 'ALUNO(A) EXEMPLO' }}</strong> concluiu, no ano letivo de
        <strong>{{ $year ?: '__________' }}</strong>, o curso/etapa <strong>{{ $course ?: '__________' }}</strong>,
        atendendo às exigências legais e regimentais aplicáveis.
      </p>
      <p>
        Este certificado é emitido para fins de comprovação de escolaridade, em conformidade com a legislação vigente.
      </p>
      @if(!empty($enrollment))
        <p>Matrícula selecionada: <strong>{{ $enrollment }}</strong>.</p>
      @endif
    </div>

    <div class="signatures">
      <div class="sig">
        <div class="line">
          <div><strong>Secretaria Escolar</strong></div>
          <div>{{ $secretaryName ?: '' }}</div>
        </div>
      </div>
      <div class="sig">
        <div class="line">
          <div><strong>Direção</strong></div>
          <div>{{ $directorName ?: '' }}</div>
        </div>
      </div>
    </div>

    <div class="doc-footer">
      <div style="max-width: 58%;">
        <div><strong>Emissão</strong>: {{ $issuedAt ?? date('d/m/Y H:i') }}</div>
        @if(!empty($issuerName) || !empty($issuerRole))
          <div><strong>Emissor</strong>: {{ trim(($issuerName ?? '') . ' ' . (!empty($issuerRole) ? ('(' . $issuerRole . ')') : '')) }}</div>
        @endif
        @if(!empty($cityUf))
          <div><strong>Cidade/UF</strong>: {{ $cityUf }}</div>
        @endif
        <div class="code"><strong>Código</strong>: {{ $validationCode ?? '__________' }}</div>
        @if(!empty($validationUrl))
          <div class="code"><strong>Validação</strong>: {{ $validationUrl }}</div>
        @endif
      </div>
      <div style="text-align: right;">
        @if(!empty($qrDataUri))
          <img class="qr" src="{{ $qrDataUri }}" alt="QR Code validação">
        @endif
      </div>
    </div>
  </div>
@endsection

