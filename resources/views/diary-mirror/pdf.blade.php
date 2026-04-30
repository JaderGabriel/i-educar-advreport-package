@extends('advanced-reports::pdf.layout')

@section('doc_title', 'Espelho de diário')
@section('doc_subtitle', 'Chamada por turma • Presença: “•” • Falta: “F”')
@section('doc_year', (string) (($data['class']->ano_letivo ?? '') ?: ''))
@section('formal_header', '1')

@section('content')
  @php($class = $data['class'])
  @php($pages = $pages ?? [])
  @php($disc = $discipline ?? [])
  @php($totalPages = max(1, count($pages)))

  <style>
    table.mirror { width: 100%; border-collapse: collapse; table-layout: fixed; }
    table.mirror th, table.mirror td { border: 1px solid #cbd5e1; padding: 2px; font-size: 8px; word-wrap: break-word; }
    table.mirror th { background: #f1f5f9; text-align: center; }
    .mirror-student { width: 16%; text-align: left; font-weight: 600; }
    .mirror-reg { width: 7%; text-align: center; }
    .mirror-day { text-align: center; vertical-align: bottom; }
    .mirror-day .wk { display:block; font-size: 6px; color: #64748b; margin-top: 1px; line-height: 1.1; }
    .mirror-cell { height: 11px; text-align: center; font-size: 9px; }
    .mirror-page-tag { font-size: 8px; color: #475569; margin: 0 0 4px 0; }
    .legend-box { margin-top: 8px; }
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
      <tr><th>Componente curricular</th><td><strong>{{ $disc['componente_nome'] ?? '—' }}</strong></td></tr>
      <tr><th>Professor(a)</th><td><strong>{{ ($disc['docente_nome'] ?? '') !== '' ? $disc['docente_nome'] : '— (preencher se ausente no cadastro)' }}</strong></td></tr>
    </table>
  </div>

  <div class="box legend-box">
    <strong>Como preencher</strong>
    <div class="muted" style="margin-top: 4px; line-height: 1.4; font-size: 8px;">
      Dias úteis (segunda a sexta) no período; dias não letivos do calendário escolar da escola, quando vinculados a esta turma, não geram coluna; “dia extra letivo” (cadastro tipo E) pode gerar coluna inclusive em fim de semana.
      Para cada dia/coluna: <strong>Presença</strong> = “<strong>•</strong>” e <strong>Falta</strong> = “<strong>F</strong>”.
      @if($totalPages > 1)
        O documento foi dividido em <strong>{{ $totalPages }}</strong> página(s) para evitar corte de colunas ou linhas.
      @endif
    </div>
  </div>

  @foreach($pages as $pageIndex => $page)
    @php($days = $page['days'] ?? [])
    @php($students = collect($page['students'] ?? []))
    <div @if($pageIndex > 0) style="page-break-before: always;" @endif>
    <p class="mirror-page-tag">Página {{ $pageIndex + 1 }} de {{ $totalPages }} @if(count($days) > 0) — {{ count($days) }} dia(s) nesta página @endif</p>

    <table class="mirror">
      <colgroup>
        <col style="width: 16%">
        <col style="width: 7%">
        @foreach($days as $d)
          <col style="width: {{ count($days) > 0 ? round(77 / count($days), 2) : 77 }}%">
        @endforeach
      </colgroup>
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
      @forelse($students as $s)
        <tr>
          <td class="mirror-student">{{ $s['student'] ?? '' }}</td>
          <td class="mirror-reg">{{ $s['registration_id'] ?? '' }}</td>
          @foreach($days as $d)
            <td class="mirror-cell"></td>
          @endforeach
        </tr>
      @empty
        <tr>
          <td colspan="{{ 2 + count($days) }}" class="muted" style="text-align:center;">Sem alunos listados para esta turma no período.</td>
        </tr>
      @endforelse
      </tbody>
    </table>
    </div>
  @endforeach

  <div style="margin-top: 14px; text-align: center;">
    <div style="border-top: 1px solid #111827; max-width: 380px; margin: 0 auto; padding-top: 6px;">
      <strong>Assinatura do(a) professor(a) do componente</strong>
      <div style="margin-top: 4px; font-size: 10px;">{{ $disc['docente_nome'] ?? '' }}</div>
      <div class="muted" style="margin-top: 4px; font-size: 8px;">{{ $disc['componente_nome'] ?? '' }}</div>
    </div>
  </div>

  @include('advanced-reports::student-documents._footer', [
    'issuedAt' => $issuedAt,
    'validationCode' => $validationCode,
    'validationUrl' => $validationUrl,
    'qrDataUri' => $qrDataUri,
    'issuerName' => $issuerName ?? null,
    'issuerRole' => $issuerRole ?? null,
    'cityUf' => $cityUf ?? null,
    'footerInline' => true,
  ])
@endsection
