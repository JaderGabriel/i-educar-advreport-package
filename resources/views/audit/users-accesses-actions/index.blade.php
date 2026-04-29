@extends('layout.default')

@push('styles')
  @if (class_exists('Asset'))
    <link rel="stylesheet" type="text/css" href="{{ Asset::get('css/ieducar.css') }}"/>
    <link rel="stylesheet" type="text/css" href="{{ Asset::get('css/advanced-reports.css') }}"/>
  @else
    <link rel="stylesheet" type="text/css" href="{{ asset('css/advanced-reports.css') }}"/>
  @endif
@endpush

@section('content')
    <div class="advanced-report-card">
        <strong class="advanced-report-card-title">Auditoria — acessos e ações de usuários</strong>
        <p class="advanced-report-card-text">
            Consolida <strong>acessos (login)</strong> e <strong>alterações de dados</strong> (triggers de auditoria) com origem (URL), IP e antes/depois.
            Use filtros para restringir o período e/ou um usuário.
        </p>

        <form action="{{ route('advanced-reports.audit.users.index') }}" method="get" id="auditUsersForm">
            <table class="tablecadastro" width="100%" border="0" cellpadding="2" cellspacing="0" role="presentation">
                <tbody>
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
                    </td>
                </tr>
                </tbody>
            </table>

            <div class="ar-actions">
                <div class="ar-actions__group">
                    <a href="{{ route('advanced-reports.audit.users.index') }}" class="btn ar-btn ar-btn--ghost">Limpar</a>
                    <button type="submit" class="btn-green ar-btn ar-btn--primary">Aplicar filtros</button>
                </div>

                <div class="ar-actions__group">
                    <button type="button" class="btn-green ar-btn ar-btn--secondary js-audit-emit-pdf" {{ !(request('date_start') && request('date_end')) ? 'disabled' : '' }}>Emitir PDF (final)</button>
                    <button type="button" class="btn ar-btn ar-btn--ghost js-audit-help" title="Ver prévia (exemplo)" aria-label="Ver prévia (exemplo)" {{ !(request('date_start') && request('date_end')) ? 'disabled' : '' }}>?</button>
                    <button type="button" class="btn ar-btn ar-btn--secondary js-audit-excel" {{ !(request('date_start') && request('date_end')) ? 'disabled' : '' }}>Exportar Excel</button>
                </div>
            </div>
        </form>
    </div>

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

    <div id="advancedReportsAuditPreviewModal" class="ar-modal">
        <div class="ar-modal__dialog">
            <div class="ar-modal__header">
                <strong>Prévia (exemplo)</strong>
                <button type="button" class="btn js-audit-preview-close">Fechar</button>
            </div>
            <iframe class="js-audit-preview-iframe ar-modal__iframe"></iframe>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        (function () {
            const form = document.getElementById('auditUsersForm');
            const modal = document.getElementById('advancedReportsAuditPreviewModal');
            const iframe = document.querySelector('.js-audit-preview-iframe');
            const closeBtn = document.querySelector('.js-audit-preview-close');
            const emitPdf = document.querySelector('.js-audit-emit-pdf');
            const helpBtn = document.querySelector('.js-audit-help');
            const excelBtn = document.querySelector('.js-audit-excel');
            const pdfBase = "{{ route('advanced-reports.audit.users.pdf') }}";
            const excelBase = "{{ route('advanced-reports.audit.users.excel') }}";

            function buildQuery() {
                if (!form) return '';
                return new URLSearchParams(new FormData(form)).toString();
            }

            function closeModal() {
                if (!iframe || !modal) return;
                iframe.src = 'about:blank';
                modal.style.display = 'none';
            }

            if (emitPdf) {
                emitPdf.addEventListener('click', function (e) {
                    e.preventDefault();
                    const q = buildQuery();
                    if (!q) return;
                    window.open(pdfBase + '?' + q, '_blank');
                });
            }

            if (excelBtn) {
                excelBtn.addEventListener('click', function (e) {
                    e.preventDefault();
                    const q = buildQuery();
                    if (!q) return;
                    window.open(excelBase + '?' + q, '_blank');
                });
            }

            if (form && modal && iframe && helpBtn && closeBtn) {
                helpBtn.addEventListener('click', function (e) {
                    e.preventDefault();
                    const params = new URLSearchParams(new FormData(form));
                    params.set('preview', '1');
                    iframe.src = pdfBase + '?' + params.toString();
                    modal.style.display = 'block';
                });
                closeBtn.addEventListener('click', function (e) { e.preventDefault(); closeModal(); });
                modal.addEventListener('click', function (e) { if (e.target === modal) closeModal(); });
            }
        })();
    </script>
@endpush
