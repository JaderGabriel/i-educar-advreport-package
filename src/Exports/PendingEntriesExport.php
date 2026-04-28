<?php

namespace iEducar\Packages\AdvancedReports\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class PendingEntriesExport implements WithMultipleSheets
{
    /**
     * @param array<string,mixed> $data
     */
    public function __construct(private readonly array $data)
    {
    }

    public function sheets(): array
    {
        $summary = $this->data['summary'] ?? [];
        $rows = $this->data['rows'] ?? [];

        if (is_object($rows) && method_exists($rows, 'all')) {
            $rows = $rows->all();
        }

        $summaryRows = [
            ['Matrículas analisadas', (int) ($summary['registrations'] ?? 0)],
            ['Pendências de nota (itens)', (int) ($summary['pending_grade_items'] ?? 0)],
            ['Pendências de frequência (itens)', (int) ($summary['pending_frequency_items'] ?? 0)],
        ];

        $pendingRows = [];
        foreach (($rows ?? []) as $r) {
            $pendingRows[] = [
                (string) ($r['student'] ?? ''),
                (string) ($r['registration_id'] ?? ''),
                (string) ($r['component'] ?? ''),
                (string) ($r['stage'] ?? ''),
                !empty($r['pending_grade']) ? 'Sim' : 'Não',
                !empty($r['pending_frequency']) ? 'Sim' : 'Não',
            ];
        }

        return [
            new SimpleArraySheet('Resumo', ['Indicador', 'Valor'], $summaryRows),
            new SimpleArraySheet('Pendências', ['Aluno', 'Matrícula', 'Componente', 'Etapa', 'Pend. nota', 'Pend. frequência'], $pendingRows),
        ];
    }
}

