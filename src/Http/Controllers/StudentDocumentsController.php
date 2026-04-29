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
    /**
     * @return array<int, array{month:int,label:string,percent:float|null}>
     */
    private function monthlyFrequencyFallbackFromOverall(?float $overallPercent): array
    {
        $labels = [
            1 => 'Janeiro', 2 => 'Fevereiro', 3 => 'Março', 4 => 'Abril', 5 => 'Maio', 6 => 'Junho',
            7 => 'Julho', 8 => 'Agosto', 9 => 'Setembro', 10 => 'Outubro', 11 => 'Novembro', 12 => 'Dezembro',
        ];

        $out = [];
        foreach ($labels as $m => $label) {
            $out[] = ['month' => $m, 'label' => $label, 'percent' => $overallPercent];
        }

        return $out;
    }
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
        $document = (string) $request->get('document', 'declaration_enrollment');

        if ($request->boolean('preview')) {
            $fake = (object) [
                'matricula_id' => 12345,
                'ano_letivo' => (int) ($request->get('ano') ?: date('Y')),
                'aluno_nome' => 'ALUNO(A) EXEMPLO',
                'instituicao_id' => null,
                'escola_id' => null,
                'instituicao' => 'Instituição (Exemplo)',
                'escola' => 'Escola (Exemplo)',
                'curso' => 'Curso (Exemplo)',
                'serie' => 'Série (Exemplo)',
                'turma' => 'Turma (Exemplo)',
            ];

            $view = match ($document) {
                'declaration_frequency' => 'advanced-reports::student-documents.declaration-frequency',
                'transfer_guide' => 'advanced-reports::student-documents.transfer-guide',
                'declaration_conclusion' => 'advanced-reports::student-documents.declaration-conclusion',
                'declaration_nada_consta' => 'advanced-reports::student-documents.declaration-nada-consta',
                default => 'advanced-reports::student-documents.declaration-enrollment',
            };

            $title = match ($document) {
                'declaration_frequency' => 'Declaração de frequência',
                'transfer_guide' => 'Guia/Declaração de transferência',
                'declaration_conclusion' => 'Declaração de conclusão',
                'declaration_nada_consta' => 'Declaração de escolaridade / Nada consta',
                default => 'Declaração de matrícula',
            };

            $extra = [];
            if ($document === 'declaration_frequency') {
                $extra['frequencia_percentual'] = 92.5;
                $extra['frequencia_mensal'] = $this->monthlyFrequencyFallbackFromOverall(92.5);
            }
            if ($document === 'declaration_conclusion') {
                $extra['historico_emissao_dias'] = (int) ($request->get('historico_emissao_dias') ?: 30);
            }

            return app(PdfRenderService::class)->download($view, [
                'title' => $title,
                'matricula' => $fake,
                'issuedAt' => now()->format('d/m/Y H:i'),
                'validationCode' => 'EXEMPLO',
                'validationUrl' => '#',
                'qrDataUri' => null,
                'issuerName' => null,
                'issuerRole' => null,
                'cityUf' => null,
                'extra' => $extra,
                'municipality' => 'Prefeitura Municipal (Exemplo) • Secretaria de Educação',
                'schoolName' => 'Unidade Escolar (Exemplo)',
                'contact' => 'Endereço (Exemplo) • Tel: (00) 0000-0000 • E-mail: exemplo@rede.gov.br',
            ], 'documento-previa.pdf');
        }

        $matriculaId = (int) $request->get('matricula_id');
        $matriculaIds = array_values(array_filter(array_map('intval', (array) $request->get('matricula_ids', []))));

        $ano = $request->get('ano') ? (int) $request->get('ano') : null;
        $escolaId = $request->get('ref_cod_escola') ? (int) $request->get('ref_cod_escola') : null;
        $cursoId = $request->get('ref_cod_curso') ? (int) $request->get('ref_cod_curso') : null;
        $serieId = $request->get('ref_cod_serie') ? (int) $request->get('ref_cod_serie') : null;
        $turmaId = $request->get('ref_cod_turma') ? (int) $request->get('ref_cod_turma') : null;

        if (!$matriculaId && empty($matriculaIds) && (!$ano || !$escolaId || !$cursoId)) {
            abort(422, 'Informe ao menos ano, escola e curso (alunos são opcionais).');
        }

        $loadMatricula = static function (int $id) {
            return DB::table('pmieducar.matricula as m')
                ->join('pmieducar.aluno as a', 'a.cod_aluno', '=', 'm.ref_cod_aluno')
                ->join('cadastro.pessoa as p', 'p.idpes', '=', 'a.ref_idpes')
                ->leftJoin('pmieducar.escola as e', 'e.cod_escola', '=', 'm.ref_ref_cod_escola')
                ->leftJoin('cadastro.pessoa as ep', 'ep.idpes', '=', 'e.ref_idpes')
                ->leftJoin('cadastro.juridica as ej', 'ej.idpes', '=', 'ep.idpes')
                ->leftJoin('pmieducar.escola_complemento as ec', 'ec.ref_cod_escola', '=', 'e.cod_escola')
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
                ->selectRaw('COALESCE(ej.fantasia, ec.nm_escola, \'\') as escola')
                ->selectRaw('c.nm_curso as curso')
                ->selectRaw('s.nm_serie as serie')
                ->selectRaw('t.nm_turma as turma')
                ->where('m.cod_matricula', $id)
                ->first();
        };

        $issuerName = auth()->user()?->name;
        $issuerRole = null;
        $cityUf = null;

        $issuedAt = now();
        $issuedAtHuman = $issuedAt->format('d/m/Y H:i');
        $issuedAtIso = $issuedAt->toISOString();

        $idsToEmit = [];
        if ($matriculaId) {
            $idsToEmit = [$matriculaId];
        } elseif (!empty($matriculaIds)) {
            $idsToEmit = $matriculaIds;
        } else {
            $idsToEmit = DB::table('pmieducar.matricula as m')
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
                ->orderBy('m.cod_matricula')
                ->limit(200)
                ->pluck('m.cod_matricula')
                ->map(fn ($v) => (int) $v)
                ->all();
        }

        if (empty($idsToEmit)) {
            abort(404, 'Nenhuma matrícula encontrada para os filtros informados.');
        }

        $items = [];
        foreach ($idsToEmit as $id) {
            $matricula = $loadMatricula($id);
            if (!$matricula) {
                continue;
            }

            $extra = [];
            if ($document === 'declaration_frequency') {
                $freq = DB::selectOne('SELECT modules.frequencia_da_matricula(?) as frequencia', [$id]);
                $freqPercent = $freq?->frequencia !== null ? (float) $freq->frequencia : null;
                $extra['frequencia_percentual'] = $freqPercent;
                $extra['frequencia_mensal'] = $this->monthlyFrequencyFallbackFromOverall($freqPercent);
            }
            if ($document === 'declaration_conclusion') {
                $extra['historico_emissao_dias'] = (int) ($request->get('historico_emissao_dias') ?: 0);
            }

            $items[] = [
                'matricula_id' => (int) $id,
                'matricula' => $matricula,
                'extra' => $extra,
            ];
        }

        if (empty($items)) {
            abort(404, 'Nenhuma matrícula encontrada para os filtros informados.');
        }

        $first = $items[0]['matricula'];
        $header = app(OfficialHeaderService::class)->forSchool(
            !empty($first->instituicao_id) ? (int) $first->instituicao_id : null,
            !empty($first->escola_id) ? (int) $first->escola_id : null,
        );

        $payload = [
            'mode' => count($items) > 1 ? 'batch' : 'single',
            'document' => $document,
            'ano' => $ano,
            'escola_id' => $escolaId,
            'curso_id' => $cursoId,
            'serie_id' => $serieId,
            'turma_id' => $turmaId,
            'count' => count($items),
        ];

        $signing = app(DocumentSigningService::class);
        $code = $signing->generateCode(8);
        $validationUrl = route('advanced-reports.documents.validate', ['code' => $code]);
        $qrDataUri = app(QrCodeService::class)->pngDataUri($validationUrl, 4);
        $payloadToStore = array_merge($payload, [
            'validation_url' => $validationUrl,
        ]);
        $mac = $signing->mac($code, $document, $issuedAtIso, $payloadToStore);

        AdvancedReportsDocument::query()->create([
            'code' => $code,
            'type' => $document,
            'issued_at' => $issuedAt,
            'issued_by_user_id' => auth()->id(),
            'issued_ip' => $request->ip(),
            'issued_user_agent' => substr((string) $request->userAgent(), 0, 255),
            'version' => DocumentSigningService::VERSION,
            'mac' => $mac,
            'payload' => $payloadToStore,
        ]);

        $view = match ($document) {
            'declaration_frequency' => 'advanced-reports::student-documents.declaration-frequency',
            'transfer_guide' => 'advanced-reports::student-documents.transfer-guide',
            'declaration_conclusion' => 'advanced-reports::student-documents.declaration-conclusion',
            'declaration_nada_consta' => 'advanced-reports::student-documents.declaration-nada-consta',
            default => 'advanced-reports::student-documents.declaration-enrollment',
        };

        $title = match ($document) {
            'declaration_frequency' => 'Declaração de frequência',
            'transfer_guide' => 'Guia/Declaração de transferência',
            'declaration_conclusion' => 'Declaração de conclusão',
            'declaration_nada_consta' => 'Declaração de escolaridade / Nada consta',
            default => 'Declaração de matrícula',
        };

        if (count($items) === 1) {
            $one = $items[0];

            return app(PdfRenderService::class)->download($view, [
                'title' => $title,
                'matricula' => $one['matricula'],
                'issuedAt' => $issuedAtHuman,
                'validationCode' => $code,
                'validationUrl' => $validationUrl,
                'qrDataUri' => $qrDataUri,
                'issuerName' => $issuerName,
                'issuerRole' => $issuerRole,
                'cityUf' => $cityUf,
                'extra' => $one['extra'] ?? [],
                'municipality' => $header['municipality'] ?? null,
                'schoolName' => $header['schoolName'] ?? null,
                'contact' => $header['contact'] ?? null,
            ], str_replace(' ', '-', strtolower($title)) . '-' . (int) $one['matricula_id'] . '.pdf');
        }

        return app(PdfRenderService::class)->download('advanced-reports::student-documents.pdf-batch', [
            'title' => $title,
            'document' => $document,
            'items' => $items,
            'issuedAt' => $issuedAtHuman,
            'validationCode' => $code,
            'validationUrl' => $validationUrl,
            'qrDataUri' => $qrDataUri,
            'issuerName' => $issuerName,
            'issuerRole' => $issuerRole,
            'cityUf' => $cityUf,
            'municipality' => $header['municipality'] ?? null,
            'schoolName' => $header['schoolName'] ?? null,
            'contact' => $header['contact'] ?? null,
        ], str_replace(' ', '-', strtolower($title)) . '-lote.pdf');
    }
}

