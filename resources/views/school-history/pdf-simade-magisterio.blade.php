@extends('advanced-reports::pdf.layout')

@section('doc_title', 'Histórico escolar')
@section('doc_subtitle', ($templateLabel ?? 'SIMADE — Magistério / Curso Normal (frente/verso)'))
@section('doc_year', '')
@section('formal_header', '1')

@section('content')
  @php($student = $data['student'])
  @php($items = $data['items'] ?? [])

  <h1>HISTÓRICO ESCOLAR — CURSO NORMAL (MAGISTÉRIO)</h1>

  <div class="box">
    <table>
      <tr><th>Aluno(a)</th><td>{{ $student->aluno_nome }}</td></tr>
      <tr><th>Aluno (ID)</th><td>{{ $student->aluno_id }}</td></tr>
      @if(!empty($templateLabel))
        <tr><th>Modelo</th><td>{{ $templateLabel }}</td></tr>
      @endif
    </table>
  </div>

  <p class="muted" style="margin-top: 8px;">
    Template focado em cursos do tipo “Normal/Magistério”. A estrutura exata pode variar conforme normativas da rede.
  </p>

  @foreach($items as $item)
    @php($h = $item['history'])
    @php($disciplines = $item['disciplines'])

    <h2 style="margin-top: 14px;">{{ $h->nm_serie }} — {{ $h->ano }}</h2>
    <div class="muted" style="margin-bottom: 6px;">
      Escola: {{ $h->escola }} ({{ $h->escola_cidade }}/{{ $h->escola_uf }})
      @if(!empty($h->nm_curso)) — Curso: {{ $h->nm_curso }} @endif
      @if(!empty($h->frequencia)) — Frequência: {{ $h->frequencia }}% @endif
      @if(!empty($h->carga_horaria)) — Carga horária: {{ $h->carga_horaria }} @endif
    </div>

    <table>
      <thead>
      <tr>
        <th>Componente</th>
        <th>Nota</th>
        <th>Faltas</th>
        <th>Carga horária</th>
        <th>Dependência</th>
      </tr>
      </thead>
      <tbody>
      @foreach($disciplines as $d)
        <tr>
          <td>{{ $d->nm_disciplina }}</td>
          <td>{{ $d->nota ?? '-' }}</td>
          <td>{{ $d->faltas ?? '-' }}</td>
          <td>{{ $d->carga_horaria_disciplina ?? '-' }}</td>
          <td>{{ ($d->dependencia ?? 0) ? 'Sim' : 'Não' }}</td>
        </tr>
      @endforeach
      </tbody>
    </table>
  @endforeach

  @include('advanced-reports::pdf._issuer-signature', [
    'issuerName' => $issuerName ?? null,
    'schoolInep' => $schoolInep ?? null,
  ])

  @include('advanced-reports::student-documents._authority-signatures', [
    'authorities' => $authorities ?? [],
  ])

  @include('advanced-reports::student-documents._footer', [
    'issuedAt' => $issuedAt,
    'validationCode' => $validationCode,
    'validationUrl' => $validationUrl,
    'qrDataUri' => $qrDataUri,
    'issuerName' => $issuerName ?? null,
    'issuerRole' => null,
    'cityUf' => null,
    'book' => $book ?? null,
    'page' => $page ?? null,
    'record' => $record ?? null,
  ])
@endsection

