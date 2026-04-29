@extends('advanced-reports::pdf.layout')

@section('doc_title', 'Alunos por situação')
@section('doc_subtitle', 'Matrículas por situação (resumo + listagem)')
@section('doc_year', (string) ($year ?? ''))
@section('formal_header', '1')

@section('content')
    @php($summary = $data['summary'] ?? [])
    @php($rows = $data['rows'] ?? collect())
    @php($labels = $labels ?? [])

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
            <th>Aluno(a)</th>
            <th style="width: 90px;">Matrícula</th>
            <th style="width: 140px;">Situação</th>
            <th>Escola</th>
            <th>Curso</th>
            <th>Série</th>
            <th>Turma</th>
        </tr>
        </thead>
        <tbody>
        @foreach($rows as $r)
            <tr>
                <td>{{ $r['aluno'] ?? '' }}</td>
                <td>{{ $r['matricula_id'] ?? '' }}</td>
                <td>{{ $r['situacao'] ?? '' }}</td>
                <td>{{ $r['escola'] ?? '' }}</td>
                <td>{{ $r['curso'] ?? '' }}</td>
                <td>{{ $r['serie'] ?? '' }}</td>
                <td>{{ $r['turma'] ?? '' }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>

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

