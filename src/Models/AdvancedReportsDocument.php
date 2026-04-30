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
            'historico' => 'Histórico escolar',
            'declaration_enrollment' => 'Declaração de matrícula',
            'declaration_frequency' => 'Declaração de frequência',
            'declaration_conclusion' => 'Declaração de conclusão',
            'transfer_guide' => 'Guia/Declaração de transferência',
            'transfer_packet' => 'Comprovante de matrícula + declaração de transferência',
            'approval_packet' => 'Declaração de matrícula + declaração de conclusão (ficha individual)',
            'declaration_nada_consta' => 'Declaração de escolaridade / Nada consta',
            'vacancies_by_school_class' => 'Vagas por turma',
            'students_by_situation' => 'Alunos por situação',
            'movements_general' => 'Relatório de movimentações (geral)',
            'diary_mirror' => 'Espelho de diário (chamada)',
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
            'Ano letivo' => (string) ($p['year'] ?? ''),
            'Curso/Etapa' => (string) ($p['course'] ?? ''),
            'Turma' => (string) ($p['class'] ?? ''),
            'Matrícula (ref.)' => (string) ($p['enrollment'] ?? ''),
        ];

        // Livro/Folha/Registro: somente para histórico escolar
        if ((string) ($this->type ?? '') === 'historico') {
            $book = $p['book'] ?? null;
            $page = $p['page'] ?? null;
            $record = $p['record'] ?? null;

            $summary['Livro/Folha/Registro'] = trim(
                (string) ($book ?? '-') . ' / ' . (string) ($page ?? '-') . ' / ' . (string) ($record ?? '-')
            );
        }

        // Remove chaves vazias (sem expor payload completo)
        return array_filter($summary, fn ($v) => !is_null($v) && $v !== '');
    }
}
