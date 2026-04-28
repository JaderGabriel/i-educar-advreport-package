<?php

namespace iEducar\Packages\AdvancedReports\Models;

use Illuminate\Database\Eloquent\Model;

class AdvancedReportsDocument extends Model
{
    protected $table = 'advanced_reports_documents';

    protected $fillable = [
        'code',
        'type',
        'issued_at',
        'mac',
        'version',
        'payload',
    ];

    protected $casts = [
        'issued_at' => 'datetime',
        'payload' => 'array',
    ];

    /**
     * @return array<string, mixed>
     */
    public function publicSummary(): array
    {
        $p = is_array($this->payload) ? $this->payload : [];

        $summary = [
            'Tipo' => (string) ($this->type ?? ''),
            'Emitido em' => optional($this->issued_at)->format('d/m/Y H:i'),
            'Emissor' => trim((string) ($p['issuer_name'] ?? '') . (!empty($p['issuer_role']) ? (' (' . $p['issuer_role'] . ')') : '')),
            'Cidade/UF' => (string) ($p['city_uf'] ?? ''),
            'Livro/Folha/Registro' => trim((string) ($p['book'] ?? '-') . ' / ' . (string) ($p['page'] ?? '-') . ' / ' . (string) ($p['record'] ?? '-')),
            'Ano letivo' => (string) ($p['year'] ?? ''),
            'Curso/Etapa' => (string) ($p['course'] ?? ''),
            'Turma' => (string) ($p['class'] ?? ''),
            'Matrícula (ref.)' => (string) ($p['enrollment'] ?? ''),
        ];

        // Remove chaves vazias (sem expor payload completo)
        return array_filter($summary, fn ($v) => !is_null($v) && $v !== '');
    }
}

