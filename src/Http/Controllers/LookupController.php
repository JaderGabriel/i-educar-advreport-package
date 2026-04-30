<?php

namespace iEducar\Packages\AdvancedReports\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class LookupController
{
    /**
     * Origem em pmieducar.historico_escolar: o núcleo grava 1 no processamento
     * (ProcessamentoApiController) e no cadastro manual (educar_historico_escolar_cad);
     * NULL/0 aparecem em legados ou fluxos que não persistem origem numérica.
     */
    private function applyHistoricoEscolarOrigemNativo($query, string $alias = 'he'): void
    {
        $col = "{$alias}.origem";
        $query->where(function ($w) use ($col) {
            $w->whereNull($col)->orWhereIn($col, [0, 1]);
        });
    }

    private function historyNativeMetaQuery(int $alunoId)
    {
        // Rotina nativa: histórico no i-Educar (ativo, vinculado à matrícula quando existir).
        return DB::table('pmieducar.historico_escolar as he')
            ->where('he.ref_cod_aluno', $alunoId)
            ->where('he.ativo', 1)
            ->whereNotNull('he.ref_cod_matricula')
            ->tap(fn ($q) => $this->applyHistoricoEscolarOrigemNativo($q, 'he'))
            ->orderByDesc('he.ano')
            ->orderByDesc('he.sequencial');
    }

    public function readySchoolHistories(Request $request)
    {
        $turmaId = (int) $request->get('turma_id');
        $escolaId = (int) $request->get('escola_id');
        $instituicaoId = (int) $request->get('instituicao_id');
        $year = $request->get('ano') ? (int) $request->get('ano') : null;
        $cursoId = $request->get('curso_id') ? (int) $request->get('curso_id') : null;
        $serieId = $request->get('serie_id') ? (int) $request->get('serie_id') : null;

        // Carregamento em nível de escola: exige ao menos ano + instituição + escola.
        if (!$year || !$escolaId || !$instituicaoId) {
            return response()->json([]);
        }

        $metaSub = DB::table('pmieducar.historico_escolar as he')
            ->where('he.ativo', 1)
            ->whereNotNull('he.ref_cod_matricula')
            ->tap(fn ($q) => $this->applyHistoricoEscolarOrigemNativo($q, 'he'))
            ->where('he.ref_cod_escola', $escolaId)
            ->where('he.ref_cod_instituicao', $instituicaoId)
            ->where('he.ano', $year)
            ->selectRaw('DISTINCT ON (he.ref_cod_aluno) he.ref_cod_aluno')
            ->selectRaw('he.ano as ano_historico')
            ->selectRaw('he.registro as registro')
            ->selectRaw('he.livro as livro')
            ->selectRaw('he.folha as folha')
            ->orderByRaw('he.ref_cod_aluno, he.ano DESC, he.sequencial DESC');

        // Base: alunos que possuem histórico escolar nativo (pmieducar.historico_escolar)
        // para a instituição/escola/ano informados. Os filtros de curso/série/turma são
        // refinamentos via matrícula (quando disponíveis).
        $q = DB::table('pmieducar.aluno as a')
            ->join('cadastro.pessoa as p', 'p.idpes', '=', 'a.ref_idpes')
            ->joinSub($metaSub, 'hm', function ($j) {
                $j->on('hm.ref_cod_aluno', '=', 'a.cod_aluno');
            });

        if ($cursoId || $serieId || $turmaId) {
            $q->join('pmieducar.matricula as m', function ($j) use ($year, $escolaId) {
                $j->on('m.ref_cod_aluno', '=', 'a.cod_aluno')
                    ->where('m.ativo', 1)
                    ->where('m.dependencia', false)
                    ->where('m.ano', $year)
                    ->where('m.ref_ref_cod_escola', $escolaId);
            })
                ->leftJoin('pmieducar.curso as c', 'c.cod_curso', '=', 'm.ref_cod_curso')
                ->leftJoin('pmieducar.serie as s', 's.cod_serie', '=', 'm.ref_ref_cod_serie')
                ->when($cursoId, fn ($qq) => $qq->where('m.ref_cod_curso', $cursoId))
                ->when($serieId, fn ($qq) => $qq->where('m.ref_ref_cod_serie', $serieId));

            if ($turmaId) {
                $q->join('pmieducar.matricula_turma as mt', function ($join) use ($turmaId) {
                    $join->on('mt.ref_cod_matricula', '=', 'm.cod_matricula')
                        ->where('mt.ativo', 1)
                        ->where('mt.ref_cod_turma', $turmaId);
                });
            }
        } else {
            $q->leftJoin('pmieducar.matricula as m', function ($j) use ($year, $escolaId) {
                $j->on('m.ref_cod_aluno', '=', 'a.cod_aluno')
                    ->where('m.ativo', 1)
                    ->where('m.dependencia', false)
                    ->where('m.ano', $year)
                    ->where('m.ref_ref_cod_escola', $escolaId);
            })
                ->leftJoin('pmieducar.curso as c', 'c.cod_curso', '=', 'm.ref_cod_curso')
                ->leftJoin('pmieducar.serie as s', 's.cod_serie', '=', 'm.ref_ref_cod_serie');
        }

        $rows = $q
            ->selectRaw('a.cod_aluno as aluno_id')
            ->selectRaw('p.nome as aluno_nome')
            ->selectRaw('hm.ano_historico as ano')
            ->selectRaw('hm.registro as registro')
            ->selectRaw('hm.livro as livro')
            ->selectRaw('hm.folha as folha')
            ->selectRaw('c.nm_curso as curso')
            ->selectRaw('s.nm_serie as serie')
            ->orderBy('p.nome')
            ->limit(500)
            ->get();

        $items = $rows->map(static function ($r) {
            return [
                'aluno_id' => (int) $r->aluno_id,
                'aluno_nome' => (string) $r->aluno_nome,
                'ano' => (int) ($r->ano ?? 0),
                'curso' => (string) ($r->curso ?? ''),
                'serie' => (string) ($r->serie ?? ''),
                'registro' => (string) ($r->registro ?? ''),
                'livro' => (string) ($r->livro ?? ''),
                'folha' => (string) ($r->folha ?? ''),
            ];
        })->values();

        return response()->json($items);
    }

