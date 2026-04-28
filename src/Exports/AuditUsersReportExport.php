<?php

namespace iEducar\Packages\AdvancedReports\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class AuditUsersReportExport implements WithMultipleSheets
{
    /**
     * @param array<string,mixed> $data
     * @param array<int,string> $operationOptions
     */
    public function __construct(private readonly array $data, private readonly array $operationOptions)
    {
    }

    public function sheets(): array
    {
        $summary = (array) ($this->data['summary'] ?? []);
        $accesses = $this->data['accesses'] ?? [];
        $changes = $this->data['changes'] ?? [];

        if (is_object($accesses) && method_exists($accesses, 'all')) {
            $accesses = $accesses->all();
        }
        if (is_object($changes) && method_exists($changes, 'all')) {
            $changes = $changes->all();
        }

        $summaryRows = [
            ['Período início', (string) data_get($summary, 'period.start', '')],
            ['Período fim', (string) data_get($summary, 'period.end', '')],
            ['Acessos (total)', (int) data_get($summary, 'accesses_total', 0)],
            ['Acessos (sucesso)', (int) data_get($summary, 'accesses_success', 0)],
            ['Acessos (falha)', (int) data_get($summary, 'accesses_failed', 0)],
            ['Alterações (total)', (int) data_get($summary, 'changes_total', 0)],
            ['Alterações (INSERT)', (int) data_get($summary, 'changes_insert', 0)],
            ['Alterações (UPDATE)', (int) data_get($summary, 'changes_update', 0)],
            ['Alterações (DELETE)', (int) data_get($summary, 'changes_delete', 0)],
        ];

        $accessRows = [];
        foreach (($accesses ?? []) as $r) {
            $accessRows[] = [
                (string) ($r['date'] ?? ''),
                (string) ($r['user_id'] ?? ''),
                (string) ($r['user_name'] ?? ''),
                !empty($r['success']) ? 'Sucesso' : 'Falha',
                (string) ($r['internal_ip'] ?? ''),
                (string) ($r['external_ip'] ?? ''),
            ];
        }

        $changeRows = [];
        foreach (($changes ?? []) as $r) {
            $changeRows[] = [
                (string) ($r['date'] ?? ''),
                (string) ($r['id'] ?? ''),
                (string) ($r['operation'] ?? ''),
                (string) ($r['user_id'] ?? ''),
                (string) ($r['user_name'] ?? ''),
                (string) ($r['schema'] ?? ''),
                (string) ($r['table'] ?? ''),
                (string) ($r['ip'] ?? ''),
                (string) ($r['origin'] ?? ''),
            ];
        }

        return [
            new SimpleArraySheet('Resumo', ['Indicador', 'Valor'], $summaryRows),
            new SimpleArraySheet('Acessos', ['Data/hora', 'Usuário ID', 'Usuário', 'Resultado', 'IP interno', 'IP externo'], $accessRows),
            new SimpleArraySheet('Alterações', ['Data/hora', 'Audit ID', 'Operação', 'Usuário ID', 'Usuário', 'Schema', 'Tabela', 'IP', 'Origem (URL)'], $changeRows),
        ];
    }
}

