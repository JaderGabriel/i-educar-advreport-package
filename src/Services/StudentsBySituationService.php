<?php

namespace iEducar\Packages\AdvancedReports\Services;

use Carbon\Carbon;
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

    private function humanizeDatePt(?string $ymd): ?string
    {
        if ($ymd === null || $ymd === '') {
            return null;
        }

        try {
            return Carbon::parse($ymd)->locale('pt_BR')->isoFormat('D [de] MMMM [de] YYYY');
        } catch (\Throwable) {
            return null;
        }
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
        // Com turma filtrada: último vínculo da matrícula naquela turma (inclui transferidos, abandono etc., mesmo com mt.ativo = 0).
        // Sem turma: último vínculo ativo (comportamento anterior, uma linha por matrícula na enturmação vigente).
        $base = DB::table('pmieducar.matricula as m')
            ->join('pmieducar.aluno as a', 'a.cod_aluno', '=', 'm.ref_cod_aluno')
            ->join('cadastro.pessoa as p', 'p.idpes', '=', 'a.ref_idpes')
            ->join('pmieducar.matricula_turma as mt', function ($j) use ($schoolClassId) {
                $j->on('mt.ref_cod_matricula', '=', 'm.cod_matricula');
                if ($schoolClassId) {
                    $j->where('mt.ref_cod_turma', '=', (int) $schoolClassId)
                        ->whereRaw(
                            'mt.sequencial = (
                                SELECT MAX(mt2.sequencial)
                                FROM pmieducar.matricula_turma mt2
                                WHERE mt2.ref_cod_matricula = m.cod_matricula
                                  AND mt2.ref_cod_turma = ?
                            )',
                            [(int) $schoolClassId]
                        );
                } else {
                    $j->where('mt.ativo', 1)
                        ->whereRaw(
                            'mt.sequencial = (
                                SELECT MAX(mt2.sequencial)
                                FROM pmieducar.matricula_turma mt2
                                WHERE mt2.ref_cod_matricula = m.cod_matricula
                                  AND mt2.ativo = 1
                            )'
                        );
                }
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

        // Disciplinas da turma: mesma lógica do relatório (view considera cct x série/escola).
        $compByTurma = DB::table('relatorio.view_componente_curricular as vcc')
            ->selectRaw('vcc.cod_turma as turma_id')
            ->selectRaw("string_agg(DISTINCT vcc.nome::text, ' | ' ORDER BY vcc.nome) as componentes")
            ->groupBy('vcc.cod_turma');

        $rawRows = (clone $base)
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
            ->selectRaw(
                'CASE WHEN vs.cod_situacao = 3 THEN NULL ELSE (COALESCE(mt.data_exclusao::date, m.data_cancel::date))::text END as situacao_data'
            )
            ->orderByRaw('LOWER(COALESCE(t.nm_turma, \'\'))')
            ->orderByRaw('LOWER(p.nome)')
            ->limit($limit)
            ->get();

        $rows = $rawRows
            ->map(function ($r) {
                $dataYmd = $r->situacao_data ?? null;

                return [
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
                    'data_fato' => $dataYmd ? $this->humanizeDatePt($dataYmd) : null,
                    'data_fato_curta' => $dataYmd ? Carbon::parse($dataYmd)->format('d/m/Y') : null,
                ];
            })
            ->unique('matricula_id')
            ->values();

        $summary = [];
        foreach ($rows as $r) {
            $sid = (int) $r['situacao_id'];
            $summary[$sid] = ($summary[$sid] ?? 0) + 1;
        }

        return compact('summary', 'rows');
    }
}
