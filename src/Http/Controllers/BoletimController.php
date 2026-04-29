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
        if ($request->boolean('preview')) {
            $fake = [
                'matricula' => [
                    'cod_matricula' => 12345,
                    'ano' => (int) ($request->get('ano') ?: date('Y')),
                    'escola' => 'Escola Municipal Exemplo',
                    'curso' => 'Ensino Fundamental',
                    'serie' => '5º Ano',
                    'turma' => 'Turma A',
                ],
                'etapas_count' => 4,
                'frequencia' => 92.5,
                'rows' => [
                    ['id' => 1, 'nome' => 'Língua Portuguesa', 'etapas' => ['1' => 7.5, '2' => 8.0, '3' => 7.0, '4' => 8.5, 'Rc' => '-']],
                    ['id' => 2, 'nome' => 'Matemática', 'etapas' => ['1' => 6.0, '2' => 6.5, '3' => 7.0, '4' => 7.5, 'Rc' => 7.0]],
                ],
            ];

            return app(PdfRenderService::class)->download('advanced-reports::boletim.pdf', [
                'ano' => $fake['matricula']['ano'],
                'data' => $fake,
                'issuedAt' => now()->format('d/m/Y H:i'),
                'validationCode' => 'EXEMPLO',
                'validationUrl' => '#',
                'qrDataUri' => null,
                'municipality' => null,
                'schoolName' => null,
                'contact' => null,
            ], 'boletim-previa.pdf');
        }

        $matriculaIds = array_values(array_filter(array_map('intval', (array) $request->get('matricula_ids', []))));
        $matriculaId = (int) $request->get('matricula_id');
        if (!$matriculaId && count($matriculaIds) === 1) {
            $matriculaId = (int) $matriculaIds[0];
        }

        $etapa = $request->get('etapa');

        $ano = $request->get('ano') ? (int) $request->get('ano') : null;
        $escolaId = $request->get('ref_cod_escola') ? (int) $request->get('ref_cod_escola') : null;
        $cursoId = $request->get('ref_cod_curso') ? (int) $request->get('ref_cod_curso') : null;
        $serieId = $request->get('ref_cod_serie') ? (int) $request->get('ref_cod_serie') : null;
        $turmaId = $request->get('ref_cod_turma') ? (int) $request->get('ref_cod_turma') : null;

        if (!$matriculaId && empty($matriculaIds) && (!$ano || !$escolaId || !$cursoId)) {
            abort(422, 'Informe ao menos ano, escola e curso (aluno é opcional).');
        }

        if (!$matriculaId && count($matriculaIds) > 1 && (!$ano || !$escolaId || !$cursoId)) {
            abort(422, 'Para emitir boletins de vários alunos selecionados, informe também ano, escola e curso (filtro de segurança).');
        }

        $issuedAt = now();
        $issuedAtHuman = $issuedAt->format('d/m/Y H:i');
        $issuedAtIso = DocumentSigningService::issuedAtForMac($issuedAt);

        if ($matriculaId) {
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
            $schoolInep = null;
            if (!empty($headerMeta?->escola_id)) {
                $schoolInep = DB::table('modules.educacenso_cod_escola')
                    ->where('cod_escola', (int) $headerMeta->escola_id)
                    ->value('cod_escola_inep');
            }

            $issuerName = auth()->user()?->name;

            $payload = [
                'mode' => 'single',
                'matricula_id' => $matriculaId,
                'etapa' => $etapa,
                'ano_letivo' => (string) ($data['matricula']['ano'] ?? ''),
            ];

            $signing = app(DocumentSigningService::class);
            $code = $signing->generateCode(8);
            $validationUrl = route('advanced-reports.documents.validate', ['code' => $code]);
            $qrDataUri = app(QrCodeService::class)->pngDataUri($validationUrl, 4);
            $payloadToStore = array_merge($payload, [
                'validation_url' => $validationUrl,
            ]);
            $mac = $signing->mac($code, 'boletim', $issuedAtIso, $payloadToStore);

            AdvancedReportsDocument::query()->create([
                'code' => $code,
                'type' => 'boletim',
                'issued_at' => $issuedAt,
                'issued_by_user_id' => auth()->id(),
                'issued_ip' => $request->ip(),
                'issued_user_agent' => substr((string) $request->userAgent(), 0, 255),
                'version' => DocumentSigningService::VERSION,
                'mac' => $mac,
                'payload' => $payloadToStore,
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
                'issuerName' => $issuerName,
                'schoolInep' => $schoolInep ? (string) $schoolInep : null,
            ], 'boletim-' . $matriculaId . '.pdf');
        }

        // Lote (por escola/curso e filtros opcionais), ou somente matrículas selecionadas
        $matriculas = DB::table('pmieducar.matricula as m')
            ->join('pmieducar.aluno as a', 'a.cod_aluno', '=', 'm.ref_cod_aluno')
            ->join('cadastro.pessoa as p', 'p.idpes', '=', 'a.ref_idpes')
            ->leftJoin('pmieducar.matricula_turma as mt', function ($join) {
                $join->on('mt.ref_cod_matricula', '=', 'm.cod_matricula');
                $join->where('mt.ativo', 1);
            })
            ->where('m.ativo', 1)
            ->where('m.ano', $ano)
            ->where('m.ref_ref_cod_escola', $escolaId)
            ->where('m.ref_cod_curso', $cursoId)
            ->when($serieId, fn ($q) => $q->where('m.ref_ref_cod_serie', $serieId))
            ->when($turmaId, fn ($q) => $q->where('mt.ref_cod_turma', $turmaId))
            ->when(count($matriculaIds) > 1, fn ($q) => $q->whereIn('m.cod_matricula', $matriculaIds))
            ->orderBy('p.nome')
            ->limit(200)
            ->get(['m.cod_matricula', 'p.nome as aluno_nome']);

        if ($matriculas->isEmpty()) {
            abort(404, 'Nenhuma matrícula encontrada para os filtros informados.');
        }

        $instituicaoId = DB::table('pmieducar.escola')->where('cod_escola', $escolaId)->value('ref_cod_instituicao');
        $header = app(OfficialHeaderService::class)->forSchool(
            $instituicaoId ? (int) $instituicaoId : null,
            $escolaId
        );
        $schoolInep = null;
        if ($escolaId) {
            $schoolInep = DB::table('modules.educacenso_cod_escola')
                ->where('cod_escola', (int) $escolaId)
                ->value('cod_escola_inep');
        }
        $issuerName = auth()->user()?->name;

        $signing = app(DocumentSigningService::class);
        $items = [];

        foreach ($matriculas as $m) {
            $matriculaId = (int) $m->cod_matricula;
            $data = $service->build($matriculaId, $etapa ? (string) $etapa : null);

            $payload = [
                'mode' => 'single',
                'matricula_id' => $matriculaId,
                'etapa' => $etapa,
                'ano_letivo' => (string) ($data['matricula']['ano'] ?? ''),
            ];

            $code = $signing->generateCode(8);
            $validationUrl = route('advanced-reports.documents.validate', ['code' => $code]);
            $qrDataUri = app(QrCodeService::class)->pngDataUri($validationUrl, 4);
            $payloadToStore = array_merge($payload, [
                'validation_url' => $validationUrl,
            ]);
            $mac = $signing->mac($code, 'boletim', $issuedAtIso, $payloadToStore);

            AdvancedReportsDocument::query()->create([
                'code' => $code,
                'type' => 'boletim',
                'issued_at' => $issuedAt,
                'issued_by_user_id' => auth()->id(),
                'issued_ip' => $request->ip(),
                'issued_user_agent' => substr((string) $request->userAgent(), 0, 255),
                'version' => DocumentSigningService::VERSION,
                'mac' => $mac,
                'payload' => $payloadToStore,
            ]);

            $items[] = [
                'aluno_nome' => (string) $m->aluno_nome,
                'matricula_id' => $matriculaId,
                'data' => $data,
                'validationCode' => $code,
                'validationUrl' => $validationUrl,
                'qrDataUri' => $qrDataUri,
            ];
        }

        return app(PdfRenderService::class)->download('advanced-reports::boletim.pdf-batch', [
            'ano' => $ano,
            'items' => $items,
            'issuedAt' => $issuedAtHuman,
            'municipality' => $header['municipality'] ?? null,
            'schoolName' => $header['schoolName'] ?? null,
            'contact' => $header['contact'] ?? null,
            'issuerName' => $issuerName,
            'schoolInep' => $schoolInep ? (string) $schoolInep : null,
        ], 'boletins-' . $ano . '-' . $escolaId . '-' . $cursoId . '.pdf');
    }
}

