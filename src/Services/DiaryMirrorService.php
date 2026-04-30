<?php

namespace iEducar\Packages\AdvancedReports\Services;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class DiaryMirrorService
{
    /**
     * Monta a lista de dias no intervalo. Por padrão exclui sábados e domingos.
     * Com turma + escola + ano letivo, aplica o calendário escolar (pmieducar + modules.calendario_turma):
     * dias “não letivos” vinculados à turma são excluídos; “extra letivo” (tipo E) inclui o dia mesmo em fim de semana.
     *
     * @return list<array{date: string, label: string, day: int, weekday: string}>
     */
    public function buildDays(
        string $startDateYmd,
        string $endDateYmd,
        int $maxSchoolDays = 80,
        bool $skipWeekends = true,
        ?int $turmaId = null,
        ?int $escolaId = null,
        ?int $anoLetivo = null,
    ): array {
        $start = Carbon::parse($startDateYmd)->startOfDay();
        $end = Carbon::parse($endDateYmd)->startOfDay();
        if ($end->lessThan($start)) {
            [$start, $end] = [$end, $start];
        }

        $nonLetivo = [];
        $extraLetivo = [];
        if ($turmaId && $escolaId && $anoLetivo) {
            $flags = $this->loadCalendarLetivoFlags($turmaId, $escolaId, $anoLetivo);
            $nonLetivo = $flags['non'];
            $extraLetivo = $flags['extra'];
        }

        $out = [];
        $cur = $start->copy();
        $guard = 0;
        while ($cur->lessThanOrEqualTo($end) && count($out) < $maxSchoolDays && $guard < 400) {
            $guard++;
            $ymd = $cur->toDateString();
            $dow = (int) $cur->dayOfWeekIso; // 1=Mon .. 7=Sun

            if (isset($nonLetivo[$ymd])) {
                $cur->addDay();

                continue;
            }

            if ($skipWeekends && ($dow === 6 || $dow === 7) && !isset($extraLetivo[$ymd])) {
                $cur->addDay();

                continue;
            }

            $out[] = [
                'date' => $ymd,
                'label' => $cur->format('d/m'),
                'day' => (int) $cur->format('d'),
                'weekday' => $cur->locale('pt_BR')->isoFormat('ddd'),
            ];
            $cur->addDay();
        }

        return $out;
    }

    /**
     * @return array{non: array<string, true>, extra: array<string, true>}
     */
    private function loadCalendarLetivoFlags(int $turmaId, int $escolaId, int $anoLetivo): array
    {
        $rows = DB::table('pmieducar.calendario_ano_letivo as cal')
            ->join('pmieducar.calendario_dia as cd', 'cd.ref_cod_calendario_ano_letivo', '=', 'cal.cod_calendario_ano_letivo')
            ->join('pmieducar.calendario_dia_motivo as m', 'm.cod_calendario_dia_motivo', '=', 'cd.ref_cod_calendario_dia_motivo')
            ->join('modules.calendario_turma as ct', function ($join) use ($turmaId) {
                $join->on('ct.calendario_ano_letivo_id', '=', 'cd.ref_cod_calendario_ano_letivo');
                $join->on('ct.mes', '=', 'cd.mes');
                $join->on('ct.dia', '=', 'cd.dia');
                $join->on('ct.ano', '=', 'cal.ano');
                $join->where('ct.turma_id', '=', $turmaId);
            })
            ->where('cal.ref_cod_escola', $escolaId)
            ->where('cal.ano', $anoLetivo)
            ->where('cal.ativo', 1)
            ->where('cd.ativo', 1)
            ->whereNull('cd.data_exclusao')
            ->where('m.ativo', 1)
            ->whereNull('m.data_exclusao')
            ->whereNotNull('cd.ref_cod_calendario_dia_motivo')
            ->select([
                'cal.ano as cal_ano',
                'cd.mes',
                'cd.dia',
                DB::raw('UPPER(TRIM(BOTH FROM m.tipo)) as tipo'),
            ])
            ->get();

        $non = [];
        $extra = [];
        foreach ($rows as $r) {
            $y = (int) ($r->cal_ano ?? 0);
            $m = (int) ($r->mes ?? 0);
            $d = (int) ($r->dia ?? 0);
            if ($y < 1 || $m < 1 || $m > 12 || $d < 1 || $d > 31) {
                continue;
            }
            try {
                $dt = Carbon::createFromDate($y, $m, $d)->startOfDay();
            } catch (\Throwable) {
                continue;
            }
            $key = $dt->toDateString();
            $tipo = (string) ($r->tipo ?? '');
            if ($tipo === 'E') {
                $extra[$key] = true;
            } else {
                $non[$key] = true;
            }
        }

        return ['non' => $non, 'extra' => $extra];
    }

