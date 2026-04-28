<?php

namespace iEducar\Packages\AdvancedReports\Services;

use App\Models\LegacyCourse;
use App\Models\LegacySchool;
use App\Models\LegacyStudent;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class SocioeconomicReportService
{
    public function getFilters(int $year = null, ?int $institutionId = null, ?int $schoolId = null, ?int $courseId = null): array
    {
        $anosLetivos = DB::table('pmieducar.matricula')
            ->selectRaw('ano as id, ano as nome')
            ->distinct()
            ->orderBy('ano')
            ->get();

        $instituicoes = DB::table('pmieducar.instituicao')
            ->select('cod_instituicao', 'nm_instituicao')
            ->where('ativo', 1)
            ->orderBy('nm_instituicao')
            ->get();

        $escolas = collect();
        if ($institutionId) {
            $escolas = DB::table('pmieducar.escola as escola')
                ->leftJoin('cadastro.pessoa as pessoa', 'escola.ref_idpes', '=', 'pessoa.idpes')
                ->leftJoin('cadastro.juridica as juridica', 'juridica.idpes', '=', 'pessoa.idpes')
                ->leftJoin('pmieducar.escola_complemento as complemento', 'complemento.ref_cod_escola', '=', 'escola.cod_escola')
                ->where('escola.ref_cod_instituicao', $institutionId)
                ->where('escola.ativo', 1)
                ->orderByRaw('COALESCE(juridica.fantasia, complemento.nm_escola)')
                ->get([
                    'escola.cod_escola',
                    DB::raw('COALESCE(juridica.fantasia, complemento.nm_escola) as nome'),
                ]);
        }

        $cursos = collect();
        if ($schoolId) {
            $cursos = LegacyCourse::query()
                ->where('ref_cod_instituicao', $institutionId)
                ->orderBy('nm_curso')
                ->get(['cod_curso', 'nm_curso']);
        }

        return compact('anosLetivos', 'instituicoes', 'escolas', 'cursos');
    }

    public function buildData(int $year, ?int $institutionId = null, ?int $schoolId = null, ?int $courseId = null): array
    {
        $baseQuery = LegacyStudent::query()
            ->join('pmieducar.matricula', 'matricula.ref_cod_aluno', '=', 'aluno.cod_aluno')
            ->leftJoin('pmieducar.matricula_turma', 'matricula_turma.ref_cod_matricula', '=', 'matricula.cod_matricula')
            ->leftJoin('pmieducar.turma', 'turma.cod_turma', '=', 'matricula_turma.ref_cod_turma')
            ->leftJoin('pmieducar.escola', 'escola.cod_escola', '=', 'matricula.ref_ref_cod_escola')
            ->leftJoin('pmieducar.curso', 'curso.cod_curso', '=', 'matricula.ref_cod_curso')
            ->leftJoin('cadastro.fisica', 'fisica.idpes', '=', 'aluno.ref_idpes')
            ->leftJoin('cadastro.fisica_raca', 'fisica_raca.ref_idpes', '=', 'aluno.ref_idpes')
            ->leftJoin('pmieducar.aluno_aluno_beneficio', 'aluno_aluno_beneficio.aluno_id', '=', 'aluno.cod_aluno')
            ->leftJoin('pmieducar.aluno_beneficio', 'aluno_beneficio.cod_aluno_beneficio', '=', 'aluno_aluno_beneficio.aluno_beneficio_id')
            ->where('matricula.ano', $year)
            ->where('matricula.ativo', 1);

        if ($institutionId) {
            $baseQuery->where('escola.ref_cod_instituicao', $institutionId);
        }

        if ($schoolId) {
            $baseQuery->where('escola.cod_escola', $schoolId);
        }

        if ($courseId) {
            $baseQuery->where('curso.cod_curso', $courseId);
        }

        // cadastro.fisica_raca não tem coluna "raca", e sim "ref_cod_raca"
        $raceStats = (clone $baseQuery)
            ->selectRaw('COALESCE(fisica_raca.ref_cod_raca, 0) as raca, COUNT(DISTINCT aluno.cod_aluno) as total')
            ->groupBy('fisica_raca.ref_cod_raca')
            ->get();

        $genderStats = (clone $baseQuery)
            ->selectRaw('COALESCE(fisica.sexo, \'N\') as sexo, COUNT(DISTINCT aluno.cod_aluno) as total')
            ->groupBy('fisica.sexo')
            ->get();

        $benefitStats = (clone $baseQuery)
            ->selectRaw('COALESCE(aluno_beneficio.nm_beneficio, \'Sem benefício\') as beneficio, COUNT(DISTINCT aluno.cod_aluno) as total')
            ->groupBy('aluno_beneficio.nm_beneficio')
            ->orderByDesc('total')
            ->get();

        $schoolStats = (clone $baseQuery)
            ->leftJoin('pmieducar.escola_complemento as complemento', 'complemento.ref_cod_escola', '=', 'escola.cod_escola')
            ->leftJoin('cadastro.juridica as juridica', 'juridica.idpes', '=', 'escola.ref_idpes')
            ->selectRaw('escola.cod_escola, COALESCE(juridica.fantasia, complemento.nm_escola) as nome, COUNT(DISTINCT aluno.cod_aluno) as total')
            ->groupBy('escola.cod_escola', 'nome')
            ->orderBy('nome')
            ->get();

        return [
            'race' => $raceStats,
            'gender' => $genderStats,
            'benefits' => $benefitStats,
            'schools' => $schoolStats,
        ];
    }
}

