<?php

namespace iEducar\Packages\AdvancedReports\Http\Controllers;

use App\Http\Controllers\Controller;
use iEducar\Packages\AdvancedReports\Models\AdvancedReportsDocument;
use iEducar\Packages\AdvancedReports\Services\DocumentSigningService;
use iEducar\Packages\AdvancedReports\Services\OfficialHeaderService;
use iEducar\Packages\AdvancedReports\Services\PdfRenderService;
use iEducar\Packages\AdvancedReports\Services\QrCodeService;
use iEducar\Packages\AdvancedReports\Services\AdvancedReportsFilterService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class StudentDocumentsController extends Controller
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

        return view('advanced-reports::student-documents.index', [
            'ano' => $ano,
            'instituicaoId' => $instituicaoId,
            'escolaId' => $escolaId,
            'cursoId' => $cursoId,
            'document' => $request->get('document', 'declaration_enrollment'),
            ...$filterData,
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
            ->leftJoin('pmieducar.instituicao as i', 'i.cod_instituicao', '=', 'e.ref_cod_instituicao')
            ->leftJoin('pmieducar.curso as c', 'c.cod_curso', '=', 'm.ref_cod_curso')
            ->leftJoin('pmieducar.serie as s', 's.cod_serie', '=', 'm.ref_ref_cod_serie')
            ->leftJoin('pmieducar.matricula_turma as mt', function ($join) {
                $join->on('mt.ref_cod_matricula', '=', 'm.cod_matricula');
                $join->where('mt.ativo', 1);
            })
            ->leftJoin('pmieducar.turma as t', 't.cod_turma', '=', 'mt.ref_cod_turma')
            ->selectRaw('m.cod_matricula as matricula_id')
            ->selectRaw('m.ano as ano_letivo')
            ->selectRaw('p.nome as aluno_nome')
            ->selectRaw('e.ref_cod_instituicao as instituicao_id')
            ->selectRaw('e.cod_escola as escola_id')
            ->selectRaw('i.nm_instituicao as instituicao')
            ->selectRaw('COALESCE(e.fantasia, \'\') as escola')
            ->selectRaw('c.nm_curso as curso')
            ->selectRaw('s.nm_serie as serie')
            ->selectRaw('t.nm_turma as turma')
            ->where('m.cod_matricula', $matriculaId)
            ->first();

        if (!$matricula) {
            abort(404, 'Matrícula não encontrada.');
        }

        $issuerName = auth()->user()?->name;
        $issuerRole = null;
        $cityUf = null;

        $header = app(OfficialHeaderService::class)->forSchool(
            !empty($matricula->instituicao_id) ? (int) $matricula->instituicao_id : null,
            !empty($matricula->escola_id) ? (int) $matricula->escola_id : null,
        );

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
            'issued_by_user_id' => auth()->id(),
            'issued_ip' => $request->ip(),
            'issued_user_agent' => substr((string) $request->userAgent(), 0, 255),
            'version' => DocumentSigningService::VERSION,
            'mac' => $mac,
            'payload' => array_merge($payload, [
                'validation_url' => $validationUrl,
            ]),
        ]);

        $view = match ($document) {
            'declaration_frequency' => 'advanced-reports::student-documents.declaration-frequency',
            'transfer_guide' => 'advanced-reports::student-documents.transfer-guide',
            'declaration_nada_consta' => 'advanced-reports::student-documents.declaration-nada-consta',
            default => 'advanced-reports::student-documents.declaration-enrollment',
        };

        $title = match ($document) {
            'declaration_frequency' => 'Declaração de frequência',
            'transfer_guide' => 'Guia/Declaração de transferência',
            'declaration_nada_consta' => 'Declaração de escolaridade / Nada consta',
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
            'extra' => $extra,
            'municipality' => $header['municipality'] ?? null,
            'schoolName' => $header['schoolName'] ?? null,
            'contact' => $header['contact'] ?? null,
        ], str_replace(' ', '-', strtolower($title)) . '-' . $matriculaId . '.pdf');
    }
}

