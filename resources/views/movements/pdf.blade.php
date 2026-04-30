@extends('advanced-reports::pdf.layout')

@section('doc_title', 'Relatório de Movimentações (Geral)')
@section('doc_subtitle', 'Período: ' . $data_inicial . ' a ' . $data_final)
@section('doc_year', (string) $ano)
@section('formal_header', '1')

@section('content')
    <h1>Relatório de Movimentações (Geral) — {{ $ano }}</h1>
    <div class="muted">
        Período: {{ $data_inicial }} a {{ $data_final }}
    </div>

    <div class="box" style="margin-top: 10px;">
        <strong>Legenda e interpretação</strong>
        <div class="muted" style="margin-top: 6px; line-height: 1.45;">
            As colunas destacadas representam tipos de movimentação no período selecionado.
            <span style="display:inline-block; margin-left: 10px; padding: 2px 8px; border-radius: 999px; background: #dcfce7; border: 1px solid #86efac;"><strong>Verde</strong>: entradas/admissões (AD)</span>
            <span style="display:inline-block; margin-left: 6px; padding: 2px 8px; border-radius: 999px; background: #fee2e2; border: 1px solid #fca5a5;"><strong>Vermelho</strong>: saídas por transferência (TR)</span>
            <span style="display:inline-block; margin-left: 6px; padding: 2px 8px; border-radius: 999px; background: #fef9c3; border: 1px solid #fde047;"><strong>Amarelo</strong>: óbito (e outras situações não motivadas)</span>
        </div>
        <div class="muted" style="margin-top: 6px;">
            Siglas: <strong>AD</strong>=Admitidos, <strong>DF</strong>=Deixou de frequentar (abandono), <strong>TR</strong>=Transferidos.
            <span style="margin-left: 8px;">* = AEE</span>
            <span style="margin-left: 8px;">** = regime por ciclo</span>
        </div>
    </div>

    <style>
      .mv-in { background: #dcfce7; }
      .mv-out { background: #fee2e2; }
      .mv-warn { background: #fef9c3; }
      .mv-in, .mv-out, .mv-warn { font-weight: 700; text-align: center; }
      .mv-num { text-align: center; }
      .mv-school { white-space: nowrap; }
    </style>

    <table>
        <thead>
        <tr>
            <th>Escola</th>
            <th>Ed. Inf. Int.</th>
            <th>Ed. Inf. Parc.</th>
            <th>1º</th>
            <th>2º</th>
            <th>3º</th>
            <th>4º</th>
            <th>5º</th>
            <th>6º</th>
            <th>7º</th>
            <th>8º</th>
            <th>9º</th>
            <th class="mv-in">AD</th>
            <th>DF</th>
            <th class="mv-out">TR</th>
            <th>Rem.</th>
            <th>Recla.</th>
            <th class="mv-warn">Óbito</th>
            <th>Localização</th>
        </tr>
        </thead>
        <tbody>
        @foreach($data as $row)
            <tr>
                <td class="mv-school">{{ trim(($row['escola'] ?? '') . ' ' . ($row['ciclo'] ?? '') . ($row['aee'] ?? '')) }}</td>
                <td class="mv-num">{{ $row['ed_inf_int'] ?? 0 }}</td>
                <td class="mv-num">{{ $row['ed_inf_parc'] ?? 0 }}</td>
                <td class="mv-num">{{ $row['ano_1'] ?? 0 }}</td>
                <td class="mv-num">{{ $row['ano_2'] ?? 0 }}</td>
                <td class="mv-num">{{ $row['ano_3'] ?? 0 }}</td>
                <td class="mv-num">{{ $row['ano_4'] ?? 0 }}</td>
                <td class="mv-num">{{ $row['ano_5'] ?? 0 }}</td>
                <td class="mv-num">{{ $row['ano_6'] ?? 0 }}</td>
                <td class="mv-num">{{ $row['ano_7'] ?? 0 }}</td>
                <td class="mv-num">{{ $row['ano_8'] ?? 0 }}</td>
                <td class="mv-num">{{ $row['ano_9'] ?? 0 }}</td>
                <td class="mv-in">{{ $row['admitidos'] ?? 0 }}</td>
                <td class="mv-num">{{ $row['aband'] ?? 0 }}</td>
                <td class="mv-out">{{ $row['transf'] ?? 0 }}</td>
                <td class="mv-num">{{ $row['rem'] ?? 0 }}</td>
                <td class="mv-num">{{ $row['recla'] ?? 0 }}</td>
                <td class="mv-warn">{{ $row['obito'] ?? 0 }}</td>
                <td>{{ $row['localizacao'] ?? '' }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>

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

