@extends('advanced-reports::pdf.layout')

@section('doc_title', 'Alunos por situação')
@section('doc_subtitle', 'Matrículas por situação (resumo + listagem)')
@section('doc_year')
    {{ (string) ($year ?? '') }}
@endsection
@section('formal_header', '1')

@section('content')
    @php
        $summary = $data['summary'] ?? [];
        $rows = $data['rows'] ?? collect();
        $labels = $labels ?? [];
    @endphp

    <h1>ALUNOS POR SITUAÇÃO</h1>
    <p class="muted">Ano letivo: <strong>{{ $year ?? '' }}</strong></p>

    <div class="box">
        <strong>Resumo</strong>
        <table style="margin-top: 8px;">
            <tr>
                <th>Situação</th>
                <th style="width: 90px; text-align:right;">Total</th>
            </tr>
            @foreach(($labels ?? []) as $id => $label)
                <tr>
                    <td>{{ $label }}</td>
                    <td style="text-align:right;">{{ (int) ($summary[$id] ?? 0) }}</td>
                </tr>
            @endforeach
        </table>
    </div>

    <table>
        <thead>
        <tr>
            <th style="width: 72px;">Matrícula</th>
            <th>Aluno(a)</th>
            <th>Curso</th>
            <th>Turma</th>
            <th style="width: 64px;">Turno</th>
            <th style="width: 128px;">Situação</th>
            <th style="width: 32%;">Componentes curriculares (turma)</th>
        </tr>
        </thead>
        <tbody>
        @foreach($rows as $r)
            @php
              $rawComp = (string) ($r['componentes'] ?? '');
              $compParts = $rawComp !== '' ? preg_split('/\s*\|\s*/', $rawComp) : [];
              $compColors = [
                '#b91c1c', '#1d4ed8', '#047857', '#a16207', '#7c3aed',
                '#be185d', '#0f766e', '#c2410c', '#4338ca', '#15803d',
                '#9d174d', '#0369a1',
              ];
            @endphp
            <tr>
                <td>{{ $r['matricula_id'] ?? '' }}</td>
                <td>{{ $r['aluno'] ?? '' }}</td>
                <td>{{ $r['curso'] ?? '' }}</td>
                <td>{{ $r['turma'] ?? '' }}</td>
                <td>{{ $r['turno'] ?? '' }}</td>
                <td style="vertical-align: top;">
                    <div>{{ $r['situacao'] ?? '' }}</div>
                    @if((int) ($r['situacao_id'] ?? 0) !== 3 && !empty($r['data_fato']))
                        <div class="muted" style="font-size: 8px; margin-top: 3px; line-height: 1.3;">
                            Data do fato: <strong>{{ $r['data_fato'] }}</strong>
                        </div>
                    @endif
                </td>
                <td style="font-size: 8px; line-height: 1.45; vertical-align: top; word-wrap: break-word; overflow-wrap: break-word;">
                    @forelse($compParts as $i => $piece)
                        @php($c = $compColors[$i % count($compColors)])
                        <span style="display: inline-block; margin: 1px 3px 2px 0; padding: 2px 5px; border-radius: 3px; border: 1px solid {{ $c }}; color: {{ $c }}; background-color: #f8fafc; font-weight: 600;">{{ trim($piece) }}</span>
                    @empty
                        <span class="muted">—</span>
                    @endforelse
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>

  @include('advanced-reports::pdf._issuer-signature', [
    'issuerName' => $issuerName ?? null,
    'schoolInep' => $schoolInep ?? null,
  ])

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
  ])
@endsection