    /**
     * Pagina o espelho: limita colunas de dias e linhas de alunos por página para evitar corte no PDF.
     *
     * @param  Collection<int, array{student: string, registration_id: int}>  $students
     * @return list<array{days: list<array<string, mixed>>, students: list<array{student: string, registration_id: int}>}>
     */
    public function paginateMirrorPages(
        array $days,
        Collection $students,
        int $maxDayColumns = 14,
        int $maxStudentRows = 22,
    ): array {
        $maxDayColumns = max(4, min(24, $maxDayColumns));
        $maxStudentRows = max(5, min(40, $maxStudentRows));

        $dayChunks = array_chunk($days, $maxDayColumns);
        if ($dayChunks === []) {
            $dayChunks = [[]];
        }

        $studentList = $students->values()->all();
        $studentChunks = array_chunk($studentList, $maxStudentRows);
        if ($studentChunks === []) {
            $studentChunks = [[]];
        }

        $pages = [];
        foreach ($studentChunks as $stChunk) {
            foreach ($dayChunks as $dChunk) {
                $pages[] = [
                    'days' => array_values($dChunk),
                    'students' => array_values($stChunk),
                ];
            }
        }

        return $pages;
    }

    /**
     * Docentes vinculados à turma com componentes (modules.professor_turma + disciplinas).
     *
     * @return list<array{servidor_id: int|null, docente_nome: string, componente_curricular_id: int|null, componente_nome: string, componente_abrev: string|null}>
     */
    public function listTeacherDisciplinesForClass(int $turmaId, int $anoLetivo, int $instituicaoId): array
    {
        $rows = DB::table('modules.professor_turma as pt')
            ->join('modules.professor_turma_disciplina as ptd', 'ptd.professor_turma_id', '=', 'pt.id')
            ->join('modules.componente_curricular as cc', function ($join) {
                $join->on('cc.id', '=', 'ptd.componente_curricular_id');
                $join->on('cc.instituicao_id', '=', 'pt.instituicao_id');
            })
            ->join('pmieducar.servidor as srv', 'srv.cod_servidor', '=', 'pt.servidor_id')
            ->join('cadastro.pessoa as pdoc', 'pdoc.idpes', '=', 'srv.cod_servidor')
            ->where('pt.turma_id', $turmaId)
            ->where('pt.ano', $anoLetivo)
            ->where('pt.instituicao_id', $instituicaoId)
            ->where(function ($q) {
                $q->whereNull('srv.ativo')->orWhere('srv.ativo', 1);
            })
            ->selectRaw('pt.servidor_id')
            ->selectRaw('TRIM(BOTH FROM pdoc.nome) as docente_nome')
            ->selectRaw('ptd.componente_curricular_id')
            ->selectRaw('TRIM(BOTH FROM cc.nome) as componente_nome')
            ->selectRaw('cc.abreviatura as componente_abrev')
            ->orderBy('componente_nome')
            ->orderBy('docente_nome')
            ->get();

        $out = [];
        $seen = [];
        foreach ($rows as $r) {
            $key = ((int) ($r->servidor_id ?? 0)) . ':' . ((int) ($r->componente_curricular_id ?? 0));
            if (isset($seen[$key])) {
                continue;
            }
            $seen[$key] = true;
            $out[] = [
                'servidor_id' => (int) ($r->servidor_id ?? 0) ?: null,
                'docente_nome' => (string) ($r->docente_nome ?? ''),
                'componente_curricular_id' => (int) ($r->componente_curricular_id ?? 0) ?: null,
                'componente_nome' => (string) ($r->componente_nome ?? ''),
                'componente_abrev' => $r->componente_abrev ? (string) $r->componente_abrev : null,
            ];
        }

        if ($out !== []) {
            return $out;
        }

        return $this->listTeacherDisciplinesFromCurriculumTurmaFixed($turmaId);
    }