    public function matriculas(Request $request)
    {
        $q = trim((string) $request->get('q', ''));
        if (mb_strlen($q) < 3) {
            return response()->json([]);
        }

        $qLike = '%' . str_replace('%', '', $q) . '%';
        $qId = ctype_digit($q) ? (int) $q : null;

        $rows = DB::table('pmieducar.matricula as m')
            ->join('pmieducar.aluno as a', 'a.cod_aluno', '=', 'm.ref_cod_aluno')
            ->join('cadastro.pessoa as p', 'p.idpes', '=', 'a.ref_idpes')
            ->leftJoin('pmieducar.escola as e', 'e.cod_escola', '=', 'm.ref_ref_cod_escola')
            ->leftJoin('cadastro.pessoa as ep', 'ep.idpes', '=', 'e.ref_idpes')
            ->leftJoin('cadastro.juridica as ej', 'ej.idpes', '=', 'ep.idpes')
            ->leftJoin('pmieducar.escola_complemento as ec', 'ec.ref_cod_escola', '=', 'e.cod_escola')
            ->selectRaw('m.cod_matricula as id')
            ->selectRaw('p.nome as aluno_nome')
            ->selectRaw('m.ano as ano_letivo')
            ->selectRaw('COALESCE(ej.fantasia, ec.nm_escola, \'\') as escola')
            ->when($qId, fn ($qq) => $qq->where('m.cod_matricula', $qId))
            ->when(!$qId, fn ($qq) => $qq->where('p.nome', 'ILIKE', $qLike))
            ->orderByDesc('m.ano')
            ->limit(20)
            ->get();

        $items = $rows->map(static function ($r) {
            $label = trim(sprintf(
                '%d - %s (%s) %s',
                (int) $r->id,
                (string) $r->aluno_nome,
                (string) ($r->ano_letivo ?? ''),
                (string) ($r->escola ? ('- ' . $r->escola) : '')
            ));

            return [
                'id' => (int) $r->id,
                'label' => Str::squish($label),
            ];
        })->values();

        return response()->json($items);
    }

    public function alunos(Request $request)
    {
        $q = trim((string) $request->get('q', ''));
        if (mb_strlen($q) < 3) {
            return response()->json([]);
        }

        $qLike = '%' . str_replace('%', '', $q) . '%';
        $qId = ctype_digit($q) ? (int) $q : null;

        $rows = DB::table('pmieducar.aluno as a')
            ->join('cadastro.pessoa as p', 'p.idpes', '=', 'a.ref_idpes')
            ->selectRaw('a.cod_aluno as id')
            ->selectRaw('p.nome as nome')
            ->when($qId, fn ($qq) => $qq->where('a.cod_aluno', $qId))
            ->when(!$qId, fn ($qq) => $qq->where('p.nome', 'ILIKE', $qLike))
            ->orderBy('p.nome')
            ->limit(20)
            ->get();

        $items = $rows->map(static function ($r) {
            return [
                'id' => (int) $r->id,
                'label' => Str::squish(((int) $r->id) . ' - ' . (string) $r->nome),
            ];
        })->values();

        return response()->json($items);
    }

