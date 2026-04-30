<?php

namespace iEducar\Packages\AdvancedReports\Http\Controllers;

use App\Http\Controllers\Controller;
use iEducar\Packages\AdvancedReports\Models\AdvancedReportsDocument;
use iEducar\Packages\AdvancedReports\Services\AdvancedReportsFilterService;
use iEducar\Packages\AdvancedReports\Services\DocumentSigningService;
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
            'simade_model_1' => 'SIMADE: Modelo 1 (EF 9 anos — frente/verso)',
            'simade_model_32' => 'SIMADE: Modelo 32 (Res. 2197/2012 — frente/verso)',
            'simade_magisterio' => 'SIMADE: Magistério (Curso Normal — frente/verso)',
            'mg_regular_eja_emti_prop' => 'MG: Regular/EJA/Correção de fluxo/EMTI propedêutico (ASIE 6/2021)',
            'mg_nem_piloto' => 'MG: Novo Ensino Médio piloto (REANP)',
            'mg_emti_prof' => 'MG: EMTI profissional (REANP)',
            'mg_tecnico_semestral' => 'MG: Técnico semestral (Histórico + Diploma — REANP)',
            'mg_normal_infantil' => 'MG: Normal nível médio Educação Infantil (Histórico + Diploma — REANP)',
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
        ], 'historico-escolar-' . $aid . '.pdf', 'a4', 'portrait', $disposition);
    }
}
