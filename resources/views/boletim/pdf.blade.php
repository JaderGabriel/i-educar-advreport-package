@extends('advanced-reports::pdf.layout')

@section('doc_title', 'Boletim do aluno')
@section('doc_subtitle', 'Emissão em PDF (sem Jasper) • QR Code para validação')
@section('doc_year', (string) ($ano ?? ''))
@section('formal_header', '1')

@section('content')
  @php($m = $data['matricula'] ?? [])

  <h1 style="text-align: center; margin-bottom: 10px;">Boletim do Aluno</h1>

  <div class="box">
    <table>
      <tr><th>Matrícula (ID)</th><td>{{ $m['cod_matricula'] ?? '' }}</td></tr>
      <tr><th>Ano letivo</th><td>{{ $m['ano'] ?? '' }}</td></tr>
      <tr><th>Aluno</th><td><strong>{{ $m['aluno_nome'] ?? '' }}</strong></td></tr>
      <tr><th>Data de nascimento</th><td>{{ $m['aluno_nascimento'] ?? '' }}</td></tr>
      <tr><th>Escola</th><td>{{ $m['escola'] ?? '' }}</td></tr>
      <tr><th>Curso</th><td>{{ $m['curso'] ?? '' }}</td></tr>
      <tr><th>Série</th><td>{{ $m['serie'] ?? '' }}</td></tr>
      <tr><th>Turma</th><td>{{ $m['turma'] ?? '' }}</td></tr>
      <tr><th>Turno</th><td>{{ $m['turno'] ?? '' }}</td></tr>
      <tr><th>Professor(a)</th><td>{{ $m['professor'] ?? '' }}</td></tr>
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
          <td>
            <div><strong>{{ is_array($cell) ? ($cell['nota'] ?? '-') : ($cell ?? '-') }}</strong></div>
            @if(is_array($cell) && array_key_exists('faltas', $cell) && $cell['faltas'] !== null)
              <div style="font-size: 10px; color: #555;">
                Faltas: {{ $cell['faltas'] }}@if(array_key_exists('faltas_pct', $cell) && $cell['faltas_pct'] !== null) ({{ $cell['faltas_pct'] }}%)@endif
              </div>
            @endif
          </td>
        @endfor
        @php($rc = $r['etapas']['Rc'] ?? null)
        <td>
          <div><strong>{{ is_array($rc) ? ($rc['nota'] ?? '-') : ($rc ?? '-') }}</strong></div>
          @if(is_array($rc) && array_key_exists('faltas', $rc) && $rc['faltas'] !== null)
            <div style="font-size: 10px; color: #555;">
              Faltas: {{ $rc['faltas'] }}@if(array_key_exists('faltas_pct', $rc) && $rc['faltas_pct'] !== null) ({{ $rc['faltas_pct'] }}%)@endif
            </div>
          @endif
        </td>
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
    'issuerName' => null,
    'issuerRole' => null,
    'cityUf' => null,
    'book' => null,
    'page' => null,
    'record' => null,
  ])
@endsection

