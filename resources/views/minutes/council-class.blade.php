@extends('advanced-reports::pdf.layout')

@section('doc_title', 'Ata de conselho de classe')
@section('doc_subtitle', 'Registro por turma — períodos avaliativos selecionados')
@section('doc_year', (string) ($year ?? ''))
@section('formal_header', '1')

@section('content')
  @php($blocks = $data['blocks'] ?? [])
  @php($nb = count($blocks))

  <h1>ATA DE CONSELHO DE CLASSE</h1>

  <p class="muted" style="margin-bottom: 14px;">
    Documento com uma seção por turma. As notas exibidas referem-se apenas às etapas/períodos informados na emissão.
    O rodapé e o cabeçalho repetem em todas as páginas; a numeração é automática.
  </p>

  @foreach($blocks as $bIdx => $block)
    @php($class = $block['class'])
    @php($students = $block['students'] ?? collect())
    @php($etapas = $block['etapas'] ?? [])
    @php($etapaLabels = $block['etapa_labels'] ?? [])
    @php($periodoTexto = collect($etapas)->map(fn ($e) => $etapaLabels[$e] ?? ($e . 'º período'))->implode(', '))
    @php($professors = $block['professors'] ?? [])
    @php($secretary = $block['secretary_name'] ?? '')
    @php($isLast = ($bIdx === $nb - 1))

    <div class="council-turma-block" style="{{ $isLast ? '' : 'page-break-after: always;' }}">
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
                      @foreach($d['etapa_columns'] ?? [] as $col)
                        <th>{{ $col['label'] }}</th>
                      @endforeach
                    </tr>
                    </thead>
                    <tbody>
                    @foreach(($d['rows'] ?? []) as $cr)
                      <tr>
                        <td>{{ $cr['nome'] }}</td>
                        @foreach($d['etapa_columns'] ?? [] as $col)
                          <td>{{ $cr['etapas'][$col['key']] ?? '-' }}</td>
                        @endforeach
                      </tr>
                    @endforeach
                    </tbody>
                  </table>
                </div>
              </td>
            </tr>
          @endif
        @endforeach
        </tbody>
      </table>

      <div style="margin-top: 20px;">
        <strong>Assinaturas — turma {{ $class->turma }}</strong>
        <table style="margin-top: 12px; width: 100%;">
          <tr>
            <td style="width: 50%; vertical-align: top; padding: 8px;">
              <div style="min-height: 64px; border: 1px solid #ccc; padding: 8px;">
                <div style="font-size: 9px; color: #444;">Professor(es) da turma</div>
                @if(count($professors) === 0)
                  <div class="muted" style="margin-top:6px;">Nenhum vínculo em <code>modules.professor_turma</code> para esta turma.</div>
                @else
                  <div style="font-weight: bold; font-size: 10px; margin-top: 4px;">
                    {{ implode(' · ', $professors) }}
                  </div>
                @endif
                <div style="margin-top: 28px; border-top: 1px solid #999; padding-top: 4px; text-align: center; font-size: 9px;">
                  Assinatura do(a) professor(a)
                </div>
              </div>
            </td>
            <td style="width: 50%; vertical-align: top; padding: 8px;">
              <div style="min-height: 64px; border: 1px solid #ccc; padding: 8px;">
                <div style="font-size: 9px; color: #444;">Secretário(a) escolar</div>
                <div style="font-weight: bold; font-size: 10px; margin-top: 4px;">{{ $secretary !== '' ? $secretary : '—' }}</div>
                <div style="margin-top: 28px; border-top: 1px solid #999; padding-top: 4px; text-align: center; font-size: 9px;">
                  Assinatura do(a) secretário(a)
                </div>
              </div>
            </td>
          </tr>
        </table>
      </div>
    </div>
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
    'issuerRole' => $issuerRole ?? null,
    'cityUf' => $cityUf ?? null,
  ])
@endsection
