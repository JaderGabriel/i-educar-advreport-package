@extends('advanced-reports::pdf.layout')

@section('doc_title', 'Pendências de lançamento')
@section('doc_subtitle', 'Notas e/ou frequência (faltas) por turma')
@section('doc_year', (string) (($data['class']->ano_letivo ?? '') ?: ''))

@section('content')
  @php($class = $data['class'])
  @php($rows = $data['rows'] ?? collect())
  @php($s = $data['summary'] ?? [])

  <h1>PENDÊNCIAS DE LANÇAMENTO</h1>

  <div class="box">
    <table>
      <tr><th>Escola</th><td>{{ $class->escola }}</td></tr>
      <tr><th>Turma</th><td>{{ $class->turma }} ({{ $class->turno }})</td></tr>
      <tr><th>Curso/Série</th><td>{{ $class->curso }} — {{ $class->serie }}</td></tr>
      <tr><th>Ano letivo</th><td>{{ $class->ano_letivo }}</td></tr>
    </table>
  </div>

  <div class="box">
    <table>
      <tr>
        <th>Matrículas analisadas</th><td>{{ (int) ($s['registrations'] ?? 0) }}</td>
        <th>Pendências de nota</th><td>{{ (int) ($s['pending_grade_items'] ?? 0) }}</td>
        <th>Pendências de frequência</th><td>{{ (int) ($s['pending_frequency_items'] ?? 0) }}</td>
      </tr>
    </table>
  </div>

  <p class="muted">
    Observação: “pendência” aqui significa ausência de lançamento identificado via serviço de boletim do i-Educar.
  </p>

  <table>
    <thead>
    <tr>
      <th>Aluno(a)</th>
      <th>Matrícula</th>
      <th>Componente</th>
      <th>Etapa</th>
      <th>Pend. nota</th>
      <th>Pend. frequência</th>
    </tr>
    </thead>
    <tbody>
    @foreach($rows as $r)
      <tr>
        <td>{{ $r['student'] ?? '' }}</td>
        <td>{{ $r['registration_id'] ?? '' }}</td>
        <td>{{ $r['component'] ?? '' }}</td>
        <td>{{ $r['stage'] ?? '' }}</td>
        <td>{{ !empty($r['pending_grade']) ? 'Sim' : 'Não' }}</td>
        <td>{{ !empty($r['pending_frequency']) ? 'Sim' : 'Não' }}</td>
      </tr>
    @endforeach
    </tbody>
  </table>
@endsection

