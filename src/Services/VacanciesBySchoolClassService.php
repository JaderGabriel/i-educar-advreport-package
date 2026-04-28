<?php

namespace iEducar\Packages\AdvancedReports\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class VacanciesBySchoolClassService
{
    /**
     * @return array{items: Collection<int,object>, summary: array<string,int>}
     */
    public function build(?int $year, ?int $institutionId, ?int $schoolId, ?int $courseId, ?int $gradeId, ?int $schoolClassId): array
    {
        $query = DB::table('pmieducar.turma as t')
            ->join('pmieducar.escola as e', 'e.cod_escola', '=', 't.ref_ref_cod_escola')
            ->leftJoin('cadastro.pessoa as ep', 'ep.idpes', '=', 'e.ref_idpes')
            ->leftJoin('cadastro.juridica as ej', 'ej.idpes', '=', 'ep.idpes')
            ->leftJoin('pmieducar.escola_complemento as ec', 'ec.ref_cod_escola', '=', 'e.cod_escola')
            ->leftJoin('pmieducar.instituicao as i', 'i.cod_instituicao', '=', 'e.ref_cod_instituicao')
            ->leftJoin('pmieducar.curso as c', 'c.cod_curso', '=', 't.ref_cod_curso')
            ->leftJoin('pmieducar.serie as s', 's.cod_serie', '=', 't.ref_ref_cod_serie')
            ->leftJoin('pmieducar.turma_turno as tt', 'tt.id', '=', 't.turma_turno_id')
            ->leftJoin('pmieducar.matricula_turma as mt', function ($join) {
                $join->on('mt.ref_cod_turma', '=', 't.cod_turma');
                $join->where('mt.ativo', 1);
            })
            ->leftJoin('pmieducar.matricula as m', function ($join) {
                $join->on('m.cod_matricula', '=', 'mt.ref_cod_matricula');
                $join->where('m.dependencia', false);
            })
            ->where('t.ativo', 1);

        if ($year) {
            $query->where('t.ano', $year);
        }

        if ($institutionId) {
            $query->where('e.ref_cod_instituicao', $institutionId);
        }

        if ($schoolId) {
            $query->where('t.ref_ref_cod_escola', $schoolId);
        }

        if ($courseId) {
            $query->where('t.ref_cod_curso', $courseId);
        }

        if ($gradeId) {
            // para multisseriadas, este campo pode não refletir todas as séries; por ora, filtra pelo padrão
            $query->where('t.ref_ref_cod_serie', $gradeId);
        }

        if ($schoolClassId) {
            $query->where('t.cod_turma', $schoolClassId);
        }

        $items = $query
            ->groupBy([
                't.cod_turma',
                't.nm_turma',
                't.ano',
                't.max_aluno',
                'e.cod_escola',
                'ej.fantasia',
                'ec.nm_escola',
                'i.nm_instituicao',
                'c.nm_curso',
                's.nm_serie',
                'tt.nome',
            ])
            ->orderByRaw('COALESCE(ej.fantasia, ec.nm_escola, \'\')')
            ->orderBy('c.nm_curso')
            ->orderBy('s.nm_serie')
            ->orderBy('t.nm_turma')
            ->selectRaw('t.cod_turma as turma_id')
            ->selectRaw('t.nm_turma as turma')
            ->selectRaw('t.ano as ano_letivo')
            ->selectRaw('COALESCE(t.max_aluno, 0) as capacidade')
            ->selectRaw('COALESCE(ej.fantasia, ec.nm_escola, \'\') as escola')
            ->selectRaw('COALESCE(i.nm_instituicao, \'\') as instituicao')
            ->selectRaw('COALESCE(c.nm_curso, \'\') as curso')
            ->selectRaw('COALESCE(s.nm_serie, \'\') as serie')
            ->selectRaw('COALESCE(tt.nome, \'\') as turno')
            ->selectRaw('COUNT(m.cod_matricula) as matriculados')
            ->selectRaw('GREATEST(COALESCE(t.max_aluno, 0) - COUNT(m.cod_matricula), 0) as vagas')
            ->get();

        $summary = [
            'turmas' => (int) $items->count(),
            'capacidade' => (int) $items->sum('capacidade'),
            'matriculados' => (int) $items->sum('matriculados'),
            'vagas' => (int) $items->sum('vagas'),
        ];

        return compact('items', 'summary');
    }
}

