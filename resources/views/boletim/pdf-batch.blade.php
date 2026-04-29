@extends('advanced-reports::pdf.layout')

@section('doc_title', 'Boletim do aluno')
@section('doc_subtitle', 'Emissão em lote • QR Code para validação')
@section('doc_year', (string) ($ano ?? ''))
@section('formal_header', '1')

@section('content')
  @foreach(($items ?? []) as $it)
    @php($data = $it['data'] ?? [])
    @php($m = $data['matricula'] ?? [])

    <h1>BOLETIM DO ALUNO</h1>
    <div class="muted" style="margin-bottom: 6px;">
      {{ $it['aluno_nome'] ?? '' }} • Matrícula {{ $it['matricula_id'] ?? '' }}
    </div>

    <div class="box">
      <table>
        <tr><th>Matrícula (ID)</th><td>{{ $m['cod_matricula'] ?? '' }}</td></tr>
        <tr><th>Ano letivo</th><td>{{ $m['ano'] ?? '' }}</td></tr>
        <tr><th>Escola</th><td>{{ $m['escola'] ?? '' }}</td></tr>
        <tr><th>Curso</th><td>{{ $m['curso'] ?? '' }}</td></tr>
        <tr><th>Série</th><td>{{ $m['serie'] ?? '' }}</td></tr>
        <tr><th>Turma</th><td>{{ $m['turma'] ?? '' }}</td></tr>
        <tr><th>% Frequência</th><td><strong>{{ $data['frequencia'] ?? '-' }}%</strong></td></tr>
      </table>
    </div>

    <h2>Componentes curriculares</h2>
    <table>
      <thead>
      <tr>
        <th>Componente</th>
        @for($i = 1; $i <= (int) ($data['etapas_count'] ?? 0); $i++)
          <th>Etapa {{ $i }}</th>
        @endfor
        <th>Rc</th>
      </tr>
      </thead>
      <tbody>
      @foreach(($data['rows'] ?? []) as $r)
        <tr>
          <td>{{ $r['nome'] }}</td>
          @for($i = 1; $i <= (int) ($data['etapas_count'] ?? 0); $i++)
            @php($cell = $r['etapas'][(string) $i] ?? null)
            <td>{{ is_array($cell) ? ($cell['nota'] ?? '-') : ($cell ?? '-') }}</td>
          @endfor
          @php($rc = $r['etapas']['Rc'] ?? null)
          <td>{{ is_array($rc) ? ($rc['nota'] ?? '-') : ($rc ?? '-') }}</td>
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
      'validationCode' => $it['validationCode'] ?? '',
      'validationUrl' => $it['validationUrl'] ?? '',
      'qrDataUri' => $it['qrDataUri'] ?? null,
      'issuerName' => null,
      'issuerRole' => null,
      'cityUf' => null,
      'book' => null,
      'page' => null,
      'record' => null,
    ])

    @if(!$loop->last)
      <div style="page-break-after: always;"></div>
    @endif
  @endforeach
@endsection

