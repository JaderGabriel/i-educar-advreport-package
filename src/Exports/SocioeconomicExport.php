<?php

namespace iEducar\Packages\AdvancedReports\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class SocioeconomicExport implements WithMultipleSheets
{
    public function __construct(
        private readonly int $year,
        private readonly array $data,
    ) {
    }

    public function sheets(): array
    {
        return [
            new SimpleArraySheet('Raça/Cor', ['Raça/Cor', 'Total'], $this->rows('race', fn ($r) => [$r->raca_label ?? ($r->raca ?? 'Não informada'), (int) ($r->total ?? 0)])),
            new SimpleArraySheet('Gênero', ['Gênero', 'Total'], $this->rows('gender', fn ($r) => [$r->sexo_label ?? ($r->sexo ?? 'Não informado'), (int) ($r->total ?? 0)])),
            new SimpleArraySheet('Benefícios', ['Benefício', 'Total'], $this->rows('benefits', fn ($r) => [$r->beneficio ?? 'Sem benefício', (int) ($r->total ?? 0)])),
            new SimpleArraySheet('Escolas', ['Escola', 'Total'], $this->rows('schools', fn ($r) => [$r->nome ?? 'Escola', (int) ($r->total ?? 0)])),
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

