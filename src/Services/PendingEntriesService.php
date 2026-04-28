<?php

namespace iEducar\Packages\AdvancedReports\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class PendingEntriesService
{
    /**
     * Retorna pendências de lançamento (notas e/ou frequência) por matrícula/componente/etapa.
     *
     * Implementação “segura” (sem depender de tabelas internas do módulo de avaliação):
     * - usa a API legada `Avaliacao_Service_Boletim` como fonte de verdade para existência de lançamentos.
     *
     * @return array{class: object, summary: array<string,int>, rows: Collection<int,array<string,mixed>>}
     */
    public function build(int $schoolClassId, ?int $stage = null, bool $checkGrades = true, bool $checkFrequency = true): array
    {
        $class = DB::table('pmieducar.turma as t')
            ->join('pmieducar.escola as e', 'e.cod_escola', '=', 't.ref_ref_cod_escola')
            ->leftJoin('pmieducar.curso as c', 'c.cod_curso', '=', 't.ref_cod_curso')
            ->leftJoin('pmieducar.serie as s', 's.cod_serie', '=', 't.ref_ref_cod_serie')
            ->leftJoin('pmieducar.turma_turno as tt', 'tt.id', '=', 't.turma_turno_id')
            ->where('t.cod_turma', $schoolClassId)
            ->selectRaw('t.cod_turma as turma_id')
            ->selectRaw('t.nm_turma as turma')
            ->selectRaw('t.ano as ano_letivo')
            ->selectRaw('e.nome as escola')
            ->selectRaw('COALESCE(c.nm_curso, \'\') as curso')
            ->selectRaw('COALESCE(s.nm_serie, \'\') as serie')
            ->selectRaw('COALESCE(tt.nome, \'\') as turno')
            ->first();

        if (!$class) {
            abort(404, 'Turma não encontrada.');
        }

        $registrations = DB::table('pmieducar.matricula_turma as mt')
            ->join('pmieducar.matricula as m', 'm.cod_matricula', '=', 'mt.ref_cod_matricula')
            ->join('pmieducar.aluno as a', 'a.cod_aluno', '=', 'm.ref_cod_aluno')
            ->join('cadastro.pessoa as p', 'p.idpes', '=', 'a.ref_idpes')
            ->where('mt.ref_cod_turma', $schoolClassId)
            ->where('mt.ativo', 1)
            ->where('m.ativo', 1)
            ->where('m.dependencia', false)
            ->orderBy('p.nome')
            ->get([
                DB::raw('m.cod_matricula as registration_id'),
                DB::raw('p.nome as student'),
            ]);

        $rows = collect();
        $counts = [
            'registrations' => (int) $registrations->count(),
            'pending_grade_items' => 0,
            'pending_frequency_items' => 0,
        ];

        foreach ($registrations as $r) {
            $registrationId = (int) $r->registration_id;

            /** @var \Avaliacao_Service_Boletim $boletim */
            $boletim = new \Avaliacao_Service_Boletim([
                'matricula' => $registrationId,
            ]);

            $etapas = (int) ($boletim->getOption('etapas') ?? 0);
            if ($etapas <= 0) {
                continue;
            }

            $componentes = $boletim->getComponentes() ?? [];
            if (empty($componentes)) {
                continue;
            }

            $stagesToCheck = $stage ? [$stage] : range(1, $etapas);

            foreach ($componentes as $componenteId => $componente) {
                $componentName = (string) ($componente->nome ?? ('Componente ' . $componenteId));

                foreach ($stagesToCheck as $etapa) {
                    $pendingGrade = false;
                    $pendingFrequency = false;

                    if ($checkGrades) {
                        $nota = $boletim->getNotaComponente((int) $componenteId, (int) $etapa);
                        $value = $nota?->notaArredondada ?? ($nota?->nota ?? null);
                        $pendingGrade = ($value === null || $value === '');
                    }

                    if ($checkFrequency) {
                        // `getFalta` se ajusta conforme regra (geral x por componente) na API do diário.
                        // Aqui usamos o mesmo service do boletim para manter consistência.
                        $falta = $boletim->getFalta((int) $etapa, (int) $componenteId);
                        $qty = $falta?->quantidade ?? null;
                        $pendingFrequency = ($qty === null || $qty === '');
                    }

                    if (!$pendingGrade && !$pendingFrequency) {
                        continue;
                    }

                    if ($pendingGrade) {
                        $counts['pending_grade_items']++;
                    }
                    if ($pendingFrequency) {
                        $counts['pending_frequency_items']++;
                    }

                    $rows->push([
                        'student' => (string) $r->student,
                        'registration_id' => $registrationId,
                        'component_id' => (int) $componenteId,
                        'component' => $componentName,
                        'stage' => (int) $etapa,
                        'pending_grade' => $pendingGrade,
                        'pending_frequency' => $pendingFrequency,
                    ]);
                }
            }
        }

        return [
            'class' => $class,
            'summary' => $counts,
            'rows' => $rows,
        ];
    }
}

