@extends('advanced-reports::pdf.layout')

@section('doc_title', 'Auditoria — acessos e ações de usuários')
@section('doc_subtitle', 'Acessos (login) + trilha de alterações de dados')

@section('content')
    @php($summary = $data['summary'] ?? [])
    @php($accesses = $data['accesses'] ?? collect())
    @php($changes = $data['changes'] ?? collect())
    @php($filters = $filters ?? [])
    @php($operationOptions = $operationOptions ?? [])

    <h1>AUDITORIA — ACESSOS E AÇÕES DE USUÁRIOS</h1>

    <div class="box">
        <strong>Filtros aplicados</strong>
        <table style="margin-top: 8px;">
            <tr>
                <th>Período</th>
                <td>{{ $filters['date_start'] ?? '' }} — {{ $filters['date_end'] ?? '' }}</td>
            </tr>
            <tr>
                <th>Usuário (ID)</th>
                <td>{{ $filters['user_id'] ?? '-' }}</td>
            </tr>
            <tr>
                <th>IP contém</th>
                <td>{{ $filters['ip'] ?? '-' }}</td>
            </tr>
            <tr>
                <th>Acesso</th>
                <td>
                    @if(($filters['success'] ?? null) === 1)
                        Somente sucesso
                    @elseif(($filters['success'] ?? null) === 0)
                        Somente falha
                    @else
                        Todos
                    @endif
                </td>
            </tr>
            <tr>
                <th>Operação</th>
                <td>
                    @php($op = $filters['operation'] ?? null)
                    {{ $op ? ($operationOptions[$op] ?? ('Operação ' . $op)) : 'Todas' }}
                </td>
            </tr>
            <tr>
                <th>Tabela/Schema contém</th>
                <td>{{ $filters['table'] ?? '-' }}</td>
            </tr>
            <tr>
                <th>Origem (URL) contém</th>
                <td>{{ $filters['origin'] ?? '-' }}</td>
            </tr>
        </table>
    </div>

    <div class="box">
        <strong>Resumo</strong>
        <table style="margin-top: 8px;">
            <tr>
                <th>Período</th>
                <td>{{ data_get($summary, 'period.start') }} — {{ data_get($summary, 'period.end') }}</td>
            </tr>
            <tr>
                <th>Acessos</th>
                <td>
                    Total: {{ (int) data_get($summary, 'accesses_total', 0) }} |
                    Sucesso: {{ (int) data_get($summary, 'accesses_success', 0) }} |
                    Falha: {{ (int) data_get($summary, 'accesses_failed', 0) }}
                </td>
            </tr>
            <tr>
                <th>Alterações</th>
                <td>
                    Total: {{ (int) data_get($summary, 'changes_total', 0) }} |
                    INSERT: {{ (int) data_get($summary, 'changes_insert', 0) }} |
                    UPDATE: {{ (int) data_get($summary, 'changes_update', 0) }} |
                    DELETE: {{ (int) data_get($summary, 'changes_delete', 0) }}
                </td>
            </tr>
        </table>
    </div>

    <h2 style="margin-top: 14px;">Acessos (login)</h2>
    <p class="muted">Fonte: <code>portal.acesso</code>. Listagem limitada.</p>
    <table>
        <thead>
        <tr>
            <th style="width: 120px;">Data/hora</th>
            <th style="width: 55px;">ID</th>
            <th>Usuário</th>
            <th style="width: 55px;">OK?</th>
            <th style="width: 90px;">IP int.</th>
            <th style="width: 90px;">IP ext.</th>
        </tr>
        </thead>
        <tbody>
        @foreach($accesses as $r)
            <tr>
                <td>{{ $r['date'] ?? '' }}</td>
                <td>{{ $r['user_id'] ?? '' }}</td>
                <td>{{ $r['user_name'] ?? '-' }}</td>
                <td>{{ !empty($r['success']) ? 'Sim' : 'Não' }}</td>
                <td>{{ $r['internal_ip'] ?? '' }}</td>
                <td>{{ $r['external_ip'] ?? '' }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>

    <h2 style="margin-top: 14px;">Alterações de dados (trilha)</h2>
    <p class="muted">Fonte: <code>ieducar_audit</code>. Listagem limitada.</p>
    <table>
        <thead>
        <tr>
            <th style="width: 120px;">Data/hora</th>
            <th style="width: 45px;">ID</th>
            <th style="width: 55px;">Op.</th>
            <th style="width: 55px;">Usuário</th>
            <th style="width: 120px;">Tabela</th>
            <th style="width: 85px;">IP</th>
            <th>Origem (URL)</th>
        </tr>
        </thead>
        <tbody>
        @foreach($changes as $r)
            <tr>
                <td>{{ $r['date'] ?? '' }}</td>
                <td>{{ $r['id'] ?? '' }}</td>
                <td>{{ $r['operation'] ?? '' }}</td>
                <td>{{ $r['user_id'] ?? '' }}</td>
                <td>{{ ($r['schema'] ?? '') . '.' . ($r['table'] ?? '') }}</td>
                <td>{{ $r['ip'] ?? '' }}</td>
                <td>{{ $r['origin'] ?? '' }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
@endsection

