<?php

namespace iEducar\Packages\AdvancedReports\Http\Controllers;

use App\Http\Controllers\Controller;
use iEducar\Packages\AdvancedReports\Exports\StudentsBySituationExport;
use iEducar\Packages\AdvancedReports\Models\AdvancedReportsDocument;
use iEducar\Packages\AdvancedReports\Services\AdvancedReportsFilterService;
use iEducar\Packages\AdvancedReports\Services\DocumentSigningService;
use iEducar\Packages\AdvancedReports\Services\OfficialHeaderService;
use iEducar\Packages\AdvancedReports\Services\PdfRenderService;
use iEducar\Packages\AdvancedReports\Services\QrCodeService;
use iEducar\Packages\AdvancedReports\Services\StudentsBySituationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\Response;

class StudentsBySituationController extends Controller
{
    public function index(Request $request, AdvancedReportsFilterService $filters, StudentsBySituationService $service): View
    {
        $ano = $request->get('ano');
        $instituicaoId = $request->get('ref_cod_instituicao');
        $escolaId = $request->get('ref_cod_escola');
        $cursoId = $request->get('ref_cod_curso');

        $serieId = $request->get('ref_cod_serie');
        $turmaId = $request->get('ref_cod_turma');
        $situacao = $request->get('situacao') ? (int) $request->get('situacao') : null;

        $filterData = $filters->getFilters(
            $ano ? (int) $ano : null,
            $instituicaoId ? (int) $instituicaoId : null,
            $escolaId ? (int) $escolaId : null,
            $cursoId ? (int) $cursoId : null,
        );

        $data = null;
        if ($ano) {
            $data = $service->build(
                (int) $ano,
                $instituicaoId ? (int) $instituicaoId : null,
                $escolaId ? (int) $escolaId : null,
                $cursoId ? (int) $cursoId : null,
                $serieId ? (int) $serieId : null,
                $turmaId ? (int) $turmaId : null,
                $situacao,
            );
        }

        return view('advanced-reports::students-by-situation.index', array_merge($filterData, [
            'ano' => $ano,
            'instituicaoId' => $instituicaoId,
            'escolaId' => $escolaId,
            'cursoId' => $cursoId,
            'serieId' => $serieId,
            'turmaId' => $turmaId,
            'situacao' => $situacao,
            'situacaoOptions' => $service->situationOptions(),
            'data' => $data,
        ]));
    }

    public function pdf(Request $request, StudentsBySituationService $service): Response
    {
        $ano = (int) $request->get('ano');
        if (!$ano) {
            abort(422, 'Informe o ano.');
        }

        $data = $service->build(
            $ano,
            $request->get('ref_cod_instituicao') ? (int) $request->get('ref_cod_instituicao') : null,
            $request->get('ref_cod_escola') ? (int) $request->get('ref_cod_escola') : null,
            $request->get('ref_cod_curso') ? (int) $request->get('ref_cod_curso') : null,
            $request->get('ref_cod_serie') ? (int) $request->get('ref_cod_serie') : null,
            $request->get('ref_cod_turma') ? (int) $request->get('ref_cod_turma') : null,
            $request->get('situacao') ? (int) $request->get('situacao') : null,
        );

        $instituicaoId = $request->get('ref_cod_instituicao') ? (int) $request->get('ref_cod_instituicao') : null;
        $escolaId = $request->get('ref_cod_escola') ? (int) $request->get('ref_cod_escola') : null;
        if (!$instituicaoId && $escolaId) {
            $instituicaoId = (int) (DB::table('pmieducar.escola')->where('cod_escola', $escolaId)->value('ref_cod_instituicao') ?: 0) ?: null;
        }

        $header = $escolaId
            ? app(OfficialHeaderService::class)->forSchool($instituicaoId, $escolaId)
            : ['municipality' => null, 'schoolName' => null, 'contact' => null];

        $issuedAt = now();
        $issuedAtHuman = $issuedAt->format('d/m/Y H:i');
        $issuedAtIso = $issuedAt->toISOString();

        $payload = [
            'report' => 'students_by_situation',
            'ano' => $ano,
            'year' => (string) $ano,
            'instituicao_id' => $instituicaoId,
            'escola_id' => $escolaId,
            'curso_id' => $request->get('ref_cod_curso') ? (int) $request->get('ref_cod_curso') : null,
            'serie_id' => $request->get('ref_cod_serie') ? (int) $request->get('ref_cod_serie') : null,
            'turma_id' => $request->get('ref_cod_turma') ? (int) $request->get('ref_cod_turma') : null,
            'situacao' => $request->get('situacao') ? (int) $request->get('situacao') : null,
        ];

        $signing = app(DocumentSigningService::class);
        $code = $signing->generateCode(8);
        $validationUrl = route('advanced-reports.documents.validate', ['code' => $code]);
        $qrDataUri = app(QrCodeService::class)->pngDataUri($validationUrl, 4);
        $payloadToStore = array_merge($payload, [
            'validation_url' => $validationUrl,
            'issuer_name' => auth()->user()?->name,
        ]);
        $mac = $signing->mac($code, 'students_by_situation', $issuedAtIso, $payloadToStore);

        AdvancedReportsDocument::query()->create([
            'code' => $code,
            'type' => 'students_by_situation',
            'issued_at' => $issuedAt,
            'issued_by_user_id' => auth()->id(),
            'issued_ip' => $request->ip(),
            'issued_user_agent' => substr((string) $request->userAgent(), 0, 255),
            'version' => DocumentSigningService::VERSION,
            'mac' => $mac,
            'payload' => $payloadToStore,
        ]);

        return app(PdfRenderService::class)->download('advanced-reports::students-by-situation.pdf', [
            'year' => $ano,
            'data' => $data,
            'labels' => $service->situationOptions(),
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
        ], 'alunos-por-situacao-' . $ano . '.pdf');
    }

    public function excel(Request $request, StudentsBySituationService $service)
    {
        $ano = (int) $request->get('ano');
        if (!$ano) {
            abort(422, 'Informe o ano.');
        }

        $data = $service->build(
            $ano,
            $request->get('ref_cod_instituicao') ? (int) $request->get('ref_cod_instituicao') : null,
            $request->get('ref_cod_escola') ? (int) $request->get('ref_cod_escola') : null,
            $request->get('ref_cod_curso') ? (int) $request->get('ref_cod_curso') : null,
            $request->get('ref_cod_serie') ? (int) $request->get('ref_cod_serie') : null,
            $request->get('ref_cod_turma') ? (int) $request->get('ref_cod_turma') : null,
            $request->get('situacao') ? (int) $request->get('situacao') : null,
        );

        return Excel::download(new StudentsBySituationExport($data, $service->situationOptions()), 'alunos-por-situacao-' . $ano . '.xlsx');
    }
}

