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
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class DiaryMirrorController extends Controller
{
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
        $ano = (int) $request->get('ano');
        $instituicaoId = $request->get('ref_cod_instituicao') ? (int) $request->get('ref_cod_instituicao') : null;
        $escolaId = $request->get('ref_cod_escola') ? (int) $request->get('ref_cod_escola') : null;
        $cursoId = $request->get('ref_cod_curso') ? (int) $request->get('ref_cod_curso') : null;
        $serieId = $request->get('ref_cod_serie') ? (int) $request->get('ref_cod_serie') : null;
        $turmaId = (int) $request->get('ref_cod_turma');

        $startDate = (string) $request->get('data_inicial', '');
        $endDate = (string) $request->get('data_final', '');

        if (!$ano || !$turmaId || $startDate === '' || $endDate === '') {
            abort(422, 'Informe ano, turma, data inicial e data final.');
        }

        // Preview: usa turma real (id), mas dados mínimos são suficientes.
        if ($request->boolean('preview')) {
            $data = [
                'class' => (object) [
                    'turma_id' => $turmaId,
                    'turma' => 'Turma Exemplo (prévia)',
                    'ano_letivo' => $ano ?: (int) date('Y'),
                    'escola' => 'Escola Municipal Exemplo',
                    'instituicao' => 'Instituição Exemplo',
                    'curso' => 'Ensino Fundamental',
                    'serie' => '5º ano',
                    'turno' => 'Matutino',
                ],
                'days' => $service->buildDays($startDate ?: date('Y-m-01'), $endDate ?: date('Y-m-15')),
                'students' => collect([
                    ['student' => 'Aluno(a) Exemplo A', 'registration_id' => 100001],
                    ['student' => 'Aluno(a) Exemplo B', 'registration_id' => 100002],
                ]),
            ];

            return app(PdfRenderService::class)->download('advanced-reports::diary-mirror.pdf', [
                'data' => $data,
                'filters' => [
                    'data_inicial' => $startDate,
                    'data_final' => $endDate,
                ],
            ], 'espelho-diario-previa.pdf', 'a4', 'landscape', 'inline');
        }

        $data = $service->build($turmaId, $startDate, $endDate);
        $class = $data['class'] ?? null;

        // Header oficial
        if (!$instituicaoId && $escolaId) {
            $instituicaoId = (int) (DB::table('pmieducar.escola')->where('cod_escola', $escolaId)->value('ref_cod_instituicao') ?: 0) ?: null;
        }
        if (!$escolaId && !empty($class?->escola_id)) {
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

        // Validação/registro
        $issuerName = auth()->user()?->name;
        $issuerRole = null;
        $cityUf = null;

        $issuedAt = now();
        $issuedAtHuman = $issuedAt->format('d/m/Y H:i');
        $issuedAtIso = DocumentSigningService::issuedAtForMac($issuedAt);

        $payload = [
            'report' => 'diary_mirror',
            'ano' => $ano,
            'year' => (string) $ano,
            'instituicao_id' => $instituicaoId,
            'escola_id' => $escolaId,
            'curso_id' => $cursoId,
            'serie_id' => $serieId,
            'turma_id' => $turmaId,
            'data_inicial' => $startDate,
            'data_final' => $endDate,
            'issuer_name' => $issuerName,
        ];

        $signing = app(DocumentSigningService::class);
        $code = $signing->generateCode(8);
        $validationUrl = route('advanced-reports.documents.validate', ['code' => $code]);
        $qrDataUri = app(QrCodeService::class)->pngDataUri($validationUrl, 4);
        $payloadToStore = array_merge($payload, [
            'validation_url' => $validationUrl,
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

        return app(PdfRenderService::class)->download('advanced-reports::diary-mirror.pdf', [
            'data' => $data,
            'filters' => [
                'data_inicial' => $startDate,
                'data_final' => $endDate,
            ],
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
        ], 'espelho-diario-turma-' . $turmaId . '.pdf', 'a4', 'landscape', 'inline');
    }
}

