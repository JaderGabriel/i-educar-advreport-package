<?php

namespace iEducar\Packages\AdvancedReports\Services;

use App\Models\LegacyCourse;
use App\Models\LegacySchool;
use Illuminate\Support\Facades\DB;

class AdvancedReportsFilterService
{
    public function getFilters(?int $year = null, ?int $institutionId = null, ?int $schoolId = null, ?int $courseId = null): array
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
        if ($schoolId && $year) {
            $cursos = DB::table('pmieducar.escola_curso as ec')
                ->join('pmieducar.curso as c', 'c.cod_curso', '=', 'ec.ref_cod_curso')
                ->where('ec.ref_cod_escola', $schoolId)
                ->where('ec.ativo', 1)
                ->where('c.ativo', 1)
                ->whereRaw('? = ANY (ec.anos_letivos)', [$year])
                ->orderBy('c.nm_curso')
                ->get([
                    'c.cod_curso',
                    'c.nm_curso',
                ]);
        }

        return compact('anosLetivos', 'instituicoes', 'escolas', 'cursos');
    }
}
