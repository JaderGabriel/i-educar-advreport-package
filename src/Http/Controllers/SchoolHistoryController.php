<?php

namespace iEducar\Packages\AdvancedReports\Http\Controllers;

use App\Http\Controllers\Controller;
use iEducar\Packages\AdvancedReports\Models\AdvancedReportsDocument;
use iEducar\Packages\AdvancedReports\Services\DocumentSigningService;
use iEducar\Packages\AdvancedReports\Services\PdfRenderService;
use iEducar\Packages\AdvancedReports\Services\QrCodeService;
use iEducar\Packages\AdvancedReports\Services\SchoolHistoryService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SchoolHistoryController extends Controller
{
    public function index(Request $request)
    {
        return view('advanced-reports::school-history.index', [
            'alunoId' => $request->get('aluno_id'),
        ]);
    }

    public function pdf(Request $request, SchoolHistoryService $service): Response
    {
        $alunoId = (int) $request->get('aluno_id');

        if (!$alunoId) {
            abort(422, 'Informe o aluno.');
        }

        $data = $service->build($alunoId);

        $issuedAt = now();
        $issuedAtHuman = $issuedAt->format('d/m/Y H:i');
        $issuedAtIso = $issuedAt->toISOString();

        $payload = [
            'aluno_id' => $alunoId,
        ];

        $signing = app(DocumentSigningService::class);
        $code = $signing->generateCode(8);
        $mac = $signing->mac($code, 'historico', $issuedAtIso, $payload);
        $validationUrl = route('advanced-reports.documents.validate', ['code' => $code]);
        $qrDataUri = app(QrCodeService::class)->pngDataUri($validationUrl, 4);

        AdvancedReportsDocument::query()->create([
            'code' => $code,
            'type' => 'historico',
            'issued_at' => $issuedAt,
            'version' => DocumentSigningService::VERSION,
            'mac' => $mac,
            'payload' => array_merge($payload, [
                'validation_url' => $validationUrl,
            ]),
        ]);

        return app(PdfRenderService::class)->download('advanced-reports::school-history.pdf', [
            'data' => $data,
            'issuedAt' => $issuedAtHuman,
            'validationCode' => $code,
            'validationUrl' => $validationUrl,
            'qrDataUri' => $qrDataUri,
        ], 'historico-escolar-' . $alunoId . '.pdf');
    }
}

