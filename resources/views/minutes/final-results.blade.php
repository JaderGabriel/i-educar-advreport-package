@extends('advanced-reports::pdf.layout')

@section('doc_title', 'Ata de resultados finais')
@section('doc_subtitle', 'Ata e quadro de situação por turma')
@section('doc_year', (string) (($data['class']->ano_letivo ?? '') ?: ''))
@section('formal_header', '1')

@section('content')
  @php($class = $data['class'])
  @php($students = $data['students'] ?? collect())

  <h1>ATA DE RESULTADOS FINAIS</h1>

  <div class="box">
    <table>
      <tr><th>Instituição</th><td>{{ $class->instituicao }}</td></tr>
      <tr><th>Escola</th><td>{{ $class->escola }}</td></tr>
      <tr><th>Turma</th><td>{{ $class->turma }} ({{ $class->turno }})</td></tr>
      <tr><th>Curso/Série</th><td>{{ $class->curso }} — {{ $class->serie }}</td></tr>
      <tr><th>Ano letivo</th><td>{{ $class->ano_letivo }}</td></tr>
    </table>
  </div>

  <p class="muted">
    Esta ata consolida a situação final registrada na matrícula (campo <code>pmieducar.matricula.aprovado</code>).
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

      @if(!empty($withDetails) && !empty($row['details']) && !empty($row['details']['rows']))
        @php($d = $row['details'])
        <tr>
          <td colspan="5" style="padding: 0;">
            <div class="box" style="margin: 0; border-top: 0;">
              <strong>Notas por componente/etapa</strong>
              <table style="margin-top: 8px;">
                <thead>
                <tr>
                  <th>Componente</th>
                  @for($i = 1; $i <= (int) ($d['etapas_count'] ?? 0); $i++)
                    <th>Etapa {{ $i }}</th>
                  @endfor
                  <th>Rc</th>
                </tr>
                </thead>
                <tbody>
                @foreach(($d['rows'] ?? []) as $cr)
                  <tr>
                    <td>{{ $cr['nome'] }}</td>
                    @for($i = 1; $i <= (int) ($d['etapas_count'] ?? 0); $i++)
                      <td>{{ $cr['etapas'][(string) $i] ?? '-' }}</td>
                    @endfor
                    <td>{{ $cr['etapas']['Rc'] ?? '-' }}</td>
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

  <div style="margin-top: 18px;">
    <strong>Assinaturas</strong>
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

