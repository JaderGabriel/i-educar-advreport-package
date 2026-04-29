@extends('advanced-reports::pdf.layout-landscape')

@section('doc_title', 'Diploma/Certificado (modelo)')
@section('doc_subtitle', 'Documento modelo para impressão')
@section('doc_year', (string) ($year ?: date('Y')))
@section('formal_header', '1')

@section('content')
  @php
    $pages = $students ?? [[
        'studentName' => $studentName ?? 'ALUNO(A) EXEMPLO',
        'course' => $course ?? null,
        'class' => $class ?? null,
        'year' => $year ?? null,
        'matricula_id' => $enrollment ?? null,
        'issuedAt' => $issuedAt ?? now()->format('d/m/Y H:i'),
        'validationCode' => $validationCode ?? '',
        'validationUrl' => $validationUrl ?? '',
        'qrDataUri' => $qrDataUri ?? '',
    ]];
  @endphp

  <style>
    /* Evita sobreposição de múltiplos rodapés no mesmo PDF (o layout fixa .ar-official-footer). */
    .ar-official-footer { position: relative !important; left: auto !important; right: auto !important; bottom: auto !important; }

    body { font-family: "Times New Roman", serif; color: #111827; }
    .diploma-page {
      width: 100%;
      border: 1px solid #e5e7eb;
      padding: 22px 30px 18px 30px;
      box-sizing: border-box;
      position: relative;
      min-height: 520px;
    }
    .diploma-header { text-align: center; margin-bottom: 22px; }
    .diploma-title { font-size: 30px; margin-top: 6px; margin-bottom: 4px; }
    .diploma-subtitle { font-size: 14px; color: #4b5563; }
    .diploma-body { font-size: 13px; line-height: 1.65; margin-top: 16px; text-align: justify; }
    .diploma-sign-row {
      margin-top: 34px;
      display: flex;
      justify-content: space-between;
      gap: 18px;
      font-size: 11px;
    }
    .diploma-sign { width: 46%; text-align: center; }
    .diploma-line { border-top: 1px solid #111827; margin-top: 34px; padding-top: 4px; }
    .badge-side {
      position: absolute;
      top: 18px;
      right: 30px;
      font-size: 10px;
      padding: 3px 8px;
      border-radius: 9999px;
      border: 1px solid #9ca3af;
      text-transform: uppercase;
    }
    .verso { font-size: 12px; line-height: 1.65; text-align: justify; }
  </style>

  @foreach($pages as $page)
    @php($p = is_array($page) ? $page : [])
    @php($studentName = $p['studentName'] ?? ($studentName ?? 'ALUNO(A) EXEMPLO'))
    @php($courseName = $p['course'] ?? ($course ?? null))
    @php($className = $p['class'] ?? ($class ?? null))
    @php($yearText = $p['year'] ?? ($year ?? null))
    @php($matriculaId = $p['matricula_id'] ?? ($enrollment ?? null))
    @php($pIssuedAt = $p['issuedAt'] ?? ($issuedAt ?? now()->format('d/m/Y H:i')))
    @php($pCode = $p['validationCode'] ?? ($validationCode ?? ''))
    @php($pUrl = $p['validationUrl'] ?? ($validationUrl ?? ''))
    @php($pQr = $p['qrDataUri'] ?? ($qrDataUri ?? ''))

    @if($side === 'front' || $side === 'both')
      <div class="diploma-page">
        <div class="badge-side">Frente</div>

        <div class="diploma-header">
          <div class="diploma-title">Diploma de Conclusão</div>
          <div class="diploma-subtitle">
            Modelo:
            @switch($template)
              @case('modern') Moderno minimalista @break
              @case('seal') Oficial com brasão @break
              @case('bilingual') Bilíngue @break
              @default Clássico institucional
            @endswitch
          </div>
        </div>

        <div class="diploma-body">
          <p>
            Certificamos que <strong>{{ $studentName }}</strong>, regularmente matriculado(a) no curso
            <strong>{{ $courseName ?: '__________' }}</strong>, turma <strong>{{ $className ?: '__________' }}</strong>,
            concluiu, no ano letivo de <strong>{{ $yearText ?: '__________' }}</strong>, as exigências aplicáveis ao processo
            de conclusão conforme registros desta unidade.
          </p>
          <p>
            Este diploma é emitido para fins de comprovação de escolaridade, em conformidade com a legislação educacional vigente.
          </p>
          @if(!empty($matriculaId))
            <p>Referência de matrícula: <strong>{{ $matriculaId }}</strong>.</p>
          @endif
        </div>

        <div class="diploma-sign-row">
          <div class="diploma-sign">
            <div class="diploma-line">
              <div><strong>Direção</strong></div>
              <div>{{ $directorName ?: '' }}</div>
              @if(!empty($schoolInep))
                <div style="margin-top:4px;">INEP (escola): {{ $schoolInep }}</div>
              @endif
            </div>
          </div>
          <div class="diploma-sign">
            <div class="diploma-line">
              <div><strong>Secretaria</strong></div>
              <div>{{ $secretaryName ?: '' }}</div>
              @if(!empty($schoolInep))
                <div style="margin-top:4px;">INEP (escola): {{ $schoolInep }}</div>
              @endif
            </div>
          </div>
        </div>
      </div>

      @include('advanced-reports::student-documents._footer', [
        'issuedAt' => $pIssuedAt,
        'validationCode' => $pCode,
        'validationUrl' => $pUrl,
        'qrDataUri' => $pQr,
        'issuerName' => $issuerName ?? null,
        'issuerRole' => $issuerRole ?? null,
        'cityUf' => $cityUf ?? null,
        'book' => null,
        'page' => null,
        'record' => null,
      ])

      @if($side === 'both')
        <div style="page-break-after: always;"></div>
      @endif
    @endif

    @if($side === 'back' || $side === 'both')
      <div class="diploma-page">
        <div class="badge-side">Verso</div>
        <div class="verso">
          <p><strong>Observações legais / verso</strong></p>
          <p>
            Este documento é emitido como modelo institucional. A validade administrativa depende dos registros oficiais no sistema
            e da conformidade com normas locais da rede de ensino. Em caso de dúvidas, utilize o QR Code abaixo para validação.
          </p>
        </div>
      </div>

      @include('advanced-reports::student-documents._footer', [
        'issuedAt' => $pIssuedAt,
        'validationCode' => $pCode,
        'validationUrl' => $pUrl,
        'qrDataUri' => $pQr,
        'issuerName' => $issuerName ?? null,
        'issuerRole' => $issuerRole ?? null,
        'cityUf' => $cityUf ?? null,
        'book' => null,
        'page' => null,
        'record' => null,
      ])
    @endif

    @if(!$loop->last)
      <div style="page-break-after: always;"></div>
    @endif
  @endforeach
@endsection
