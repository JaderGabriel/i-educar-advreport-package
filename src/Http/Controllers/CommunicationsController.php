<?php

namespace iEducar\Packages\AdvancedReports\Http\Controllers;

use App\Http\Controllers\Controller;
use iEducar\Packages\AdvancedReports\Models\AdvancedReportsDocument;
use iEducar\Packages\AdvancedReports\Services\AdvancedReportsFilterService;
use iEducar\Packages\AdvancedReports\Services\CommunicationCatalog;
use iEducar\Packages\AdvancedReports\Services\DocumentSigningService;
use iEducar\Packages\AdvancedReports\Services\OfficialHeaderService;
use iEducar\Packages\AdvancedReports\Services\PdfRenderService;
use iEducar\Packages\AdvancedReports\Services\QrCodeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class CommunicationsController extends Controller
{
    public function index(Request $request, AdvancedReportsFilterService $filters, string $slug): View
    {
        CommunicationCatalog::assertSlug($slug);

        $ano = $request->get('ano');
        $instituicaoId = $request->get('ref_cod_instituicao');
        $escolaId = $request->get('ref_cod_escola');
        $cursoId = $request->get('ref_cod_curso');

        $filterData = $filters->getFilters(
            $ano ? (int) $ano : null,
            $instituicaoId ? (int) $instituicaoId : null,
            $escolaId ? (int) $escolaId : null,
            $cursoId ? (int) $cursoId : null
        );

        $def = CommunicationCatalog::definition($slug);

        return view('advanced-reports::communications.index', [
            'slug' => $slug,
            'definition' => $def,
            'ano' => $ano,
            'instituicaoId' => $instituicaoId,
            'escolaId' => $escolaId,
            'cursoId' => $cursoId,
            ...$filterData,
        ]);
    }

    public function pdf(Request $request, string $slug): Response
    {
        CommunicationCatalog::assertSlug($slug);
        $def = CommunicationCatalog::definition($slug);
        $docType = CommunicationCatalog::documentType($slug);
        $fields = $this->extractFieldsFromRequest($request, $def);

        $ano = $request->get('ano') ? (int) $request->get('ano') : null;
        $escolaId = $request->get('ref_cod_escola') ? (int) $request->get('ref_cod_escola') : null;
        $cursoId = $request->get('ref_cod_curso') ? (int) $request->get('ref_cod_curso') : null;
        $turmaId = $request->get('ref_cod_turma') ? (int) $request->get('ref_cod_turma') : null;
        $serieId = $request->get('ref_cod_serie') ? (int) $request->get('ref_cod_serie') : null;

        if ($request->boolean('preview')) {
            $header = $this->previewHeader();
            $ctx = [
                'turma_nome' => 'Turma (Exemplo)',
                'serie_nome' => 'Série (Exemplo)',
                'curso_nome' => 'Curso (Exemplo)',
                'escola_nome' => 'Unidade Escolar (Exemplo)',
                'ano_letivo' => (string) ($request->get('ano') ?: date('Y')),
            ];
            $items = [[
                'matricula_id' => null,
                'aluno_nome' => 'ESTUDANTE (EXEMPLO)',
                'destinatario_prefixo' => 'Ao(À) responsável legal pelo(a) estudante',
            ]];

            return app(PdfRenderService::class)->download('advanced-reports::communications.pdf', [
                'slug' => $slug,
                'definition' => $def,
                'fields' => $fields,
                'context' => $ctx,
                'items' => $items,
                'issuedAt' => now()->format('d/m/Y H:i'),
                'validationCode' => 'EXEMPLO',
                'validationUrl' => '#',
                'qrDataUri' => null,
                'issuerName' => auth()->user()?->name,
                'issuerRole' => null,
                'cityUf' => null,
                'schoolInep' => '00000000',
                'municipality' => $header['municipality'] ?? null,
                'schoolName' => $header['schoolName'] ?? null,
                'contact' => $header['contact'] ?? null,
            ], 'comunicado-previa.pdf');
        }

        if (!$ano || !$escolaId || !$cursoId) {
            abort(422, 'Informe ano, escola e curso para emitir o comunicado.');
        }

        $matriculaId = (int) $request->get('matricula_id');
        $matriculaIds = array_values(array_filter(array_map('intval', (array) $request->get('matricula_ids', []))));

        $idsToEmit = [];
        if ($matriculaId) {
            $idsToEmit = [$matriculaId];
        } elseif (!empty($matriculaIds)) {
            $idsToEmit = array_slice($matriculaIds, 0, 200);
        }

        $wantsSpecificRecipients = $matriculaId > 0 || !empty($matriculaIds);

        $header = app(OfficialHeaderService::class)->forSchool(
            $this->instituicaoIdFromEscola($escolaId),
            $escolaId,
        );

        $schoolInep = DB::table('modules.educacenso_cod_escola')
            ->where('cod_escola', $escolaId)
            ->value('cod_escola_inep');

        $ctx = $this->loadContextFromFilters($ano, $escolaId, $cursoId, $turmaId, $serieId);

        $items = [];
        if (!empty($idsToEmit)) {
            foreach ($idsToEmit as $id) {
                if ($id <= 0) {
                    continue;
                }
                $row = $this->loadMatriculaRecipient($id, $ano, $escolaId, $cursoId);
                if (!$row) {
                    continue;
                }
                $items[] = [
                    'matricula_id' => $id,
                    'aluno_nome' => (string) ($row->aluno_nome ?? ''),
                    'destinatario_prefixo' => 'Ao(À) responsável legal pelo(a) estudante',
                ];
            }
        }

        if ($wantsSpecificRecipients && empty($items)) {
            abort(404, 'Nenhuma matrícula válida encontrada para os filtros e seleção informados.');
        }

        if (empty($items)) {
            $items[] = [
                'matricula_id' => null,
                'aluno_nome' => null,
                'destinatario_prefixo' => $this->genericDestinatarioPrefixo($ctx),
            ];
        }

        $issuedAt = now();
        $issuedAtHuman = $issuedAt->format('d/m/Y H:i');
        $issuedAtIso = DocumentSigningService::issuedAtForMac($issuedAt);

        $payload = [
            'mode' => count($items) > 1 ? 'batch' : 'single',
            'slug' => $slug,
            'ano' => $ano,
            'escola_id' => $escolaId,
            'curso_id' => $cursoId,
            'turma_id' => $turmaId,
            'count' => count($items),
            'ref' => $fields['ref_documento'],
        ];

        $signing = app(DocumentSigningService::class);
        $code = $signing->generateCode(8);
        $validationUrl = route('advanced-reports.documents.validate', ['code' => $code]);
        $qrDataUri = app(QrCodeService::class)->pngDataUri($validationUrl, 4);
        $payloadToStore = array_merge($payload, [
            'validation_url' => $validationUrl,
            'year' => (string) $ano,
            'course' => (string) ($ctx['curso_nome'] ?? ''),
            'class' => (string) ($ctx['turma_nome'] ?? ''),
        ]);
        $mac = $signing->mac($code, $docType, $issuedAtIso, $payloadToStore);

        AdvancedReportsDocument::query()->create([
            'code' => $code,
            'type' => $docType,
            'issued_at' => $issuedAt,
            'issued_by_user_id' => auth()->id(),
            'issued_ip' => $request->ip(),
            'issued_user_agent' => substr((string) $request->userAgent(), 0, 255),
            'version' => DocumentSigningService::VERSION,
            'mac' => $mac,
            'payload' => $payloadToStore,
        ]);

        $issuerName = auth()->user()?->name;
        $fileSlug = preg_replace('/[^a-z0-9]+/', '-', strtolower($slug));

        $pdfData = [
            'slug' => $slug,
            'definition' => $def,
            'fields' => $fields,
            'context' => $ctx,
            'items' => $items,
            'issuedAt' => $issuedAtHuman,
            'validationCode' => $code,
            'validationUrl' => $validationUrl,
            'qrDataUri' => $qrDataUri,
            'issuerName' => $issuerName,
            'issuerRole' => null,
            'cityUf' => null,
            'schoolInep' => $schoolInep ? (string) $schoolInep : null,
            'municipality' => $header['municipality'] ?? null,
            'schoolName' => $header['schoolName'] ?? null,
            'contact' => $header['contact'] ?? null,
        ];

        if (count($items) === 1) {
            return app(PdfRenderService::class)->download(
                'advanced-reports::communications.pdf',
                $pdfData,
                'comunicado-' . $fileSlug . '-' . ($items[0]['matricula_id'] ?: 'geral') . '.pdf'
            );
        }

        return app(PdfRenderService::class)->download(
            'advanced-reports::communications.pdf-batch',
            $pdfData,
            'comunicado-' . $fileSlug . '-lote.pdf'
        );
    }

    /**
     * @return array<string, string|null>
     */
    private function extractFieldsFromRequest(Request $request, array $def): array
    {
        $corpo = trim((string) $request->get('corpo', ''));
        if ($corpo === '') {
            $corpo = (string) ($def['default_corpo'] ?? '');
        }

        return [
            'ref_documento' => trim((string) $request->get('ref_documento', '')) ?: null,
            'data_documento' => trim((string) $request->get('data_documento', '')) ?: null,
            'assunto' => trim((string) $request->get('assunto', '')) ?: (string) ($def['default_assunto'] ?? ''),
            'corpo' => $corpo,
            'local_evento' => trim((string) $request->get('local_evento', '')) ?: null,
            'data_evento' => trim((string) $request->get('data_evento', '')) ?: null,
            'hora_evento' => trim((string) $request->get('hora_evento', '')) ?: null,
            'pauta' => trim((string) $request->get('pauta', '')) ?: null,
            'prazo_resposta' => trim((string) $request->get('prazo_resposta', '')) ?: null,
        ];
    }

    private function previewHeader(): array
    {
        return [
            'municipality' => 'Prefeitura Municipal (Exemplo) • Secretaria de Educação',
            'schoolName' => 'Unidade Escolar (Exemplo)',
            'contact' => 'Endereço (Exemplo) • Tel: (00) 0000-0000 • E-mail: exemplo@rede.gov.br',
        ];
    }

    private function instituicaoIdFromEscola(int $escolaId): ?int
    {
        $v = DB::table('pmieducar.escola')->where('cod_escola', $escolaId)->value('ref_cod_instituicao');

        return $v ? (int) $v : null;
    }

    /**
     * @return array{turma_nome: ?string, serie_nome: ?string, curso_nome: ?string, escola_nome: string, ano_letivo: string}
     */
    private function loadContextFromFilters(int $ano, int $escolaId, int $cursoId, ?int $turmaId, ?int $serieId): array
    {
        $escola = DB::table('pmieducar.escola as e')
            ->leftJoin('cadastro.pessoa as ep', 'ep.idpes', '=', 'e.ref_idpes')
            ->leftJoin('cadastro.juridica as ej', 'ej.idpes', '=', 'ep.idpes')
            ->leftJoin('pmieducar.escola_complemento as ec', 'ec.ref_cod_escola', '=', 'e.cod_escola')
            ->where('e.cod_escola', $escolaId)
            ->selectRaw('COALESCE(ej.fantasia, ec.nm_escola, \'\') as escola_nome')
            ->first();

        $curso = DB::table('pmieducar.curso')->where('cod_curso', $cursoId)->value('nm_curso');

        $turmaNome = null;
        $serieNome = null;
        if ($turmaId) {
            $t = DB::table('pmieducar.turma as t')
                ->leftJoin('pmieducar.serie as s', 's.cod_serie', '=', 't.ref_ref_cod_serie')
                ->where('t.cod_turma', $turmaId)
                ->selectRaw('t.nm_turma as turma_nome')
                ->selectRaw('s.nm_serie as serie_nome')
                ->first();
            if ($t) {
                $turmaNome = (string) ($t->turma_nome ?? '');
                $serieNome = (string) ($t->serie_nome ?? '');
            }
        } elseif ($serieId) {
            $serieNome = (string) (DB::table('pmieducar.serie')->where('cod_serie', $serieId)->value('nm_serie') ?? '');
        }

        return [
            'turma_nome' => $turmaNome ?: null,
            'serie_nome' => $serieNome ?: null,
            'curso_nome' => $curso ? (string) $curso : '',
            'escola_nome' => (string) ($escola->escola_nome ?? ''),
            'ano_letivo' => (string) $ano,
        ];
    }

    private function genericDestinatarioPrefixo(array $ctx): string
    {
        if (!empty($ctx['turma_nome'])) {
            return 'Aos(Às) responsáveis legais pelos estudantes da turma ' . $ctx['turma_nome'];
        }

        $curso = trim((string) ($ctx['curso_nome'] ?? ''));
        $serie = trim((string) ($ctx['serie_nome'] ?? ''));
        if ($curso !== '' && $serie !== '') {
            return 'Aos(Às) responsáveis legais pelos estudantes do curso ' . $curso . ', série ' . $serie;
        }
        if ($curso !== '') {
            return 'Aos(Às) responsáveis legais pelos estudantes do curso ' . $curso;
        }

        return 'Aos(Às) responsáveis legais pelos estudantes matriculados nesta unidade escolar';
    }

    private function loadMatriculaRecipient(int $matriculaId, int $ano, int $escolaId, int $cursoId): ?object
    {
        return DB::table('pmieducar.matricula as m')
            ->join('pmieducar.aluno as a', 'a.cod_aluno', '=', 'm.ref_cod_aluno')
            ->join('cadastro.pessoa as p', 'p.idpes', '=', 'a.ref_idpes')
            ->where('m.cod_matricula', $matriculaId)
            ->where('m.ativo', 1)
            ->where('m.ano', $ano)
            ->where('m.ref_ref_cod_escola', $escolaId)
            ->where('m.ref_cod_curso', $cursoId)
            ->selectRaw('p.nome as aluno_nome')
            ->first();
    }
}
