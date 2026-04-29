<?php

namespace iEducar\Packages\AdvancedReports\Http\Controllers;

use App\Http\Controllers\Controller;
use iEducar\Packages\AdvancedReports\Exports\VacanciesBySchoolClassExport;
use iEducar\Packages\AdvancedReports\Models\AdvancedReportsDocument;
use iEducar\Packages\AdvancedReports\Services\AdvancedReportsFilterService;
use iEducar\Packages\AdvancedReports\Services\DocumentSigningService;
use iEducar\Packages\AdvancedReports\Services\OfficialHeaderService;
use iEducar\Packages\AdvancedReports\Services\PdfRenderService;
use iEducar\Packages\AdvancedReports\Services\QrCodeService;
use iEducar\Packages\AdvancedReports\Services\VacanciesBySchoolClassService;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\Response;

class VacanciesBySchoolClassController extends Controller
{
    private function resolveFilterLabels(?int $instituicaoId, ?int $escolaId, ?int $cursoId, ?int $serieId, ?int $turmaId): array
    {
        $instituicao = $instituicaoId
            ? DB::table('pmieducar.instituicao')->where('cod_instituicao', $instituicaoId)->value('nm_instituicao')
            : null;

        $escola = $escolaId
            ? DB::table('pmieducar.escola as e')
                ->leftJoin('cadastro.pessoa as p', 'p.idpes', '=', 'e.ref_idpes')
                ->leftJoin('cadastro.juridica as j', 'j.idpes', '=', 'p.idpes')
                ->leftJoin('pmieducar.escola_complemento as ec', 'ec.ref_cod_escola', '=', 'e.cod_escola')
                ->where('e.cod_escola', $escolaId)
                ->value(DB::raw('COALESCE(j.fantasia, ec.nm_escola)'))
            : null;

        $curso = $cursoId
            ? DB::table('pmieducar.curso')->where('cod_curso', $cursoId)->value('nm_curso')
            : null;

        $serie = $serieId
            ? DB::table('pmieducar.serie')->where('cod_serie', $serieId)->value('nm_serie')
            : null;

        $turma = $turmaId
            ? DB::table('pmieducar.turma')->where('cod_turma', $turmaId)->value('nm_turma')
            : null;

        return [
            'instituicao' => $instituicao ? (string) $instituicao : null,
            'escola' => $escola ? (string) $escola : null,
            'curso' => $curso ? (string) $curso : null,
            'serie' => $serie ? (string) $serie : null,
            'turma' => $turma ? (string) $turma : null,
        ];
    }

    public function index(Request $request, AdvancedReportsFilterService $filters): View
    {
        $ano = $request->get('ano');
        $instituicaoId = $request->get('ref_cod_instituicao');
        $escolaId = $request->get('ref_cod_escola');
        $cursoId = $request->get('ref_cod_curso');
        $serieId = $request->get('ref_cod_serie');
        $turmaId = $request->get('ref_cod_turma');

        $filterData = $filters->getFilters(
            $ano ? (int) $ano : null,
            $instituicaoId ? (int) $instituicaoId : null,
            $escolaId ? (int) $escolaId : null,
            $cursoId ? (int) $cursoId : null
        );

        return view('advanced-reports::vacancies/index', array_merge($filterData, [
            'ano' => $ano,
            'instituicaoId' => $instituicaoId,
            'escolaId' => $escolaId,
            'cursoId' => $cursoId,
            'serieId' => $serieId,
            'turmaId' => $turmaId,
        ]));
    }