    /**
     * @return list<array{servidor_id: int|null, docente_nome: string, componente_curricular_id: int|null, componente_nome: string, componente_abrev: string|null}>
     */
    private function listTeacherDisciplinesFromCurriculumTurmaFixed(int $turmaId): array
    {
        $rows = DB::table('modules.componente_curricular_turma as cct')
            ->join('pmieducar.turma as t', 't.cod_turma', '=', 'cct.turma_id')
            ->join('pmieducar.escola as esc', 'esc.cod_escola', '=', 't.ref_ref_cod_escola')
            ->join('modules.componente_curricular as cc', function ($join) {
                $join->on('cc.id', '=', 'cct.componente_curricular_id');
                $join->on('cc.instituicao_id', '=', 'esc.ref_cod_instituicao');
            })
            ->leftJoin('cadastro.pessoa as pd', 'pd.idpes', '=', 'cct.docente_vinculado')
            ->where('cct.turma_id', $turmaId)
            ->selectRaw('cct.docente_vinculado as servidor_id')
            ->selectRaw('TRIM(BOTH FROM COALESCE(pd.nome, \'\')) as docente_nome')
            ->selectRaw('cct.componente_curricular_id')
            ->selectRaw('TRIM(BOTH FROM cc.nome) as componente_nome')
            ->selectRaw('cc.abreviatura as componente_abrev')
            ->orderBy('componente_nome')
            ->get();

        $out = [];
        $seen = [];
        foreach ($rows as $r) {
            $key = (string) ($r->componente_curricular_id ?? '');
            if (isset($seen[$key])) {
                continue;
            }
            $seen[$key] = true;
            $sid = (int) ($r->servidor_id ?? 0);
            $out[] = [
                'servidor_id' => $sid > 0 ? $sid : null,
                'docente_nome' => (string) ($r->docente_nome ?? ''),
                'componente_curricular_id' => (int) ($r->componente_curricular_id ?? 0) ?: null,
                'componente_nome' => (string) ($r->componente_nome ?? ''),
                'componente_abrev' => $r->componente_abrev ? (string) $r->componente_abrev : null,
            ];
        }

        return $out;
    }

    /**
     * @return array{class: object, students: Collection<int, array{student: string, registration_id: int}>}
     */
    public function build(int $schoolClassId, string $startDateYmd, string $endDateYmd): array
    {
        $class = DB::table('pmieducar.turma as t')
            ->join('pmieducar.escola as e', 'e.cod_escola', '=', 't.ref_ref_cod_escola')
            ->leftJoin('cadastro.pessoa as ep', 'ep.idpes', '=', 'e.ref_idpes')
            ->leftJoin('cadastro.juridica as ej', 'ej.idpes', '=', 'ep.idpes')
            ->leftJoin('pmieducar.escola_complemento as ec', 'ec.ref_cod_escola', '=', 'e.cod_escola')
            ->leftJoin('pmieducar.instituicao as i', 'i.cod_instituicao', '=', 'e.ref_cod_instituicao')
            ->leftJoin('pmieducar.curso as c', 'c.cod_curso', '=', 't.ref_cod_curso')
            ->leftJoin('pmieducar.serie as s', 's.cod_serie', '=', 't.ref_ref_cod_serie')
            ->leftJoin('pmieducar.turma_turno as tt', 'tt.id', '=', 't.turma_turno_id')
            ->where('t.cod_turma', $schoolClassId)
            ->selectRaw('t.cod_turma as turma_id')
            ->selectRaw('t.nm_turma as turma')
            ->selectRaw('t.ano as ano_letivo')
            ->selectRaw('COALESCE(ej.fantasia, ec.nm_escola, ep.nome, \'\') as escola')
            ->selectRaw('e.ref_cod_instituicao as instituicao_id')
            ->selectRaw('e.cod_escola as escola_id')
            ->selectRaw('COALESCE(i.nm_instituicao, \'\') as instituicao')
            ->selectRaw('COALESCE(c.nm_curso, \'\') as curso')
            ->selectRaw('COALESCE(s.nm_serie, \'\') as serie')
            ->selectRaw('COALESCE(tt.nome, \'\') as turno')
            ->first();

        if (!$class) {
            abort(404, 'Turma não encontrada.');
        }

        $students = DB::table('pmieducar.matricula_turma as mt')
            ->join('pmieducar.matricula as m', 'm.cod_matricula', '=', 'mt.ref_cod_matricula')
            ->join('pmieducar.aluno as a', 'a.cod_aluno', '=', 'm.ref_cod_aluno')
            ->join('cadastro.pessoa as p', 'p.idpes', '=', 'a.ref_idpes')
            ->where('mt.ref_cod_turma', $schoolClassId)
            ->where('mt.ativo', 1)
            ->where('m.ativo', 1)
            ->where('m.dependencia', false)
            ->orderBy('p.nome')
            ->get([
                DB::raw('p.nome as student'),
                DB::raw('m.cod_matricula as registration_id'),
            ])
            ->map(static fn ($r) => [
                'student' => (string) $r->student,
                'registration_id' => (int) $r->registration_id,
            ]);

        return [
            'class' => $class,
            'students' => $students,
        ];
    }
}
