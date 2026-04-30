@extends('advanced-reports::pdf.layout')

@section('doc_title', 'Relatório Socioeconômico')
@section('doc_subtitle', 'Distribuições: raça/cor • gênero • benefícios • alunos por escola')
@section('doc_year', (string) $ano)

@section('content')
    <h1>Relatório Socioeconômico — {{ $ano }}</h1>

    <p style="font-size:9px;color:#555;">Legenda de dados: Raça/Cor conforme cadastro físico; Gênero conforme cadastro físico; Benefícios conforme 
        programas sociais cadastrados no aluno; Alunos por escola indicam a concentração geográfica das matrículas no recorte filtrado.</p>


    <h2>Distribuição por raça/cor</h2>
    @if(!empty($withCharts) && !empty($charts['race']))
        <div style="margin:6px 0 10px 0;">
            <img src="{{ $charts['race'] }}" style="width: 100%; height: auto;" alt="Gráfico raça/cor">
        </div>
    @endif
    <table>
        <thead>
        <tr>
            <th>Raça/Cor</th>
            <th>Total de alunos</th>
        </tr>
        </thead>
        <tbody>
        @foreach($data['race'] as $row)
            <tr>
                <td>{{ $row->raca_label ?? ($row->raca ?? 'Não informada') }}</td>
                <td>{{ $row->total }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>

    <h2>Distribuição por gênero</h2>
    @if(!empty($withCharts) && !empty($charts['gender']))
        <div style="margin:6px 0 10px 0;">
            <img src="{{ $charts['gender'] }}" style="width: 100%; height: auto;" alt="Gráfico gênero">
        </div>
    @endif
    <table>
        <thead>
        <tr>
            <th>Gênero</th>
            <th>Total de alunos</th>
        </tr>
        </thead>
        <tbody>
        @foreach($data['gender'] as $row)
            <tr>
                <td>{{ $row->sexo_label ?? ($row->sexo ?? 'Não informado') }}</td>
                <td>{{ $row->total }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>

    <h2>Benefícios / Programas sociais</h2>
    @if(!empty($withCharts) && !empty($charts['benefits']))
        <div style="margin:6px 0 10px 0;">
            <img src="{{ $charts['benefits'] }}" style="width: 100%; height: auto;" alt="Gráfico benefícios">
        </div>
    @endif
    <table>
        <thead>
        <tr>
            <th>Benefício</th>
            <th>Total de alunos</th>
        </tr>
        </thead>
        <tbody>
        @foreach($data['benefits'] as $row)
            <tr>
                <td>{{ $row->beneficio }}</td>
                <td>{{ $row->total }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>

    <h2>Alunos por escola</h2>
    @if(!empty($withCharts) && !empty($charts['schools']))
        <div style="margin:6px 0 10px 0;">
            <img src="{{ $charts['schools'] }}" style="width: 100%; height: auto;" alt="Gráfico escolas">
        </div>
    @endif
    <table>
        <thead>
        <tr>
            <th>Escola</th>
            <th>Total de alunos</th>
        </tr>
        </thead>
        <tbody>
        @foreach($data['schools'] as $row)
            <tr>
                <td>{{ $row->nome }}</td>
                <td>{{ $row->total }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>

@endsection
