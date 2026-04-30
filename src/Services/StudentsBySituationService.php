<?php

namespace iEducar\Packages\AdvancedReports\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class StudentsBySituationService
{
    /**
     * Mapeamento mínimo para exibição (compatível com relatorio.view_situacao / relatorio.get_situacao_componente).
     *
     * @return array<int,string>
     */
    public function situationOptions(): array
    {
        return [
            1 => 'Aprovado',
            2 => 'Retido/Reprovado',
            3 => 'Cursando',
            4 => 'Transferido',
            5 => 'Reclassificado',
            6 => 'Deixou de Frequentar (abandono)',
            12 => 'Aprovado com dependência',
            13 => 'Aprovado pelo conselho',
            14 => 'Retido por falta',
            15 => 'Falecido',
        ];
    }

    /**
     * @return array{summary: array<int,int>, rows: Collection<int,array<string,mixed>>}
     */
    public function build(
        int $year,
        ?int $institutionId = null,
        ?int $schoolId = null,
        ?int $courseId = null,
        ?int $gradeId = null,
        ?int $schoolClassId = null,
        ?int $situation = null,
        int $limit = 5000,
    ): array {
        // Uma linha por matrícula: evita duplicar quando há mais de um vínculo em matricula_turma
        // (ex.: remanejamentos); usa o vínculo ativo com maior sequencial.
        $base = DB::table('pmieducar.matricula as m')
            ->join('pmieducar.aluno as a', 'a.cod_aluno', '=', 'm.ref_cod_aluno')
            ->join('cadastro.pessoa as p', 'p.idpes', '=', 'a.ref_idpes')
            ->join('pmieducar.matricula_turma as mt', function ($j) {
                $j->on('mt.ref_cod_matricula', '=', 'm.cod_matricula')
                    ->where('mt.ativo', 1)
                    ->whereRaw('mt.sequencial = (
                        SELECT MAX(mt2.sequencial)
                        FROM pmieducar.matricula_turma mt2
                        WHERE mt2.ref_cod_matricula = m.cod_matricula
                          AND mt2.ativo = 1
                    )');
            })
            ->leftJoin('pmieducar.turma as t', 't.cod_turma', '=', 'mt.ref_cod_turma')
            ->leftJoin('pmieducar.turma_turno as tt', 'tt.id', '=', 't.turma_turno_id')
            ->leftJoin('pmieducar.escola as e', 'e.cod_escola', '=', 'm.ref_ref_cod_escola')
            ->leftJoin('cadastro.pessoa as ep', 'ep.idpes', '=', 'e.ref_idpes')
            ->leftJoin('cadastro.juridica as ej', 'ej.idpes', '=', 'ep.idpes')
            ->leftJoin('pmieducar.escola_complemento as ec', 'ec.ref_cod_escola', '=', 'e.cod_escola')
            ->leftJoin('pmieducar.curso as c', 'c.cod_curso', '=', 'm.ref_cod_curso')
            ->leftJoin('pmieducar.serie as s', 's.cod_serie', '=', 'm.ref_ref_cod_serie')
            ->join('relatorio.view_situacao as vs', function ($j) {
                $j->on('vs.cod_matricula', '=', 'm.cod_matricula')
                    ->on('vs.cod_turma', '=', 'mt.ref_cod_turma')
                    ->on('vs.sequencial', '=', 'mt.sequencial');
            })
            ->where('m.ativo', 1)
            ->where('m.ano', $year)
            ->when($institutionId, fn ($q) => $q->where('e.ref_cod_instituicao', $institutionId))
            ->when($schoolId, fn ($q) => $q->where('e.cod_escola', $schoolId))
            ->when($courseId, fn ($q) => $q->where('c.cod_curso', $courseId))
            ->when($gradeId, fn ($q) => $q->where('s.cod_serie', $gradeId))
            ->when($schoolClassId, fn ($q) => $q->where('t.cod_turma', $schoolClassId))
            ->when($situation, fn ($q) => $q->where('vs.cod_situacao', $situation));

        $compByTurma = DB::table('modules.componente_curricular_turma as cct')
            ->join('modules.componente_curricular as cc', 'cc.id', '=', 'cct.componente_curricular_id')
            ->groupBy('cct.turma_id')
            ->select('cct.turma_id')
            ->selectRaw("string_agg(cc.nome::text, ' | ' ORDER BY cc.nome) as componentes");

        $summaryRows = (clone $base)
            ->selectRaw('vs.cod_situacao as situacao, COUNT(DISTINCT m.cod_matricula) as total')
            ->groupBy('vs.cod_situacao')
            ->orderBy('vs.cod_situacao')
            ->get();

        $summary = [];
        foreach ($summaryRows as $r) {
            $summary[(int) $r->situacao] = (int) $r->total;
        }

        $rows = (clone $base)
            ->leftJoinSub($compByTurma, 'comp', function ($j) {
                $j->on('comp.turma_id', '=', 't.cod_turma');
            })
            ->selectRaw('m.cod_matricula as matricula_id')
            ->selectRaw('p.nome as aluno')
            ->selectRaw('COALESCE(ej.fantasia, ec.nm_escola, \'\') as escola')
            ->selectRaw('COALESCE(c.nm_curso, \'\') as curso')
            ->selectRaw('COALESCE(s.nm_serie, \'\') as serie')
            ->selectRaw('COALESCE(t.nm_turma, \'\') as turma')
            ->selectRaw('COALESCE(tt.nome, \'\') as turno')
            ->selectRaw('vs.cod_situacao as situacao_id')
            ->selectRaw('vs.texto_situacao as situacao')
            ->selectRaw('COALESCE(comp.componentes, \'\') as componentes')
            ->orderByRaw('LOWER(COALESCE(t.nm_turma, \'\'))')
            ->orderByRaw('LOWER(p.nome)')
            ->limit($limit)
            ->get()
            ->map(fn ($r) => [
                'matricula_id' => (int) $r->matricula_id,
                'aluno' => (string) $r->aluno,
                'escola' => (string) $r->escola,
                'curso' => (string) $r->curso,
                'serie' => (string) $r->serie,
                'turma' => (string) $r->turma,
                'turno' => (string) ($r->turno ?? ''),
                'situacao_id' => (int) $r->situacao_id,
                'situacao' => (string) $r->situacao,
                'componentes' => (string) ($r->componentes ?? ''),
            ]);

        return compact('summary', 'rows');
    }
}
