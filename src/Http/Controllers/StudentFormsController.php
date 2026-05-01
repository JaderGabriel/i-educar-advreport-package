<?php

namespace iEducar\Packages\AdvancedReports\Http\Controllers;

use App\Http\Controllers\Controller;
use iEducar\Packages\AdvancedReports\Models\AdvancedReportsDocument;
use iEducar\Packages\AdvancedReports\Services\AdvancedReportsFilterService;
use iEducar\Packages\AdvancedReports\Services\BoletimService;
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
    private const TYPE_INDIVIDUAL = 'individual';

    private const TYPE_ENROLLMENT = 'enrollment';

    private const TYPE_MEDIA_AUTHORIZATION = 'media_authorization';

    public function individualIndex(Request $request, AdvancedReportsFilterService $filters): View
    {
        return $this->index($request, $filters, self::TYPE_INDIVIDUAL);
    }

    public function enrollmentIndex(Request $request, AdvancedReportsFilterService $filters): View
    {
        return $this->index($request, $filters, self::TYPE_ENROLLMENT);
    }

    public function mediaAuthorizationIndex(Request $request, AdvancedReportsFilterService $filters): View
    {
        return $this->index($request, $filters, self::TYPE_MEDIA_AUTHORIZATION);
    }

    public function individualPdf(Request $request): Response
    {
        return $this->pdf($request, self::TYPE_INDIVIDUAL);
    }

    public function enrollmentPdf(Request $request): Response
    {
        return $this->pdf($request, self::TYPE_ENROLLMENT);
    }

    public function mediaAuthorizationPdf(Request $request): Response
    {
        return $this->pdf($request, self::TYPE_MEDIA_AUTHORIZATION);
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
        $meta = $this->typeMeta($type);

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
                'nome_social_aluno' => '',
                'data_nascimento_br' => '01/01/2010',
                'sexo_desc' => 'Feminino',
                'naturalidade_txt' => 'Município Exemplo / UF',
                'nacionalidade_desc' => 'Brasileira',
                'aluno_email' => 'email@exemplo.gov.br',
                'telefone_principal' => '(11) 99999-0000',
                'aluno_inep' => '123456789012',
                'nis_formatado' => '123.45678.90-1',
                'cpf_formatado' => '000.000.000-00',
                'rg_completo' => '12.345.678-9 SSP / UF',
                'nm_pai' => 'Nome do Pai (Exemplo)',
                'nm_mae' => 'Nome da Mãe (Exemplo)',
                'tipo_responsavel' => 'm',
                'tipo_responsavel_desc' => 'Mãe',
                'responsavel_legal_nome' => 'Nome da Mãe (Exemplo)',
                'emancipado' => false,
                'data_matricula_br' => '05/02/' . date('Y'),
                'situacao_matricula_txt' => 'Transferido',
                'turno_enturmacao' => 'Matutino',
                'semestre' => '—',
                'dependencia' => false,
                'ultima_matricula_flag' => true,
                'formando' => false,
                'observacao_matricula' => '',
                'responsavel_exibicao' => 'Responsável legal (Exemplo)',
            ];

            $extra = [];
            if ($type === self::TYPE_INDIVIDUAL) {
                $extra['desempenho_boletim'] = [
                    'etapas_count' => 4,
                    'frequencia' => 94.5,
                    'rows' => [
                        [
                            'id' => 1,
                            'nome' => 'Língua Portuguesa',
                            'media_final' => '8,0',
                            'faltas_total_anual' => 6,
                            'etapas' => [
                                '1' => ['nota' => '7,5', 'faltas' => 2, 'faltas_pct' => null],
                                '2' => ['nota' => '8,0', 'faltas' => 1, 'faltas_pct' => null],
                                '3' => ['nota' => '8,0', 'faltas' => 0, 'faltas_pct' => null],
                                '4' => ['nota' => '8,5', 'faltas' => 1, 'faltas_pct' => null],
                                'Rc' => ['nota' => '—', 'faltas' => 2, 'faltas_pct' => null],
                            ],
                        ],
                        [
                            'id' => 2,
                            'nome' => 'Matemática',
                            'media_final' => '9,0',
                            'faltas_total_anual' => 0,
                            'etapas' => [
                                '1' => ['nota' => '8,5', 'faltas' => 0, 'faltas_pct' => null],
                                '2' => ['nota' => '9,0', 'faltas' => 0, 'faltas_pct' => null],
                                '3' => ['nota' => '9,0', 'faltas' => 0, 'faltas_pct' => null],
                                '4' => ['nota' => '9,5', 'faltas' => 0, 'faltas_pct' => null],
                                'Rc' => ['nota' => '—', 'faltas' => 0, 'faltas_pct' => null],
                            ],
                        ],
                    ],
                ];
            }

            return app(PdfRenderService::class)->download($meta['view_single'], [
                'title' => $meta['title'],
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
            $matricula = $this->loadMatriculaRow($id);
            if (!$matricula) {
                continue;
            }

            $this->enrichMatriculaRow($matricula);

            $extra = [];
            if ($type === self::TYPE_INDIVIDUAL) {
                $extra['desempenho_boletim'] = $this->buildDesempenhoBoletimForFicha($id);
                if ($extra['desempenho_boletim'] === null) {
                    $alunoId = (int) ($matricula->aluno_id ?? 0);
                    $extra['disciplinas'] = $this->disciplinesForMatricula($alunoId, $id, (int) ($matricula->ano_letivo ?? 0));
                }
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

        $issuedAt = now();
        $issuedAtHuman = $issuedAt->format('d/m/Y H:i');
        $issuedAtIso = DocumentSigningService::issuedAtForMac($issuedAt);

        $signing = app(DocumentSigningService::class);
        $code = $signing->generateCode(8);
        $validationUrl = route('advanced-reports.documents.validate', ['code' => $code]);
        $qrDataUri = app(QrCodeService::class)->pngDataUri($validationUrl, 4);
        $payloadToStore = array_merge($payload, [
            'validation_url' => $validationUrl,
        ]);
        $mac = $signing->mac($code, $meta['doc_type'], $issuedAtIso, $payloadToStore);

        AdvancedReportsDocument::query()->create([
            'code' => $code,
            'type' => $meta['doc_type'],
            'issued_at' => $issuedAt,
            'issued_by_user_id' => auth()->id(),
            'issued_ip' => $request->ip(),
            'issued_user_agent' => substr((string) $request->userAgent(), 0, 255),
            'version' => DocumentSigningService::VERSION,
            'mac' => $mac,
            'payload' => $payloadToStore,
        ]);

        $issuerName = auth()->user()?->name;
        $issuerRole = null;
        $cityUf = null;

        $authorities = null;
        if ($type === self::TYPE_INDIVIDUAL && !empty($first->escola_id)) {
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

        $viewSingle = $meta['view_single'];

        if (count($items) === 1) {
            $one = $items[0];

            return app(PdfRenderService::class)->download($viewSingle, [
                'title' => $meta['title'],
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
            ], str_replace(' ', '-', strtolower($meta['title'])) . '-' . (int) ($one['matricula_id'] ?? 0) . '.pdf');
        }

        return app(PdfRenderService::class)->download('advanced-reports::student-forms.pdf-batch', [
            'title' => $meta['title'],
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
        ], str_replace(' ', '-', strtolower($meta['title'])) . '-lote.pdf');
    }

    /**
     * @return array{title: string, doc_type: string, view_single: string}
     */
    private function typeMeta(string $type): array
    {
        return match ($type) {
            self::TYPE_INDIVIDUAL => [
                'title' => 'Ficha individual',
                'doc_type' => 'student_form:individual',
                'view_single' => 'advanced-reports::student-forms.ficha-individual',
            ],
            self::TYPE_ENROLLMENT => [
                'title' => 'Ficha de matrícula',
                'doc_type' => 'student_form:enrollment',
                'view_single' => 'advanced-reports::student-forms.ficha-matricula',
            ],
            self::TYPE_MEDIA_AUTHORIZATION => [
                'title' => 'Termo de autorização de uso de imagem e voz',
                'doc_type' => 'student_form:media_authorization',
                'view_single' => 'advanced-reports::student-forms.termo-autorizacao-imagem-voz',
            ],
            default => throw new \InvalidArgumentException('Tipo de ficha inválido.'),
        };
    }

    private function loadMatriculaRow(int $id): ?object
    {
        $mtPriorizada = <<<'SQL'
(
  SELECT DISTINCT ON (mtz.ref_cod_matricula)
    mtz.ref_cod_matricula,
    mtz.ref_cod_turma,
    mtz.data_enturmacao,
    mtz.data_exclusao,
    mtz.turno_id
  FROM pmieducar.matricula_turma mtz
  ORDER BY mtz.ref_cod_matricula,
    (CASE WHEN mtz.transferido IS TRUE THEN 1 ELSE 0 END) DESC,
    mtz.sequencial DESC
) as mt
SQL;

        $row = DB::table('pmieducar.matricula as m')
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
            ->leftJoin('cadastro.fisica as fi', 'fi.idpes', '=', 'a.ref_idpes')
            ->leftJoin('public.municipio as munnasc', 'munnasc.idmun', '=', 'fi.idmun_nascimento')
            ->leftJoin('cadastro.pessoa as presp', 'presp.idpes', '=', 'fi.idpes_responsavel')
            ->leftJoin('cadastro.documento as doc', 'doc.idpes', '=', 'a.ref_idpes')
            ->leftJoin('modules.educacenso_cod_aluno as eca', 'eca.cod_aluno', '=', 'a.cod_aluno')
            ->leftJoin('pmieducar.turma_turno as tturno', 'tturno.id', '=', 'mt.turno_id')
            ->selectRaw('m.cod_matricula as matricula_id')
            ->selectRaw('a.cod_aluno as aluno_id')
            ->selectRaw('m.ano as ano_letivo')
            ->selectRaw('m.aprovado as matricula_aprovado')
            ->selectRaw('p.nome as aluno_nome')
            ->selectRaw('p.email as aluno_email')
            ->selectRaw('e.ref_cod_instituicao as instituicao_id')
            ->selectRaw('e.cod_escola as escola_id')
            ->selectRaw('i.nm_instituicao as instituicao')
            ->selectRaw('COALESCE(ej.fantasia, ec.nm_escola, \'\') as escola')
            ->selectRaw('c.nm_curso as curso')
            ->selectRaw('s.nm_serie as serie')
            ->selectRaw('t.nm_turma as turma')
            ->selectRaw("to_char(mt.data_enturmacao::date, 'DD/MM/YYYY') as data_entrada_turma_br")
            ->selectRaw("to_char(COALESCE(mt.data_exclusao::date, m.data_cancel::date), 'DD/MM/YYYY') as data_fim_turma_br")
            ->selectRaw('fi.nome_social as nome_social_aluno')
            ->selectRaw("to_char(fi.data_nasc::date, 'DD/MM/YYYY') as data_nascimento_br")
            ->selectRaw('fi.sexo as sexo_cadastro')
            ->selectRaw('fi.nacionalidade as nacionalidade_cod')
            ->selectRaw('fi.nis_pis_pasep as nis_num')
            ->selectRaw('fi.cpf as cpf_num')
            ->selectRaw('fi.idpes_responsavel as idpes_responsavel')
            ->selectRaw('a.nm_pai as nm_pai')
            ->selectRaw('a.nm_mae as nm_mae')
            ->selectRaw('a.tipo_responsavel as tipo_responsavel')
            ->selectRaw('a.emancipado as emancipado')
            ->selectRaw('TRIM(BOTH FROM presp.nome) as responsavel_legal_nome')
            ->selectRaw('doc.rg as rg_num')
            ->selectRaw('doc.sigla_uf_exp_rg as rg_uf')
            ->selectRaw('doc.data_exp_rg as data_exp_rg')
            ->selectRaw('eca.cod_aluno_inep as aluno_inep')
            ->selectRaw('tturno.nome as turno_enturmacao')
            ->selectRaw("NULLIF(TRIM(BOTH FROM CONCAT(munnasc.nome, CASE WHEN munnasc.sigla_uf IS NOT NULL AND TRIM(BOTH FROM munnasc.sigla_uf) <> '' THEN ' / ' || munnasc.sigla_uf ELSE '' END)), '') as naturalidade_txt")
            ->selectRaw('m.observacao as observacao_matricula')
            ->selectRaw("to_char(m.data_matricula::date, 'DD/MM/YYYY') as data_matricula_br")
            ->selectRaw('m.semestre as semestre')
            ->selectRaw('m.dependencia as dependencia')
            ->selectRaw('m.ultima_matricula as ultima_matricula_flag')
            ->selectRaw('m.formando as formando')
            ->selectRaw("(select trim(both ' ' from concat('(', fp.ddd::text, ') ', to_char(fp.fone, 'FM99999999999'))) from cadastro.fone_pessoa fp where fp.idpes = p.idpes order by fp.tipo asc limit 1) as telefone_principal")
            ->where('m.cod_matricula', $id)
            ->first();

        return $row;
    }

    private function enrichMatriculaRow(object $m): void
    {
        $m->sexo_desc = match (strtoupper(trim((string) ($m->sexo_cadastro ?? '')))) {
            'M' => 'Masculino',
            'F' => 'Feminino',
            default => null,
        };

        $m->nacionalidade_desc = match ((int) ($m->nacionalidade_cod ?? 0)) {
            1 => 'Brasileira',
            2 => 'Naturalizado brasileiro',
            3 => 'Estrangeira',
            default => null,
        };

        $m->cpf_formatado = $this->formatCpfDigits($m->cpf_num ?? null);
        $m->nis_formatado = $this->formatNisDigits($m->nis_num ?? null);

        $rg = trim((string) ($m->rg_num ?? ''));
        $ufRg = trim((string) ($m->rg_uf ?? ''));
        $m->rg_completo = $rg !== '' ? ($rg . ($ufRg !== '' ? ' / ' . $ufRg : '')) : null;

        $tipo = strtolower(trim((string) ($m->tipo_responsavel ?? '')));
        $m->tipo_responsavel_desc = match ($tipo) {
            'p' => 'Pai',
            'm' => 'Mãe',
            'a' => 'O(a) próprio(a) estudante',
            'o' => 'Outra pessoa',
            '' => null,
            default => 'Outro (' . $tipo . ')',
        };

        $m->emancipado = filter_var($m->emancipado ?? false, FILTER_VALIDATE_BOOLEAN);

        $m->dependencia = filter_var($m->dependencia ?? false, FILTER_VALIDATE_BOOLEAN);
        $m->ultima_matricula_flag = (int) ($m->ultima_matricula_flag ?? 0) === 1;
        $m->formando = filter_var($m->formando ?? false, FILTER_VALIDATE_BOOLEAN);

        $m->situacao_matricula_txt = $this->matriculaSituationLabel((int) ($m->matricula_aprovado ?? 0));

        $m->responsavel_exibicao = $this->deriveResponsavelExibicao($m);
    }

    private function deriveResponsavelExibicao(object $m): string
    {
        $nomeResp = trim((string) ($m->responsavel_legal_nome ?? ''));
        if ($nomeResp !== '') {
            return $nomeResp;
        }

        $tipo = strtolower(trim((string) ($m->tipo_responsavel ?? '')));
        if ($tipo === 'a' && trim((string) ($m->aluno_nome ?? '')) !== '') {
            return trim((string) $m->aluno_nome);
        }
        if ($tipo === 'p' && trim((string) ($m->nm_pai ?? '')) !== '') {
            return trim((string) $m->nm_pai);
        }
        if ($tipo === 'm' && trim((string) ($m->nm_mae ?? '')) !== '') {
            return trim((string) $m->nm_mae);
        }

        return '';
    }

    private function matriculaSituationLabel(int $aprovado): ?string
    {
        if (class_exists(\App\Models\RegistrationStatus::class)) {
            /** @var array<int, string> $map */
            $map = \App\Models\RegistrationStatus::getRegistrationAndEnrollmentStatus();

            return $map[$aprovado] ?? ('Código ' . $aprovado);
        }

        return $aprovado > 0 ? ('Código ' . $aprovado) : null;
    }

    private function formatCpfDigits(mixed $cpf): ?string
    {
        if ($cpf === null || $cpf === '') {
            return null;
        }
        $digits = preg_replace('/\D/', '', (string) $cpf);
        if ($digits === '' || strlen($digits) > 11) {
            return null;
        }
        $digits = str_pad($digits, 11, '0', STR_PAD_LEFT);

        return substr($digits, 0, 3) . '.' . substr($digits, 3, 3) . '.' . substr($digits, 6, 3) . '-' . substr($digits, 9, 2);
    }

    private function formatNisDigits(mixed $nis): ?string
    {
        if ($nis === null || $nis === '') {
            return null;
        }
        $digits = preg_replace('/\D/', '', (string) $nis);
        if ($digits === '' || strlen($digits) > 11) {
            return null;
        }
        $digits = str_pad($digits, 11, '0', STR_PAD_LEFT);

        return substr($digits, 0, 3) . '.' . substr($digits, 3, 5) . '.' . substr($digits, 8, 2) . '-' . substr($digits, 10, 1);
    }

    /**
     * Mesma lógica do boletim (etapas, recuperação “Rc”, faltas por período) + média final consolidada.
     *
     * @return array{etapas_count: int, frequencia: mixed, rows: list<array<string, mixed>>}|null
     */
    private function buildDesempenhoBoletimForFicha(int $matriculaId): ?array
    {
        try {
            /** @var array{etapas_count?: int, frequencia?: mixed, rows?: list<array<string, mixed>>, matricula?: array<string, mixed>} $data */
            $data = app(BoletimService::class)->build($matriculaId, null);
        } catch (\Throwable $e) {
            report($e);

            return null;
        }

        $rows = $data['rows'] ?? [];
        if ($rows === []) {
            return null;
        }

        $notaAlunoId = (int) (DB::table('modules.nota_aluno')
            ->where('matricula_id', $matriculaId)
            ->orderByDesc('id')
            ->value('id') ?? 0);

        $mediasFinais = [];
        if ($notaAlunoId > 0) {
            foreach (DB::table('modules.nota_componente_curricular_media')
                ->where('nota_aluno_id', $notaAlunoId)
                ->get(['componente_curricular_id', 'media_arredondada', 'media']) as $m) {
                $cid = (int) $m->componente_curricular_id;
                $arred = trim((string) ($m->media_arredondada ?? ''));
                $mediasFinais[$cid] = $arred !== '' ? $arred : $this->formatNotaBoletimCell($m->media);
            }
        }

        foreach ($rows as $k => $row) {
            $cid = (int) ($row['id'] ?? 0);
            $rows[$k]['media_final'] = $mediasFinais[$cid] ?? null;

            $sumFaltas = 0;
            foreach ($row['etapas'] ?? [] as $etapaKey => $cell) {
                if ($etapaKey === 'Rc') {
                    continue;
                }
                if (is_array($cell) && array_key_exists('faltas', $cell) && $cell['faltas'] !== null) {
                    $sumFaltas += (int) $cell['faltas'];
                }
            }
            $rcCell = is_array($row['etapas'] ?? null) ? ($row['etapas']['Rc'] ?? null) : null;
            if (is_array($rcCell) && array_key_exists('faltas', $rcCell) && $rcCell['faltas'] !== null) {
                $sumFaltas += (int) $rcCell['faltas'];
            }
            $rows[$k]['faltas_total_anual'] = $sumFaltas > 0 ? $sumFaltas : null;
        }

        return [
            'etapas_count' => (int) ($data['etapas_count'] ?? 0),
            'frequencia' => $data['frequencia'] ?? null,
            'rows' => $rows,
        ];
    }

    /**
     * Disciplinas com nota/falta/carga para a ficha individual.
     * 1) Preferência: histórico escolar oficial (`historico_escolar` + `historico_disciplinas`).
     * 2) Se não houver histórico ou linhas de disciplina: lançamentos do diário (turma/série + médias e faltas).
     *
     * @return list<array{nome: string, nota: mixed, faltas: mixed, carga_horaria: mixed}>
     */
    private function disciplinesForMatricula(int $alunoId, int $matriculaId, int $anoLetivo): array
    {
        if ($matriculaId <= 0) {
            return [];
        }

        if ($alunoId > 0 && $anoLetivo > 0) {
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

            if ($history) {
                $fromHistory = DB::table('pmieducar.historico_disciplinas as d')
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

                if ($fromHistory !== []) {
                    return $fromHistory;
                }
            }
        }

        return $this->disciplinesFromDiarioLancamentos($matriculaId);
    }

    /**
     * Componentes da turma (ou grade escola/série) com média em {@see modules.nota_componente_curricular_media}
     * e total de faltas em {@see modules.falta_componente_curricular}.
     *
     * @return list<array{nome: string, nota: mixed, faltas: mixed, carga_horaria: mixed}>
     */
    private function disciplinesFromDiarioLancamentos(int $matriculaId): array
    {
        $turmaId = $this->activeTurmaIdForMatricula($matriculaId);

        $meta = DB::table('pmieducar.matricula as m')
            ->where('m.cod_matricula', $matriculaId)
            ->selectRaw('m.ref_ref_cod_serie as serie_id')
            ->selectRaw('m.ref_ref_cod_escola as escola_id')
            ->first();

        $serieId = (int) ($meta->serie_id ?? 0);
        $escolaId = (int) ($meta->escola_id ?? 0);

        $componentRows = collect();
        if ($turmaId > 0) {
            $componentRows = DB::table('modules.componente_curricular_turma as cct')
                ->join('modules.componente_curricular as cc', 'cc.id', '=', 'cct.componente_curricular_id')
                ->where('cct.turma_id', $turmaId)
                ->orderBy('cc.nome')
                ->get(['cct.componente_curricular_id', 'cc.nome', 'cct.carga_horaria']);
        }

        if ($componentRows->isEmpty() && $serieId > 0 && $escolaId > 0) {
            $componentRows = DB::table('pmieducar.escola_serie_disciplina as esd')
                ->join('modules.componente_curricular as cc', 'cc.id', '=', 'esd.ref_cod_disciplina')
                ->where('esd.ref_ref_cod_serie', $serieId)
                ->where('esd.ref_ref_cod_escola', $escolaId)
                ->where('esd.ativo', 1)
                ->orderBy('cc.nome')
                ->get(['esd.ref_cod_disciplina as componente_curricular_id', 'cc.nome', 'esd.carga_horaria']);
        }

        if ($componentRows->isEmpty()) {
            return [];
        }

        $notaAlunoId = (int) (DB::table('modules.nota_aluno')
            ->where('matricula_id', $matriculaId)
            ->orderByDesc('id')
            ->value('id') ?? 0);

        $medias = [];
        if ($notaAlunoId > 0) {
            foreach (DB::table('modules.nota_componente_curricular_media')
                ->where('nota_aluno_id', $notaAlunoId)
                ->get(['componente_curricular_id', 'media_arredondada', 'media']) as $m) {
                $cid = (int) $m->componente_curricular_id;
                $arred = trim((string) ($m->media_arredondada ?? ''));
                if ($arred !== '') {
                    $medias[$cid] = $arred;

                    continue;
                }
                $medias[$cid] = $this->formatNotaBoletimCell($m->media);
            }
        }

        $faltaAlunoId = (int) (DB::table('modules.falta_aluno')
            ->where('matricula_id', $matriculaId)
            ->orderByDesc('id')
            ->value('id') ?? 0);

        $faltasSum = [];
        if ($faltaAlunoId > 0) {
            foreach (DB::table('modules.falta_componente_curricular')
                ->selectRaw('componente_curricular_id, COALESCE(SUM(quantidade), 0)::int as total')
                ->where('falta_aluno_id', $faltaAlunoId)
                ->groupBy('componente_curricular_id')
                ->get() as $r) {
                $faltasSum[(int) $r->componente_curricular_id] = (int) $r->total;
            }
        }

        $out = [];
        foreach ($componentRows as $c) {
            $cid = (int) $c->componente_curricular_id;
            $ch = $c->carga_horaria;
            $out[] = [
                'nome' => (string) $c->nome,
                'nota' => $medias[$cid] ?? '—',
                'faltas' => array_key_exists($cid, $faltasSum) ? $faltasSum[$cid] : null,
                'carga_horaria' => $this->formatCargaHorariaFicha($ch),
            ];
        }

        return $out;
    }

    private function activeTurmaIdForMatricula(int $matriculaId): int
    {
        $row = DB::selectOne(
            'SELECT mt.ref_cod_turma
             FROM pmieducar.matricula_turma mt
             WHERE mt.ref_cod_matricula = ? AND mt.ativo = 1
             ORDER BY (CASE WHEN mt.transferido IS TRUE THEN 1 ELSE 0 END) DESC, mt.sequencial DESC
             LIMIT 1',
            [$matriculaId]
        );

        return (int) ($row->ref_cod_turma ?? 0);
    }

    private function formatNotaBoletimCell(mixed $media): ?string
    {
        if ($media === null || $media === '') {
            return null;
        }
        if (is_numeric($media)) {
            return str_replace('.', ',', (string) round((float) $media, 1));
        }

        return (string) $media;
    }

    private function formatCargaHorariaFicha(mixed $ch): mixed
    {
        if ($ch === null || $ch === '') {
            return null;
        }
        if (is_numeric($ch)) {
            $n = (float) $ch;
            $s = fmod($n, 1.0) === 0.0 ? (string) (int) $n : rtrim(rtrim(sprintf('%.1f', $n), '0'), '.');

            return $s . 'h';
        }

        return (string) $ch;
    }
}
