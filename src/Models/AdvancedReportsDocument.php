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
        'issued_by_user_id',
        'issued_ip',
        'issued_user_agent',
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

        $typeHuman = match ((string) ($this->type ?? '')) {
            'boletim' => 'Boletim do aluno',
            'boletim_batch' => 'Boletim do aluno (lote)',
            'declaration_enrollment' => 'Declaração de matrícula',
            'declaration_frequency' => 'Declaração de frequência',
            'declaration_conclusion' => 'Declaração de conclusão',
            'transfer_guide' => 'Guia/Declaração de transferência',
            'declaration_nada_consta' => 'Declaração de escolaridade / Nada consta',
            'vacancies_by_school_class' => 'Vagas por turma',
            'students_by_situation' => 'Alunos por situação',
            'diploma' => 'Diploma (modelo)',
            'certificate' => 'Certificado (modelo)',
            'declaration' => 'Declaração (modelo)',
            default => (string) ($this->type ?? ''),
        };

        $summary = [
            'Tipo' => $typeHuman,
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

