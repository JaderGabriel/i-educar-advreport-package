<?php

namespace iEducar\Packages\AdvancedReports\Services;

use Illuminate\Support\Facades\DB;

class AgeDistortionService
{
    public function getFilters(?int $year = null, ?int $institutionId = null, ?int $schoolId = null, ?int $courseId = null): array
    {
        return app(AdvancedReportsFilterService::class)->getFilters($year, $institutionId, $schoolId, $courseId);
    }

    /**
     * Retorna dados agregados para distorção idade/série, inspirado no relatório Jasper.
     *
     * Regras (compatíveis com o relatório Portabilis):
     * - idade = ano - ano_nascimento (aproximação por ano civil, sem mês/dia)
     * - considera idades entre 5 e 17
     *
     * @return array<string, mixed>
     */
    public function buildData(int $year, int $institutionId, int $courseId, ?int $schoolId = null, ?int $gradeId = null, int $situation = 0): array
    {
        $rows = DB::table('pmieducar.matricula as m')
            ->join('pmieducar.matricula_turma as mt', 'mt.ref_cod_matricula', '=', 'm.cod_matricula')
            ->join('relatorio.view_situacao as vs', function ($j) {
                $j->on('vs.cod_matricula', '=', 'm.cod_matricula')
                    ->on('vs.cod_turma', '=', 'mt.ref_cod_turma')
                    ->on('vs.sequencial', '=', 'mt.sequencial');
            })
            ->join('pmieducar.aluno as a', 'a.cod_aluno', '=', 'm.ref_cod_aluno')
            ->join('cadastro.fisica as f', 'f.idpes', '=', 'a.ref_idpes')
            ->join('pmieducar.serie as s', 's.cod_serie', '=', 'm.ref_ref_cod_serie')
            ->join('pmieducar.curso as c', 'c.cod_curso', '=', 'm.ref_cod_curso')
            ->join('pmieducar.escola as e', 'e.cod_escola', '=', 'm.ref_ref_cod_escola')
            ->where('m.ativo', 1)
            ->where('a.ativo', 1)
            ->where('s.ativo', 1)
            ->where('m.ano', $year)
            ->where('m.ref_cod_curso', $courseId)
            ->when($schoolId, fn ($q) => $q->where('m.ref_ref_cod_escola', $schoolId))
            ->when($gradeId, fn ($q) => $q->where('m.ref_ref_cod_serie', $gradeId))
            ->when($situation > 0, fn ($q) => $q->where('vs.cod_situacao', $situation))
            ->where('e.ref_cod_instituicao', $institutionId)
            ->selectRaw('m.ref_ref_cod_serie as grade_id')
            ->selectRaw('s.nm_serie as grade_name')
            ->selectRaw('COALESCE(s.idade_ideal, 0) as ideal_age')
            ->selectRaw('(:year - EXTRACT(YEAR FROM f.data_nasc)::int) as age', ['year' => $year])
            ->selectRaw('COUNT(DISTINCT m.cod_matricula) as total')
            ->whereRaw('(:year - EXTRACT(YEAR FROM f.data_nasc)::int) > 4', ['year' => $year])
            ->whereRaw('(:year - EXTRACT(YEAR FROM f.data_nasc)::int) < 18', ['year' => $year])
            ->groupBy('m.ref_ref_cod_serie', 's.nm_serie', 's.idade_ideal', 'age')
            ->orderBy('s.nm_serie')
            ->orderBy('age')
            ->get();

        // Agrega em PHP para facilitar percentuais.
        $byGrade = [];
        foreach ($rows as $r) {
            $gid = (int) $r->grade_id;
            if (!isset($byGrade[$gid])) {
                $byGrade[$gid] = [
                    'grade_id' => $gid,
                    'grade_name' => (string) $r->grade_name,
                    'ideal_age' => (int) $r->ideal_age,
                    'total_students' => 0,
                    'ages' => [],
                ];
            }

            $age = (int) $r->age;
            $count = (int) $r->total;
            $byGrade[$gid]['total_students'] += $count;
            $byGrade[$gid]['ages'][$age] = ($byGrade[$gid]['ages'][$age] ?? 0) + $count;
        }

        // Normalize: preencher idades 5..17
        foreach ($byGrade as &$g) {
            for ($age = 5; $age <= 17; $age++) {
                $g['ages'][$age] = $g['ages'][$age] ?? 0;
            }
            ksort($g['ages']);

            $ideal = (int) $g['ideal_age'];
            $idealCount = $ideal >= 5 && $ideal <= 17 ? (int) ($g['ages'][$ideal] ?? 0) : 0;
            $g['ideal_count'] = $idealCount;
            $g['distortion_count'] = max(0, (int) $g['total_students'] - $idealCount);
            $g['distortion_pct'] = $g['total_students'] > 0
                ? round(($g['distortion_count'] * 100) / $g['total_students'], 2)
                : 0.0;
        }
        unset($g);

        // Sumário geral
        $summaryTotal = array_sum(array_map(fn ($g) => (int) $g['total_students'], $byGrade));
        $summaryDistortion = array_sum(array_map(fn ($g) => (int) $g['distortion_count'], $byGrade));

        return [
            'summary' => [
                'total_students' => (int) $summaryTotal,
                'distortion_students' => (int) $summaryDistortion,
                'distortion_pct' => $summaryTotal > 0 ? round(($summaryDistortion * 100) / $summaryTotal, 2) : 0.0,
            ],
            'grades' => array_values($byGrade),
        ];
    }
}

