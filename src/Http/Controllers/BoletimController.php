<?php

namespace iEducar\Packages\AdvancedReports\Http\Controllers;

use App\Http\Controllers\Controller;
use iEducar\Packages\AdvancedReports\Models\AdvancedReportsDocument;
use iEducar\Packages\AdvancedReports\Services\BoletimService;
use iEducar\Packages\AdvancedReports\Services\DocumentSigningService;
use iEducar\Packages\AdvancedReports\Services\OfficialHeaderService;
use iEducar\Packages\AdvancedReports\Services\PdfRenderService;
use iEducar\Packages\AdvancedReports\Services\QrCodeService;
use iEducar\Packages\AdvancedReports\Services\AdvancedReportsFilterService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class BoletimController extends Controller
{
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

        return view('advanced-reports::boletim.index', [
            'ano' => $ano,
            'instituicaoId' => $instituicaoId,
            'escolaId' => $escolaId,
            'cursoId' => $cursoId,
            'etapa' => $request->get('etapa'),
            ...$filterData,
        ]);
    }

    public function pdf(Request $request, BoletimService $service): Response
    {
        $matriculaId = (int) $request->get('matricula_id');
        $etapa = $request->get('etapa');

        if (!$matriculaId) {
            abort(422, 'Informe a matrícula.');
        }

        $data = $service->build($matriculaId, $etapa ? (string) $etapa : null);

        $headerMeta = DB::table('pmieducar.matricula as m')
            ->leftJoin('pmieducar.escola as e', 'e.cod_escola', '=', 'm.ref_ref_cod_escola')
            ->selectRaw('e.ref_cod_instituicao as instituicao_id')
            ->selectRaw('e.cod_escola as escola_id')
            ->where('m.cod_matricula', $matriculaId)
            ->first();

        $header = app(OfficialHeaderService::class)->forSchool(
            !empty($headerMeta?->instituicao_id) ? (int) $headerMeta->instituicao_id : null,
            !empty($headerMeta?->escola_id) ? (int) $headerMeta->escola_id : null,
        );

        $issuedAt = now();
        $issuedAtHuman = $issuedAt->format('d/m/Y H:i');
        $issuedAtIso = $issuedAt->toISOString();

        $payload = [
            'matricula_id' => $matriculaId,
            'etapa' => $etapa,
            'ano_letivo' => (string) ($data['matricula']['ano'] ?? ''),
        ];

        $signing = app(DocumentSigningService::class);
        $code = $signing->generateCode(8);
        $mac = $signing->mac($code, 'boletim', $issuedAtIso, $payload);
        $validationUrl = route('advanced-reports.documents.validate', ['code' => $code]);
        $qrDataUri = app(QrCodeService::class)->pngDataUri($validationUrl, 4);

        AdvancedReportsDocument::query()->create([
            'code' => $code,
            'type' => 'boletim',
            'issued_at' => $issuedAt,
            'issued_by_user_id' => auth()->id(),
            'issued_ip' => $request->ip(),
            'issued_user_agent' => substr((string) $request->userAgent(), 0, 255),
            'version' => DocumentSigningService::VERSION,
            'mac' => $mac,
            'payload' => array_merge($payload, [
                'validation_url' => $validationUrl,
            ]),
        ]);

        return app(PdfRenderService::class)->download('advanced-reports::boletim.pdf', [
            'ano' => $data['matricula']['ano'] ?? null,
            'data' => $data,
            'issuedAt' => $issuedAtHuman,
            'validationCode' => $code,
            'validationUrl' => $validationUrl,
            'qrDataUri' => $qrDataUri,
            'municipality' => $header['municipality'] ?? null,
            'schoolName' => $header['schoolName'] ?? null,
            'contact' => $header['contact'] ?? null,
        ], 'boletim-' . $matriculaId . '.pdf');
    }
}

