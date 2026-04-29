<?php

namespace iEducar\Packages\AdvancedReports\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class VacanciesBySchoolClassExport implements FromCollection, WithHeadings
{
    /**
     * @param Collection<int,object> $items
     */
    public function __construct(private readonly Collection $items)
    {
    }

    public function collection(): Collection
    {
        return $this->items->map(static function ($row) {
            return [
                'instituicao' => SpreadsheetFormulaInjectionGuard::sanitizeScalar((string) ($row->instituicao ?? '')),
                'escola' => SpreadsheetFormulaInjectionGuard::sanitizeScalar((string) ($row->escola ?? '')),
                'turma' => SpreadsheetFormulaInjectionGuard::sanitizeScalar((string) ($row->turma ?? '')),
                'turno' => SpreadsheetFormulaInjectionGuard::sanitizeScalar((string) ($row->turno ?? '')),
                'curso' => SpreadsheetFormulaInjectionGuard::sanitizeScalar((string) ($row->curso ?? '')),
                'serie' => SpreadsheetFormulaInjectionGuard::sanitizeScalar((string) ($row->serie ?? '')),
                'capacidade' => (int) ($row->capacidade ?? 0),
                'matriculados' => (int) ($row->matriculados ?? 0),
                'vagas' => (int) ($row->vagas ?? 0),
            ];
        });
    }

    public function headings(): array
    {
        return [
            'Instituição',
            'Escola',
            'Turma',
            'Turno',
            'Curso',
            'Série',
            'Capacidade',
            'Matriculados',
            'Vagas',
        ];
    }
}

