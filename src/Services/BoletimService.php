<?php

namespace iEducar\Packages\AdvancedReports\Services;

use Illuminate\Support\Facades\DB;

class BoletimService
{
    /**
     * @return array<string, mixed>
     */
    public function build(int $matriculaId, ?string $etapa = null): array
    {
        // Classe legacy (sem namespace) já existente no i-Educar.
        /** @var \Avaliacao_Service_Boletim $boletim */
        $boletim = new \Avaliacao_Service_Boletim([
            'matricula' => $matriculaId,
            'etapa' => $etapa,
        ]);

        $matricula = $boletim->getOption('matriculaData') ?? [];
        $etapas = (int) ($boletim->getOption('etapas') ?? 0);

        $componentes = $boletim->getComponentes() ?? [];
        $mediasComponentes = $boletim->getMediasComponentes() ?? [];

        $meta = DB::table('pmieducar.matricula as m')
            ->join('pmieducar.aluno as a', 'a.cod_aluno', '=', 'm.ref_cod_aluno')
            ->join('cadastro.pessoa as p', 'p.idpes', '=', 'a.ref_idpes')
            ->leftJoin('cadastro.fisica as f', 'f.idpes', '=', 'a.ref_idpes')
            ->leftJoin('pmieducar.escola as e', 'e.cod_escola', '=', 'm.ref_ref_cod_escola')
            ->leftJoin('cadastro.pessoa as ep', 'ep.idpes', '=', 'e.ref_idpes')
            ->leftJoin('cadastro.juridica as ej', 'ej.idpes', '=', 'ep.idpes')
            ->leftJoin('pmieducar.escola_complemento as ec', 'ec.ref_cod_escola', '=', 'e.cod_escola')
            ->leftJoin('pmieducar.curso as c', 'c.cod_curso', '=', 'm.ref_cod_curso')
            ->leftJoin('pmieducar.serie as s', 's.cod_serie', '=', 'm.ref_ref_cod_serie')
            ->leftJoin('pmieducar.matricula_turma as mt', function ($join) {
                $join->on('mt.ref_cod_matricula', '=', 'm.cod_matricula');
                $join->where('mt.ativo', 1);
            })
            ->leftJoin('pmieducar.turma as t', 't.cod_turma', '=', 'mt.ref_cod_turma')
            ->leftJoin('pmieducar.turma_turno as tt', 'tt.id', '=', 't.turma_turno_id')
            ->where('m.cod_matricula', $matriculaId)
            ->selectRaw('m.ref_cod_curso as curso_id')
            ->selectRaw('m.ref_ref_cod_serie as serie_id')
            ->selectRaw('m.ref_ref_cod_escola as escola_id')
            ->selectRaw('p.nome as aluno_nome')
            ->selectRaw('f.data_nasc as aluno_nascimento')
            ->selectRaw('COALESCE(ej.fantasia, ec.nm_escola, \'\') as escola')
            ->selectRaw('c.nm_curso as curso')
            ->selectRaw('s.nm_serie as serie')
            ->selectRaw('t.nm_turma as turma')
            ->selectRaw('t.cod_turma as turma_id')
            ->selectRaw('COALESCE(tt.nome, \'\') as turno')
            ->first();

        // Frequência consolidada (regra global ou por componente) já implementada na função SQL do core.
        $freq = DB::selectOne('SELECT modules.frequencia_da_matricula(?) as frequencia', [$matriculaId]);

        $professor = null;
        if (!empty($meta?->turma_id) && !empty($matricula['ano'])) {
            $professor = DB::table('modules.professor_turma as pt')
                ->join('pmieducar.servidor as s', 's.cod_servidor', '=', 'pt.servidor_id')
                // Em i-Educar, `pmieducar.servidor.cod_servidor` é o idpes (pessoa). Não existe `ref_idpes`.
                ->join('cadastro.pessoa as pp', 'pp.idpes', '=', 's.cod_servidor')
                ->where('pt.turma_id', (int) $meta->turma_id)
                ->where('pt.ano', (int) $matricula['ano'])
                ->orderBy('pt.funcao_exercida')
                ->value('pp.nome');
        }

        $faltasByComponente = [];
        $horaFalta = null;
        $cargaHorariaByComponente = [];
        if (!empty($meta?->turma_id) && $etapas > 0) {
            $horaFalta = DB::table('pmieducar.curso')
                ->where('cod_curso', (int) ($meta->curso_id ?? 0))
                ->value('hora_falta');

            $faltaAlunoId = DB::table('modules.falta_aluno')
                ->where('matricula_id', $matriculaId)
                ->orderByDesc('id')
                ->value('id');

            if ($faltaAlunoId) {
                $faltas = DB::table('modules.falta_componente_curricular')
                    ->where('falta_aluno_id', (int) $faltaAlunoId)
                    ->get(['componente_curricular_id', 'etapa', 'quantidade']);

                foreach ($faltas as $f) {
                    $cid = (int) $f->componente_curricular_id;
                    $stage = (string) $f->etapa;
                    $faltasByComponente[$cid][$stage] = (int) ($f->quantidade ?? 0);
                }
            }

            // Pré-carrega carga horária por componente (para estimar % faltas por etapa).
            foreach (array_keys($componentes) as $cid) {
                $cid = (int) $cid;
                $carga = DB::table('modules.componente_curricular_turma')
                    ->where('componente_curricular_id', $cid)
                    ->where('turma_id', (int) $meta->turma_id)
                    ->value('carga_horaria');

                if ($carga === null) {
                    $carga = DB::table('pmieducar.escola_serie_disciplina')
                        ->where('ref_cod_disciplina', $cid)
                        ->where('ref_ref_cod_serie', (int) ($meta->serie_id ?? 0))
                        ->where('ref_ref_cod_escola', (int) ($meta->escola_id ?? 0))
                        ->value('carga_horaria');
                }

                if ($carga === null) {
                    $carga = DB::table('modules.componente_curricular_ano_escolar')
                        ->where('componente_curricular_id', $cid)
                        ->where('ano_escolar_id', (int) ($meta->serie_id ?? 0))
                        ->value('carga_horaria');
                }

                $cargaHorariaByComponente[$cid] = $carga !== null ? (float) $carga : null;
            }
        }

        $rows = [];
        foreach ($componentes as $componenteId => $componente) {
            $nome = $componente->nome ?? ('Componente ' . $componenteId);

            // Prioriza NOTAS efetivamente lançadas; se não existir, cai para médias calculadas.
            $byStage = [];
            for ($i = 1; $i <= max($etapas, 0); $i++) {
                $nota = $boletim->getNotaComponente((int) $componenteId, $i);
                $value = $nota?->notaArredondada ?? ($nota?->nota ?? null);
                $byStage[(string) $i] = $value;
            }

            /** @phpstan-ignore-next-line */
            // Em runtime, o core aceita 'Rc' como etapa, apesar da assinatura tipada do analisador.
            // Aqui apenas silenciamos o aviso do analisador estático.
            $notaRc = $boletim->getNotaComponente((int) $componenteId, 'Rc'); // @phpstan-ignore-line
            if ($notaRc) {
                $byStage['Rc'] = $notaRc->notaArredondada ?? ($notaRc->nota ?? null);
            }

            // Fallback: algumas regras usam médias; preenche o que estiver vazio.
            $list = $mediasComponentes[$componenteId] ?? [];
            foreach ($list as $m) {
                $stageKey = (string) ($m->etapa ?? '');
                if ($stageKey === '') {
                    continue;
                }
                if (array_key_exists($stageKey, $byStage) && $byStage[$stageKey] !== null && $byStage[$stageKey] !== '') {
                    continue;
                }
                $byStage[$stageKey] = $m->mediaArredondada ?? ($m->media ?? null);
            }

            $row = [
                'id' => (int) $componenteId,
                'nome' => (string) $nome,
                'etapas' => [],
            ];

            for ($i = 1; $i <= max($etapas, 0); $i++) {
                $faltas = $faltasByComponente[(int) $componenteId][(string) $i] ?? null;
                $faltasPct = null;
                $carga = $cargaHorariaByComponente[(int) $componenteId] ?? null;
                if ($faltas !== null && $horaFalta !== null && $carga !== null && $etapas > 0) {
                    $cargaEtapa = $carga / $etapas;
                    if ($cargaEtapa > 0) {
                        $faltasPct = round(((float) $faltas * (float) $horaFalta) * 100 / $cargaEtapa, 1);
                    }
                }
                $row['etapas'][(string) $i] = [
                    'nota' => $byStage[(string) $i] ?? null,
                    'faltas' => $faltas,
                    'faltas_pct' => $faltasPct,
                ];
            }

            // Recuperação (quando existir) costuma ser 'Rc' (ver regras/fluxo do boletim).
            if (array_key_exists('Rc', $byStage)) {
                $row['etapas']['Rc'] = [
                    'nota' => $byStage['Rc'],
                    'faltas' => $faltasByComponente[(int) $componenteId]['Rc'] ?? null,
                ];
            }

            $rows[] = $row;
        }

        usort($rows, fn ($a, $b) => strcmp($a['nome'], $b['nome']));

        return [
            'matricula' => array_merge($matricula, [
                'aluno_nome' => $meta?->aluno_nome ?? ($matricula['nome'] ?? null),
                'aluno_nascimento' => !empty($meta?->aluno_nascimento) ? substr((string) $meta->aluno_nascimento, 0, 10) : null,
                'escola' => $meta?->escola ?? ($matricula['escola'] ?? null),
                'curso' => $meta?->curso ?? ($matricula['curso'] ?? null),
                'serie' => $meta?->serie ?? ($matricula['serie'] ?? null),
                'turma' => $meta?->turma ?? ($matricula['turma'] ?? null),
                'turno' => $meta?->turno ?? null,
                'professor' => $professor ? (string) $professor : null,
            ]),
            'etapas_count' => $etapas,
            'frequencia' => $freq?->frequencia,
            'rows' => $rows,
        ];
    }
}

