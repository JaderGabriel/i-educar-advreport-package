@extends('advanced-reports::pdf.layout')

@section('doc_title', 'Espelho de diário')
@section('doc_subtitle', 'Chamada por turma • Presença: “•” • Falta: “F”')
@section('doc_year', (string) (($data['class']->ano_letivo ?? '') ?: ''))
@section('formal_header', '1')

@section('content')
  @php($class = $data['class'])
  @php($days = $data['days'] ?? [])
  @php($students = $data['students'] ?? collect())

  <style>
    table.mirror { width: 100%; border-collapse: collapse; }
    table.mirror th, table.mirror td { border: 1px solid #cbd5e1; padding: 3px; font-size: 9px; }
    table.mirror th { background: #f1f5f9; text-align: center; }
    .mirror-student { width: 44mm; text-align: left; font-weight: 600; }
    .mirror-reg { width: 18mm; text-align: center; }
    .mirror-day { width: 7mm; text-align: center; }
    .mirror-day .wk { display:block; font-size: 7px; color: #64748b; margin-top: 1px; }
    .mirror-cell { height: 12px; text-align: center; font-size: 10px; }
    .legend-box { margin-top: 10px; }
  </style>

  <h1>ESPELHO DE DIÁRIO (CHAMADA)</h1>

  <div class="box">
    <table>
      <tr><th>Instituição</th><td>{{ $class->instituicao ?? '' }}</td></tr>
      <tr><th>Escola</th><td>{{ $class->escola ?? '' }}</td></tr>
      <tr><th>Turma</th><td>{{ $class->turma ?? '' }} ({{ $class->turno ?? '' }})</td></tr>
      <tr><th>Curso/Série</th><td>{{ $class->curso ?? '' }} — {{ $class->serie ?? '' }}</td></tr>
      <tr><th>Ano letivo</th><td>{{ $class->ano_letivo ?? '' }}</td></tr>
      <tr><th>Período</th><td>{{ $filters['data_inicial'] ?? '' }} a {{ $filters['data_final'] ?? '' }}</td></tr>
    </table>
  </div>

  <div class="box legend-box">
    <strong>Como preencher</strong>
    <div class="muted" style="margin-top: 6px; line-height: 1.45;">
      Para cada dia/coluna, marque a situação do aluno:
      <strong>Presença</strong> = “<strong>•</strong>” (ponto) e <strong>Falta</strong> = “<strong>F</strong>”.
      Outros registros (atraso, justificativa etc.) podem ser anotados no campo do dia conforme prática da rede.
    </div>
  </div>

  <table class="mirror">
    <thead>
    <tr>
      <th class="mirror-student">Aluno(a)</th>
      <th class="mirror-reg">Matr.</th>
      @foreach($days as $d)
        <th class="mirror-day">
          {{ $d['label'] ?? '' }}
          <span class="wk">{{ $d['weekday'] ?? '' }}</span>
        </th>
      @endforeach
    </tr>
    </thead>
    <tbody>
    @foreach($students as $idx => $s)
      <tr>
        <td class="mirror-student">{{ $s['student'] ?? '' }}</td>
        <td class="mirror-reg">{{ $s['registration_id'] ?? '' }}</td>
        @foreach($days as $d)
          <td class="mirror-cell"></td>
        @endforeach
      </tr>
    @endforeach
    </tbody>
  </table>

  @include('advanced-reports::pdf._issuer-signature', [
    'issuerName' => $issuerName ?? null,
    'schoolInep' => $schoolInep ?? null,
  ])

  @include('advanced-reports::student-documents._footer', [
    'issuedAt' => $issuedAt,
    'validationCode' => $validationCode,
    'validationUrl' => $validationUrl,
    'qrDataUri' => $qrDataUri,
    'issuerName' => $issuerName ?? null,
    'issuerRole' => $issuerRole ?? null,
    'cityUf' => $cityUf ?? null,
  ])
@endsection

