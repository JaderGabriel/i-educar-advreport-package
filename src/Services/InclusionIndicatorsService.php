<?php

namespace iEducar\Packages\AdvancedReports\Services;

use Illuminate\Support\Facades\DB;

class InclusionIndicatorsService
{
    public function getFilters(?int $year = null, ?int $institutionId = null, ?int $schoolId = null, ?int $courseId = null): array
    {
        // Reutiliza a mesma lógica do filtro básico do pacote
        return app(AdvancedReportsFilterService::class)->getFilters($year, $institutionId, $schoolId, $courseId);
    }

    /**
     * Indicadores de inclusão/necessidades educacionais especiais no recorte.
     *
     * @return array<string, mixed>
     */
    public function buildData(int $year, ?int $institutionId = null, ?int $schoolId = null, ?int $courseId = null): array
    {
        $base = DB::table('public.exporter_student as s')
            ->where('s.year', $year);

        if ($institutionId) {
            $base->where('s.institution_id', $institutionId);
        }
        if ($schoolId) {
            $base->where('s.school_id', $schoolId);
        }
        if ($courseId) {
            $base->where('s.course_id', $courseId);
        }

        $totalStudents = (clone $base)->distinct('s.student_id')->count('s.student_id');

        $withNis = (clone $base)
            ->whereNotNull('s.nis')
            ->where('s.nis', '<>', '')
            ->distinct('s.student_id')
            ->count('s.student_id');

        $withBenefits = (clone $base)
            ->leftJoin('public.exporter_benefits as b', 'b.student_id', '=', 's.student_id')
            ->whereNotNull('b.benefits')
            ->distinct('s.student_id')
            ->count('s.student_id');

        $withDisabilities = (clone $base)
            ->leftJoin('public.exporter_disabilities as d', 'd.person_id', '=', 's.person_id')
            ->whereNotNull('d.disabilities')
            ->distinct('s.student_id')
            ->count('s.student_id');

        // Distribuição por tipo de deficiência
        $disabilityByType = DB::table('public.exporter_student as s')
            ->join('cadastro.fisica_deficiencia as fd', 'fd.ref_idpes', '=', 's.person_id')
            ->join('cadastro.deficiencia as def', 'def.cod_deficiencia', '=', 'fd.ref_cod_deficiencia')
            ->when($institutionId, fn ($q) => $q->where('s.institution_id', $institutionId))
            ->when($schoolId, fn ($q) => $q->where('s.school_id', $schoolId))
            ->when($courseId, fn ($q) => $q->where('s.course_id', $courseId))
            ->where('s.year', $year)
            ->select('def.nm_deficiencia as deficiencia', DB::raw('COUNT(DISTINCT s.student_id) as total'))
            ->groupBy('def.nm_deficiencia')
            ->orderByDesc('total')
            ->get();

        // Por escola (top 20)
        $bySchool = (clone $base)
            ->leftJoin('public.exporter_disabilities as d', 'd.person_id', '=', 's.person_id')
            ->selectRaw('s.school as escola, s.school_id, COUNT(DISTINCT s.student_id) as total')
            ->selectRaw('COUNT(DISTINCT CASE WHEN d.disabilities IS NOT NULL THEN s.student_id END) as com_deficiencia')
            ->selectRaw('COUNT(DISTINCT CASE WHEN s.nis IS NOT NULL AND s.nis <> \'\' THEN s.student_id END) as com_nis')
            ->groupBy('s.school', 's.school_id')
            ->orderByDesc('total')
            ->limit(20)
            ->get();

        return [
            'summary' => [
                'total_students' => (int) $totalStudents,
                'with_disabilities' => (int) $withDisabilities,
                'with_nis' => (int) $withNis,
                'with_benefits' => (int) $withBenefits,
            ],
            'disability_by_type' => $disabilityByType,
            'by_school' => $bySchool,
        ];
    }
}