    public function pdf(Request $request, VacanciesBySchoolClassService $service): Response
    {
        $ano = (int) $request->get('ano');
        $instituicaoId = $request->get('ref_cod_instituicao') ? (int) $request->get('ref_cod_instituicao') : null;
        $escolaId = $request->get('ref_cod_escola') ? (int) $request->get('ref_cod_escola') : null;
        $cursoId = $request->get('ref_cod_curso') ? (int) $request->get('ref_cod_curso') : null;
        $serieId = $request->get('ref_cod_serie') ? (int) $request->get('ref_cod_serie') : null;
        $turmaId = $request->get('ref_cod_turma') ? (int) $request->get('ref_cod_turma') : null;

        if (!$ano || !$escolaId) {
            abort(422, 'Informe ano e escola.');
        }

        if ($request->boolean('preview')) {
            $filterLabels = [
                'instituicao' => 'Instituição (exemplo)',
                'escola' => 'Escola Municipal Exemplo',
                'curso' => 'Ensino Fundamental',
                'serie' => '5º ano',
                'turma' => '-',
            ];
            $items = collect([(object) [
                'escola' => $filterLabels['escola'],
                'turma' => 'Turma A',
                'turno' => 'Matutino',
                'curso' => $filterLabels['curso'],
                'serie' => $filterLabels['serie'],
                'capacidade' => 35,
                'matriculados' => 30,
                'vagas' => 5,
            ]]);
            $data = [
                'items' => $items,
                'summary' => [
                    'turmas' => 1,
                    'capacidade' => 35,
                    'matriculados' => 30,
                    'vagas' => 5,
                ],
            ];

            return app(PdfRenderService::class)->download('advanced-reports::vacancies/pdf', [
                'title' => 'Vagas por turma',
                'subtitle' => 'Capacidade, ocupação e vagas disponíveis',
                'year' => (string) $ano,
                'filters' => [
                    'instituicao' => $instituicaoId,
                    'escola' => $escolaId,
                    'curso' => $cursoId,
                    'serie' => $serieId,
                    'turma' => $turmaId,
                ],
                'filterLabels' => $filterLabels,
                'data' => $data,
                'municipality' => null,
                'schoolName' => null,
                'contact' => null,
                'issuedAt' => now()->format('d/m/Y H:i'),
                'validationCode' => 'EXEMPLO',
                'validationUrl' => '#',
                'qrDataUri' => null,
                'issuerName' => null,
                'issuerRole' => null,
                'cityUf' => null,
            ], 'vagas-previa.pdf', 'a4', 'landscape', 'inline');
        }

        $data = $service->build($ano, $instituicaoId, $escolaId, $cursoId, $serieId, $turmaId);
        $filterLabels = $this->resolveFilterLabels($instituicaoId, $escolaId, $cursoId, $serieId, $turmaId);

        if (!$instituicaoId) {
            $instituicaoId = (int) (DB::table('pmieducar.escola')->where('cod_escola', $escolaId)->value('ref_cod_instituicao') ?: 0) ?: null;
        }

        $header = app(OfficialHeaderService::class)->forSchool($instituicaoId, $escolaId);

        $issuedAt = now();
        $issuedAtHuman = $issuedAt->format('d/m/Y H:i');
        $issuedAtIso = $issuedAt->toISOString();

        $payload = [
            'report' => 'vacancies_by_school_class',
            'ano' => $ano,
            'year' => (string) $ano,
            'instituicao_id' => $instituicaoId,
            'escola_id' => $escolaId,
            'curso_id' => $cursoId,
            'serie_id' => $serieId,
            'turma_id' => $turmaId,
        ];

        $signing = app(DocumentSigningService::class);
        $code = $signing->generateCode(8);
        $validationUrl = route('advanced-reports.documents.validate', ['code' => $code]);
        $qrDataUri = app(QrCodeService::class)->pngDataUri($validationUrl, 4);
        $payloadToStore = array_merge($payload, [
            'validation_url' => $validationUrl,
            'issuer_name' => auth()->user()?->name,
        ]);
        $mac = $signing->mac($code, 'vacancies_by_school_class', $issuedAtIso, $payloadToStore);

        AdvancedReportsDocument::query()->create([
            'code' => $code,
            'type' => 'vacancies_by_school_class',
            'issued_at' => $issuedAt,
            'issued_by_user_id' => auth()->id(),
            'issued_ip' => $request->ip(),
            'issued_user_agent' => substr((string) $request->userAgent(), 0, 255),
            'version' => DocumentSigningService::VERSION,
            'mac' => $mac,
            'payload' => $payloadToStore,
        ]);

        return app(PdfRenderService::class)->download('advanced-reports::vacancies/pdf', [
            'title' => 'Vagas por turma',
            'subtitle' => 'Capacidade, ocupação e vagas disponíveis',
            'year' => (string) $ano,
            'filters' => [
                'instituicao' => $instituicaoId,
                'escola' => $escolaId,
                'curso' => $cursoId,
                'serie' => $serieId,
                'turma' => $turmaId,
            ],
            'filterLabels' => $filterLabels,
            'data' => $data,
            'municipality' => $header['municipality'] ?? null,
            'schoolName' => $header['schoolName'] ?? null,
            'contact' => $header['contact'] ?? null,
            'issuedAt' => $issuedAtHuman,
            'validationCode' => $code,
            'validationUrl' => $validationUrl,
            'qrDataUri' => $qrDataUri,
            'issuerName' => auth()->user()?->name,
            'issuerRole' => null,
            'cityUf' => null,
        ], 'vagas-por-turma-' . $ano . '.pdf', 'a4', 'landscape', 'attachment');
    }

    public function excel(Request $request, VacanciesBySchoolClassService $service)
    {
        $ano = (int) $request->get('ano');
        $instituicaoId = $request->get('ref_cod_instituicao') ? (int) $request->get('ref_cod_instituicao') : null;
        $escolaId = $request->get('ref_cod_escola') ? (int) $request->get('ref_cod_escola') : null;
        $cursoId = $request->get('ref_cod_curso') ? (int) $request->get('ref_cod_curso') : null;
        $serieId = $request->get('ref_cod_serie') ? (int) $request->get('ref_cod_serie') : null;
        $turmaId = $request->get('ref_cod_turma') ? (int) $request->get('ref_cod_turma') : null;

        if (!$ano || !$escolaId) {
            abort(422, 'Informe ano e escola.');
        }

        $data = $service->build($ano, $instituicaoId, $escolaId, $cursoId, $serieId, $turmaId);

        return Excel::download(
            new VacanciesBySchoolClassExport($data['items']),
            'vagas-por-turma-' . $ano . '.xlsx'
        );
    }
}

