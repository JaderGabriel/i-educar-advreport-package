<?php

namespace iEducar\Packages\AdvancedReports\Http\Controllers;

use App\Http\Controllers\Controller;
use iEducar\Packages\AdvancedReports\Models\AdvancedReportsDocument;
use iEducar\Packages\AdvancedReports\Services\AdvancedReportsFilterService;
use iEducar\Packages\AdvancedReports\Services\DiaryMirrorService;
use iEducar\Packages\AdvancedReports\Services\DocumentSigningService;
use iEducar\Packages\AdvancedReports\Services\OfficialHeaderService;
use iEducar\Packages\AdvancedReports\Services\PdfRenderService;
use iEducar\Packages\AdvancedReports\Services\QrCodeService;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;
use ZipArchive;

class DiaryMirrorController extends Controller
{
    /** Colunas de dias por página (paisagem A4) — evita “vazar” da folha. */
    private const MIRROR_MAX_DAY_COLUMNS = 12;

    /** Linhas de alunos por página. */
    private const MIRROR_MAX_STUDENT_ROWS = 18;

    public function index(Request $request, AdvancedReportsFilterService $filters): View
    {
        $ano = $request->get('ano');
        $instituicaoId = $request->get('ref_cod_instituicao');
        $escolaId = $request->get('ref_cod_escola');
        $cursoId = $request->get('ref_cod_curso');
        $serieId = $request->get('ref_cod_serie');
        $turmaId = $request->get('ref_cod_turma');

        $filtersData = $filters->getFilters(
            $ano ? (int) $ano : null,
            $instituicaoId ? (int) $instituicaoId : null,
            $escolaId ? (int) $escolaId : null,
            $cursoId ? (int) $cursoId : null
        );

        return view('advanced-reports::diary-mirror.index', array_merge($filtersData, [
            'ano' => $ano,
            'instituicaoId' => $instituicaoId,
            'escolaId' => $escolaId,
            'cursoId' => $cursoId,
            'serieId' => $serieId,
            'turmaId' => $turmaId,
        ]));
    }

