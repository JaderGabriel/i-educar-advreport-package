@extends('advanced-reports::pdf.diploma-shell')

@php
  $pageTitle = 'Diploma — ' . ($year ?? date('Y'));
  $brasaoUrl = url('intranet/imagens/brasao-republica.png');
@endphp

@push('styles')
  <style>
    @page { size: A4 landscape; margin: 0; }
    * { box-sizing: border-box; }
    html, body {
      margin: 0;
      padding: 0;
      font-family: "DejaVu Serif", "Times New Roman", Times, serif;
      color: #1a1a1a;
      -webkit-print-color-adjust: exact;
      print-color-adjust: exact;
    }
    .diploma-sheet {
      width: 297mm;
      height: 210mm;
      max-width: 100%;
      margin: 0;
      padding: 10mm 12mm 12mm 12mm;
      position: relative;
      overflow: hidden;
      page-break-inside: avoid;
    }
    .diploma-page-break-after {
      page-break-after: always;
    }
    .diploma-frame {
      position: relative;
      z-index: 1;
      height: 100%;
      padding: 6mm 8mm 5mm 8mm;
      border: 3px double #6b4c1b;
      outline: 1px solid #2c1810;
      outline-offset: 2mm;
      background: #fffef8;
    }
    .diploma-badge {
      position: absolute;
      top: 4mm;
      right: 5mm;
      z-index: 2;
      font-size: 9px;
      letter-spacing: 0.12em;
      text-transform: uppercase;
      color: #5c4033;
      font-family: DejaVu Sans, sans-serif;
    }
    .diploma-entity {
      text-align: center;
      font-family: DejaVu Sans, sans-serif;
      font-size: 11px;
      color: #4a3728;
      line-height: 1.35;
      margin-bottom: 4mm;
      max-width: 100%;
      word-wrap: break-word;
    }
    .diploma-title {
      text-align: center;
      font-size: 34px;
      font-weight: 700;
      letter-spacing: 0.04em;
      margin: 2mm 0 1mm;
      color: #2c1810;
      text-transform: uppercase;
    }
    .diploma-subline {
      text-align: center;
      font-size: 13px;
      color: #5c4033;
      margin-bottom: 5mm;
      font-style: italic;
    }
    .diploma-body {
      font-size: 15px;
      line-height: 1.75;
      text-align: justify;
      max-width: 100%;
      word-wrap: break-word;
      overflow-wrap: break-word;
      hyphens: auto;
    }
    .diploma-body p { margin: 0 0 3.5mm 0; }
    .diploma-signatures {
      margin-top: 6mm;
      width: 100%;
      display: table;
      table-layout: fixed;
    }
    .diploma-signatures .cell {
      display: table-cell;
      width: 50%;
      vertical-align: bottom;
      text-align: center;
      padding: 0 4mm;
    }
    .diploma-signatures .line {
      border-top: 2px solid #2c1810;
      padding-top: 3mm;
      margin-top: 14mm;
      font-size: 12px;
    }
    .diploma-signatures .role {
      font-weight: 700;
      font-size: 13px;
      margin-bottom: 1mm;
    }
    .diploma-signatures .name {
      font-size: 12px;
      color: #374151;
      min-height: 14px;
    }
    .diploma-footer {
      position: absolute;
      left: 14mm;
      right: 14mm;
      bottom: 8mm;
      font-family: DejaVu Sans, sans-serif;
      font-size: 8px;
      color: #374151;
      border-top: 1px dashed #b8a88a;
      padding-top: 2mm;
    }
    .diploma-footer table {
      width: 100%;
      border-collapse: collapse;
    }
    .diploma-footer td { vertical-align: top; word-break: break-word; }
    .diploma-footer .qr { width: 64px; height: 64px; border: 1px solid #d1c4b0; padding: 2px; background: #fff; }
    .diploma-verso .diploma-frame {
      background: #fffef8;
    }
    .diploma-verso-bg {
      position: absolute;
      left: 0;
      top: 0;
      right: 0;
      bottom: 0;
      z-index: 0;
      background-color: #fffef8;
      background-image: url('{{ $brasaoUrl }}');
      background-position: center center;
      background-repeat: no-repeat;
      background-size: 58% auto;
      opacity: 0.14;
      pointer-events: none;
    }
    .diploma-verso .diploma-frame {
      position: relative;
      z-index: 1;
      background: rgba(255, 254, 248, 0.92);
    }
    .diploma-verso .diploma-body { font-size: 14px; }
    .diploma-director-solo {
      margin-top: 8mm;
      text-align: center;
      max-width: 70mm;
      margin-left: auto;
      margin-right: auto;
    }
    .diploma-director-solo .line {
      border-top: 2px solid #2c1810;
      padding-top: 3mm;
      margin-top: 10mm;
    }
    .diploma-director-solo .role { font-weight: 700; font-size: 13px; }
    .diploma-director-solo .name { font-size: 12px; color: #374151; margin-top: 1mm; }
  </style>
@endpush

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

    @php($breakAfterFront = ($side === 'both') || (($side === 'front') && ! $loop->last))
    @php($breakAfterVerso = ($side === 'both' || $side === 'back') && ! $loop->last)

    @if($side === 'front' || $side === 'both')
      <div class="diploma-sheet diploma-frente {{ $breakAfterFront ? 'diploma-page-break-after' : '' }}">
        <div class="diploma-badge">Frente</div>
        <div class="diploma-frame">
          @if(!empty($municipality) || !empty($schoolName))
            <div class="diploma-entity">
              @if(!empty($municipality))<div>{{ $municipality }}</div>@endif
              @if(!empty($schoolName))<div><strong>{{ $schoolName }}</strong></div>@endif
              @if(!empty($contact))<div>{{ $contact }}</div>@endif
            </div>
          @endif

          <div class="diploma-title">Diploma de conclusão</div>
          <div class="diploma-subline">Documento de habilitação escolar</div>

          <div class="diploma-body">
            <p>
              Certificamos que <strong>{{ $studentName }}</strong>, regularmente matriculado(a) no curso
              <strong>{{ $courseName ?: '__________' }}</strong>, turma <strong>{{ $className ?: '__________' }}</strong>,
              concluiu, no ano letivo de <strong>{{ $yearText ?: '__________' }}</strong>, as exigências aplicáveis ao processo
              de conclusão, conforme registros desta unidade de ensino.
            </p>
            <p>
              Este diploma é emitido para fins de comprovação de escolaridade, em conformidade com a legislação educacional vigente.
            </p>
            @if(!empty($matriculaId))
              <p>Registro de matrícula (i-Educar): <strong>{{ $matriculaId }}</strong>.</p>
            @endif
          </div>

          <div class="diploma-signatures">
            <div class="cell">
              <div class="line">
                <div class="role">Secretário(a) escolar</div>
                <div class="name">{{ $secretaryName ?: ' ' }}</div>
              </div>
            </div>
            <div class="cell">
              <div class="line">
                <div class="role">Diretor(a)</div>
                <div class="name">{{ $directorName ?: ' ' }}</div>
              </div>
            </div>
          </div>

          <div class="diploma-footer">
            <table>
              <tr>
                <td>
                  <div><strong>Emissão</strong>: {{ $pIssuedAt }}</div>
                  @if(!empty($pCode))
                    <div><strong>Código de validação</strong>: {{ $pCode }}</div>
                  @endif
                  @if(!empty($pUrl))
                    <div style="word-break: break-all;"><strong>Validação</strong>: {{ $pUrl }}</div>
                  @endif
                  @if(!empty($schoolInep))
                    <div><strong>INEP (escola)</strong>: {{ $schoolInep }}</div>
                  @endif
                </td>
                <td style="width: 72px; text-align: right;">
                  @if(!empty($pQr))
                    <img class="qr" src="{{ $pQr }}" alt="QR">
                  @endif
                </td>
              </tr>
            </table>
          </div>
        </div>
      </div>
    @endif

    @if($side === 'back' || $side === 'both')
      <div class="diploma-sheet diploma-verso {{ $breakAfterVerso ? 'diploma-page-break-after' : '' }}">
        <div class="diploma-verso-bg"></div>
        <div class="diploma-badge">Verso</div>
        <div class="diploma-frame">
          <div class="diploma-title" style="font-size: 26px;">Registro e observações</div>
          <div class="diploma-subline">Informações complementares do diploma</div>

          <div class="diploma-body">
            <p><strong>Observações legais</strong></p>
            <p>
              Este documento integra os registros escolares oficiais. A autenticidade pode ser conferida pelo código e pelo
              QR Code indicados na face frontal deste diploma. Documentos complementares (histórico escolar, registros em livro,
              entre outros) observam os prazos e procedimentos da legislação e do regulamento da rede de ensino.
            </p>
            <p>
              A validade administrativa e os efeitos legais decorrem da regularidade do processo de registro acadêmico no sistema
              institucional e da conformidade com as normas locais aplicáveis.
            </p>
          </div>

          <div class="diploma-director-solo">
            <div class="line">
              <div class="role">Diretor(a)</div>
              <div class="name">{{ $directorName ?: ' ' }}</div>
            </div>
          </div>

          <div class="diploma-footer">
            <table>
              <tr>
                <td>
                  <div><strong>Emissão</strong>: {{ $pIssuedAt }}</div>
                  @if(!empty($pCode))
                    <div><strong>Código de validação</strong>: {{ $pCode }}</div>
                  @endif
                </td>
                <td style="width: 72px; text-align: right;">
                  @if(!empty($pQr))
                    <img class="qr" src="{{ $pQr }}" alt="QR">
                  @endif
                </td>
              </tr>
            </table>
          </div>
        </div>
      </div>
    @endif
  @endforeach
@endsection
