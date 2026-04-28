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
    /**
     * @return array<string, string>
     */
    private function templates(): array
    {
        return [
            'classic' => 'Clássico (padrão)',
            'modern' => 'Moderno (limpo)',
            'simade_model_1' => 'SIMADE: Modelo 1 (EF 9 anos — frente/verso)',
            'simade_model_32' => 'SIMADE: Modelo 32 (Res. 2197/2012 — frente/verso)',
            'simade_magisterio' => 'SIMADE: Magistério (Curso Normal — frente/verso)',
            'mg_regular_eja_emti_prop' => 'MG: Regular/EJA/Correção de fluxo/EMTI propedêutico (ASIE 6/2021)',
            'mg_nem_piloto' => 'MG: Novo Ensino Médio piloto (REANP)',
            'mg_emti_prof' => 'MG: EMTI profissional (REANP)',
            'mg_tecnico_semestral' => 'MG: Técnico semestral (Histórico + Diploma — REANP)',
            'mg_normal_infantil' => 'MG: Normal nível médio Educação Infantil (Histórico + Diploma — REANP)',
        ];
    }

    public function index(Request $request)
    {
        return view('advanced-reports::school-history.index', [
            'alunoId' => $request->get('aluno_id'),
            'template' => $request->get('template', 'classic'),
            'templates' => $this->templates(),
        ]);
    }

    public function pdf(Request $request, SchoolHistoryService $service): Response
    {
        $alunoId = (int) $request->get('aluno_id');
        $book = $request->get('book');
        $page = $request->get('page');
        $record = $request->get('record');
        $template = (string) $request->get('template', 'classic');

        if (!$alunoId) {
            abort(422, 'Informe o aluno.');
        }

        $data = $service->build($alunoId);

        $issuedAt = now();
        $issuedAtHuman = $issuedAt->format('d/m/Y H:i');
        $issuedAtIso = $issuedAt->toISOString();

        $payload = [
            'aluno_id' => $alunoId,
            'book' => $book,
            'page' => $page,
            'record' => $record,
            'template' => $template,
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

        $templates = $this->templates();
        if (!array_key_exists($template, $templates)) {
            $template = 'classic';
        }

        $view = match ($template) {
            'modern' => 'advanced-reports::school-history.pdf-modern',
            'simade_model_1' => 'advanced-reports::school-history.pdf-simade-model-1',
            'simade_model_32' => 'advanced-reports::school-history.pdf-simade-model-32',
            'simade_magisterio' => 'advanced-reports::school-history.pdf-simade-magisterio',
            default => 'advanced-reports::school-history.pdf',
        };

        $disposition = $request->boolean('preview') ? 'inline' : 'attachment';

        return app(PdfRenderService::class)->download($view, [
            'data' => $data,
            'issuedAt' => $issuedAtHuman,
            'validationCode' => $code,
            'validationUrl' => $validationUrl,
            'qrDataUri' => $qrDataUri,
            'book' => $book,
            'page' => $page,
            'record' => $record,
            'template' => $template,
            'templateLabel' => $templates[$template] ?? null,
        ], 'historico-escolar-' . $alunoId . '.pdf', 'a4', 'portrait', $disposition);
    }
}

