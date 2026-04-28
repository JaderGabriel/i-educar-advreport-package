@extends('advanced-reports::pdf.layout-landscape')

@section('doc_title', 'Vagas por turma')
@section('doc_subtitle', 'Capacidade, ocupação e vagas disponíveis')
@section('doc_year', (string) ($year ?? ''))

@section('content')
  @php($items = $data['items'] ?? collect())
  @php($s = $data['summary'] ?? [])

  <h1>VAGAS POR TURMA</h1>
  <p class="muted">
    Relatório gerado a partir de <code>pmieducar.turma.max_aluno</code> e enturmações ativas em <code>pmieducar.matricula_turma</code>.
  </p>

  <div class="box">
    <table>
      <tr>
        <th>Turmas</th><td>{{ (int) ($s['turmas'] ?? 0) }}</td>
        <th>Capacidade</th><td>{{ (int) ($s['capacidade'] ?? 0) }}</td>
        <th>Matriculados</th><td>{{ (int) ($s['matriculados'] ?? 0) }}</td>
        <th>Vagas</th><td><strong>{{ (int) ($s['vagas'] ?? 0) }}</strong></td>
      </tr>
    </table>
  </div>

  <table>
    <thead>
    <tr>
      <th>Escola</th>
      <th>Turma</th>
      <th>Turno</th>
      <th>Curso</th>
      <th>Série</th>
      <th>Capacidade</th>
      <th>Matriculados</th>
      <th>Vagas</th>
    </tr>
    </thead>
    <tbody>
    @foreach($items as $row)
      <tr>
        <td>{{ $row->escola }}</td>
        <td>{{ $row->turma }}</td>
        <td>{{ $row->turno }}</td>
        <td>{{ $row->curso }}</td>
        <td>{{ $row->serie }}</td>
        <td>{{ (int) $row->capacidade }}</td>
        <td>{{ (int) $row->matriculados }}</td>
        <td><strong>{{ (int) $row->vagas }}</strong></td>
      </tr>
    @endforeach
    </tbody>
  </table>
@endsection