    public function users(Request $request)
    {
        $q = trim((string) $request->get('q', ''));
        if (mb_strlen($q) < 3) {
            return response()->json([]);
        }

        $qLike = '%' . str_replace('%', '', $q) . '%';
        $qId = ctype_digit($q) ? (int) $q : null;

        // Usuários do sistema (pmieducar.usuario) vinculados a uma pessoa (cadastro.pessoa).
        $rows = DB::table('pmieducar.usuario as u')
            ->join('cadastro.pessoa as p', 'p.idpes', '=', 'u.cod_usuario')
            ->selectRaw('u.cod_usuario as id')
            ->selectRaw('p.nome as nome')
            ->when($qId, fn ($qq) => $qq->where('u.cod_usuario', $qId))
            ->when(!$qId, fn ($qq) => $qq->where('p.nome', 'ILIKE', $qLike))
            ->orderBy('p.nome')
            ->limit(20)
            ->get();

        $items = $rows->map(static function ($r) {
            return [
                'id' => (int) $r->id,
                'label' => Str::squish(((int) $r->id) . ' - ' . (string) $r->nome),
            ];
        })->values();

        return response()->json($items);
    }

    public function classEnrollments(Request $request)
    {
        $turmaId = (int) $request->get('turma_id');
        if (!$turmaId) {
            return response()->json([]);
        }

        $document = (string) $request->get('document', '');
        $year = $request->get('ano') ? (int) $request->get('ano') : null;
        $onlyWithHistory = $request->boolean('only_with_history');
        $situacaoMatricula = $request->get('situacao');
        $situacaoId = $situacaoMatricula !== null && $situacaoMatricula !== '' ? (int) $situacaoMatricula : null;

        $q = DB::table('pmieducar.matricula_turma as mt')
            ->join('pmieducar.matricula as m', 'm.cod_matricula', '=', 'mt.ref_cod_matricula')
            ->join('pmieducar.aluno as a', 'a.cod_aluno', '=', 'm.ref_cod_aluno')
            ->join('cadastro.pessoa as p', 'p.idpes', '=', 'a.ref_idpes')
            ->where('mt.ref_cod_turma', $turmaId)
            ->where('m.dependencia', false)
            ->when($year, fn ($qq) => $qq->where('m.ano', $year));

        // Transferência: a enturmação na turma de origem costuma ficar inativa (mt.ativo=0) e
        // relatorio.view_situacao exige matricula.ativo=1 — não usar o join com a view aqui.
        if ($document === 'transfer_guide') {
            $q->where(function ($w) {
                $w->where('m.aprovado', 4)
                    ->orWhere('mt.transferido', true);
            });
        } else {
            $q->where('mt.ativo', 1)
                ->where('m.ativo', 1);
        }

        if ($onlyWithHistory) {
            $q->whereExists(function ($sub) {
                $sub->selectRaw('1')
                    ->from('pmieducar.historico_escolar as he')
                    ->whereColumn('he.ref_cod_aluno', 'a.cod_aluno')
                    ->where('he.ativo', 1)
                    ->whereNotNull('he.ref_cod_matricula')
                    ->tap(fn ($q) => $this->applyHistoricoEscolarOrigemNativo($q, 'he'));
            });
        }

        if (in_array($document, ['diploma', 'certificate'], true)) {
            $q->join('relatorio.view_situacao as vs', function ($j) {
                $j->on('vs.cod_matricula', '=', 'm.cod_matricula')
                    ->on('vs.cod_turma', '=', 'mt.ref_cod_turma')
                    ->on('vs.sequencial', '=', 'mt.sequencial');
            });
            if ($situacaoId !== null && $situacaoId > 0) {
                $q->where('vs.cod_situacao', $situacaoId);
            } else {
                $q->whereIn('vs.cod_situacao', [1, 12, 13]);
            }
        } elseif ($document === 'declaration_conclusion') {
            $q->join('relatorio.view_situacao as vs', function ($j) {
                $j->on('vs.cod_matricula', '=', 'm.cod_matricula')
                    ->on('vs.cod_turma', '=', 'mt.ref_cod_turma')
                    ->on('vs.sequencial', '=', 'mt.sequencial');
            })->whereIn('vs.cod_situacao', [1, 12, 13]);
        }

        $rows = $q
            ->selectRaw('m.cod_matricula as matricula_id')
            ->selectRaw('a.cod_aluno as aluno_id')
            ->selectRaw('p.nome as aluno_nome')
            ->orderBy('p.nome')
            ->limit(400)
            ->get();

        $items = $rows->map(static function ($r) {
            return [
                'matricula_id' => (int) $r->matricula_id,
                'aluno_id' => (int) $r->aluno_id,
                'label' => Str::squish((string) $r->aluno_nome . ' — matrícula ' . (int) $r->matricula_id),
            ];
        })->values();

        return response()->json($items);
    }

