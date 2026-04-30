<?php

namespace iEducar\Packages\AdvancedReports\Http\Controllers;

use App\Http\Controllers\Controller;
use iEducar\Packages\AdvancedReports\Models\AdvancedReportsDocument;
use iEducar\Packages\AdvancedReports\Services\AdvancedReportsFilterService;
use iEducar\Packages\AdvancedReports\Services\DocumentSigningService;
use iEducar\Packages\AdvancedReports\Services\IssuerSignatureDetails;
use iEducar\Packages\AdvancedReports\Services\OfficialHeaderService;
use iEducar\Packages\AdvancedReports\Services\PdfRenderService;
use iEducar\Packages\AdvancedReports\Services\QrCodeService;
use iEducar\Packages\AdvancedReports\Services\SchoolHistoryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class SchoolHistoryController extends Controller
{
    /**
     * Critério alinhado à listagem de históricos prontos (LookupController):
     * - ativo = 1
     * - origem NULL, 0 ou 1 (núcleo e legados tratados como nativos na listagem)
     * - vinculado a uma matrícula (ref_cod_matricula preenchido)
     *
     * @return array{book:?string,page:?string,record:?string,year:?int}
     */
    private function nativeHistoryMeta(int $alunoId): array
    {
        $row = DB::table('pmieducar.historico_escolar as he')
            ->where('he.ref_cod_aluno', $alunoId)
            ->where('he.ativo', 1)
            ->whereNotNull('he.ref_cod_matricula')
            ->where(function ($w) {
                $w->whereNull('he.origem')->orWhereIn('he.origem', [0, 1]);
            })
            ->orderByDesc('he.ano')
            ->orderByDesc('he.sequencial')
            ->selectRaw('he.livro as book')
            ->selectRaw('he.folha as page')
            ->selectRaw('he.registro as record')
            ->selectRaw('he.ano as year')
            ->first();

        if (!$row) {
            abort(422, 'Este aluno não possui histórico escolar gerado pela rotina nativa.');
        }

        return [
            'book' => $row->book !== null ? (string) $row->book : null,
            'page' => $row->page !== null ? (string) $row->page : null,
            'record' => $row->record !== null ? (string) $row->record : null,
            'year' => !empty($row->year) ? (int) $row->year : null,
        ];
    }

    /**
     * @return array<string, string>
     */
    private function templates(): array
    {
        return [
            'classic' => 'Clássico (padrão)',
            'modern' => 'Moderno (limpo)',
            'simade_model_1' => 'SIMADE — Modelo 1 (frente/verso)',
            'simade_model_32' => 'SIMADE — Modelo 32 (frente/verso)',
            'simade_magisterio' => 'SIMADE — Magistério / Curso Normal (frente/verso)',
            'mg_regular_eja_emti_prop' => 'Modelo ASIE 6/2021 — Regular/EJA/Correção de fluxo/EMTI propedêutico',
            'mg_nem_piloto' => 'Modelo REANP — Novo Ensino Médio (piloto)',
            'mg_emti_prof' => 'Modelo REANP — EMTI profissional',
            'mg_tecnico_semestral' => 'Modelo REANP — Técnico semestral (Histórico + Diploma)',
            'mg_normal_infantil' => 'Modelo REANP — Normal nível médio (Educação Infantil) (Histórico + Diploma)',
        ];
    }

    /**
     * @return array{
     *   director_idpes: ?int, director_name: string, director_inep: ?string, director_matricula_interna: ?string,
     *   secretary_idpes: ?int, secretary_name: string, secretary_inep: ?string, secretary_matricula_interna: ?string,
     *   school_inep: ?string
     * }
     */
    private function resolveSchoolSigners(int $escolaId): array
    {
        $row = DB::table('pmieducar.escola as e')
            ->leftJoin('modules.educacenso_cod_escola as ine', 'ine.cod_escola', '=', 'e.cod_escola')
            ->leftJoin('cadastro.pessoa as pg', 'pg.idpes', '=', 'e.ref_idpes_gestor')
            ->leftJoin('cadastro.pessoa as ps', 'ps.idpes', '=', 'e.ref_idpes_secretario_escolar')
            ->where('e.cod_escola', $escolaId)
            ->selectRaw('e.ref_idpes_gestor as director_idpes')
            ->selectRaw('pg.nome as director_name')
            ->selectRaw('e.ref_idpes_secretario_escolar as secretary_idpes')
            ->selectRaw('ps.nome as secretary_name')
            ->selectRaw('ine.cod_escola_inep::text as school_inep')
            ->first();

        if (! $row) {
            return [
                'director_idpes' => null,
                'director_name' => '',
                'director_inep' => null,
                'director_matricula_interna' => null,
                'secretary_idpes' => null,
                'secretary_name' => '',
                'secretary_inep' => null,
                'secretary_matricula_interna' => null,
                'school_inep' => null,
            ];
        }

        $svc = app(IssuerSignatureDetails::class);
        $directorIdpes = ! empty($row->director_idpes) ? (int) $row->director_idpes : null;
        $secretaryIdpes = ! empty($row->secretary_idpes) ? (int) $row->secretary_idpes : null;

        $dir = $directorIdpes ? $svc->forPersonId($directorIdpes) : ['issuerPersonInep' => null, 'issuerMatriculaFuncional' => null];
        $sec = $secretaryIdpes ? $svc->forPersonId($secretaryIdpes) : ['issuerPersonInep' => null, 'issuerMatriculaFuncional' => null];

        return [
            'director_idpes' => $directorIdpes,
            'director_name' => (string) ($row->director_name ?? ''),
            'director_inep' => $dir['issuerPersonInep'] ?? null,
            'director_matricula_interna' => $dir['issuerMatriculaFuncional'] ?? null,
            'secretary_idpes' => $secretaryIdpes,
            'secretary_name' => (string) ($row->secretary_name ?? ''),
            'secretary_inep' => $sec['issuerPersonInep'] ?? null,
            'secretary_matricula_interna' => $sec['issuerMatriculaFuncional'] ?? null,
            'school_inep' => $row->school_inep ?? null,
        ];
    }

    public function index(Request $request, AdvancedReportsFilterService $filters)
    {
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

        return view('advanced-reports::school-history.index', [
            'ano' => $ano,
            'instituicaoId' => $instituicaoId,
            'escolaId' => $escolaId,
            'cursoId' => $cursoId,
            'template' => $request->get('template', 'classic'),
            'templates' => $this->templates(),
            ...$filterData,
        ]);
    }

    private function resolveSingleView(string $template): string
    {
        $templates = $this->templates();
        if (!array_key_exists($template, $templates)) {
            $template = 'classic';
        }

        return match ($template) {
            'modern' => 'advanced-reports::school-history.pdf-modern',
            'simade_model_1' => 'advanced-reports::school-history.pdf-simade-model-1',
            'simade_model_32' => 'advanced-reports::school-history.pdf-simade-model-32',
            'simade_magisterio' => 'advanced-reports::school-history.pdf-simade-magisterio',
            default => 'advanced-reports::school-history.pdf',
        };
    }

    public function pdf(Request $request, SchoolHistoryService $service): Response
    {
        $template = (string) $request->get('template', 'classic');
        $templates = $this->templates();
        if (!array_key_exists($template, $templates)) {
            $template = 'classic';
        }

        $instituicaoId = $request->get('ref_cod_instituicao') ? (int) $request->get('ref_cod_instituicao') : null;
        $escolaId = $request->get('ref_cod_escola') ? (int) $request->get('ref_cod_escola') : null;
        $header = ($instituicaoId && $escolaId)
            ? app(OfficialHeaderService::class)->forSchool($instituicaoId, $escolaId)
            : ['municipality' => null, 'schoolName' => null, 'contact' => null];

        $issuerName = auth()->user()?->name;
        $issuerDetails = auth()->id()
            ? app(IssuerSignatureDetails::class)->forPersonId((int) auth()->id())
            : ['issuerPersonInep' => null, 'issuerMatriculaFuncional' => null, 'issuerPersonIdpes' => null];

        $signers = $escolaId ? $this->resolveSchoolSigners($escolaId) : [
            'director_name' => '',
            'director_inep' => null,
            'director_matricula_interna' => null,
            'secretary_name' => '',
            'secretary_inep' => null,
            'secretary_matricula_interna' => null,
            'school_inep' => null,
        ];
        $authorities = [
            'secretary' => [
                'name' => $signers['secretary_name'] ?? '',
                'inep' => $signers['secretary_inep'] ?? null,
                'matricula_interna' => $signers['secretary_matricula_interna'] ?? null,
            ],
            'director' => [
                'name' => $signers['director_name'] ?? '',
                'inep' => $signers['director_inep'] ?? null,
                'matricula_interna' => $signers['director_matricula_interna'] ?? null,
            ],
        ];

        if ($request->boolean('preview')) {
            $view = $this->resolveSingleView($template);
            $student = (object) [
                'aluno_id' => 0,
                'aluno_nome' => 'Aluno Exemplo (prévia)',
            ];
            $history = (object) [
                'nm_serie' => '5º Ano',
                'ano' => (int) ($request->get('ano') ?: date('Y')),
                'escola' => 'Escola Municipal Exemplo',
                'escola_cidade' => 'Município',
                'escola_uf' => 'UF',
                'nm_curso' => 'Ensino Fundamental',
                'frequencia' => 92,
                'carga_horaria' => '',
                'observacao' => 'Documento ilustrativo — dados fictícios.',
            ];
            $discipline = (object) [
                'nm_disciplina' => 'Língua Portuguesa',
                'nota' => '8,0',
                'faltas' => 2,
                'carga_horaria_disciplina' => '200h',
                'dependencia' => 0,
            ];
            $data = [
                'student' => $student,
                'person' => [],
                'items' => [
                    [
                        'history' => $history,
                        'disciplines' => collect([$discipline]),
                    ],
                ],
            ];

            return app(PdfRenderService::class)->download(
                $view,
                [
                    'data' => $data,
                    'issuedAt' => now()->format('d/m/Y H:i'),
                    'validationCode' => 'EXEMPLO',
                    'validationUrl' => '#',
                    'qrDataUri' => null,
                    'book' => null,
                    'page' => null,
                    'record' => null,
                    'template' => $template,
                    'templateLabel' => 'Prévia (exemplo) — ' . ($templates[$template] ?? $template),
                    'issuerName' => $issuerName,
                    'issuerRole' => null,
                    'issuerPersonInep' => $issuerDetails['issuerPersonInep'] ?? null,
                    'issuerMatriculaFuncional' => $issuerDetails['issuerMatriculaFuncional'] ?? null,
                    'schoolInep' => $signers['school_inep'] ?? null,
                    'authorities' => $authorities,
                    'municipality' => $header['municipality'] ?? null,
                    'schoolName' => $header['schoolName'] ?? null,
                    'contact' => $header['contact'] ?? null,
                ],
                'historico-previa.pdf',
                'a4',
                'portrait',
                'inline'
            );
        }

        $issuedAt = now();
        $issuedAtHuman = $issuedAt->format('d/m/Y H:i');
        $issuedAtIso = DocumentSigningService::issuedAtForMac($issuedAt);
        $disposition = 'attachment';

        $signing = app(DocumentSigningService::class);
        $qrService = app(QrCodeService::class);

        $alunoIds = array_values(array_unique(array_filter(array_map(
            static fn ($v) => (int) $v,
            (array) $request->get('aluno_ids', [])
        ), static fn ($v) => $v > 0)));

        $alunoId = (int) $request->get('aluno_id');
        if ($alunoId) {
            $alunoIds = [$alunoId];
        }

        if (empty($alunoIds)) {
            abort(422, 'Selecione ao menos um aluno para emitir o histórico escolar.');
        }

        $isBatch = count($alunoIds) > 1;
        if ($isBatch && !in_array($template, ['classic', 'modern'], true)) {
            abort(422, 'Para emissão em lote, selecione um modelo compatível (Clássico ou Moderno).');
        }

        if ($isBatch) {
            $items = [];

            foreach ($alunoIds as $aid) {
                $meta = $this->nativeHistoryMeta($aid);
                $book = $meta['book'] ?? null;
                $page = $meta['page'] ?? null;
                $record = $meta['record'] ?? null;

                $data = $service->build($aid);

                $payload = [
                    'aluno_id' => $aid,
                    'book' => $book,
                    'page' => $page,
                    'record' => $record,
                    'template' => $template,
                    'source' => 'native_history',
                ];

                $code = $signing->generateCode(8);
                $validationUrl = route('advanced-reports.documents.validate', ['code' => $code]);
                $qrDataUri = $qrService->pngDataUri($validationUrl, 4);
                $payloadToStore = array_merge($payload, [
                    'validation_url' => $validationUrl,
                ]);
                $mac = $signing->mac($code, 'historico', $issuedAtIso, $payloadToStore);

                AdvancedReportsDocument::query()->create([
                    'code' => $code,
                    'type' => 'historico',
                    'issued_at' => $issuedAt,
                    'issued_by_user_id' => auth()->id(),
                    'issued_ip' => $request->ip(),
                    'issued_user_agent' => substr((string) $request->userAgent(), 0, 255),
                    'version' => DocumentSigningService::VERSION,
                    'mac' => $mac,
                    'payload' => $payloadToStore,
                ]);

                $items[] = [
                    'data' => $data,
                    'validationCode' => $code,
                    'validationUrl' => $validationUrl,
                    'qrDataUri' => $qrDataUri,
                    'book' => $book,
                    'page' => $page,
                    'record' => $record,
                ];
            }

            return app(PdfRenderService::class)->download('advanced-reports::school-history.pdf-batch', [
                'items' => $items,
                'issuedAt' => $issuedAtHuman,
                'template' => $template,
                'templateLabel' => $templates[$template] ?? null,
                'issuerName' => $issuerName,
                'issuerRole' => null,
                'issuerPersonInep' => $issuerDetails['issuerPersonInep'] ?? null,
                'issuerMatriculaFuncional' => $issuerDetails['issuerMatriculaFuncional'] ?? null,
                'schoolInep' => $signers['school_inep'] ?? null,
                'authorities' => $authorities,
                'municipality' => $header['municipality'] ?? null,
                'schoolName' => $header['schoolName'] ?? null,
                'contact' => $header['contact'] ?? null,
            ], 'historico-escolar-lote.pdf', 'a4', 'portrait', $disposition);
        }

        $aid = (int) $alunoIds[0];
        $meta = $this->nativeHistoryMeta($aid);
        $book = $meta['book'] ?? null;
        $page = $meta['page'] ?? null;
        $record = $meta['record'] ?? null;

        $data = $service->build($aid);

        $payload = [
            'aluno_id' => $aid,
            'book' => $book,
            'page' => $page,
            'record' => $record,
            'template' => $template,
            'source' => 'native_history',
        ];

        $code = $signing->generateCode(8);
        $validationUrl = route('advanced-reports.documents.validate', ['code' => $code]);
        $qrDataUri = $qrService->pngDataUri($validationUrl, 4);
        $payloadToStore = array_merge($payload, [
            'validation_url' => $validationUrl,
        ]);
        $mac = $signing->mac($code, 'historico', $issuedAtIso, $payloadToStore);

        AdvancedReportsDocument::query()->create([
            'code' => $code,
            'type' => 'historico',
            'issued_at' => $issuedAt,
            'issued_by_user_id' => auth()->id(),
            'issued_ip' => $request->ip(),
            'issued_user_agent' => substr((string) $request->userAgent(), 0, 255),
            'version' => DocumentSigningService::VERSION,
            'mac' => $mac,
            'payload' => $payloadToStore,
        ]);

        $view = $this->resolveSingleView($template);

        return app(PdfRenderService::class)->download($view, [
            'data' => $data,
            'issuedAt' => $issuedAtHuman,
            'validationCode' => $code,
            'validationUrl' => $validationUrl,
            'qrDataUri' => $qrDataUri,
            'book' => $book,
            'page' => $page,
            'record' => $record,
            'template' => $template,
            'templateLabel' => $templates[$template] ?? null,
            'issuerName' => $issuerName,
            'issuerRole' => null,
            'issuerPersonInep' => $issuerDetails['issuerPersonInep'] ?? null,
            'issuerMatriculaFuncional' => $issuerDetails['issuerMatriculaFuncional'] ?? null,
            'schoolInep' => $signers['school_inep'] ?? null,
            'authorities' => $authorities,
            'municipality' => $header['municipality'] ?? null,
            'schoolName' => $header['schoolName'] ?? null,
            'contact' => $header['contact'] ?? null,
        ], 'historico-escolar-' . $aid . '.pdf', 'a4', 'portrait', $disposition);
    }
}
