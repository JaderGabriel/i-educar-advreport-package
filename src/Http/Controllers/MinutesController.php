<?php

namespace iEducar\Packages\AdvancedReports\Http\Controllers;

use App\Http\Controllers\Controller;
use iEducar\Packages\AdvancedReports\Models\AdvancedReportsDocument;
use iEducar\Packages\AdvancedReports\Services\DocumentSigningService;
use iEducar\Packages\AdvancedReports\Services\MinutesService;
use iEducar\Packages\AdvancedReports\Services\PdfRenderService;
use iEducar\Packages\AdvancedReports\Services\QrCodeService;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class MinutesController extends Controller
{
    public function index(Request $request): View
    {
        return view('advanced-reports::minutes.index', [
            'document' => $request->get('document', 'final_results'),
        ]);
    }

    public function pdf(Request $request, MinutesService $service): Response
    {
        $document = (string) $request->get('document', 'final_results'); // final_results|signatures
        $schoolClassId = (int) $request->get('ref_cod_turma');
        $withDetails = $request->boolean('with_details');
        $issuerName = $request->get('issuer_name');
        $issuerRole = $request->get('issuer_role');
        $cityUf = $request->get('city_uf');

        if (!$schoolClassId) {
            abort(422, 'Informe a turma.');
        }

        $data = $service->buildFinalResults($schoolClassId, $withDetails);

        $issuedAt = now();
        $issuedAtHuman = $issuedAt->format('d/m/Y H:i');
        $issuedAtIso = $issuedAt->toISOString();

        $payload = [
            'document' => $document,
            'turma_id' => $schoolClassId,
            'with_details' => $withDetails ? 1 : 0,
            'issuer_name' => $issuerName,
            'issuer_role' => $issuerRole,
            'city_uf' => $cityUf,
        ];

        $signing = app(DocumentSigningService::class);
        $code = $signing->generateCode(8);
        $mac = $signing->mac($code, 'ata', $issuedAtIso, $payload);
        $validationUrl = route('advanced-reports.documents.validate', ['code' => $code]);
        $qrDataUri = app(QrCodeService::class)->pngDataUri($validationUrl, 4);

        AdvancedReportsDocument::query()->create([
            'code' => $code,
            'type' => 'ata',
            'issued_at' => $issuedAt,
            'version' => DocumentSigningService::VERSION,
            'mac' => $mac,
            'payload' => array_merge($payload, [
                'validation_url' => $validationUrl,
            ]),
        ]);

        $view = match ($document) {
            'signatures' => 'advanced-reports::minutes.signatures',
            default => 'advanced-reports::minutes.final-results',
        };

        $title = match ($document) {
            'signatures' => 'Lista de assinaturas (responsáveis)',
            default => 'Ata de resultados finais',
        };

        $disposition = $request->boolean('preview') ? 'inline' : 'attachment';

        return app(PdfRenderService::class)->download($view, [
            'title' => $title,
            'data' => $data,
            'withDetails' => $withDetails,
            'issuedAt' => $issuedAtHuman,
            'validationCode' => $code,
            'validationUrl' => $validationUrl,
            'qrDataUri' => $qrDataUri,
            'issuerName' => $issuerName,
            'issuerRole' => $issuerRole,
            'cityUf' => $cityUf,
        ], 'ata-' . $document . '-turma-' . $schoolClassId . '.pdf', 'a4', 'portrait', $disposition);
    }
}

