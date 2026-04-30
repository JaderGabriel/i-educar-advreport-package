<?php

namespace iEducar\Packages\AdvancedReports\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class StudentsBySituationExport implements WithMultipleSheets
{
    /**
     * @param array<string,mixed> $data
     * @param array<int,string> $labels
     */
    public function __construct(private readonly array $data, private readonly array $labels) {}

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
                (string) ($r['matricula_id'] ?? ''),
                (string) ($r['aluno'] ?? ''),
                (string) ($r['curso'] ?? ''),
                (string) ($r['turma'] ?? ''),
                (string) ($r['turno'] ?? ''),
                (string) ($r['situacao'] ?? ''),
                (string) ($r['componentes'] ?? ''),
                (string) ($r['escola'] ?? ''),
                (string) ($r['serie'] ?? ''),
            ];
        }

        return [
            new SimpleArraySheet('Resumo', ['Situação', 'Total'], $summaryRows),
            new SimpleArraySheet('Alunos', [
                'Matrícula',
                'Aluno',
                'Curso',
                'Turma',
                'Turno',
                'Situação',
                'Componentes (turma)',
                'Escola',
                'Série',
            ], $detailRows),
        ];
    }
}
