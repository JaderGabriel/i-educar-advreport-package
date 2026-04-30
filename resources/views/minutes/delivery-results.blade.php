@extends('advanced-reports::pdf.layout')

@section('doc_title', 'Ata de entrega de resultados')
@section('doc_subtitle', 'Ciência dos responsáveis — períodos avaliativos selecionados')
@section('doc_year', (string) (($data['class']->ano_letivo ?? '') ?: ''))
@section('formal_header', '1')

@section('content')
  @php($class = $data['class'])
  @php($students = $data['students'] ?? collect())
  @php($etapas = $data['etapas'] ?? [])
  @php($etapaLabels = $data['etapa_labels'] ?? [])
  @php($periodoTexto = collect($etapas)->map(fn ($e) => $etapaLabels[$e] ?? ($e . 'º período'))->implode(', '))

  <h1>ATA DE ENTREGA DE RESULTADOS</h1>

  <div class="box">
    <table>
      <tr><th>Instituição</th><td>{{ $class->instituicao }}</td></tr>
      <tr><th>Escola</th><td>{{ $class->escola }}</td></tr>
      <tr><th>Turma</th><td>{{ $class->turma }} ({{ $class->turno }})</td></tr>
      <tr><th>Curso/Série</th><td>{{ $class->curso }} — {{ $class->serie }}</td></tr>
      <tr><th>Ano letivo</th><td>{{ $class->ano_letivo }}</td></tr>
      <tr><th>Período(s) avaliativo(s)</th><td>{{ $periodoTexto }}</td></tr>
    </table>
  </div>

  <p class="muted">
    Esta ata apresenta os resultados parciais lançados no diário, restritos ao(s) período(s) informado(s) na emissão.
    Os responsáveis abaixo manifestam ciência do desempenho escolar indicado.
  </p>

  <table>
    <thead>
    <tr>
      <th>#</th>
      <th>Aluno(a)</th>
      <th>Matrícula</th>
      <th>% Freq.</th>
      <th>Situação</th>
    </tr>
    </thead>
    <tbody>
    @foreach($students as $idx => $row)
      <tr>
        <td>{{ $idx + 1 }}</td>
        <td>{{ $row['student'] }}</td>
        <td>{{ $row['registration_id'] }}</td>
        <td>{{ isset($row['frequency']) ? ($row['frequency'] . '%') : '-' }}</td>
        <td>{{ $row['status'] }}</td>
      </tr>

      @if(!empty($row['details']) && !empty($row['details']['rows']))
        @php($d = $row['details'])
        <tr>
          <td colspan="5" style="padding: 0;">
            <div class="box" style="margin: 0; border-top: 0;">
              <strong>Notas por componente (períodos selecionados)</strong>
              <table style="margin-top: 8px;">
                <thead>
                <tr>
                  <th>Componente</th>
                  @if(!empty($d['etapa_columns']))
                    @foreach($d['etapa_columns'] as $col)
                      <th>{{ $col['label'] }}</th>
                    @endforeach
                  @else
                    @for($i = 1; $i <= (int) ($d['etapas_count'] ?? 0); $i++)
                      <th>Etapa {{ $i }}</th>
                    @endfor
                    @if(!empty($d['include_rc']))
                      <th>Rc</th>
                    @endif
                  @endif
                </tr>
                </thead>
                <tbody>
                @foreach(($d['rows'] ?? []) as $cr)
                  <tr>
                    <td>{{ $cr['nome'] }}</td>
                    @if(!empty($d['etapa_columns']))
                      @foreach($d['etapa_columns'] as $col)
                        <td>{{ $cr['etapas'][$col['key']] ?? '-' }}</td>
                      @endforeach
                    @else
                      @for($i = 1; $i <= (int) ($d['etapas_count'] ?? 0); $i++)
                        <td>{{ $cr['etapas'][(string) $i] ?? '-' }}</td>
                      @endfor
                      @if(!empty($d['include_rc']))
                        <td>{{ $cr['etapas']['Rc'] ?? '-' }}</td>
                      @endif
                    @endif
                  </tr>
                @endforeach
                </tbody>
              </table>
            </div>
          </td>
        </tr>
      @endif

      <tr>
        <td colspan="5" style="padding: 0;">
          <div class="box" style="margin: 0; border-top: 0;">
            <strong>Assinatura(s) do(s) responsável(is)</strong>
            @php($guardians = $row['guardians'] ?? [])
            @if(count($guardians) === 0)
              <p class="muted" style="margin: 8px 0 0;">Não há responsáveis cadastrados na ficha do aluno (mãe, pai ou responsável legal).</p>
            @else
              <table style="margin-top: 10px; width: 100%; border-collapse: collapse;">
                @foreach(array_chunk($guardians, 2) as $pair)
                  <tr>
                    @foreach($pair as $g)
                      <td style="padding: 6px; vertical-align: top; width: 50%;">
                        <div style="min-height: 72px; border: 1px solid #ccc; padding: 8px;">
                          <div style="font-size: 9px; color: #444;">Nome completo</div>
                          <div style="font-weight: bold; font-size: 10px; margin-bottom: 6px;">{{ $g['nome'] }}</div>
                          <div style="font-size: 9px; color: #444;">CPF (mascarado)</div>
                          <div style="font-size: 10px;">{{ $g['cpf_masked'] ?? '—' }}</div>
                          <div style="margin-top: 18px; border-top: 1px solid #999; padding-top: 4px; text-align: center; font-size: 9px;">
                            Assinatura do responsável
                          </div>
                        </div>
                      </td>
                    @endforeach
                    @if(count($pair) === 1)
                      <td style="padding: 6px;"></td>
                    @endif
                  </tr>
                @endforeach
              </table>
            @endif
          </div>
        </td>
      </tr>
    @endforeach
    </tbody>
  </table>

  <div style="margin-top: 18px;">
    <strong>Instituição</strong>
    <table style="margin-top: 8px;">
      <tr>
        <td style="height: 52px; vertical-align: bottom; text-align: center;">
          ___________________________________________<br>
          Direção
        </td>
        <td style="height: 52px; vertical-align: bottom; text-align: center;">
          ___________________________________________<br>
          Secretaria Escolar
        </td>
      </tr>
    </table>
  </div>

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
    'issuerRole' => $issuerRole ?? null,
    'cityUf' => $cityUf ?? null,
  ])
@endsection
