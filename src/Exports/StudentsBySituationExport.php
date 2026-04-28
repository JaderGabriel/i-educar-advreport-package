<?php

namespace iEducar\Packages\AdvancedReports\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class StudentsBySituationExport implements WithMultipleSheets
{
    /**
     * @param array<string,mixed> $data
     * @param array<int,string> $labels
     */
    public function __construct(private readonly array $data, private readonly array $labels)
    {
    }

    public function sheets(): array
    {
        $summary = $this->data['summary'] ?? [];
        $rows = $this->data['rows'] ?? [];

        if (is_object($rows) && method_exists($rows, 'all')) {
            $rows = $rows->all();
        }

        $summaryRows = [];
        foreach (($summary ?? []) as $sid => $total) {
            $summaryRows[] = [$this->labels[(int) $sid] ?? ('Situação ' . $sid), (int) $total];
        }

        $detailRows = [];
        foreach (($rows ?? []) as $r) {
            $detailRows[] = [
                (string) ($r['aluno'] ?? ''),
                (string) ($r['matricula_id'] ?? ''),
                (string) ($r['situacao'] ?? ''),
                (string) ($r['escola'] ?? ''),
                (string) ($r['curso'] ?? ''),
                (string) ($r['serie'] ?? ''),
                (string) ($r['turma'] ?? ''),
            ];
        }

        return [
            new SimpleArraySheet('Resumo', ['Situação', 'Total'], $summaryRows),
            new SimpleArraySheet('Alunos', ['Aluno', 'Matrícula', 'Situação', 'Escola', 'Curso', 'Série', 'Turma'], $detailRows),
        ];
    }
}

