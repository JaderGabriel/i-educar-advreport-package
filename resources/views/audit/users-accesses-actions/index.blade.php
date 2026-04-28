@extends('layout.default')

@section('content')
    <div class="advanced-report-card">
        <strong class="advanced-report-card-title">Auditoria — acessos e ações de usuários</strong>
        <p class="advanced-report-card-text">
            Consolida **acessos (login)** e **alterações de dados** (triggers de auditoria) com origem (URL), IP e antes/depois.
            Use filtros para restringir o período e/ou um usuário.
        </p>

        <form action="{{ route('advanced-reports.audit.users.index') }}" method="get">
            <table cellspacing="0" cellpadding="0" border="0" width="100%">
                <tr>
                    <td class="formmdtd" valign="top"><span class="form">Período</span></td>
                    <td class="formmdtd" valign="top">
                        <input class="geral" type="date" name="date_start" value="{{ request('date_start') }}" style="width: 160px;">
                        <span style="margin: 0 6px;">até</span>
                        <input class="geral" type="date" name="date_end" value="{{ request('date_end') }}" style="width: 160px;">
                    </td>
                </tr>

                <tr>
                    <td class="formmdtd" valign="top"><span class="form">Usuário</span></td>
                    <td class="formmdtd" valign="top">
                        <select class="geral" name="user_id" style="width: 520px;">
                            <option value="">Todos</option>
                            @foreach(($users ?? []) as $u)
                                <option value="{{ data_get($u, 'id') }}" @selected((string) request('user_id') === (string) data_get($u, 'id'))>
                                    {{ data_get($u, 'nome') }} ({{ data_get($u, 'id') }})
                                </option>
                            @endforeach
                        </select>
                    </td>
                </tr>

                <tr>
                    <td class="formmdtd" valign="top"><span class="form">IP contém</span></td>
                    <td class="formmdtd" valign="top">
                        <input class="geral" type="text" name="ip" value="{{ request('ip') }}" style="width: 220px;">
                        <span style="margin-left:10px;" class="form">Acesso</span>
                        <select class="geral" name="success" style="width: 170px;margin-left:6px;">
                            <option value="">Todos</option>
                            <option value="1" @selected((string) request('success') === '1')>Somente sucesso</option>
                            <option value="0" @selected((string) request('success') === '0')>Somente falha</option>
                        </select>
                    </td>
                </tr>

                <tr>
                    <td class="formmdtd" valign="top"><span class="form">Alterações</span></td>
                    <td class="formmdtd" valign="top">
                        <span class="form">Operação</span>
                        <select class="geral" name="operation" style="width: 190px;margin-left:6px;">
                            <option value="">Todas</option>
                            @foreach(($operationOptions ?? []) as $id => $label)
                                <option value="{{ $id }}" @selected((string) request('operation') === (string) $id)>{{ $label }}</option>
                            @endforeach
                        </select>

                        <span style="margin-left:10px;" class="form">Tabela/Schema contém</span>
                        <input class="geral" type="text" name="table" value="{{ request('table') }}" style="width: 220px;margin-left:6px;">
                    </td>
                </tr>

                <tr>
                    <td class="formmdtd" valign="top"><span class="form">Origem (URL) contém</span></td>
                    <td class="formmdtd" valign="top">
                        <input class="geral" type="text" name="origin" value="{{ request('origin') }}" style="width: 520px;">
                        <button type="submit" class="btn-green" style="margin-left: 8px;">Aplicar</button>
                    </td>
                </tr>
            </table>
        </form>
    </div>

    @if(request('date_start') && request('date_end'))
        <div class="advanced-report-card" style="margin-top: 12px;">
            <strong class="advanced-report-card-title">Emissão</strong>
            <p class="advanced-report-card-text">PDF abre como prévia no navegador. Excel exporta 3 abas (Resumo/Acessos/Alterações).</p>
            <div style="display:flex;gap:8px;flex-wrap:wrap;align-items:center;">
                <select class="geral js-export-type" style="width: 180px;"
                        data-pdf="{{ route('advanced-reports.audit.users.pdf') . '?' . http_build_query(request()->all()) }}"
                        data-excel="{{ route('advanced-reports.audit.users.excel') . '?' . http_build_query(request()->all()) }}">
                    <option value="pdf">Gerar PDF</option>
                    <option value="excel">Exportar Excel</option>
                </select>
                <button type="button" class="btn-green js-export-run">Executar</button>
            </div>
        </div>
    @endif

    @if(!empty($data))
        @php($summary = $data['summary'] ?? [])
        @php($accesses = $data['accesses'] ?? collect())
        @php($changes = $data['changes'] ?? collect())

        <div class="advanced-report-card" style="margin-top: 12px;">
            <strong class="advanced-report-card-title">Resumo</strong>
            <div style="overflow:auto;">
                <table class="tablelistagem" width="100%" cellspacing="1" cellpadding="4" border="0">
                    <tr>
                        <th class="formdktd">Indicador</th>
                        <th class="formdktd" style="width: 160px;">Valor</th>
                    </tr>
                    <tr><td class="formlttd">Acessos (total)</td><td class="formlttd">{{ $summary['accesses_total'] ?? 0 }}</td></tr>
                    <tr><td class="formlttd">Acessos (sucesso)</td><td class="formlttd">{{ $summary['accesses_success'] ?? 0 }}</td></tr>
                    <tr><td class="formlttd">Acessos (falha)</td><td class="formlttd">{{ $summary['accesses_failed'] ?? 0 }}</td></tr>
                    <tr><td class="formlttd">Alterações (total)</td><td class="formlttd">{{ $summary['changes_total'] ?? 0 }}</td></tr>
                    <tr><td class="formlttd">Alterações (INSERT)</td><td class="formlttd">{{ $summary['changes_insert'] ?? 0 }}</td></tr>
                    <tr><td class="formlttd">Alterações (UPDATE)</td><td class="formlttd">{{ $summary['changes_update'] ?? 0 }}</td></tr>
                    <tr><td class="formlttd">Alterações (DELETE)</td><td class="formlttd">{{ $summary['changes_delete'] ?? 0 }}</td></tr>
                </table>
            </div>
        </div>

        <div class="advanced-report-card" style="margin-top: 12px;">
            <strong class="advanced-report-card-title">Acessos (login)</strong>
            <p class="advanced-report-card-text">Fonte: <code>portal.acesso</code>. Listagem limitada para performance.</p>
            <div style="overflow:auto;">
                <table class="tablelistagem" width="100%" cellspacing="1" cellpadding="4" border="0">
                    <tr>
                        <th class="formdktd" style="width: 160px;">Data/hora</th>
                        <th class="formdktd" style="width: 90px;">Usuário ID</th>
                        <th class="formdktd">Usuário</th>
                        <th class="formdktd" style="width: 90px;">Resultado</th>
                        <th class="formdktd" style="width: 120px;">IP interno</th>
                        <th class="formdktd" style="width: 120px;">IP externo</th>
                    </tr>
                    @forelse($accesses as $r)
                        <tr>
                            <td class="formlttd">{{ $r['date'] ?? '' }}</td>
                            <td class="formlttd">{{ $r['user_id'] ?? '' }}</td>
                            <td class="formlttd">{{ $r['user_name'] ?? '-' }}</td>
                            <td class="formlttd">{{ !empty($r['success']) ? 'Sucesso' : 'Falha' }}</td>
                            <td class="formlttd">{{ $r['internal_ip'] ?? '' }}</td>
                            <td class="formlttd">{{ $r['external_ip'] ?? '' }}</td>
                        </tr>
                    @empty
                        <tr><td class="formlttd" colspan="6">Nenhum acesso no período/filtros.</td></tr>
                    @endforelse
                </table>
            </div>
        </div>

        <div class="advanced-report-card" style="margin-top: 12px;">
            <strong class="advanced-report-card-title">Alterações de dados (trilha de auditoria)</strong>
            <p class="advanced-report-card-text">Fonte: <code>ieducar_audit</code>. Mostra origem (URL), IP e operação. Listagem limitada para performance.</p>
            <div style="overflow:auto;">
                <table class="tablelistagem" width="100%" cellspacing="1" cellpadding="4" border="0">
                    <tr>
                        <th class="formdktd" style="width: 160px;">Data/hora</th>
                        <th class="formdktd" style="width: 70px;">ID</th>
                        <th class="formdktd" style="width: 70px;">Op.</th>
                        <th class="formdktd" style="width: 90px;">Usuário ID</th>
                        <th class="formdktd" style="width: 220px;">Usuário</th>
                        <th class="formdktd" style="width: 180px;">Tabela</th>
                        <th class="formdktd" style="width: 120px;">IP</th>
                        <th class="formdktd">Origem (URL)</th>
                    </tr>
                    @forelse($changes as $r)
                        <tr>
                            <td class="formlttd">{{ $r['date'] ?? '' }}</td>
                            <td class="formlttd">{{ $r['id'] ?? '' }}</td>
                            <td class="formlttd">{{ $r['operation'] ?? '' }}</td>
                            <td class="formlttd">{{ $r['user_id'] ?? '' }}</td>
                            <td class="formlttd">{{ $r['user_name'] ?? '-' }}</td>
                            <td class="formlttd">{{ ($r['schema'] ?? '') . '.' . ($r['table'] ?? '') }}</td>
                            <td class="formlttd">{{ $r['ip'] ?? '' }}</td>
                            <td class="formlttd">{{ $r['origin'] ?? '' }}</td>
                        </tr>
                    @empty
                        <tr><td class="formlttd" colspan="8">Nenhuma alteração no período/filtros.</td></tr>
                    @endforelse
                </table>
            </div>
        </div>
    @endif

    <script>
        (function () {
            const typeSelect = document.querySelector('.js-export-type');
            const runBtn = document.querySelector('.js-export-run');
            if (typeSelect && runBtn) {
                runBtn.addEventListener('click', function () {
                    const url = (typeSelect.value === 'excel') ? typeSelect.dataset.excel : typeSelect.dataset.pdf;
                    if (!url) return;
                    window.open(url, '_blank');
                });
            }

            // A seleção de usuário é feita via <select> (lista completa).
        })();
    </script>
@endsection