    public function classEnrollmentCounters(Request $request)
    {
        $turmaId = (int) $request->get('turma_id');
        if (!$turmaId) {
            return response()->json([]);
        }

        $document = (string) $request->get('document', '');
        $year = $request->get('ano') ? (int) $request->get('ano') : null;
        $situacaoMatricula = $request->get('situacao');
        $situacaoId = $situacaoMatricula !== null && $situacaoMatricula !== '' ? (int) $situacaoMatricula : null;

        $eligibleStatuses = match ($document) {
            'diploma', 'certificate', 'declaration_conclusion' => [1, 12, 13],
            'transfer_guide' => [4],
            default => null,
        };

        if ($document === 'transfer_guide') {
            $base = DB::table('pmieducar.matricula_turma as mt')
                ->join('pmieducar.matricula as m', 'm.cod_matricula', '=', 'mt.ref_cod_matricula')
                ->where('mt.ref_cod_turma', $turmaId)
                ->where('m.dependencia', false)
                ->when($year, fn ($qq) => $qq->where('m.ano', $year))
                ->where(function ($w) {
                    $w->where('m.aprovado', 4)
                        ->orWhere('mt.transferido', true);
                });

            $eligible = (int) (clone $base)->distinct('m.cod_matricula')->count('m.cod_matricula');
            $total = $eligible;

            return response()->json([
                'total' => $total,
                'eligible' => $eligible,
                'ineligible' => 0,
                'eligible_statuses' => $eligibleStatuses,
                'by_status' => [4 => $eligible],
            ]);
        }

        $base = DB::table('pmieducar.matricula_turma as mt')
            ->join('pmieducar.matricula as m', 'm.cod_matricula', '=', 'mt.ref_cod_matricula')
            ->where('mt.ref_cod_turma', $turmaId)
            ->where('mt.ativo', 1)
            ->where('m.ativo', 1)
            ->where('m.dependencia', false)
            ->when($year, fn ($qq) => $qq->where('m.ano', $year))
            ->leftJoin('relatorio.view_situacao as vs', function ($j) {
                $j->on('vs.cod_matricula', '=', 'm.cod_matricula')
                    ->on('vs.cod_turma', '=', 'mt.ref_cod_turma')
                    ->on('vs.sequencial', '=', 'mt.sequencial');
            });

        $total = (int) (clone $base)->distinct('m.cod_matricula')->count('m.cod_matricula');

        $eligibleBase = clone $base;
        if (in_array($document, ['diploma', 'certificate'], true) && $situacaoId !== null && $situacaoId > 0) {
            $eligibleBase->where('vs.cod_situacao', $situacaoId);
            $eligible = (int) $eligibleBase->distinct('m.cod_matricula')->count('m.cod_matricula');
        } else {
            $eligible = $eligibleStatuses
                ? (int) (clone $base)->whereIn('vs.cod_situacao', $eligibleStatuses)->distinct('m.cod_matricula')->count('m.cod_matricula')
                : $total;
        }

        $byStatusRows = (clone $base)
            ->selectRaw('COALESCE(vs.cod_situacao, 0) as cod_situacao')
            ->selectRaw('COUNT(DISTINCT m.cod_matricula) as total')
            ->groupBy('cod_situacao')
            ->orderBy('cod_situacao')
            ->get();

        $byStatus = [];
        foreach ($byStatusRows as $r) {
            $byStatus[(int) $r->cod_situacao] = (int) $r->total;
        }

        return response()->json([
            'total' => $total,
            'eligible' => $eligible,
            'ineligible' => max(0, $total - $eligible),
            'eligible_statuses' => $eligibleStatuses,
            'by_status' => $byStatus,
        ]);
    }

    public function schoolHistoryMeta(Request $request)
    {
        $alunoId = (int) $request->get('aluno_id');
        if (!$alunoId) {
            return response()->json([
                'ok' => false,
                'message' => 'Informe o aluno.',
            ], 422);
        }

        $row = $this->historyNativeMetaQuery($alunoId)
            ->selectRaw('he.livro as book')
            ->selectRaw('he.folha as page')
            ->selectRaw('he.registro as record')
            ->selectRaw('he.ano as year')
            ->first();

        if (!$row) {
            return response()->json([
                'ok' => false,
                'message' => 'Nenhum histórico nativo encontrado para este aluno.',
            ], 404);
        }

        return response()->json([
            'ok' => true,
            'book' => (string) ($row->book ?? ''),
            'page' => (string) ($row->page ?? ''),
            'record' => (string) ($row->record ?? ''),
            'year' => (int) ($row->year ?? 0),
        ]);
    }
}
