<?php

namespace iEducar\Packages\AdvancedReports\Http\Controllers;

use App\Http\Controllers\Controller;
use iEducar\Packages\AdvancedReports\Models\AdvancedReportsDocument;
use iEducar\Packages\AdvancedReports\Services\AdvancedReportsFilterService;
use iEducar\Packages\AdvancedReports\Services\DocumentSigningService;
use iEducar\Packages\AdvancedReports\Services\IssuerSignatureDetails;
use iEducar\Packages\AdvancedReports\Services\OfficialHeaderService;
use iEducar\Packages\AdvancedReports\Services\PdfRenderService;
use iEducar\Packages\AdvancedReports\Services\QrCodeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class StudentFormsController extends Controller
{
    public function individualIndex(Request $request, AdvancedReportsFilterService $filters): View
    {
        return $this->index($request, $filters, 'individual');
    }

    public function enrollmentIndex(Request $request, AdvancedReportsFilterService $filters): View
    {
        return $this->index($request, $filters, 'enrollment');
    }

    public function individualPdf(Request $request): Response
    {
        return $this->pdf($request, 'individual');
    }

    public function enrollmentPdf(Request $request): Response
    {
        return $this->pdf($request, 'enrollment');
    }

    private function index(Request $request, AdvancedReportsFilterService $filters, string $type): View
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

        return view('advanced-reports::student-forms.index', [
            'type' => $type,
            'ano' => $ano,
            'instituicaoId' => $instituicaoId,
            'escolaId' => $escolaId,
            'cursoId' => $cursoId,
            ...$filterData,
        ]);
    }

    private function pdf(Request $request, string $type): Response
    {
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
                'data_entrada_turma_br' => '10/02/' . date('Y'),
                'data_fim_turma_br' => '28/04/' . date('Y'),
            ];

            $title = $type === 'individual' ? 'Ficha individual' : 'Ficha de matrícula';
            $view = $type === 'individual'
                ? 'advanced-reports::student-forms.ficha-individual'
                : 'advanced-reports::student-forms.ficha-matricula';

            $extra = [];
            if ($type === 'individual') {
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
                'issuerName' => auth()->user()?->name,
                'issuerRole' => null,
                'cityUf' => null,
                'schoolInep' => '00000000',
                'extra' => $extra,
                'municipality' => 'Prefeitura Municipal (Exemplo) • Secretaria de Educação',
                'schoolName' => 'Unidade Escolar (Exemplo)',
                'contact' => 'Endereço (Exemplo) • Tel: (00) 0000-0000 • E-mail: exemplo@rede.gov.br',
                'authorities' => [
                    'secretary' => ['idpes' => null, 'name' => 'Secretário(a) (Exemplo)', 'inep' => null, 'matricula_interna' => null],
                    'director' => ['idpes' => null, 'name' => 'Diretor(a) (Exemplo)', 'inep' => null, 'matricula_interna' => null],
                ],
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
            if ($type === 'individual') {
                $alunoId = (int) ($matricula->aluno_id ?? 0);
                $extra['disciplinas'] = $this->disciplinesForMatricula($alunoId, $id, (int) ($matricula->ano_letivo ?? 0));
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
            'type' => $type,
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
        $mac = $signing->mac($code, "student_form:$type", $issuedAtIso, $payloadToStore);

        AdvancedReportsDocument::query()->create([
            'code' => $code,
            'type' => "student_form:$type",
            'issued_at' => $issuedAt,
            'issued_by_user_id' => auth()->id(),
            'issued_ip' => $request->ip(),
            'issued_user_agent' => substr((string) $request->userAgent(), 0, 255),
            'version' => DocumentSigningService::VERSION,
            'mac' => $mac,
            'payload' => $payloadToStore,
        ]);

        $title = $type === 'individual' ? 'Ficha individual' : 'Ficha de matrícula';

        $issuerName = auth()->user()?->name;
        $issuerRole = null;
        $cityUf = null;

        $authorities = null;
        if ($type === 'individual' && !empty($first->escola_id)) {
            $authorities = [
                'secretary' => ['idpes' => null, 'name' => null, 'inep' => null, 'matricula_interna' => null],
                'director' => ['idpes' => null, 'name' => null, 'inep' => null, 'matricula_interna' => null],
            ];

            $authRow = DB::table('pmieducar.escola as e')
                ->leftJoin('cadastro.pessoa as ps', 'ps.idpes', '=', 'e.ref_idpes_secretario_escolar')
                ->leftJoin('cadastro.pessoa as pg', 'pg.idpes', '=', 'e.ref_idpes_gestor')
                ->where('e.cod_escola', (int) $first->escola_id)
                ->selectRaw('e.ref_idpes_secretario_escolar as secretary_idpes')
                ->selectRaw('ps.nome as secretary_name')
                ->selectRaw('e.ref_idpes_gestor as director_idpes')
                ->selectRaw('pg.nome as director_name')
                ->first();

            $sigSvc = app(IssuerSignatureDetails::class);

            $sid = (int) ($authRow->secretary_idpes ?? 0);
            if ($sid > 0) {
                $d = $sigSvc->forPersonId($sid);
                $authorities['secretary'] = [
                    'idpes' => $sid,
                    'name' => (string) ($authRow->secretary_name ?? ''),
                    'inep' => $d['issuerPersonInep'] ?? null,
                    'matricula_interna' => $d['issuerMatriculaFuncional'] ?? null,
                ];
            }

            $did = (int) ($authRow->director_idpes ?? 0);
            if ($did > 0) {
                $d = $sigSvc->forPersonId($did);
                $authorities['director'] = [
                    'idpes' => $did,
                    'name' => (string) ($authRow->director_name ?? ''),
                    'inep' => $d['issuerPersonInep'] ?? null,
                    'matricula_interna' => $d['issuerMatriculaFuncional'] ?? null,
                ];
            }
        }

        $viewSingle = $type === 'individual'
            ? 'advanced-reports::student-forms.ficha-individual'
            : 'advanced-reports::student-forms.ficha-matricula';

        if (count($items) === 1) {
            $one = $items[0];

            return app(PdfRenderService::class)->download($viewSingle, [
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
                'authorities' => $authorities,
            ], str_replace(' ', '-', strtolower($title)) . '-' . (int) ($one['matricula_id'] ?? 0) . '.pdf');
        }

        return app(PdfRenderService::class)->download('advanced-reports::student-forms.pdf-batch', [
            'title' => $title,
            'type' => $type,
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
            'authorities' => $authorities,
        ], str_replace(' ', '-', strtolower($title)) . '-lote.pdf');
    }

    /**
     * @return list<array{nome: string, nota: mixed, faltas: mixed, carga_horaria: mixed}>
     */
    private function disciplinesForMatricula(int $alunoId, int $matriculaId, int $anoLetivo): array
    {
        if ($alunoId <= 0 || $matriculaId <= 0 || $anoLetivo <= 0) {
            return [];
        }

        // Busca disciplinas do histórico escolar mais recente da matrícula, caindo para o ano letivo
        $history = DB::table('pmieducar.historico_escolar as h')
            ->where('h.ref_cod_aluno', $alunoId)
            ->where('h.ativo', 1)
            ->where('h.ref_cod_matricula', $matriculaId)
            ->orderByDesc('h.sequencial')
            ->first();

        if (!$history) {
            $history = DB::table('pmieducar.historico_escolar as h')
                ->where('h.ref_cod_aluno', $alunoId)
                ->where('h.ativo', 1)
                ->where('h.ano', $anoLetivo)
                ->orderByDesc('h.sequencial')
                ->first();
        }

        if (!$history) {
            return [];
        }

        return DB::table('pmieducar.historico_escolar_disciplina as d')
            ->where('d.ref_ref_cod_aluno', $alunoId)
            ->where('d.ref_sequencial', (int) $history->sequencial)
            ->orderByRaw('COALESCE(d.ordenamento, 999999) ASC')
            ->orderBy('d.nm_disciplina')
            ->get()
            ->map(static function ($d): array {
                return [
                    'nome' => (string) ($d->nm_disciplina ?? ''),
                    'nota' => $d->nota ?? '—',
                    'faltas' => $d->faltas ?? null,
                    'carga_horaria' => $d->carga_horaria_disciplina ?? null,
                ];
            })
            ->all();
    }
}

