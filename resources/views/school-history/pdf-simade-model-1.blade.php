@extends('advanced-reports::pdf.layout')

@section('doc_title', 'Histórico escolar')
@section('doc_subtitle', ($templateLabel ?? 'SIMADE — Modelo 1 (frente/verso)'))
@section('doc_year', '')
@section('formal_header', '1')

@section('content')
  @php($student = $data['student'])
  @php($items = $data['items'] ?? [])
  @php($person = $data['person'] ?? [])

  <style>
    .simade-row { width: 100%; border-collapse: collapse; margin-top: 6px; }
    .simade-row td { border: 1px solid #111; padding: 4px 6px; font-size: 10px; }
    .simade-label { font-weight: 700; font-size: 10px; }
    .simade-title { text-align:center; font-weight: 800; letter-spacing: .06em; margin: 0; }
    .simade-subtitle { text-align:center; margin: 2px 0 0; font-size: 11px; font-weight: 700; }
    .simade-small { font-size: 9px; }
    .simade-sign td { height: 52px; vertical-align: bottom; text-align: center; }
    .simade-grid th, .simade-grid td { border: 1px solid #111; }
    .simade-grid th { background: #f4f4f4; font-size: 9px; padding: 4px 6px; }
    .simade-grid td { font-size: 9px; padding: 4px 6px; }
  </style>

  @php($first = $items[0]['history'] ?? null)
  @php($estabelecimento = $first?->escola ?? '')
  @php($municipio = trim(($first?->escola_cidade ?? '') ?: ''))
  @php($estado = trim(($first?->escola_uf ?? '') ?: ''))

  <p class="simade-title">CERTIFICADO DE CONCLUSÃO DA EDUCAÇÃO BÁSICA</p>
  <table class="simade-row">
    <tr>
      <td><span class="simade-label">ESTABELECIMENTO:</span> {{ $estabelecimento }}</td>
      <td style="width: 40%;"><span class="simade-label">MUNICÍPIO:</span> {{ $municipio }}</td>
    </tr>
    <tr>
      <td><span class="simade-label">MÍNIMO / PROMOÇÃO:</span> ____________ &nbsp; <span class="simade-label">DIAS LETIVOS ANUAIS:</span> ____________ &nbsp; <span class="simade-label">CH ANUAL:</span> ____________</td>
      <td><span class="simade-label">ESTADO:</span> {{ $estado }}</td>
    </tr>
  </table>

  <p class="simade-subtitle">HISTÓRICO ESCOLAR - EDUCAÇÃO BÁSICA</p>

  <table class="simade-row" style="margin-top: 8px;">
    <tr>
      <td colspan="2">
        <span class="simade-label">Certificamos que</span>
        <span class="simade-small">{{ $student->aluno_nome }}</span>,
        <span class="simade-label">conforme Histórico Escolar e observações no anverso e verso.</span>
      </td>
    </tr>
    <tr>
      <td><span class="simade-label">Aluno (ID):</span> {{ $student->aluno_id }}</td>
      <td><span class="simade-label">Município e data de expedição:</span> {{ $municipio }} — ____/____/______</td>
    </tr>
    <tr>
      <td>
        <span class="simade-label">Naturalidade:</span>
        {{ $person['birth_city'] ?? '_____________________' }}
        &nbsp; <span class="simade-label">UF:</span>
        {{ $person['birth_uf'] ?? '____' }}
      </td>
      <td>
        <span class="simade-label">Nacionalidade:</span>
        {{ $person['nationality'] ?? '_____________________' }}
        &nbsp; <span class="simade-label">Sexo:</span>
        {{ $person['sex'] ?? '____' }}
      </td>
    </tr>
    <tr>
      <td>
        <span class="simade-label">Nascido(a) em:</span>
        {{ $person['birth_date'] ?? '____/____/______' }}
      </td>
      <td>
        <span class="simade-label">Carteira de Identidade nº:</span>
        {{ $person['rg'] ?? '_____________________' }}
        &nbsp; <span class="simade-label">Órgão/UF:</span>
        {{ ($person['rg_issuing_body'] ?? null) ? (($person['rg_issuing_body'] ?? '') . '/' . ($person['rg_uf'] ?? '')) : '______' }}
      </td>
    </tr>
    <tr>
      <td colspan="2">
        <span class="simade-label">Filho(a) de:</span>
        {{ $person['father_name'] ?? '_______________________________' }}
        <span class="simade-label">e de</span>
        {{ $person['mother_name'] ?? '_______________________________' }}
      </td>
    </tr>
  </table>

  @include('advanced-reports::student-documents._authority-signatures', [
    'authorities' => $authorities ?? [],
  ])

  <table>
    <thead>
    <tr>
      <th>Ano</th>
      <th>Série</th>
      <th>Escola</th>
      <th>Curso</th>
      <th>Frequência</th>
      <th>Carga horária</th>
    </tr>
    </thead>
    <tbody>
    @foreach($items as $item)
      @php($h = $item['history'])
      <tr>
        <td>{{ $h->ano }}</td>
        <td>{{ $h->nm_serie }}</td>
        <td>{{ $h->escola }} ({{ $h->escola_cidade }}/{{ $h->escola_uf }})</td>
        <td>{{ $h->nm_curso ?? '-' }}</td>
        <td>{{ !empty($h->frequencia) ? ($h->frequencia . '%') : '-' }}</td>
        <td>{{ $h->carga_horaria ?? '-' }}</td>
      </tr>
    @endforeach
    </tbody>
  </table>

  <div style="page-break-before: always;"></div>

  <p class="simade-title">HISTÓRICO ESCOLAR - EDUCAÇÃO BÁSICA</p>
  <p class="muted">ANVERSO E VERSO — componentes curriculares por etapa/ano (conforme registros existentes no i-Educar).</p>

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

    @if(!empty($h->observacao))
      <div class="box">
        <strong>Observações</strong>
        <div class="muted" style="margin-top: 4px;">{{ $h->observacao }}</div>
      </div>
    @endif

    <table class="simade-grid" style="width:100%; border-collapse: collapse; margin-top: 6px;">
      <thead>
      <tr>
        <th style="width: 44%;">BASE NACIONAL COMUM / COMPONENTES</th>
        <th style="width: 14%;">Aproveitamento</th>
        <th style="width: 14%;">Faltas/Horas</th>
        <th style="width: 14%;">C.H. Curricular</th>
        <th style="width: 14%;">Situação</th>
      </tr>
      </thead>
      <tbody>
      @foreach($disciplines as $d)
        <tr>
          <td>{{ $d->nm_disciplina }}</td>
          <td>{{ $d->nota ?? '-' }}</td>
          <td>{{ $d->faltas ?? '-' }}</td>
          <td>{{ $d->carga_horaria_disciplina ?? '-' }}</td>
          <td>{{ ($d->dependencia ?? 0) ? 'Depend.' : '-' }}</td>
        </tr>
      @endforeach
      </tbody>
    </table>
  @endforeach

  @include('advanced-reports::pdf._issuer-signature', [
    'issuerName' => $issuerName ?? null,
    'schoolInep' => $schoolInep ?? null,
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

