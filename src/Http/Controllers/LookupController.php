<?php

namespace iEducar\Packages\AdvancedReports\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class LookupController
{
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
            ->selectRaw('m.cod_matricula as id')
            ->selectRaw('p.nome as aluno_nome')
            ->selectRaw('m.ano as ano_letivo')
            ->selectRaw('e.nome as escola')
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
}

