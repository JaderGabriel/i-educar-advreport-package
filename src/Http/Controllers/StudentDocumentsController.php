<?php

namespace iEducar\Packages\AdvancedReports\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\LegacySchoolHistory;
use App\Models\LegacySchoolHistoryDiscipline;
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

class StudentDocumentsController extends Controller
{
    /**
     * @return list<array{nome: string, nota: mixed, faltas: mixed, carga_horaria: mixed}>
     */
    private function schoolHistoryDisciplinesForMatricula(int $alunoId, int $matriculaId, int $anoLetivo): array
    {
        $history = LegacySchoolHistory::query()
            ->where('ref_cod_aluno', $alunoId)
            ->where('ativo', 1)
            ->where('ref_cod_matricula', $matriculaId)
            ->orderByDesc('sequencial')
            ->first();

        if (!$history) {
            $history = LegacySchoolHistory::query()
                ->where('ref_cod_aluno', $alunoId)
                ->where('ativo', 1)
                ->where('ano', $anoLetivo)
                ->orderByDesc('sequencial')
                ->first();
        }

        if (!$history) {
            return [];
        }

        return LegacySchoolHistoryDiscipline::query()
            ->where('ref_ref_cod_aluno', $alunoId)
            ->where('ref_sequencial', $history->sequencial)
            ->orderByRaw('COALESCE(ordenamento, 999999) ASC')
            ->orderBy('nm_disciplina')
            ->get()
            ->map(static function (LegacySchoolHistoryDiscipline $d): array {
                return [
                    'nome' => (string) $d->nm_disciplina,
                    'nota' => $d->score() ?? $d->nota ?? '—',
                    'faltas' => $d->faltas,
                    'carga_horaria' => $d->carga_horaria_disciplina,
                ];
            })
            ->all();
    }

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
                'aluno_id' => 999001,
                'ano_letivo' => (int) ($request->get('ano') ?: date('Y')),
                'aluno_nome' => 'ALUNO(A) EXEMPLO',
                'instituicao_id' => null,
                'escola_id' => null,
                'instituicao' => 'Instituição (Exemplo)',
                'escola' => 'Escola (Exemplo)',
                'curso' => 'Curso (Exemplo)',
                'serie' => 'Série (Exemplo)',
                'turma' => 'Turma (Exemplo)',
                'matricula_aprovado' => 4,
                'data_entrada_turma_br' => '10/02/2026',
                'data_fim_turma_br' => '28/04/2026',
            ];

            $view = match ($document) {
                'declaration_frequency' => 'advanced-reports::student-documents.declaration-frequency',
                'transfer_guide' => 'advanced-reports::student-documents.transfer-guide',
                'transfer_packet' => 'advanced-reports::student-documents.transfer-packet',
                'approval_packet' => 'advanced-reports::student-documents.approval-packet',
                'declaration_conclusion' => 'advanced-reports::student-documents.declaration-conclusion',
                'declaration_nada_consta' => 'advanced-reports::student-documents.declaration-nada-consta',
                default => 'advanced-reports::student-documents.declaration-enrollment',
            };

            $title = match ($document) {
                'declaration_frequency' => 'Declaração de frequência',
                'transfer_guide' => 'Guia/Declaração de transferência',
                'transfer_packet' => 'Comprovante de matrícula e declaração de transferência',
                'approval_packet' => 'Declaração de matrícula e declaração de conclusão',
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
            if ($document === 'approval_packet') {
                $extra['historico_emissao_dias'] = (int) ($request->get('historico_emissao_dias') ?: 30);
                $extra['ficha_individual'] = true;
                $extra['disciplinas'] = [
                    ['nome' => 'Língua Portuguesa', 'nota' => '8,0', 'faltas' => 2, 'carga_horaria' => '200h'],
                    ['nome' => 'Matemática', 'nota' => '9,0', 'faltas' => 0, 'carga_horaria' => '200h'],
                ];
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
                'schoolInep' => '00000000',
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

        $mtPriorizada = <<<'SQL'
(
  SELECT DISTINCT ON (mtz.ref_cod_matricula)
    mtz.ref_cod_matricula,
    mtz.ref_cod_turma,
    mtz.data_enturmacao,
    mtz.data_exclusao
  FROM pmieducar.matricula_turma mtz
  ORDER BY mtz.ref_cod_matricula,
    (CASE WHEN mtz.transferido IS TRUE THEN 1 ELSE 0 END) DESC,
    mtz.sequencial DESC
) as mt
SQL;

        $loadMatricula = static function (int $id) use ($mtPriorizada) {
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
                ->leftJoin(DB::raw($mtPriorizada), 'mt.ref_cod_matricula', '=', 'm.cod_matricula')
                ->leftJoin('pmieducar.turma as t', 't.cod_turma', '=', 'mt.ref_cod_turma')
                ->selectRaw('m.cod_matricula as matricula_id')
                ->selectRaw('a.cod_aluno as aluno_id')
                ->selectRaw('m.ano as ano_letivo')
                ->selectRaw('m.aprovado as matricula_aprovado')
                ->selectRaw('p.nome as aluno_nome')
                ->selectRaw('e.ref_cod_instituicao as instituicao_id')
                ->selectRaw('e.cod_escola as escola_id')
                ->selectRaw('i.nm_instituicao as instituicao')
                ->selectRaw('COALESCE(ej.fantasia, ec.nm_escola, \'\') as escola')
                ->selectRaw('c.nm_curso as curso')
                ->selectRaw('s.nm_serie as serie')
                ->selectRaw('t.nm_turma as turma')
                ->selectRaw("to_char(mt.data_enturmacao::date, 'DD/MM/YYYY') as data_entrada_turma_br")
                ->selectRaw("to_char(COALESCE(mt.data_exclusao::date, m.data_cancel::date), 'DD/MM/YYYY') as data_fim_turma_br")
                ->where('m.cod_matricula', $id)
                ->first();
        };

        $issuerName = auth()->user()?->name;
        $issuerRole = null;
        $cityUf = null;

        $issuedAt = now();
        $issuedAtHuman = $issuedAt->format('d/m/Y H:i');
        $issuedAtIso = DocumentSigningService::issuedAtForMac($issuedAt);

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
            if ($document === 'approval_packet') {
                $extra['historico_emissao_dias'] = (int) ($request->get('historico_emissao_dias') ?: 0);
                $extra['ficha_individual'] = true;
                $alunoId = (int) ($matricula->aluno_id ?? 0);
                $extra['disciplinas'] = $this->schoolHistoryDisciplinesForMatricula(
                    $alunoId,
                    $id,
                    (int) $matricula->ano_letivo
                );
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
        $schoolInep = null;
        if (!empty($first->escola_id)) {
            $schoolInep = DB::table('modules.educacenso_cod_escola')
                ->where('cod_escola', (int) $first->escola_id)
                ->value('cod_escola_inep');
        }

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
            'transfer_packet' => 'advanced-reports::student-documents.transfer-packet',
            'approval_packet' => 'advanced-reports::student-documents.approval-packet',
            'declaration_conclusion' => 'advanced-reports::student-documents.declaration-conclusion',
            'declaration_nada_consta' => 'advanced-reports::student-documents.declaration-nada-consta',
            default => 'advanced-reports::student-documents.declaration-enrollment',
        };

        $title = match ($document) {
            'declaration_frequency' => 'Declaração de frequência',
            'transfer_guide' => 'Guia/Declaração de transferência',
            'transfer_packet' => 'Comprovante de matrícula e declaração de transferência',
            'approval_packet' => 'Declaração de matrícula e declaração de conclusão',
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
                'schoolInep' => $schoolInep ? (string) $schoolInep : null,
                'extra' => $one['extra'] ?? [],
                'municipality' => $header['municipality'] ?? null,
                'schoolName' => $header['schoolName'] ?? null,
                'contact' => $header['contact'] ?? null,
                'matriculaInternaAluno' => (int) $one['matricula']->matricula_id,
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
            'schoolInep' => $schoolInep ? (string) $schoolInep : null,
            'municipality' => $header['municipality'] ?? null,
            'schoolName' => $header['schoolName'] ?? null,
            'contact' => $header['contact'] ?? null,
        ], str_replace(' ', '-', strtolower($title)) . '-lote.pdf');
    }
}
