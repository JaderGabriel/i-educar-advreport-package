<?php

namespace iEducar\Packages\AdvancedReports\Http\Controllers;

use App\Http\Controllers\Controller;
use iEducar\Packages\AdvancedReports\Models\AdvancedReportsDocument;
use iEducar\Packages\AdvancedReports\Services\DocumentSigningService;
use iEducar\Packages\AdvancedReports\Services\MinutesService;
use iEducar\Packages\AdvancedReports\Services\OfficialHeaderService;
use iEducar\Packages\AdvancedReports\Services\PdfRenderService;
use iEducar\Packages\AdvancedReports\Services\QrCodeService;
use iEducar\Packages\AdvancedReports\Services\AdvancedReportsFilterService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\Response;

class MinutesController extends Controller
{
    public function index(Request $request, AdvancedReportsFilterService $filters): View
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

        return view('advanced-reports::minutes.index', [
            'document' => $request->get('document', 'final_results'),
            'etapas' => $request->get('etapas'),
            'ano' => $ano,
            'instituicaoId' => $instituicaoId,
            'escolaId' => $escolaId,
            'cursoId' => $cursoId,
            ...$filterData,
        ]);
    }

    public function pdf(Request $request, MinutesService $service): Response
    {
        $document = (string) $request->get('document', 'final_results'); // final_results|signatures|delivery_results|council_class
        if ($document === 'council_class') {
            return $this->pdfCouncilClass($request, $service);
        }

        $schoolClassId = (int) $request->get('ref_cod_turma');
        $withDetails = $request->boolean('with_details');
        $issuerName = auth()->user()?->name;
        $issuerRole = null;
        $cityUf = null;

        if (!$schoolClassId) {
            abort(422, 'Informe a turma.');
        }

        if ($request->boolean('preview')) {
            $anoLetivo = (int) ($request->get('ano') ?: date('Y'));
            $class = (object) [
                'turma_id' => $schoolClassId,
                'turma' => 'Turma Exemplo (prévia)',
                'ano_letivo' => $anoLetivo,
                'escola' => 'Escola Municipal Exemplo',
                'instituicao' => 'Instituição Exemplo',
                'curso' => 'Ensino Fundamental',
                'serie' => '5º ano',
                'turno' => 'Matutino',
                'instituicao_id' => null,
                'escola_id' => null,
            ];
            $students = collect([
                [
                    'student' => 'Aluno(a) Exemplo A',
                    'registration_id' => 100001,
                    'aluno_id' => 0,
                    'status' => 'Aprovado',
                    'frequency' => 95.0,
                    'details' => null,
                ],
                [
                    'student' => 'Aluno(a) Exemplo B',
                    'registration_id' => 100002,
                    'aluno_id' => 0,
                    'status' => 'Cursando',
                    'frequency' => 88.0,
                    'details' => null,
                ],
            ]);
            $data = [
                'class' => $class,
                'students' => $students,
            ];

            if ($document === 'delivery_results') {
                $etapas = $service->parseEtapasFilter((string) $request->get('etapas', '1,2'));
                if ($etapas === []) {
                    $etapas = [1, 2];
                }
                $etapaLabels = [];
                foreach ($etapas as $e) {
                    $etapaLabels[$e] = $e . 'º período avaliativo';
                }
                $colA = [];
                foreach ($etapas as $e) {
                    $colA[] = ['key' => (string) $e, 'label' => $e . 'º perí.'];
                }
                $data['etapas'] = $etapas;
                $data['etapa_labels'] = $etapaLabels;
                $data['students'] = $students->map(function (array $row, int $idx) use ($colA, $etapas, $service) {
                    $row['details'] = [
                        'etapas_count' => count($etapas),
                        'etapa_columns' => $colA,
                        'include_rc' => false,
                        'rows' => [
                            [
                                'id' => 1,
                                'nome' => $idx === 0 ? 'Língua Portuguesa' : 'Matemática',
                                'etapas' => array_fill_keys(array_map('strval', $etapas), $idx === 0 ? '8,5' : '7,0'),
                            ],
                        ],
                    ];
                    $row['guardians'] = $idx === 0
                        ? [
                            ['nome' => 'Responsável Exemplo Um', 'cpf_masked' => $service->maskCpf('12345678901')],
                            ['nome' => 'Responsável Exemplo Dois', 'cpf_masked' => $service->maskCpf('98765432100')],
                        ]
                        : [
                            ['nome' => 'Responsável Exemplo Três', 'cpf_masked' => null],
                        ];

                    return $row;
                });
            }

            $header = app(OfficialHeaderService::class)->forSchool(null, null);
            $view = match ($document) {
                'signatures' => 'advanced-reports::minutes.signatures',
                'delivery_results' => 'advanced-reports::minutes.delivery-results',
                default => 'advanced-reports::minutes.final-results',
            };
            $title = match ($document) {
                'signatures' => 'Lista de assinaturas (responsáveis)',
                'delivery_results' => 'Ata de entrega de resultados',
                default => 'Ata de resultados finais',
            };
            $year = (string) ($request->get('ano') ?: $anoLetivo);

            return app(PdfRenderService::class)->download($view, [
                'title' => $title,
                'data' => $data,
                'withDetails' => false,
                'year' => $year,
                'issuedAt' => now()->format('d/m/Y H:i'),
                'validationCode' => 'EXEMPLO',
                'validationUrl' => '#',
                'qrDataUri' => null,
                'issuerName' => $issuerName,
                'issuerRole' => $issuerRole,
                'cityUf' => $cityUf,
                'schoolInep' => null,
                'municipality' => $header['municipality'] ?? null,
                'schoolName' => $header['schoolName'] ?? null,
                'contact' => $header['contact'] ?? null,
            ], 'ata-previa.pdf', 'a4', 'portrait', 'inline');
        }

        $etapasParsed = $service->parseEtapasFilter((string) $request->get('etapas', ''));
        if ($document === 'delivery_results' && $etapasParsed === []) {
            abort(422, 'Informe ao menos uma etapa/período avaliativo (ex.: 1, 2 ou 1,3,4).');
        }

        try {
            $data = $document === 'delivery_results'
                ? $service->buildDeliveryResults($schoolClassId, $etapasParsed)
                : $service->buildFinalResults($schoolClassId, $withDetails);
        } catch (InvalidArgumentException $e) {
            abort(422, $e->getMessage());
        }
        $class = $data['class'] ?? null;
        $header = app(OfficialHeaderService::class)->forSchool(
            !empty($class?->instituicao_id) ? (int) $class->instituicao_id : null,
            !empty($class?->escola_id) ? (int) $class->escola_id : null,
        );
        $schoolInep = null;
        if (!empty($class?->escola_id)) {
            $schoolInep = \Illuminate\Support\Facades\DB::table('modules.educacenso_cod_escola')
                ->where('cod_escola', (int) $class->escola_id)
                ->value('cod_escola_inep');
        }

        $issuedAt = now();
        $issuedAtHuman = $issuedAt->format('d/m/Y H:i');
        $issuedAtIso = DocumentSigningService::issuedAtForMac($issuedAt);

        $payload = [
            'document' => $document,
            'turma_id' => $schoolClassId,
            'with_details' => $withDetails ? 1 : 0,
            'etapas' => $document === 'delivery_results' ? $etapasParsed : null,
            'issuer_name' => $issuerName,
            'issuer_role' => $issuerRole,
            'city_uf' => $cityUf,
        ];

        $signing = app(DocumentSigningService::class);
        $code = $signing->generateCode(8);
        $validationUrl = route('advanced-reports.documents.validate', ['code' => $code]);
        $qrDataUri = app(QrCodeService::class)->pngDataUri($validationUrl, 4);
        $payloadToStore = array_merge($payload, [
            'validation_url' => $validationUrl,
        ]);
        $mac = $signing->mac($code, 'ata', $issuedAtIso, $payloadToStore);

        AdvancedReportsDocument::query()->create([
            'code' => $code,
            'type' => 'ata',
            'issued_at' => $issuedAt,
            'issued_by_user_id' => auth()->id(),
            'issued_ip' => $request->ip(),
            'issued_user_agent' => substr((string) $request->userAgent(), 0, 255),
            'version' => DocumentSigningService::VERSION,
            'mac' => $mac,
            'payload' => $payloadToStore,
        ]);

        $view = match ($document) {
            'signatures' => 'advanced-reports::minutes.signatures',
            'delivery_results' => 'advanced-reports::minutes.delivery-results',
            default => 'advanced-reports::minutes.final-results',
        };

        $title = match ($document) {
            'signatures' => 'Lista de assinaturas (responsáveis)',
            'delivery_results' => 'Ata de entrega de resultados',
            default => 'Ata de resultados finais',
        };

        $yearStr = (string) (($class->ano_letivo ?? '') ?: '');

        // Atas: manter visualização no navegador (inline), igual aos demais relatórios (abre em nova aba).
        $disposition = 'inline';

        return app(PdfRenderService::class)->download($view, [
            'title' => $title,
            'data' => $data,
            'withDetails' => $withDetails,
            'year' => $yearStr,
            'issuedAt' => $issuedAtHuman,
            'validationCode' => $code,
            'validationUrl' => $validationUrl,
            'qrDataUri' => $qrDataUri,
            'issuerName' => $issuerName,
            'issuerRole' => $issuerRole,
            'cityUf' => $cityUf,
            'schoolInep' => $schoolInep ? (string) $schoolInep : null,
            'municipality' => $header['municipality'] ?? null,
            'schoolName' => $header['schoolName'] ?? null,
            'contact' => $header['contact'] ?? null,
        ], 'ata-' . $document . '-turma-' . $schoolClassId . '.pdf', 'a4', 'portrait', $disposition);
    }

    /**
     * Ata de conselho de classe: um PDF com uma seção por turma (quebra de página), notas nas etapas informadas.
     */
    private function pdfCouncilClass(Request $request, MinutesService $service): Response
    {
        $issuerName = auth()->user()?->name;
        $issuerRole = null;
        $cityUf = null;

        $ano = (int) $request->get('ano');
        $instituicaoId = (int) $request->get('ref_cod_instituicao');
        $escolaId = (int) $request->get('ref_cod_escola');
        $cursoId = (int) $request->get('ref_cod_curso');
        $serieId = (int) $request->get('ref_cod_serie');

        if (!$ano || !$instituicaoId || !$escolaId || !$cursoId || !$serieId) {
            abort(422, 'Preencha ano, instituição, escola, curso e série para a ata de conselho.');
        }

        $turmaIds = $this->parseCouncilTurmaIds($request);
        if ($turmaIds === []) {
            abort(422, 'Selecione ao menos uma turma (lista “Turmas nesta ata” ou turma principal nos filtros).');
        }

        $this->assertCouncilTurmasBelongToFilters($turmaIds, $ano, $escolaId, $cursoId, $serieId);

        $etapasParsed = $service->parseEtapasFilter((string) $request->get('etapas', ''));
        if ($etapasParsed === []) {
            abort(422, 'Informe ao menos uma etapa/período avaliativo (ex.: 1, 2).');
        }

        if ($request->boolean('preview')) {
            $class = (object) [
                'turma_id' => $turmaIds[0],
                'turma' => 'Turma Exemplo A (prévia)',
                'ano_letivo' => $ano,
                'escola' => 'Escola Municipal Exemplo',
                'instituicao' => 'Instituição Exemplo',
                'curso' => 'Ensino Fundamental',
                'serie' => '9º ano',
                'turno' => 'Matutino',
                'instituicao_id' => $instituicaoId,
                'escola_id' => $escolaId,
            ];
            $colA = [];
            foreach ($etapasParsed as $e) {
                $colA[] = ['key' => (string) $e, 'label' => $e . 'º perí.'];
            }
            $fakeStudents = collect([
                [
                    'student' => 'Aluno(a) Exemplo',
                    'registration_id' => 100001,
                    'aluno_id' => 0,
                    'status' => 'Cursando',
                    'frequency' => 90.0,
                    'details' => [
                        'etapas_count' => count($etapasParsed),
                        'etapa_columns' => $colA,
                        'include_rc' => false,
                        'rows' => [
                            ['id' => 1, 'nome' => 'Ciências', 'etapas' => array_fill_keys(array_map('strval', $etapasParsed), '8,0')],
                        ],
                    ],
                ],
            ]);
            $blocks = [
                [
                    'class' => $class,
                    'students' => $fakeStudents,
                    'etapas' => $etapasParsed,
                    'etapa_labels' => array_combine($etapasParsed, array_map(fn ($e) => $e . 'º período avaliativo', $etapasParsed)),
                    'professors' => ['Professor(a) Exemplo Um', 'Professor(a) Exemplo Dois'],
                    'secretary_name' => 'Secretário(a) Escolar (Exemplo)',
                ],
                [
                    'class' => (object) array_merge((array) $class, ['turma_id' => $turmaIds[0] + 1, 'turma' => 'Turma Exemplo B (prévia)']),
                    'students' => $fakeStudents,
                    'etapas' => $etapasParsed,
                    'etapa_labels' => array_combine($etapasParsed, array_map(fn ($e) => $e . 'º período avaliativo', $etapasParsed)),
                    'professors' => ['Professor(a) Exemplo Três'],
                    'secretary_name' => 'Secretário(a) Escolar (Exemplo)',
                ],
            ];
            $header = app(OfficialHeaderService::class)->forSchool($instituicaoId, $escolaId);
            $data = ['blocks' => $blocks];

            return app(PdfRenderService::class)->download('advanced-reports::minutes.council-class', [
                'title' => 'Ata de conselho de classe',
                'data' => $data,
                'year' => (string) $ano,
                'issuedAt' => now()->format('d/m/Y H:i'),
                'validationCode' => 'EXEMPLO',
                'validationUrl' => '#',
                'qrDataUri' => null,
                'issuerName' => $issuerName,
                'issuerRole' => $issuerRole,
                'cityUf' => $cityUf,
                'schoolInep' => null,
                'municipality' => $header['municipality'] ?? null,
                'schoolName' => $header['schoolName'] ?? null,
                'contact' => $header['contact'] ?? null,
            ], 'ata-conselho-previa.pdf', 'a4', 'portrait', 'inline');
        }

        try {
            $data = $service->buildCouncilClassMinutes($turmaIds, $etapasParsed);
        } catch (InvalidArgumentException $e) {
            abort(422, $e->getMessage());
        }

        $firstClass = $data['blocks'][0]['class'] ?? null;
        $header = app(OfficialHeaderService::class)->forSchool(
            !empty($firstClass?->instituicao_id) ? (int) $firstClass->instituicao_id : $instituicaoId,
            !empty($firstClass?->escola_id) ? (int) $firstClass->escola_id : $escolaId,
        );
        $schoolInep = null;
        if (!empty($firstClass?->escola_id)) {
            $schoolInep = DB::table('modules.educacenso_cod_escola')
                ->where('cod_escola', (int) $firstClass->escola_id)
                ->value('cod_escola_inep');
        }

        $issuedAt = now();
        $issuedAtHuman = $issuedAt->format('d/m/Y H:i');
        $issuedAtIso = DocumentSigningService::issuedAtForMac($issuedAt);

        $payload = [
            'document' => 'council_class',
            'turma_ids' => $turmaIds,
            'etapas' => $etapasParsed,
            'ano' => $ano,
            'issuer_name' => $issuerName,
            'issuer_role' => $issuerRole,
            'city_uf' => $cityUf,
        ];

        $signing = app(DocumentSigningService::class);
        $code = $signing->generateCode(8);
        $validationUrl = route('advanced-reports.documents.validate', ['code' => $code]);
        $qrDataUri = app(QrCodeService::class)->pngDataUri($validationUrl, 4);
        $payloadToStore = array_merge($payload, [
            'validation_url' => $validationUrl,
        ]);
        $mac = $signing->mac($code, 'ata', $issuedAtIso, $payloadToStore);

        AdvancedReportsDocument::query()->create([
            'code' => $code,
            'type' => 'ata',
            'issued_at' => $issuedAt,
            'issued_by_user_id' => auth()->id(),
            'issued_ip' => $request->ip(),
            'issued_user_agent' => substr((string) $request->userAgent(), 0, 255),
            'version' => DocumentSigningService::VERSION,
            'mac' => $mac,
            'payload' => $payloadToStore,
        ]);

        return app(PdfRenderService::class)->download('advanced-reports::minutes.council-class', [
            'title' => 'Ata de conselho de classe',
            'data' => $data,
            'year' => (string) $ano,
            'issuedAt' => $issuedAtHuman,
            'validationCode' => $code,
            'validationUrl' => $validationUrl,
            'qrDataUri' => $qrDataUri,
            'issuerName' => $issuerName,
            'issuerRole' => $issuerRole,
            'cityUf' => $cityUf,
            'schoolInep' => $schoolInep ? (string) $schoolInep : null,
            'municipality' => $header['municipality'] ?? null,
            'schoolName' => $header['schoolName'] ?? null,
            'contact' => $header['contact'] ?? null,
        ], 'ata-conselho-classe.pdf', 'a4', 'portrait', 'inline');
    }

    /**
     * @return array<int, int>
     */
    private function parseCouncilTurmaIds(Request $request): array
    {
        $raw = $request->get('turma_ids', []);
        $ids = is_array($raw)
            ? array_values(array_unique(array_filter(array_map('intval', $raw))))
            : [];
        if ($ids === [] && $request->get('ref_cod_turma')) {
            $ids = [(int) $request->get('ref_cod_turma')];
        }

        return $ids;
    }

    /**
     * @param  array<int, int>  $turmaIds
     */
    private function assertCouncilTurmasBelongToFilters(array $turmaIds, int $ano, int $escolaId, int $cursoId, int $serieId): void
    {
        $n = (int) DB::table('pmieducar.turma')
            ->whereIn('cod_turma', $turmaIds)
            ->where('ref_ref_cod_escola', $escolaId)
            ->where('ref_cod_curso', $cursoId)
            ->where('ref_ref_cod_serie', $serieId)
            ->where('ano', $ano)
            ->where('ativo', 1)
            ->count();

        if ($n !== count($turmaIds)) {
            abort(422, 'Uma ou mais turmas não pertencem aos filtros informados (escola, curso, série e ano) ou estão inativas.');
        }
    }
}

