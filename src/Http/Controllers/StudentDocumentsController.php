<?php

namespace iEducar\Packages\AdvancedReports\Http\Controllers;

use App\Http\Controllers\Controller;
use iEducar\Packages\AdvancedReports\Models\AdvancedReportsDocument;
use iEducar\Packages\AdvancedReports\Services\DocumentSigningService;
use iEducar\Packages\AdvancedReports\Services\PdfRenderService;
use iEducar\Packages\AdvancedReports\Services\QrCodeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class StudentDocumentsController extends Controller
{
    public function index(Request $request): View
    {
        return view('advanced-reports::student-documents.index', [
            'matriculaId' => $request->get('matricula_id'),
            'document' => $request->get('document', 'declaration_enrollment'),
        ]);
    }

    public function pdf(Request $request): Response
    {
        $matriculaId = (int) $request->get('matricula_id');
        $document = (string) $request->get('document', 'declaration_enrollment');

        if (!$matriculaId) {
            abort(422, 'Informe a matrícula.');
        }

        $matricula = DB::table('pmieducar.matricula as m')
            ->join('pmieducar.aluno as a', 'a.cod_aluno', '=', 'm.ref_cod_aluno')
            ->join('cadastro.pessoa as p', 'p.idpes', '=', 'a.ref_idpes')
            ->leftJoin('pmieducar.escola as e', 'e.cod_escola', '=', 'm.ref_ref_cod_escola')
            ->leftJoin('pmieducar.instituicao as i', 'i.cod_instituicao', '=', 'm.ref_cod_instituicao')
            ->leftJoin('pmieducar.curso as c', 'c.cod_curso', '=', 'm.ref_cod_curso')
            ->leftJoin('pmieducar.serie as s', 's.cod_serie', '=', 'm.ref_ref_cod_serie')
            ->leftJoin('pmieducar.turma as t', 't.cod_turma', '=', 'm.ref_cod_turma')
            ->selectRaw('m.cod_matricula as matricula_id')
            ->selectRaw('m.ano as ano_letivo')
            ->selectRaw('p.nome as aluno_nome')
            ->selectRaw('i.nm_instituicao as instituicao')
            ->selectRaw('e.nome as escola')
            ->selectRaw('c.nm_curso as curso')
            ->selectRaw('s.nm_serie as serie')
            ->selectRaw('t.nm_turma as turma')
            ->where('m.cod_matricula', $matriculaId)
            ->first();

        if (!$matricula) {
            abort(404, 'Matrícula não encontrada.');
        }

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
            'matricula_id' => $matriculaId,
            'ano_letivo' => (string) ($matricula->ano_letivo ?? ''),
            'issuer_name' => $issuerName,
            'issuer_role' => $issuerRole,
            'city_uf' => $cityUf,
            'book' => $book,
            'page' => $page,
            'record' => $record,
        ];

        $extra = [];
        if ($document === 'declaration_frequency') {
            $freq = DB::selectOne('SELECT modules.frequencia_da_matricula(?) as frequencia', [$matriculaId]);
            $extra['frequencia_percentual'] = $freq?->frequencia;
            $payload['frequencia_percentual'] = $extra['frequencia_percentual'];
        }

        $signing = app(DocumentSigningService::class);
        $code = $signing->generateCode(8);
        $mac = $signing->mac($code, $document, $issuedAtIso, $payload);
        $validationUrl = route('advanced-reports.documents.validate', ['code' => $code]);
        $qrDataUri = app(QrCodeService::class)->pngDataUri($validationUrl, 4);

        AdvancedReportsDocument::query()->create([
            'code' => $code,
            'type' => $document,
            'issued_at' => $issuedAt,
            'version' => DocumentSigningService::VERSION,
            'mac' => $mac,
            'payload' => array_merge($payload, [
                'validation_url' => $validationUrl,
            ]),
        ]);

        $view = match ($document) {
            'declaration_frequency' => 'advanced-reports::student-documents.declaration-frequency',
            'transfer_guide' => 'advanced-reports::student-documents.transfer-guide',
            default => 'advanced-reports::student-documents.declaration-enrollment',
        };

        $title = match ($document) {
            'declaration_frequency' => 'Declaração de frequência',
            'transfer_guide' => 'Guia/Declaração de transferência',
            default => 'Declaração de matrícula',
        };

        return app(PdfRenderService::class)->download($view, [
            'title' => $title,
            'matricula' => $matricula,
            'issuedAt' => $issuedAtHuman,
            'validationCode' => $code,
            'validationUrl' => $validationUrl,
            'qrDataUri' => $qrDataUri,
            'issuerName' => $issuerName,
            'issuerRole' => $issuerRole,
            'cityUf' => $cityUf,
            'book' => $book,
            'page' => $page,
            'record' => $record,
            'extra' => $extra,
        ], str_replace(' ', '-', strtolower($title)) . '-' . $matriculaId . '.pdf');
    }
}

