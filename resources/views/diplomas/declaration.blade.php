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
      position: relative;
      min-height: 100%;
    }
    h1 { font-size: 18px; margin: 0 0 10px; text-align: center; }
    .body { font-size: 12px; line-height: 1.6; text-align: justify; }
    .sign { position: absolute; left: 18px; right: 18px; bottom: 130px; display: flex; justify-content: space-between; gap: 18px; }
    .sig { width: 45%; text-align: center; font-size: 11px; }
    .line { border-top: 1px solid #111827; margin-top: 38px; padding-top: 4px; }
    .doc-meta {
      position: absolute;
      left: 18px;
      right: 18px;
      bottom: 18px;
      border: 1px dashed #cbd5e1;
      padding: 10px;
      font-size: 10px;
      color: #374151;
      display: flex;
      justify-content: space-between;
      gap: 12px;
      flex-wrap: wrap;
      background: #fff;
    }
    .code { font-family: DejaVu Sans, Arial, sans-serif; }
    .qr { width: 78px; height: 78px; border: 1px solid #e5e7eb; padding: 4px; background: #fff; }
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

    <div class="sign">
      <div class="sig">
        <div class="line">
          <div><strong>Secretaria</strong></div>
          <div>{{ $secretaryName ?: '' }}</div>
          @if(!empty($schoolInep))
            <div style="margin-top:4px;font-size:10px;">INEP (escola): {{ $schoolInep }}</div>
          @endif
        </div>
      </div>
      <div class="sig">
        <div class="line">
          <div><strong>Direção</strong></div>
          <div>{{ $directorName ?: '' }}</div>
          @if(!empty($schoolInep))
            <div style="margin-top:4px;font-size:10px;">INEP (escola): {{ $schoolInep }}</div>
          @endif
        </div>
      </div>
    </div>

    <div class="doc-meta">
      <div style="flex: 1; min-width: 220px;">
        <div><strong>Emissão</strong>: {{ $issuedAt ?? date('d/m/Y H:i') }}</div>
        @if(!empty($issuerName) || !empty($issuerRole))
          <div><strong>Emissor</strong>: {{ trim(($issuerName ?? '') . ' ' . (!empty($issuerRole) ? ('(' . $issuerRole . ')') : '')) }}</div>
          <div style="margin-top: 4px;">
            @include('advanced-reports::pdf._issuer-person-lines')
          </div>
        @endif
        @if(!empty($cityUf))
          <div><strong>Cidade/UF</strong>: {{ $cityUf }}</div>
        @endif
        <div class="code"><strong>Código</strong>: {{ $validationCode ?? '__________' }}</div>
        @if(!empty($validationUrl))
          <div class="code"><strong>Validação</strong>: {{ $validationUrl }}</div>
        @endif
      </div>
      <div style="min-width: 92px; text-align: right;">
        @if(!empty($qrDataUri))
          <img class="qr" src="{{ $qrDataUri }}" alt="QR Code validação">
        @endif
      </div>
    </div>
  </div>
@endsection

