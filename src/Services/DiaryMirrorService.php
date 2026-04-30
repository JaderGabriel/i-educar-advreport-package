<?php

namespace iEducar\Packages\AdvancedReports\Services;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class DiaryMirrorService
{
    /**
     * @return array<int, array{date: string, label: string, day: int, weekday: string}>
     */
    public function buildDays(string $startDateYmd, string $endDateYmd, int $maxDays = 45): array
    {
        $start = Carbon::parse($startDateYmd)->startOfDay();
        $end = Carbon::parse($endDateYmd)->startOfDay();
        if ($end->lessThan($start)) {
            [$start, $end] = [$end, $start];
        }

        $out = [];
        $cur = $start->copy();
        $i = 0;
        while ($cur->lessThanOrEqualTo($end) && $i < $maxDays) {
            $out[] = [
                'date' => $cur->toDateString(),
                'label' => $cur->format('d/m'),
                'day' => (int) $cur->format('d'),
                'weekday' => $cur->locale('pt_BR')->isoFormat('ddd'),
            ];
            $cur->addDay();
            $i++;
        }

        return $out;
    }

    /**
     * @return array{class: object, days: array<int,array<string,mixed>>, students: Collection<int,array{student:string,registration_id:int}>}
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

        $days = $this->buildDays($startDateYmd, $endDateYmd);

        return [
            'class' => $class,
            'days' => $days,
            'students' => $students,
        ];
    }
}

