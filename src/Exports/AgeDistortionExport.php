<?php

namespace iEducar\Packages\AdvancedReports\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class AgeDistortionExport implements WithMultipleSheets
{
    public function __construct(
        private readonly int $year,
        private readonly array $data,
    ) {
    }

    public function sheets(): array
    {
        $summary = $this->data['summary'] ?? [];

        $summaryRows = [
            ['Total de alunos (recorte)', (int) ($summary['total_students'] ?? 0)],
            ['Alunos em distorção (fora da idade ideal)', (int) ($summary['distortion_students'] ?? 0)],
            ['% distorção', (float) ($summary['distortion_pct'] ?? 0)],
        ];

        $gradesRows = [];
        foreach (($this->data['grades'] ?? []) as $g) {
            $gradesRows[] = [
                (string) ($g['grade_name'] ?? ''),
                (int) ($g['ideal_age'] ?? 0),
                (int) ($g['total_students'] ?? 0),
                (int) ($g['ideal_count'] ?? 0),
                (int) ($g['distortion_count'] ?? 0),
                (float) ($g['distortion_pct'] ?? 0),
            ];
        }

        return [
            new SimpleArraySheet('Resumo', ['Indicador', 'Valor'], $summaryRows),
            new SimpleArraySheet('Por série', ['Série', 'Idade ideal', 'Total', 'Na idade ideal', 'Distorção', '% distorção'], $gradesRows),
        ];
    }
}

