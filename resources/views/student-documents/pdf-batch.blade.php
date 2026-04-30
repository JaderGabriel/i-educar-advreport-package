@extends('advanced-reports::pdf.layout')

@section('doc_title', $title ?? 'Documento oficial')
@section('doc_subtitle', 'Emissão em lote • QR Code para validação')
@section('doc_year', (string) (data_get($items, '0.matricula.ano_letivo') ?? ''))
@section('formal_header', '1')

@section('content')
  @foreach(($items ?? []) as $it)
    @php($m = data_get($it, 'matricula'))
    @php($extra = data_get($it, 'extra', []))

    @if(($document ?? '') === 'declaration_frequency')
      <h1>DECLARAÇÃO DE FREQUÊNCIA</h1>
      <p class="muted">Percentual calculado pela função <code>modules.frequencia_da_matricula</code>.</p>
      <div class="box">
        <table>
          <tr><th>Aluno(a)</th><td>{{ $m->aluno_nome ?? '' }}</td></tr>
          <tr><th>Matrícula interna (i-Educar)</th><td>{{ $m->matricula_id ?? '' }}</td></tr>
          <tr><th>Ano letivo</th><td>{{ $m->ano_letivo ?? '' }}</td></tr>
          <tr><th>Escola</th><td>{{ $m->escola ?? '' }}</td></tr>
          <tr><th>Curso/Série/Turma</th><td>{{ ($m->curso ?? '') . ' — ' . ($m->serie ?? '') . ' — ' . ($m->turma ?? '') }}</td></tr>
          <tr><th>% Frequência</th><td><strong>{{ $extra['frequencia_percentual'] ?? '-' }}%</strong></td></tr>
        </table>
      </div>

      <div class="box">
        <strong>Frequência mensal</strong>
        <p class="muted" style="margin-top: 4px;">
          Observação: a frequência mensal depende de dados de diário/chamada por data. Quando não disponível, o valor pode aparecer como “—”.
        </p>
        <table style="margin-top: 8px;">
          <thead>
          <tr>
            <th style="width: 220px;">Mês</th>
            <th style="width: 160px;">% Frequência</th>
          </tr>
          </thead>
          <tbody>
          @foreach(($extra['frequencia_mensal'] ?? []) as $row)
            <tr>
              <td>{{ $row['label'] ?? '' }}</td>
              <td><strong>{{ isset($row['percent']) && $row['percent'] !== null ? ($row['percent'] . '%') : '—' }}</strong></td>
            </tr>
          @endforeach
          </tbody>
        </table>
      </div>

      <p>Declaramos, para os devidos fins, que o(a) aluno(a) acima identificado(a) possui frequência conforme percentual informado.</p>
    @elseif(($document ?? '') === 'transfer_guide')
      <h1>GUIA / DECLARAÇÃO DE TRANSFERÊNCIA</h1>
      <p class="muted">Modelo para fins de transferência / continuidade dos estudos, conforme registros do i-Educar.</p>
      @include('advanced-reports::student-documents._matricula-data-box', [
        'matricula' => $m,
        'showInstituicao' => false,
        'entradaLabel' => 'Início na turma',
        'saidaLabel' => 'Permaneceu até',
      ])
      <p>Declaramos que o(a) aluno(a) acima identificado(a) está vinculado(a) à escola de origem indicada, para fins de transferência/continuidade dos estudos, conforme registros no i-Educar.</p>
      @include('advanced-reports::student-documents._transfer-documentos-adicionais-observacao')
      @include('advanced-reports::student-documents._authority-signatures')
    @elseif(($document ?? '') === 'declaration_conclusion')
      @include('advanced-reports::student-documents._declaration-conclusion-inner', [
        'matricula' => $m,
        'extra' => $extra,
        'issuerName' => $issuerName ?? null,
        'schoolInep' => $schoolInep ?? null,
      ])
    @elseif(($document ?? '') === 'declaration_nada_consta')
      <h1>DECLARAÇÃO DE ESCOLARIDADE / NADA CONSTA</h1>
      <p>
        Declaramos, para os devidos fins, que <strong>{{ $m->aluno_nome ?? '' }}</strong>,
        matrícula <strong>{{ $m->matricula_id ?? '' }}</strong>, encontra-se vinculada à unidade
        <strong>{{ $m->escola ?? '' }}</strong> ({{ $m->instituicao ?? '' }}),
        no ano letivo de <strong>{{ $m->ano_letivo ?? '' }}</strong>.
      </p>
      <p class="muted">
        Observação: esta declaração é um resumo oficial para fins administrativos. Informações sensíveis não são exibidas na validação pública.
      </p>
    @else
      <h1>DECLARAÇÃO DE MATRÍCULA</h1>
      <p class="muted">Emitida com base nos registros do i-Educar para fins de comprovação.</p>
      @include('advanced-reports::student-documents._matricula-data-box', ['matricula' => $m])
      <p>Declaramos, para os devidos fins, que o(a) aluno(a) acima identificado(a) encontra-se regularmente matriculado(a) nesta unidade escolar no ano letivo informado.</p>
    @endif

    @if(in_array(($document ?? ''), ['declaration_conclusion', 'transfer_guide'], true))
      {{-- assinaturas: conclusão (partial) ou transferência (acima) --}}
    @else
      @include('advanced-reports::pdf._issuer-signature', [
        'issuerName' => $issuerName ?? null,
        'schoolInep' => $schoolInep ?? null,
      ])
    @endif

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
      'matriculaInternaAluno' => $m->matricula_id ?? null,
      'footerInline' => ($document ?? '') === 'transfer_guide',
    ])

    @if(!$loop->last)
      <div style="page-break-after: always;"></div>
    @endif
  @endforeach
@endsection

