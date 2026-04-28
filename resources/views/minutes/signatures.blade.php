@extends('advanced-reports::pdf.layout')

@section('doc_title', 'Lista de assinaturas')
@section('doc_subtitle', 'Responsáveis — lista para assinatura')
@section('doc_year', (string) (($data['class']->ano_letivo ?? '') ?: ''))

@section('content')
  @php($class = $data['class'])
  @php($students = $data['students'] ?? collect())

  <h1>LISTA DE ASSINATURAS (RESPONSÁVEIS)</h1>

  <div class="box">
    <table>
      <tr><th>Instituição</th><td>{{ $class->instituicao }}</td></tr>
      <tr><th>Escola</th><td>{{ $class->escola }}</td></tr>
      <tr><th>Turma</th><td>{{ $class->turma }} ({{ $class->turno }})</td></tr>
      <tr><th>Curso/Série</th><td>{{ $class->curso }} — {{ $class->serie }}</td></tr>
      <tr><th>Ano letivo</th><td>{{ $class->ano_letivo }}</td></tr>
    </table>
  </div>

  <table>
    <thead>
    <tr>
      <th>#</th>
      <th>Aluno(a)</th>
      <th>Matrícula</th>
      <th>Assinatura do responsável</th>
    </tr>
    </thead>
    <tbody>
    @foreach($students as $idx => $row)
      <tr>
        <td>{{ $idx + 1 }}</td>
        <td>{{ $row['student'] }}</td>
        <td>{{ $row['registration_id'] }}</td>
        <td style="height: 24px;"></td>
      </tr>
    @endforeach
    </tbody>
  </table>

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

