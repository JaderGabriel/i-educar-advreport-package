<?php

namespace iEducar\Packages\AdvancedReports\Http\Controllers;

use App\Http\Controllers\Controller;
use iEducar\Packages\AdvancedReports\Models\AdvancedReportsDocument;
use iEducar\Packages\AdvancedReports\Services\AdvancedReportsFilterService;
use iEducar\Packages\AdvancedReports\Services\DocumentSigningService;
use iEducar\Packages\AdvancedReports\Services\OfficialHeaderService;
use iEducar\Packages\AdvancedReports\Services\PdfRenderService;
use iEducar\Packages\AdvancedReports\Services\QrCodeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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

        // Para este módulo de “modelos”, o usuário escolhe apenas tipo/template/lado.
        // Demais dados devem vir do sistema (quando possível) ou serem fictícios na prévia.
        $ano = $request->get('ano') ? (int) $request->get('ano') : null;
        $escolaId = $request->get('ref_cod_escola') ? (int) $request->get('ref_cod_escola') : null;
        $instituicaoId = $request->get('ref_cod_instituicao') ? (int) $request->get('ref_cod_instituicao') : null;

        $issuerName = auth()->user()?->name;
        $issuerRole = null;
        $cityUf = null;

        $header = null;
        if ($escolaId) {
            if (!$instituicaoId) {
                $instituicaoId = (int) (DB::table('pmieducar.escola')->where('cod_escola', $escolaId)->value('ref_cod_instituicao') ?: 0) ?: null;
            }
            $header = app(OfficialHeaderService::class)->forSchool($instituicaoId, $escolaId);
        }

        $municipality = $header['municipality'] ?? null;
        $schoolName = $header['schoolName'] ?? null;
        $contact = $header['contact'] ?? null;

        // Campos do “conteúdo” (fictícios por padrão, já que este é um gerador de modelos)
        $year = $ano ? (string) $ano : null;
        $course = null;
        $class = null;
        $enrollment = null;

        $issuedAt = now();
        $issuedAtHuman = $issuedAt->format('d/m/Y H:i');
        $issuedAtIso = $issuedAt->toISOString();

        $payload = [
            'document' => $document,
            'template' => $template,
            'side' => $side,
            'ano' => $ano,
            'instituicao_id' => $instituicaoId,
            'escola_id' => $escolaId,
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
                'issued_by_user_id' => auth()->id(),
                'issued_ip' => $request->ip(),
                'issued_user_agent' => substr((string) $request->userAgent(), 0, 255),
                'version' => DocumentSigningService::VERSION,
                'mac' => $mac,
                'payload' => array_merge($payload, [
                    'validation_url' => $publicValidationUrl,
                ]),
            ]
        );

        $disposition = 'inline';

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
            'municipality' => $municipality ?: ($request->boolean('preview') ? 'Prefeitura Municipal (Exemplo) • Secretaria de Educação' : null),
            'schoolName' => $schoolName ?: ($request->boolean('preview') ? 'Unidade Escolar (Exemplo)' : null),
            'contact' => $contact ?: ($request->boolean('preview') ? 'Endereço (Exemplo) • Tel: (00) 0000-0000 • E-mail: exemplo@rede.gov.br' : null),
        ], $filename, 'a4', 'landscape', $disposition);
    }
}

