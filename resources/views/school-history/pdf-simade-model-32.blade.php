@extends('advanced-reports::pdf.layout')

@section('doc_title', 'Histórico escolar')
@section('doc_subtitle', ($templateLabel ?? 'SIMADE — Modelo 32 (frente/verso)'))
@section('doc_year', '')
@section('formal_header', '1')

@section('content')
  @php($student = $data['student'])
  @php($items = $data['items'] ?? [])
  @php($person = $data['person'] ?? [])

  <style>
    .simade-row { width: 100%; border-collapse: collapse; margin-top: 6px; }
    .simade-row td { border: 1px solid #111; padding: 4px 6px; font-size: 10px; }
    .simade-title { text-align:center; font-weight: 800; letter-spacing: .06em; margin: 0; }
    .simade-subtitle { text-align:center; margin: 2px 0 0; font-size: 11px; font-weight: 700; }
    .simade-label { font-weight: 700; font-size: 10px; }
    .simade-grid th, .simade-grid td { border: 1px solid #111; }
    .simade-grid th { background: #f4f4f4; font-size: 9px; padding: 4px 6px; }
    .simade-grid td { font-size: 9px; padding: 4px 6px; }
  </style>

  @php($first = $items[0]['history'] ?? null)
  @php($estabelecimento = $first?->escola ?? '')
  @php($municipio = trim(($first?->escola_cidade ?? '') ?: ''))
  @php($estado = trim(($first?->escola_uf ?? '') ?: ''))

  <p class="simade-title">HISTÓRICO ESCOLAR</p>
  <p class="simade-subtitle">MODELO 32 — (estrutura frente/verso conforme referências do SIMADE)</p>

  <table class="simade-row">
    <tr>
      <td><span class="simade-label">NOME:</span> {{ $student->aluno_nome }}</td>
      <td style="width: 30%;"><span class="simade-label">Aluno (ID):</span> {{ $student->aluno_id }}</td>
    </tr>
    <tr>
      <td>
        <span class="simade-label">Nasc.:</span> {{ $person['birth_date'] ?? '____/____/______' }}
        &nbsp; <span class="simade-label">Naturalidade:</span> {{ $person['birth_city'] ?? '_____________________' }}/{{ $person['birth_uf'] ?? '____' }}
      </td>
      <td><span class="simade-label">Nacionalidade:</span> {{ $person['nationality'] ?? '_____________________' }}</td>
    </tr>
    <tr>
      <td><span class="simade-label">(nome do estabelecimento)</span> {{ $estabelecimento }}</td>
      <td><span class="simade-label">MUNICÍPIO/UF:</span> {{ $municipio }}/{{ $estado }}</td>
    </tr>
    <tr>
      <td colspan="2" class="simade-small">
        <span class="simade-label">Fundamentação legal:</span> ______________________________________________________________________________________________
      </td>
    </tr>
  </table>

  <p class="muted" style="margin-top: 8px;">
    Este modelo organiza o percurso por ano/série, com quadro de aproveitamento/carga horária/faltas, e uma página “verso” com componentes.
  </p>

  <h2 style="margin-top: 14px;">Frente — percurso escolar</h2>

  @foreach($items as $item)
    @php($h = $item['history'])
    <div class="box" style="margin-top: 10px;">
      <strong>{{ $h->nm_serie }} — {{ $h->ano }}</strong>
      <div class="muted" style="margin-top: 6px;">
        Escola: {{ $h->escola }} ({{ $h->escola_cidade }}/{{ $h->escola_uf }})<br>
        Curso: {{ $h->nm_curso ?? '-' }}<br>
        Frequência: {{ !empty($h->frequencia) ? ($h->frequencia . '%') : '-' }}<br>
        Carga horária: {{ $h->carga_horaria ?? '-' }}
      </div>
      @if(!empty($h->observacao))
        <div class="muted" style="margin-top: 8px;"><strong>Observações:</strong> {{ $h->observacao }}</div>
      @endif
    </div>
  @endforeach

  <div style="page-break-before: always;"></div>

  <h2 style="margin-top: 14px;">Verso — componentes curriculares</h2>

  @foreach($items as $item)
    @php($h = $item['history'])
    @php($disciplines = $item['disciplines'])

    <table class="simade-row" style="margin-top: 10px;">
      <tr>
        <td><span class="simade-label">ANO:</span> {{ $h->ano }}</td>
        <td><span class="simade-label">ETAPA/SÉRIE:</span> {{ $h->nm_serie }}</td>
      </tr>
      <tr>
        <td><span class="simade-label">ESTABELECIMENTO:</span> {{ $h->escola }}</td>
        <td><span class="simade-label">MUNICÍPIO/UF:</span> {{ $h->escola_cidade }}/{{ $h->escola_uf }}</td>
      </tr>
    </table>

    <table class="simade-grid" style="width:100%; border-collapse: collapse; margin-top: 6px;">
      <thead>
      <tr>
        <th style="width: 44%;">ÁREAS/COMPONENTES</th>
        <th style="width: 14%;">Aproveitamento</th>
        <th style="width: 14%;">Faltas/Horas</th>
        <th style="width: 14%;">C.H. Curricular</th>
        <th style="width: 14%;">Obs.</th>
      </tr>
      </thead>
      <tbody>
      @foreach($disciplines as $d)
        <tr>
          <td>{{ $d->nm_disciplina }}</td>
          <td>{{ $d->nota ?? '-' }}</td>
          <td>{{ $d->faltas ?? '-' }}</td>
          <td>{{ $d->carga_horaria_disciplina ?? '-' }}</td>
          <td>{{ ($d->dependencia ?? 0) ? 'Depend.' : '' }}</td>
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

