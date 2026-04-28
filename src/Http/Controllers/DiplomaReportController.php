<?php

namespace iEducar\Packages\AdvancedReports\Http\Controllers;

use App\Http\Controllers\Controller;
use iEducar\Packages\AdvancedReports\Models\AdvancedReportsDocument;
use iEducar\Packages\AdvancedReports\Services\AdvancedReportsFilterService;
use iEducar\Packages\AdvancedReports\Services\DocumentSigningService;
use iEducar\Packages\AdvancedReports\Services\PdfRenderService;
use iEducar\Packages\AdvancedReports\Services\QrCodeService;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class DiplomaReportController extends Controller
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

        return view('advanced-reports::diplomas.index', array_merge($filterData, compact(
            'ano',
            'instituicaoId',
            'escolaId',
            'cursoId'
        )));
    }

    public function pdf(Request $request): Response
    {
        $document = $request->get('document', 'diploma'); // diploma|certificate|declaration
        $template = $request->get('template', 'classic');
        $side = $request->get('side', 'front');

        $year = $request->get('year');
        $course = $request->get('course');
        $class = $request->get('class');
        $enrollment = $request->get('enrollment');
        $issuerName = $request->get('issuer_name');
        $issuerRole = $request->get('issuer_role');
        $cityUf = $request->get('city_uf');
        $book = $request->get('book');
        $page = $request->get('page');
        $record = $request->get('record');

        $issuedAt = now();
        $issuedAtHuman = $issuedAt->format('d/m/Y H:i');
        $issuedAtIso = $issuedAt->toISOString();

        $payload = [
            'document' => $document,
            'template' => $template,
            'side' => $side,
            'year' => $year,
            'course' => $course,
            'class' => $class,
            'enrollment' => $enrollment,
            'issuer_name' => $issuerName,
            'issuer_role' => $issuerRole,
            'city_uf' => $cityUf,
            'book' => $book,
            'page' => $page,
            'record' => $record,
        ];

        $signing = app(DocumentSigningService::class);
        $validationCode = $signing->generateCode(8); // 16 hex (alto o suficiente e “curto”)
        $mac = $signing->mac($validationCode, (string) $document, $issuedAtIso, $payload);

        $view = match ($document) {
            'certificate' => 'advanced-reports::diplomas.certificate',
            'declaration' => 'advanced-reports::diplomas.declaration',
            default => 'advanced-reports::diplomas.pdf',
        };

        $filename = match ($document) {
            'certificate' => 'certificado-modelo.pdf',
            'declaration' => 'declaracao-modelo.pdf',
            default => 'diploma-' . $template . '-' . $side . '.pdf',
        };

        $publicValidationUrl = route('advanced-reports.documents.validate', ['code' => $validationCode]);
        $qrDataUri = app(QrCodeService::class)->pngDataUri($publicValidationUrl, 4);

        AdvancedReportsDocument::query()->updateOrCreate(
            ['code' => $validationCode],
            [
                'type' => $document,
                'issued_at' => $issuedAt,
                'version' => DocumentSigningService::VERSION,
                'mac' => $mac,
                'payload' => array_merge($payload, [
                    'validation_url' => $publicValidationUrl,
                ]),
            ]
        );

        return app(PdfRenderService::class)->download($view, [
            'document' => $document,
            'template' => $template,
            'side' => $side,
            'year' => $year,
            'course' => $course,
            'class' => $class,
            'enrollment' => $enrollment,
            'issuedAt' => $issuedAtHuman,
            'validationCode' => $validationCode,
            'validationUrl' => $publicValidationUrl,
            'qrDataUri' => $qrDataUri,
            'issuerName' => $issuerName,
            'issuerRole' => $issuerRole,
            'cityUf' => $cityUf,
            'book' => $book,
            'page' => $page,
            'record' => $record,
        ], $filename, 'a4', 'landscape');
    }
}

