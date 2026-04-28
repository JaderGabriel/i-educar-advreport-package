<?php

namespace iEducar\Packages\AdvancedReports\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class InclusionIndicatorsExport implements WithMultipleSheets
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
            ['Total de alunos', (int) ($summary['total_students'] ?? 0)],
            ['Alunos com deficiência (cadastro)', (int) ($summary['with_disabilities'] ?? 0)],
            ['Alunos com NIS', (int) ($summary['with_nis'] ?? 0)],
            ['Alunos com benefícios', (int) ($summary['with_benefits'] ?? 0)],
        ];

        $types = $this->rows('disability_by_type', fn ($r) => [$r->deficiencia ?? 'Deficiência', (int) ($r->total ?? 0)]);
        $schools = $this->rows('by_school', fn ($r) => [$r->escola ?? 'Escola', (int) ($r->total ?? 0), (int) ($r->com_deficiencia ?? 0), (int) ($r->com_nis ?? 0)]);

        return [
            new SimpleArraySheet('Resumo', ['Indicador', 'Total'], $summaryRows),
            new SimpleArraySheet('Deficiências', ['Deficiência', 'Total alunos'], $types),
            new SimpleArraySheet('Top escolas', ['Escola', 'Total', 'Com deficiência', 'Com NIS'], $schools),
        ];
    }

    private function rows(string $key, callable $map): array
    {
        $items = $this->data[$key] ?? [];
        if (is_object($items) && method_exists($items, 'all')) {
            $items = $items->all();
        }

        $rows = [];
        foreach ($items as $item) {
            $rows[] = $map($item);
        }

        return $rows;
    }
}

