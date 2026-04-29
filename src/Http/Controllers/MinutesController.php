<?php

namespace iEducar\Packages\AdvancedReports\Http\Controllers;

use App\Http\Controllers\Controller;
use iEducar\Packages\AdvancedReports\Models\AdvancedReportsDocument;
use iEducar\Packages\AdvancedReports\Services\DocumentSigningService;
use iEducar\Packages\AdvancedReports\Services\MinutesService;
use iEducar\Packages\AdvancedReports\Services\OfficialHeaderService;
use iEducar\Packages\AdvancedReports\Services\PdfRenderService;
use iEducar\Packages\AdvancedReports\Services\QrCodeService;
use iEducar\Packages\AdvancedReports\Services\AdvancedReportsFilterService;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class MinutesController extends Controller
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

        return view('advanced-reports::minutes.index', [
            'document' => $request->get('document', 'final_results'),
            'ano' => $ano,
            'instituicaoId' => $instituicaoId,
            'escolaId' => $escolaId,
            'cursoId' => $cursoId,
            ...$filterData,
        ]);
    }

    public function pdf(Request $request, MinutesService $service): Response
    {
        $document = (string) $request->get('document', 'final_results'); // final_results|signatures
        $schoolClassId = (int) $request->get('ref_cod_turma');
        $withDetails = $request->boolean('with_details');
        $issuerName = auth()->user()?->name;
        $issuerRole = null;
        $cityUf = null;

        if (!$schoolClassId) {
            abort(422, 'Informe a turma.');
        }

        if ($request->boolean('preview')) {
            $anoLetivo = (int) ($request->get('ano') ?: date('Y'));
            $class = (object) [
                'turma_id' => $schoolClassId,
                'turma' => 'Turma Exemplo (prévia)',
                'ano_letivo' => $anoLetivo,
                'escola' => 'Escola Municipal Exemplo',
                'instituicao' => 'Instituição Exemplo',
                'curso' => 'Ensino Fundamental',
                'serie' => '5º ano',
                'turno' => 'Matutino',
                'instituicao_id' => null,
                'escola_id' => null,
            ];
            $students = collect([
                [
                    'student' => 'Aluno(a) Exemplo A',
                    'registration_id' => 100001,
                    'status' => 'Aprovado',
                    'frequency' => 95.0,
                    'details' => null,
                ],
                [
                    'student' => 'Aluno(a) Exemplo B',
                    'registration_id' => 100002,
                    'status' => 'Cursando',
                    'frequency' => 88.0,
                    'details' => null,
                ],
            ]);
            $data = [
                'class' => $class,
                'students' => $students,
            ];
            $header = app(OfficialHeaderService::class)->forSchool(null, null);
            $view = match ($document) {
                'signatures' => 'advanced-reports::minutes.signatures',
                default => 'advanced-reports::minutes.final-results',
            };
            $title = match ($document) {
                'signatures' => 'Lista de assinaturas (responsáveis)',
                default => 'Ata de resultados finais',
            };

            return app(PdfRenderService::class)->download($view, [
                'title' => $title,
                'data' => $data,
                'withDetails' => false,
                'issuedAt' => now()->format('d/m/Y H:i'),
                'validationCode' => 'EXEMPLO',
                'validationUrl' => '#',
                'qrDataUri' => null,
                'issuerName' => $issuerName,
                'issuerRole' => $issuerRole,
                'cityUf' => $cityUf,
                'schoolInep' => null,
                'municipality' => $header['municipality'] ?? null,
                'schoolName' => $header['schoolName'] ?? null,
                'contact' => $header['contact'] ?? null,
            ], 'ata-previa.pdf', 'a4', 'portrait', 'inline');
        }

        $data = $service->buildFinalResults($schoolClassId, $withDetails);
        $class = $data['class'] ?? null;
        $header = app(OfficialHeaderService::class)->forSchool(
            !empty($class?->instituicao_id) ? (int) $class->instituicao_id : null,
            !empty($class?->escola_id) ? (int) $class->escola_id : null,
        );
        $schoolInep = null;
        if (!empty($class?->escola_id)) {
            $schoolInep = \Illuminate\Support\Facades\DB::table('modules.educacenso_cod_escola')
                ->where('cod_escola', (int) $class->escola_id)
                ->value('cod_escola_inep');
        }

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
        $validationUrl = route('advanced-reports.documents.validate', ['code' => $code]);
        $qrDataUri = app(QrCodeService::class)->pngDataUri($validationUrl, 4);
        $payloadToStore = array_merge($payload, [
            'validation_url' => $validationUrl,
        ]);
        $mac = $signing->mac($code, 'ata', $issuedAtIso, $payloadToStore);

        AdvancedReportsDocument::query()->create([
            'code' => $code,
            'type' => 'ata',
            'issued_at' => $issuedAt,
            'issued_by_user_id' => auth()->id(),
            'issued_ip' => $request->ip(),
            'issued_user_agent' => substr((string) $request->userAgent(), 0, 255),
            'version' => DocumentSigningService::VERSION,
            'mac' => $mac,
            'payload' => $payloadToStore,
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
            'schoolInep' => $schoolInep ? (string) $schoolInep : null,
            'municipality' => $header['municipality'] ?? null,
            'schoolName' => $header['schoolName'] ?? null,
            'contact' => $header['contact'] ?? null,
        ], 'ata-' . $document . '-turma-' . $schoolClassId . '.pdf', 'a4', 'portrait', $disposition);
    }
}