    public function pdf(Request $request, DiaryMirrorService $service): Response
    {
        $instituicaoId = $request->get('ref_cod_instituicao') ? (int) $request->get('ref_cod_instituicao') : null;
        $escolaId = $request->get('ref_cod_escola') ? (int) $request->get('ref_cod_escola') : null;
        $cursoId = $request->get('ref_cod_curso') ? (int) $request->get('ref_cod_curso') : null;
        $serieId = $request->get('ref_cod_serie') ? (int) $request->get('ref_cod_serie') : null;

        $pdfRenderer = app(PdfRenderService::class);

        if ($request->boolean('preview')) {
            $ano = (int) ($request->get('ano') ?: date('Y'));
            $turmaId = $request->get('ref_cod_turma') ? (int) $request->get('ref_cod_turma') : 0;
            $startDate = (string) ($request->get('data_inicial') ?: date('Y-m-01'));
            $endDate = (string) ($request->get('data_final') ?: date('Y-m-28'));

            $class = (object) [
                'turma_id' => $turmaId,
                'turma' => 'Turma Exemplo (prévia)',
                'ano_letivo' => $ano ?: (int) date('Y'),
                'escola' => 'Escola Municipal Exemplo',
                'instituicao' => 'Instituição Exemplo',
                'curso' => 'Ensino Fundamental',
                'serie' => '5º ano',
                'turno' => 'Matutino',
            ];

            $students = collect([
                ['student' => 'Aluno(a) Exemplo A', 'registration_id' => 100001],
                ['student' => 'Aluno(a) Exemplo B', 'registration_id' => 100002],
            ]);

            $days = $service->buildDays($startDate, $endDate);
            $pages = $service->paginateMirrorPages($days, $students, self::MIRROR_MAX_DAY_COLUMNS, self::MIRROR_MAX_STUDENT_ROWS);

            $discipline = [
                'servidor_id' => null,
                'docente_nome' => 'Professor(a) Exemplo',
                'componente_curricular_id' => null,
                'componente_nome' => 'Língua Portuguesa',
                'componente_abrev' => null,
            ];

            $issuedAtHuman = now()->format('d/m/Y H:i');

            return $pdfRenderer->download('advanced-reports::diary-mirror.pdf', [
                'data' => ['class' => $class],
                'pages' => $pages,
                'discipline' => $discipline,
                'filters' => [
                    'data_inicial' => $startDate,
                    'data_final' => $endDate,
                ],
                'year' => (string) $ano,
                'issuedAt' => $issuedAtHuman,
                'validationCode' => null,
                'validationUrl' => null,
                'qrDataUri' => null,
                'issuerName' => null,
                'issuerRole' => null,
                'cityUf' => null,
            ], 'espelho-diario-previa.pdf', 'a4', 'landscape', 'inline');
        }

        $ano = (int) $request->get('ano');
        $turmaId = (int) $request->get('ref_cod_turma');
        $startDate = (string) $request->get('data_inicial', '');
        $endDate = (string) $request->get('data_final', '');

        if (!$ano || !$turmaId || $startDate === '' || $endDate === '') {
            abort(422, 'Informe ano, turma, data inicial e data final.');
        }

        $built = $service->build($turmaId, $startDate, $endDate);
        /** @var object $class */
        $class = $built['class'];
        /** @var Collection<int, array{student: string, registration_id: int}> $students */
        $students = $built['students'];

        if (!$instituicaoId && $escolaId) {
            $instituicaoId = (int) (DB::table('pmieducar.escola')->where('cod_escola', $escolaId)->value('ref_cod_instituicao') ?: 0) ?: null;
        }
        if (!$escolaId && !empty($class->escola_id)) {
            $escolaId = (int) $class->escola_id;
        }
        $header = $escolaId
            ? app(OfficialHeaderService::class)->forSchool($instituicaoId, $escolaId)
            : ['municipality' => null, 'schoolName' => null, 'contact' => null];

        $schoolInep = null;
        if ($escolaId) {
            $schoolInep = DB::table('modules.educacenso_cod_escola')
                ->where('cod_escola', (int) $escolaId)
                ->value('cod_escola_inep');
        }

        $issuerName = auth()->user()?->name;
        $issuerRole = null;
        $cityUf = null;

        $issuedAt = now();
        $issuedAtHuman = $issuedAt->format('d/m/Y H:i');
        $issuedAtIso = DocumentSigningService::issuedAtForMac($issuedAt);

        $escolaForCalendar = (int) ($class->escola_id ?? 0);
        $anoClass = (int) ($class->ano_letivo ?? 0);
        $days = $service->buildDays(
            $startDate,
            $endDate,
            80,
            true,
            $turmaId,
            $escolaForCalendar > 0 ? $escolaForCalendar : null,
            $anoClass > 0 ? $anoClass : null,
        );
        $pages = $service->paginateMirrorPages($days, $students, self::MIRROR_MAX_DAY_COLUMNS, self::MIRROR_MAX_STUDENT_ROWS);

        $instForProf = (int) ($class->instituicao_id ?? 0);
        $assignments = $instForProf > 0
            ? $service->listTeacherDisciplinesForClass($turmaId, $ano, $instForProf)
            : [];

        if ($assignments === []) {
            $assignments[] = [
                'servidor_id' => null,
                'docente_nome' => '',
                'componente_curricular_id' => null,
                'componente_nome' => 'Registro da turma (nenhum docente/componente encontrado no cadastro)',
                'componente_abrev' => null,
            ];
        }

        $sharedPayload = [
            'report' => 'diary_mirror',
            'ano' => $ano,
            'year' => (string) $ano,
            'instituicao_id' => $instituicaoId,
            'escola_id' => $escolaId,
            'curso_id' => $cursoId,
            'serie_id' => $serieId,
            'turma_id' => $turmaId,
            'class' => (string) ($class->turma ?? ''),
            'data_inicial' => $startDate,
            'data_final' => $endDate,
            'issuer_name' => $issuerName,
        ];

        $signing = app(DocumentSigningService::class);
        $qr = app(QrCodeService::class);

        $basePdfData = [
            'data' => ['class' => $class],
            'filters' => [
                'data_inicial' => $startDate,
                'data_final' => $endDate,
            ],
            'year' => (string) $ano,
            'issuedAt' => $issuedAtHuman,
            'issuerName' => $issuerName,
            'issuerRole' => $issuerRole,
            'cityUf' => $cityUf,
            'schoolInep' => $schoolInep ? (string) $schoolInep : null,
            'municipality' => $header['municipality'] ?? null,
            'schoolName' => $header['schoolName'] ?? null,
            'contact' => $header['contact'] ?? null,
        ];

        if (count($assignments) === 1) {
            $discipline = $assignments[0];
            $code = $signing->generateCode(8);
            $validationUrl = route('advanced-reports.documents.validate', ['code' => $code]);
            $qrDataUri = $qr->pngDataUri($validationUrl, 4);
            $payloadToStore = array_merge($sharedPayload, [
                'validation_url' => $validationUrl,
                'mode' => 'single',
                'batch_id' => null,
                'batch_index' => null,
                'batch_total' => null,
                'componente' => (string) ($discipline['componente_nome'] ?? ''),
                'docente' => (string) ($discipline['docente_nome'] ?? ''),
                'servidor_id' => $discipline['servidor_id'] ?? null,
                'componente_curricular_id' => $discipline['componente_curricular_id'] ?? null,
            ]);
            $mac = $signing->mac($code, 'diary_mirror', $issuedAtIso, $payloadToStore);

            AdvancedReportsDocument::query()->create([
                'code' => $code,
                'type' => 'diary_mirror',
                'issued_at' => $issuedAt,
                'issued_by_user_id' => auth()->id(),
                'issued_ip' => $request->ip(),
                'issued_user_agent' => substr((string) $request->userAgent(), 0, 255),
                'version' => DocumentSigningService::VERSION,
                'mac' => $mac,
                'payload' => $payloadToStore,
            ]);

            return $pdfRenderer->download(
                'advanced-reports::diary-mirror.pdf',
                array_merge($basePdfData, [
                    'pages' => $pages,
                    'discipline' => $discipline,
                    'validationCode' => $code,
                    'validationUrl' => $validationUrl,
                    'qrDataUri' => $qrDataUri,
                ]),
                'espelho-diario-turma-' . $turmaId . '.pdf',
                'a4',
                'landscape',
                'inline'
            );
        }

        $tmpZip = tempnam(sys_get_temp_dir(), 'dmz');
        if ($tmpZip === false) {
            abort(500, 'Não foi possível criar arquivo temporário para o ZIP.');
        }

        $zip = new ZipArchive();
        if ($zip->open($tmpZip, ZipArchive::OVERWRITE) !== true) {
            @unlink($tmpZip);
            abort(500, 'Não foi possível montar o arquivo ZIP.');
        }

        $batchId = (string) Str::uuid();
        $batchTotal = count($assignments);

        foreach ($assignments as $index => $discipline) {
            $code = $signing->generateCode(8);
            $validationUrl = route('advanced-reports.documents.validate', ['code' => $code]);
            $qrDataUri = $qr->pngDataUri($validationUrl, 4);
            $payloadToStore = array_merge($sharedPayload, [
                'validation_url' => $validationUrl,
                'mode' => 'zip_member',
                'batch_id' => $batchId,
                'batch_index' => $index + 1,
                'batch_total' => $batchTotal,
                'componente' => (string) ($discipline['componente_nome'] ?? ''),
                'docente' => (string) ($discipline['docente_nome'] ?? ''),
                'servidor_id' => $discipline['servidor_id'] ?? null,
                'componente_curricular_id' => $discipline['componente_curricular_id'] ?? null,
            ]);
            $mac = $signing->mac($code, 'diary_mirror', $issuedAtIso, $payloadToStore);

            AdvancedReportsDocument::query()->create([
                'code' => $code,
                'type' => 'diary_mirror',
                'issued_at' => $issuedAt,
                'issued_by_user_id' => auth()->id(),
                'issued_ip' => $request->ip(),
                'issued_user_agent' => substr((string) $request->userAgent(), 0, 255),
                'version' => DocumentSigningService::VERSION,
                'mac' => $mac,
                'payload' => $payloadToStore,
            ]);

            $pdfData = array_merge($basePdfData, [
                'pages' => $pages,
                'discipline' => $discipline,
                'validationCode' => $code,
                'validationUrl' => $validationUrl,
                'qrDataUri' => $qrDataUri,
            ]);
            $binary = $pdfRenderer->renderToString('advanced-reports::diary-mirror.pdf', $pdfData, 'a4', 'landscape');

            $cSlug = Str::slug(mb_substr((string) ($discipline['componente_nome'] ?? ''), 0, 48)) ?: 'componente';
            $dSlug = Str::slug(mb_substr((string) ($discipline['docente_nome'] ?? ''), 0, 48)) ?: 'docente';
            $entryName = sprintf('%02d-espelho-turma-%d-%s-%s.pdf', $index + 1, $turmaId, $cSlug, $dSlug);
            $zip->addFromString($entryName, $binary);
        }

        $zip->close();
        $zipBytes = file_get_contents($tmpZip);
        @unlink($tmpZip);

        if ($zipBytes === false) {
            abort(500, 'Falha ao ler o arquivo ZIP gerado.');
        }

        $zipFilename = 'espelho-diario-turma-' . $turmaId . '-ano-' . $ano . '.zip';

        return response($zipBytes, 200, [
            'Content-Type' => 'application/zip',
            'Content-Disposition' => 'inline; filename="' . $zipFilename . '"',
        ]);
    }
}
