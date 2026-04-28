@extends('advanced-reports::pdf.layout')

@section('doc_title', 'Relatório de Movimentações (Geral)')
@section('doc_subtitle', 'Período: ' . $data_inicial . ' a ' . $data_final)
@section('doc_year', (string) $ano)

@section('content')
    <h1>Relatório de Movimentações (Geral) — {{ $ano }}</h1>
    <div class="muted">
        Período: {{ $data_inicial }} a {{ $data_final }}
    </div>

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
            <th>AD</th>
            <th>DF</th>
            <th>TR</th>
            <th>Rem.</th>
            <th>Recla.</th>
            <th>Óbito</th>
            <th>Localização</th>
        </tr>
        </thead>
        <tbody>
        @foreach($data as $row)
            <tr>
                <td>{{ trim(($row['escola'] ?? '') . ' ' . ($row['ciclo'] ?? '') . ($row['aee'] ?? '')) }}</td>
                <td>{{ $row['ed_inf_int'] ?? 0 }}</td>
                <td>{{ $row['ed_inf_parc'] ?? 0 }}</td>
                <td>{{ $row['ano_1'] ?? 0 }}</td>
                <td>{{ $row['ano_2'] ?? 0 }}</td>
                <td>{{ $row['ano_3'] ?? 0 }}</td>
                <td>{{ $row['ano_4'] ?? 0 }}</td>
                <td>{{ $row['ano_5'] ?? 0 }}</td>
                <td>{{ $row['ano_6'] ?? 0 }}</td>
                <td>{{ $row['ano_7'] ?? 0 }}</td>
                <td>{{ $row['ano_8'] ?? 0 }}</td>
                <td>{{ $row['ano_9'] ?? 0 }}</td>
                <td>{{ $row['admitidos'] ?? 0 }}</td>
                <td>{{ $row['aband'] ?? 0 }}</td>
                <td>{{ $row['transf'] ?? 0 }}</td>
                <td>{{ $row['rem'] ?? 0 }}</td>
                <td>{{ $row['recla'] ?? 0 }}</td>
                <td>{{ $row['obito'] ?? 0 }}</td>
                <td>{{ $row['localizacao'] ?? '' }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>

    <div class="muted">
        Legenda: AD=Admitidos, DF=Deixou de frequentar (abandono), TR=Transferidos. * = AEE, ** = regime por ciclo.
    </div>
@endsection

