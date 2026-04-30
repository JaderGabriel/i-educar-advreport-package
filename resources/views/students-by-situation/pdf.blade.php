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
            <th style="width: 120px;">Situação</th>
            <th>Componentes curriculares (turma)</th>
        </tr>
        </thead>
        <tbody>
        @foreach($rows as $r)
            @php
              $rawComp = (string) ($r['componentes'] ?? '');
              $compParts = $rawComp !== '' ? preg_split('/\s*\|\s*/', $rawComp) : [];
              $compColors = ['#b91c1c', '#1d4ed8', '#047857', '#a16207', '#7c3aed'];
            @endphp
            <tr>
                <td>{{ $r['matricula_id'] ?? '' }}</td>
                <td>{{ $r['aluno'] ?? '' }}</td>
                <td>{{ $r['curso'] ?? '' }}</td>
                <td>{{ $r['turma'] ?? '' }}</td>
                <td>{{ $r['turno'] ?? '' }}</td>
                <td>{{ $r['situacao'] ?? '' }}</td>
                <td style="font-size: 8px; line-height: 1.35;">
                    @forelse($compParts as $i => $piece)
                        <span style="color: {{ $compColors[$i % count($compColors)] }};">{{ trim($piece) }}</span>@if(!$loop->last)<span style="color:#64748b;"> · </span>@endif
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

