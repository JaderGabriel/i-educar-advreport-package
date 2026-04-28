<?php

namespace iEducar\Packages\AdvancedReports\Http\Controllers;

use App\Http\Controllers\Controller;
use iEducar\Packages\AdvancedReports\Models\AdvancedReportsDocument;
use iEducar\Packages\AdvancedReports\Services\BoletimService;
use iEducar\Packages\AdvancedReports\Services\DocumentSigningService;
use iEducar\Packages\AdvancedReports\Services\PdfRenderService;
use iEducar\Packages\AdvancedReports\Services\QrCodeService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class BoletimController extends Controller
{
    public function index(Request $request)
    {
        return view('advanced-reports::boletim.index', [
            'matriculaId' => $request->get('matricula_id'),
            'etapa' => $request->get('etapa'),
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
        ], 'boletim-' . $matriculaId . '.pdf');
    }
}

