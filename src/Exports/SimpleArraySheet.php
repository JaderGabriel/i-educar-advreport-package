<?php

namespace iEducar\Packages\AdvancedReports\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class SimpleArraySheet implements FromArray, WithHeadings, WithTitle
{
    /**
     * @param array<int, array<int, mixed>> $rows
     * @param array<int, string> $headings
     */
    public function __construct(
        private readonly string $title,
        private readonly array $headings,
        private readonly array $rows,
    ) {
    }

    public function title(): string
    {
        return $this->title;
    }

    public function headings(): array
    {
        return $this->headings;
    }

    public function array(): array
    {
        return $this->rows;
    }
}

