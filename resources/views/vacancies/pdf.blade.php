@extends('advanced-reports::pdf.layout-landscape')

@section('doc_title', 'Vagas por turma')
@section('doc_subtitle', 'Capacidade, ocupação e vagas disponíveis')
@section('doc_year', (string) ($year ?? ''))
@section('formal_header', '1')

@section('content')
  @php($items = $data['items'] ?? collect())
  @php($s = $data['summary'] ?? [])
  @php($labels = $filterLabels ?? [])

  <style>
    .muted { color: #6b7280; }
    code { font-family: DejaVu Sans, Arial, sans-serif; font-size: 10px; }
    .box { border: 1px solid #e5e7eb; border-radius: 10px; padding: 10px 12px; margin: 10px 0 14px; background: #fff; }

    .ar-table { width: 100%; border-collapse: collapse; }
    .ar-table th, .ar-table td { border: 1px solid #e5e7eb; padding: 6px 8px; vertical-align: top; }
    .ar-table th { background: #f3f4f6; color: #111827; font-weight: 700; text-align: left; }
    .ar-table td { color: #111827; }
    .ar-table--striped tbody tr:nth-child(odd) td { background: #fafafa; }
    .ar-table--compact th, .ar-table--compact td { padding: 5px 7px; }

    .num { text-align: right; white-space: nowrap; }
    .nowrap { white-space: nowrap; }
  </style>

  <h1>VAGAS POR TURMA</h1>
  <p class="muted">
    Relatório gerado a partir de <code>pmieducar.turma.max_aluno</code> e enturmações ativas em <code>pmieducar.matricula_turma</code>.
  </p>

  <div class="box">
    <strong>Filtros aplicados</strong>
    <table class="ar-table ar-table--compact" style="margin-top: 8px;">
      <tr>
        <th>Instituição</th><td>{{ $labels['instituicao'] ?? '-' }}</td>
        <th>Escola</th><td>{{ $labels['escola'] ?? '-' }}</td>
      </tr>
      <tr>
        <th>Curso</th><td>{{ $labels['curso'] ?? '-' }}</td>
        <th>Série</th><td>{{ $labels['serie'] ?? '-' }}</td>
      </tr>
      <tr>
        <th>Turma</th><td colspan="3">{{ $labels['turma'] ?? '-' }}</td>
      </tr>
    </table>
  </div>

  <div class="box">
    <table class="ar-table ar-table--compact">
      <tr>
        <th>Turmas</th><td>{{ (int) ($s['turmas'] ?? 0) }}</td>
        <th>Capacidade</th><td>{{ (int) ($s['capacidade'] ?? 0) }}</td>
        <th>Matriculados</th><td>{{ (int) ($s['matriculados'] ?? 0) }}</td>
        <th>Vagas</th><td><strong>{{ (int) ($s['vagas'] ?? 0) }}</strong></td>
      </tr>
    </table>
  </div>

  <table class="ar-table ar-table--striped">
    <thead>
    <tr>
      <th>Escola</th>
      <th>Turma</th>
      <th>Turno</th>
      <th>Curso</th>
      <th>Série</th>
      <th class="num">Capacidade</th>
      <th class="num">Matriculados</th>
      <th class="num">Vagas</th>
    </tr>
    </thead>
    <tbody>
    @foreach($items as $row)
      <tr>
        <td>{{ $row->escola }}</td>
        <td class="nowrap">{{ $row->turma }}</td>
        <td class="nowrap">{{ $row->turno }}</td>
        <td>{{ $row->curso }}</td>
        <td>{{ $row->serie }}</td>
        <td class="num">{{ (int) $row->capacidade }}</td>
        <td class="num">{{ (int) $row->matriculados }}</td>
        <td class="num"><strong>{{ (int) $row->vagas }}</strong></td>
      </tr>
    @endforeach
    </tbody>
  </table>

  @include('advanced-reports::pdf._issuer-signature', [
    'issuerName' => $issuerName ?? null,
    'schoolInep' => $schoolInep ?? null,
  ])

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
  ])
@endsection

