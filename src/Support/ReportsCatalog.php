<?php

namespace iEducar\Packages\AdvancedReports\Support;

/**
 * Catálogo de relatórios para requisitos típicos de edital (PDF / Excel / QR).
 */
final class ReportsCatalog
{
    /**
     * Formatos exigidos com frequência em TR/editais (alinhado a ISO 32000 + planilhas).
     *
     * @return list<string>
     */
    public static function formatosEdital(): array
    {
        return ['PDF', 'XLSX', 'CSV', 'DOCX', 'ODT', 'HTML', 'XML', 'TXT', 'JPEG'];
    }

    public static function versaoPacote(): string
    {
        $composer = dirname(__DIR__, 2).'/composer.json';
        if (! is_file($composer)) {
            return 'dev';
        }
        $data = json_decode((string) file_get_contents($composer), true);

        return is_array($data) ? (string) ($data['version'] ?? 'dev') : 'dev';
    }

    /**
     * @return list<array{
     *   grupo:string,
     *   titulo:string,
     *   descricao:string,
     *   rota:?string,
     *   formatos:list<string>,
     *   qr:bool,
     *   status:string
     * }>
     */
    public static function itens(): array
    {
        return [
            [
                'grupo' => 'Documentos do aluno',
                'titulo' => 'Boletim escolar',
                'descricao' => 'Emissão em PDF com cabeçalho formal e validação pública (QR).',
                'rota' => 'advanced-reports.boletim.index',
                'formatos' => ['PDF'],
                'qr' => true,
                'status' => 'ok',
            ],
            [
                'grupo' => 'Documentos do aluno',
                'titulo' => 'Histórico escolar',
                'descricao' => 'Modelos classic/modern/SIMADE com assinaturas padronizadas.',
                'rota' => 'advanced-reports.school-history.index',
                'formatos' => ['PDF'],
                'qr' => true,
                'status' => 'ok',
            ],
            [
                'grupo' => 'Documentos do aluno',
                'titulo' => 'Fichas e declarações',
                'descricao' => 'Matrícula, frequência, escolaridade e demais fichas oficiais.',
                'rota' => 'advanced-reports.student-documents.index',
                'formatos' => ['PDF'],
                'qr' => true,
                'status' => 'ok',
            ],
            [
                'grupo' => 'Documentos do aluno',
                'titulo' => 'Diploma / certificado',
                'descricao' => 'Modelos oficiais com layout Dompdf sem vazamento.',
                'rota' => 'advanced-reports.diplomas.index',
                'formatos' => ['PDF'],
                'qr' => true,
                'status' => 'ok',
            ],
            [
                'grupo' => 'Indicadores (edital)',
                'titulo' => 'Socioeconômicos',
                'descricao' => 'Agregações com gráficos, PDF validável e Excel.',
                'rota' => 'advanced-reports.socioeconomic.index',
                'formatos' => ['PDF', 'XLSX'],
                'qr' => true,
                'status' => 'ok',
            ],
            [
                'grupo' => 'Indicadores (edital)',
                'titulo' => 'Inclusão (NEE)',
                'descricao' => 'Indicadores de inclusão com PDF/Excel multiaba.',
                'rota' => 'advanced-reports.inclusion.index',
                'formatos' => ['PDF', 'XLSX'],
                'qr' => false,
                'status' => 'ok',
            ],
            [
                'grupo' => 'Indicadores (edital)',
                'titulo' => 'Distorção idade/série',
                'descricao' => 'Panorama de distorção para gestão e Censo.',
                'rota' => 'advanced-reports.age-distortion.index',
                'formatos' => ['PDF', 'XLSX'],
                'qr' => false,
                'status' => 'ok',
            ],
            [
                'grupo' => 'Indicadores (edital)',
                'titulo' => 'Vulnerabilidade social',
                'descricao' => 'Assistência e vulnerabilidade com gráficos opcionais.',
                'rota' => 'advanced-reports.social-vulnerability.index',
                'formatos' => ['PDF', 'XLSX'],
                'qr' => false,
                'status' => 'ok',
            ],
            [
                'grupo' => 'Movimentações',
                'titulo' => 'Movimento geral',
                'descricao' => 'Consulta de movimentações com PDF e Excel.',
                'rota' => 'advanced-reports.movements.index',
                'formatos' => ['PDF', 'XLSX'],
                'qr' => false,
                'status' => 'ok',
            ],
            [
                'grupo' => 'Movimentações',
                'titulo' => 'Alunos por situação',
                'descricao' => 'Situação de matrícula/enturmação por filtros da rede.',
                'rota' => 'advanced-reports.students-by-situation.index',
                'formatos' => ['PDF', 'XLSX'],
                'qr' => false,
                'status' => 'ok',
            ],
            [
                'grupo' => 'Movimentações',
                'titulo' => 'Vagas por turma',
                'descricao' => 'Oferta e ocupação de vagas por turma.',
                'rota' => 'advanced-reports.vacancies.index',
                'formatos' => ['PDF', 'XLSX'],
                'qr' => false,
                'status' => 'ok',
            ],
            [
                'grupo' => 'Pedagógico',
                'titulo' => 'Espelho de diário',
                'descricao' => 'Chamada por docente/componente, calendário letivo e ZIP validável.',
                'rota' => 'advanced-reports.diary-mirror.index',
                'formatos' => ['PDF'],
                'qr' => true,
                'status' => 'ok',
            ],
            [
                'grupo' => 'Pedagógico',
                'titulo' => 'Pendências de lançamento',
                'descricao' => 'Identificação de notas/frequências pendentes.',
                'rota' => 'advanced-reports.pending-entries.index',
                'formatos' => ['PDF', 'XLSX'],
                'qr' => false,
                'status' => 'ok',
            ],
            [
                'grupo' => 'Auditoria',
                'titulo' => 'Acessos e ações',
                'descricao' => 'Rastreabilidade de usuários (PDF/Excel) para conformidade.',
                'rota' => 'advanced-reports.audit.users.index',
                'formatos' => ['PDF', 'XLSX'],
                'qr' => false,
                'status' => 'ok',
            ],
            [
                'grupo' => 'Validação pública',
                'titulo' => 'QR / código de autenticação',
                'descricao' => 'Consulta sem login em /documentos/validar/{código}.',
                'rota' => null,
                'formatos' => ['HTML'],
                'qr' => true,
                'status' => 'ok',
            ],
        ];
    }

    /**
     * @return array<string, list<array<string, mixed>>>
     */
    public static function agrupados(): array
    {
        $out = [];
        foreach (self::itens() as $item) {
            $out[$item['grupo']][] = $item;
        }

        return $out;
    }
}
