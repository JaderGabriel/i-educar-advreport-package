@extends('advanced-reports::pdf.layout')

@section('doc_title', 'Boletim do aluno')
@section('doc_subtitle', 'Emissão em PDF (sem Jasper) • QR Code para validação')
@section('doc_year', (string) ($ano ?? ''))

@section('content')
  @php($m = $data['matricula'] ?? [])

  <h1>BOLETIM DO ALUNO</h1>

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
          <td>{{ $r['etapas'][(string) $i] ?? '-' }}</td>
        @endfor
        <td>{{ $r['etapas']['Rc'] ?? '-' }}</td>
      </tr>
    @endforeach
    </tbody>
  </table>

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

