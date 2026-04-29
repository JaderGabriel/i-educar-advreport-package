<?php

namespace iEducar\Packages\AdvancedReports\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class LookupController
{
    private function historyNativeMetaQuery(int $alunoId)
    {
        // “Rotina nativa”: histórico interno (origem = interno) vinculado a uma matrícula.
        // Critério conservador:
        // - ativo=1
        // - origem interno (NULL/false/0)
        // - ref_cod_matricula preenchido
        return DB::table('pmieducar.historico_escolar as he')
            ->where('he.ref_cod_aluno', $alunoId)
            ->where('he.ativo', 1)
            ->whereNotNull('he.ref_cod_matricula')
            ->where(function ($w) {
                $w->whereNull('he.origem')->orWhere('he.origem', 0);
            })
            ->orderByDesc('he.ano')
            ->orderByDesc('he.sequencial');
    }

    public function readySchoolHistories(Request $request)
    {
        $turmaId = (int) $request->get('turma_id');
        $escolaId = (int) $request->get('escola_id');
        $year = $request->get('ano') ? (int) $request->get('ano') : null;
        $cursoId = $request->get('curso_id') ? (int) $request->get('curso_id') : null;
        $serieId = $request->get('serie_id') ? (int) $request->get('serie_id') : null;

        // Carregamento em nível de escola: exige ao menos ano + escola.
        if (!$year || !$escolaId) {
            return response()->json([]);
        }

        $metaSub = DB::table('pmieducar.historico_escolar as he')
            ->where('he.ativo', 1)
            ->whereNotNull('he.ref_cod_matricula')
            ->where(function ($w) {
                $w->whereNull('he.origem')->orWhere('he.origem', 0);
            })
            ->selectRaw('DISTINCT ON (he.ref_cod_aluno) he.ref_cod_aluno')
            ->selectRaw('he.ano as ano_historico')
            ->selectRaw('he.registro as registro')
            ->selectRaw('he.livro as livro')
            ->selectRaw('he.folha as folha')
            ->orderByRaw('he.ref_cod_aluno, he.ano DESC, he.sequencial DESC');

        $q = DB::table('pmieducar.matricula as m')
            ->join('pmieducar.aluno as a', 'a.cod_aluno', '=', 'm.ref_cod_aluno')
            ->join('cadastro.pessoa as p', 'p.idpes', '=', 'a.ref_idpes')
            ->joinSub($metaSub, 'hm', function ($j) {
                $j->on('hm.ref_cod_aluno', '=', 'a.cod_aluno');
            })
            ->leftJoin('pmieducar.curso as c', function ($j) {
                $j->on('c.cod_curso', '=', 'm.ref_cod_curso');
            })
            ->leftJoin('pmieducar.serie as s', function ($j) {
                $j->on('s.cod_serie', '=', 'm.ref_ref_cod_serie');
            })
            ->where('m.ativo', 1)
            ->where('m.dependencia', false)
            ->where('m.ano', $year)
            ->where('m.ref_ref_cod_escola', $escolaId)
            ->when($cursoId, fn ($qq) => $qq->where('m.ref_cod_curso', $cursoId))
            ->when($serieId, fn ($qq) => $qq->where('m.ref_ref_cod_serie', $serieId));

        if ($turmaId) {
            $q->join('pmieducar.matricula_turma as mt', function ($join) use ($turmaId) {
                $join->on('mt.ref_cod_matricula', '=', 'm.cod_matricula')
                    ->where('mt.ativo', 1)
                    ->where('mt.ref_cod_turma', $turmaId);
            });
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

        $q = DB::table('pmieducar.matricula_turma as mt')
            ->join('pmieducar.matricula as m', 'm.cod_matricula', '=', 'mt.ref_cod_matricula')
            ->join('pmieducar.aluno as a', 'a.cod_aluno', '=', 'm.ref_cod_aluno')
            ->join('cadastro.pessoa as p', 'p.idpes', '=', 'a.ref_idpes')
            ->where('mt.ref_cod_turma', $turmaId)
            ->where('mt.ativo', 1)
            ->where('m.ativo', 1)
            ->where('m.dependencia', false)
            ->when($year, fn ($qq) => $qq->where('m.ano', $year));

        if ($onlyWithHistory) {
            $q->whereExists(function ($sub) {
                $sub->selectRaw('1')
                    ->from('pmieducar.historico_escolar as he')
                    ->whereColumn('he.ref_cod_aluno', 'a.cod_aluno')
                    ->where('he.ativo', 1)
                    ->whereNotNull('he.ref_cod_matricula')
                    ->where(function ($w) {
                        $w->whereNull('he.origem')->orWhere('he.origem', 0);
                    });
            });
        }

        if (in_array($document, ['diploma', 'certificate'], true)) {
            $q->join('relatorio.view_situacao as vs', function ($j) {
                $j->on('vs.cod_matricula', '=', 'm.cod_matricula')
                    ->on('vs.cod_turma', '=', 'mt.ref_cod_turma')
                    ->on('vs.sequencial', '=', 'mt.sequencial');
            })->whereIn('vs.cod_situacao', [1, 12, 13]);
        } elseif ($document === 'transfer_guide') {
            $q->join('relatorio.view_situacao as vs', function ($j) {
                $j->on('vs.cod_matricula', '=', 'm.cod_matricula')
                    ->on('vs.cod_turma', '=', 'mt.ref_cod_turma')
                    ->on('vs.sequencial', '=', 'mt.sequencial');
            })->where('vs.cod_situacao', 4);
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

