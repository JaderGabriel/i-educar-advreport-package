<?php

namespace iEducar\Packages\AdvancedReports\Services;

/**
 * Modelos sugeridos de comunicados oficiais (textos base; a escola pode ajustar no formulário antes de emitir).
 */
final class CommunicationCatalog
{
    /**
     * @return list<string>
     */
    public static function slugs(): array
    {
        return ['convocacao', 'reuniao', 'advertencia', 'comunicado-geral'];
    }

    public static function assertSlug(string $slug): void
    {
        if (! in_array($slug, self::slugs(), true)) {
            abort(404);
        }
    }

    /**
     * @return array{
     *   title: string,
     *   doc_title: string,
     *   doc_subtitle: string,
     *   default_assunto: string,
     *   default_corpo: string,
     *   show_pauta: bool,
     *   show_prazo_resposta: bool
     * }
     */
    public static function definition(string $slug): array
    {
        return match ($slug) {
            'convocacao' => [
                'title' => 'Convocação',
                'doc_title' => 'COMUNICADO — CONVOCAÇÃO',
                'doc_subtitle' => 'Comparecimento à unidade escolar',
                'default_assunto' => 'Convocação de responsáveis legais para comparecimento na escola',
                'default_corpo' => <<<'TXT'
Por intermédio deste comunicado, a direção da unidade escolar CONVOCA o(s) responsável(is) legal(is) acima identificado(s) a comparecer(em) na data, horário e local indicados neste documento, em caráter de importância para a regularidade escolar e o acompanhamento pedagógico do(a) estudante.

Na ocasião, serão tratados assuntos inerentes à vida escolar, frequência, rendimento e/ou encaminhamentos administrativos necessários ao bom andamento do processo de ensino-aprendizagem.

Ressalta-se que o comparecimento, quando exigido pela instituição, deve ser observado conforme calendário e normas da rede de ensino, sem prejuízo das comunicações complementares que a escola venha a enviar por outros meios.

Atenciosamente,
TXT,
                'show_pauta' => true,
                'show_prazo_resposta' => false,
            ],
            'reuniao' => [
                'title' => 'Reunião',
                'doc_title' => 'COMUNICADO — REUNIÃO',
                'doc_subtitle' => 'Reunião com responsáveis / comunidade escolar',
                'default_assunto' => 'Convocação para reunião de pais, mestres e/ou conselho de classe',
                'default_corpo' => <<<'TXT'
A direção da unidade escolar convida o(s) responsável(is) legal(is) a participar(em) da reunião ora convocada, objetivando o fortalecimento da parceria família-escola e o alinhamento de encaminhamentos pedagógicos e administrativos.

A reunião poderá contemplar orientações gerais, apresentação de resultados, calendário escolar, normas de convivência, uso de uniforme/transporte e demais temas pertinentes à etapa e à turma, conforme pauta abaixo ou complementações feitas pela coordenação.

Solicita-se pontualidade e, quando for o caso, a apresentação de documentação indicada pela escola.

Atenciosamente,
TXT,
                'show_pauta' => true,
                'show_prazo_resposta' => false,
            ],
            'advertencia' => [
                'title' => 'Advertência',
                'doc_title' => 'COMUNICADO — ADVERTÊNCIA',
                'doc_subtitle' => 'Comunicação formal à família (registro institucional)',
                'default_assunto' => 'Advertência / comunicação formal sobre conduta ou pendências escolares',
                'default_corpo' => <<<'TXT'
O presente documento tem natureza de COMUNICADO FORMAL dirigido ao(s) responsável(is) legal(is), com vistas ao registro institucional e ao acompanhamento do(a) estudante, nos termos das normas de convivência e do regimento da rede de ensino aplicável à unidade.

Na hipótese de descumprimento de normas, frequência irregular, uso inadequado de uniforme, condutas que afetem o ambiente escolar ou outras situações previstas em regulamento local, a escola poderá adotar os encaminhamentos cabíveis, inclusive novas comunicações, medidas pedagógicas complementares ou procedimentos administrativos conforme legislação.

O(s) responsável(is) deverá(ão) comparecer e/ou contatar a escola no prazo indicado neste comunicado, quando houver, para ciência e deliberação sobre os fatos e providências necessárias.

Atenciosamente,
TXT,
                'show_pauta' => false,
                'show_prazo_resposta' => true,
            ],
            'comunicado-geral' => [
                'title' => 'Comunicado geral',
                'doc_title' => 'COMUNICADO',
                'doc_subtitle' => 'Aviso / orientação à comunidade escolar',
                'default_assunto' => 'Comunicado geral — avisos e orientações',
                'default_corpo' => <<<'TXT'
A direção da unidade escolar vem, por meio deste comunicado, informar e orientar a comunidade escolar acerca de assuntos de interesse coletivo, tais como: calendário de avaliações, eventos, campanhas de saúde, uso de espaços, uniforme, transporte escolar, protocolos de segurança, atualizações cadastrais e demais providências necessárias ao regular funcionamento da escola.

Este documento complementa outros canais oficiais de informação utilizados pela rede (portal, aplicativos, grupos institucionais etc.), devendo ser observado o disposto nas normas locais.

Em caso de dúvidas, procure a secretaria ou a coordenação pedagógica no horário de atendimento.

Atenciosamente,
TXT,
                'show_pauta' => true,
                'show_prazo_resposta' => false,
            ],
            default => abort(404),
        };
    }

    public static function documentType(string $slug): string
    {
        self::assertSlug($slug);

        return 'communication:' . $slug;
    }
}
