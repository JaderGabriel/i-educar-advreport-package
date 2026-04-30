<?php

namespace iEducar\Packages\AdvancedReports\Services;

use App\Models\RegistrationStatus;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class MinutesService
{
    /**
     * Parseia lista de etapas (1 = primeiro período/bimestre etc.), ex.: "1, 2, 4".
     *
     * @return array<int, int> etapas únicas, ordenadas
     */
    public function parseEtapasFilter(?string $raw): array
    {
        if ($raw === null || trim($raw) === '') {
            return [];
        }

        $parts = preg_split('/\s*,\s*/', $raw, -1, PREG_SPLIT_NO_EMPTY) ?: [];
        $out = [];
        foreach ($parts as $p) {
            $n = (int) $p;
            if ($n >= 1) {
                $out[$n] = $n;
            }
        }

        ksort($out, SORT_NUMERIC);

        return array_values($out);
    }

    /**
     * CPF mascarado: apenas os 3 primeiros e os 2 últimos dígitos visíveis (11 dígitos).
     */
    public function maskCpf(?string $cpf): ?string
    {
        if ($cpf === null || $cpf === '') {
            return null;
        }

        $digits = preg_replace('/\D/', '', (string) $cpf) ?? '';
        if ($digits === '') {
            return null;
        }

        $digits = str_pad($digits, 11, '0', STR_PAD_LEFT);
        if (strlen($digits) < 5) {
            return str_repeat('*', strlen($digits));
        }

        $first = substr($digits, 0, 3);
        $last = substr($digits, -2);

        return $first . '.***.***-' . $last;
    }

    /**
     * Quantidade de etapas da matrícula (regra da turma / ano), para validar o filtro.
     */
    public function maxEtapasForRegistration(int $registrationId, int $schoolClassId): int
    {
        try {
            /** @var \Avaliacao_Service_Boletim $boletim */
            $boletim = new \Avaliacao_Service_Boletim([
                'matricula' => $registrationId,
            ]);
            $n = (int) ($boletim->getOption('etapas') ?? 0);
            if ($n > 0) {
                return $n;
            }
        } catch (\Throwable) {
        }

        $modulos = (int) DB::table('pmieducar.turma_modulo')
            ->where('ref_cod_turma', $schoolClassId)
            ->count();

        return max($modulos, 1);
    }

    /**
     * Responsáveis distintos (mãe, pai, responsável legal) com nome e CPF mascarado.
     *
     * @return array<int, array{nome: string, cpf_masked: string|null}>
     */
    public function guardiansForStudent(int $codAluno): array
    {
        $row = DB::table('pmieducar.aluno as a')
            ->join('cadastro.fisica as fi', 'fi.idpes', '=', 'a.ref_idpes')
            ->where('a.cod_aluno', $codAluno)
            ->selectRaw('fi.idpes_mae as idpes_mae')
            ->selectRaw('fi.idpes_pai as idpes_pai')
            ->selectRaw('fi.idpes_responsavel as idpes_responsavel')
            ->first();

        if (!$row) {
            return [];
        }

        $order = [];
        foreach (['idpes_mae', 'idpes_pai', 'idpes_responsavel'] as $col) {
            $id = (int) ($row->{$col} ?? 0);
            if ($id > 0) {
                $order[$id] = $id;
            }
        }

        $out = [];
        foreach ($order as $idpes) {
            $p = DB::table('cadastro.pessoa as pe')
                ->leftJoin('cadastro.fisica as f', 'f.idpes', '=', 'pe.idpes')
                ->where('pe.idpes', $idpes)
                ->selectRaw('pe.nome as nome')
                ->selectRaw('f.cpf as cpf')
                ->first();

            if (!$p || trim((string) $p->nome) === '') {
                continue;
            }

            $cpfRaw = $p->cpf ?? null;
            $cpfStr = $cpfRaw !== null && $cpfRaw !== '' ? (string) $cpfRaw : null;

            $out[] = [
                'nome' => trim((string) $p->nome),
                'cpf_masked' => $this->maskCpf($cpfStr),
            ];
        }

        return $out;
    }

    /**
     * @return array{class: object, students: Collection<int, array<string,mixed>>}
     */
    public function buildFinalResults(int $schoolClassId, bool $withDetails = false): array
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
            ->selectRaw('COALESCE(ej.fantasia, ec.nm_escola, \'\') as escola')
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

        $rows = DB::table('pmieducar.matricula_turma as mt')
            ->join('pmieducar.matricula as m', 'm.cod_matricula', '=', 'mt.ref_cod_matricula')
            ->join('pmieducar.aluno as a', 'a.cod_aluno', '=', 'm.ref_cod_aluno')
            ->join('cadastro.pessoa as p', 'p.idpes', '=', 'a.ref_idpes')
            ->where('mt.ref_cod_turma', $schoolClassId)
            ->where('mt.ativo', 1)
            ->where('m.dependencia', false)
            ->selectRaw('m.cod_matricula as registration_id')
            ->selectRaw('a.cod_aluno as aluno_id')
            ->selectRaw('p.nome as student')
            ->selectRaw('m.aprovado as status_code')
            ->orderBy('p.nome')
            ->get();

        $statusMap = (new RegistrationStatus)->getDescriptiveValues();

        $students = $rows->map(function ($r) use ($statusMap, $withDetails) {
            $code = (int) ($r->status_code ?? 0);
            $registrationId = (int) $r->registration_id;

            $frequency = null;
            try {
                $freq = DB::selectOne('SELECT modules.frequencia_da_matricula(?) as frequencia', [$registrationId]);
                $frequency = $freq?->frequencia;
            } catch (\Throwable $e) {
                $frequency = null;
            }

            $details = null;
            if ($withDetails) {
                $details = $this->buildRegistrationDetails($registrationId, null);
            }

            return [
                'student' => (string) $r->student,
                'registration_id' => $registrationId,
                'aluno_id' => (int) ($r->aluno_id ?? 0),
                'status' => (string) ($statusMap[$code] ?? ('Código ' . $code)),
                'frequency' => $frequency,
                'details' => $details,
            ];
        });

        return [
            'class' => $class,
            'students' => $students,
        ];
    }

    /**
     * Ata de entrega: mesma base da ata final, com notas só das etapas informadas e dados dos responsáveis.
     *
     * @param  array<int, int>  $etapas
     * @return array{class: object, students: Collection<int, array<string,mixed>>, etapas: array<int,int>, etapa_labels: array<int,string>}
     */
    public function buildDeliveryResults(int $schoolClassId, array $etapas): array
    {
        $base = $this->buildFinalResults($schoolClassId, false);
        $students = $base['students'];

        if ($students->isEmpty()) {
            return array_merge($base, [
                'etapas' => $etapas,
                'etapa_labels' => $this->etapaLabels($etapas),
            ]);
        }

        $firstReg = (int) $students->first()['registration_id'];
        $max = $this->maxEtapasForRegistration($firstReg, $schoolClassId);
        foreach ($etapas as $e) {
            if ($e < 1 || ($max > 0 && $e > $max)) {
                throw new InvalidArgumentException(
                    $max > 0
                        ? "Etapa {$e} inválida para esta turma (use de 1 a {$max})."
                        : "Não foi possível determinar as etapas desta turma; verifique a regra de avaliação."
                );
            }
        }

        $students = $students->map(function (array $row) use ($etapas) {
            $regId = (int) $row['registration_id'];
            $alunoId = (int) ($row['aluno_id'] ?? 0);
            $row['details'] = $this->buildRegistrationDetails($regId, $etapas);
            $row['guardians'] = $alunoId > 0 ? $this->guardiansForStudent($alunoId) : [];

            return $row;
        });

        return [
            'class' => $base['class'],
            'students' => $students,
            'etapas' => $etapas,
            'etapa_labels' => $this->etapaLabels($etapas),
        ];
    }

    /**
     * @param  array<int, int>  $etapas
     * @return array<int, string>
     */
    private function etapaLabels(array $etapas): array
    {
        $labels = [];
        foreach ($etapas as $e) {
            $labels[$e] = $e . 'º período avaliativo';
        }

        return $labels;
    }

    /**
     * @param  array<int, int>|null  $onlyNumericEtapas  se não nulo, só essas etapas (sem coluna Rc)
     *
     * @return array<string,mixed>|null
     */
    private function buildRegistrationDetails(int $registrationId, ?array $onlyNumericEtapas): ?array
    {
        try {
            /** @var \Avaliacao_Service_Boletim $boletim */
            $boletim = new \Avaliacao_Service_Boletim([
                'matricula' => $registrationId,
            ]);

            $etapasTotal = (int) ($boletim->getOption('etapas') ?? 0);
            $componentes = $boletim->getComponentes() ?? [];
            $mediasComponentes = $boletim->getMediasComponentes() ?? [];

            $etapaColumns = [];
            if ($onlyNumericEtapas !== null) {
                foreach ($onlyNumericEtapas as $e) {
                    $etapaColumns[] = [
                        'key' => (string) $e,
                        'label' => $e . 'º perí.',
                    ];
                }
            }

            $rows = [];
            foreach ($componentes as $componenteId => $componente) {
                $nome = $componente->nome ?? ('Componente ' . $componenteId);

                $byStage = [];
                if ($onlyNumericEtapas === null) {
                    for ($i = 1; $i <= max($etapasTotal, 0); $i++) {
                        $nota = $boletim->getNotaComponente((int) $componenteId, $i);
                        $value = $nota?->notaArredondada ?? ($nota?->nota ?? null);
                        $byStage[(string) $i] = $value;
                    }

                    $notaRc = $boletim->getNotaComponente((int) $componenteId, 'Rc'); // @phpstan-ignore-line
                    if ($notaRc) {
                        $byStage['Rc'] = $notaRc->notaArredondada ?? ($notaRc->nota ?? null);
                    }
                } else {
                    foreach ($onlyNumericEtapas as $i) {
                        $nota = $boletim->getNotaComponente((int) $componenteId, $i);
                        $value = $nota?->notaArredondada ?? ($nota?->nota ?? null);
                        $byStage[(string) $i] = $value;
                    }
                }

                // fallback para médias quando nota não existir
                $list = $mediasComponentes[$componenteId] ?? [];
                foreach ($list as $m) {
                    $stageKey = (string) ($m->etapa ?? '');
                    if ($stageKey === '' || $stageKey === 'Rc') {
                        continue;
                    }
                    if ($onlyNumericEtapas !== null && ! in_array((int) $stageKey, $onlyNumericEtapas, true)) {
                        continue;
                    }
                    if (array_key_exists($stageKey, $byStage) && $byStage[$stageKey] !== null && $byStage[$stageKey] !== '') {
                        continue;
                    }
                    $byStage[$stageKey] = $m->mediaArredondada ?? ($m->media ?? null);
                }

                $rows[] = [
                    'id' => (int) $componenteId,
                    'nome' => (string) $nome,
                    'etapas' => $byStage,
                ];
            }

            usort($rows, fn ($a, $b) => strcmp($a['nome'], $b['nome']));

            if ($onlyNumericEtapas !== null) {
                return [
                    'etapas_count' => count($onlyNumericEtapas),
                    'etapa_columns' => $etapaColumns,
                    'rows' => $rows,
                    'include_rc' => false,
                ];
            }

            return [
                'etapas_count' => $etapasTotal,
                'etapa_columns' => [],
                'rows' => $rows,
                'include_rc' => true,
            ];
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * Ata de conselho de classe: um bloco por turma (notas nas etapas informadas), assinaturas professor(es) e secretário.
     *
     * @param  array<int, int>  $turmaIds
     * @param  array<int, int>  $etapas
     * @return array{blocks: array<int, array<string, mixed>>}
     */
    public function buildCouncilClassMinutes(array $turmaIds, array $etapas): array
    {
        $blocks = [];
        foreach ($turmaIds as $tid) {
            $tid = (int) $tid;
            if ($tid <= 0) {
                continue;
            }
            $data = $this->buildDeliveryResults($tid, $etapas);
            $escolaId = (int) ($data['class']->escola_id ?? 0);
            $secretary = '';
            if ($escolaId > 0) {
                $secretary = (string) (DB::table('pmieducar.escola as e')
                    ->leftJoin('cadastro.pessoa as ps', 'ps.idpes', '=', 'e.ref_idpes_secretario_escolar')
                    ->where('e.cod_escola', $escolaId)
                    ->value('ps.nome') ?? '');
            }

            $students = $data['students']->map(function (array $row) {
                unset($row['guardians']);

                return $row;
            })->values();

            $blocks[] = [
                'class' => $data['class'],
                'students' => $students,
                'etapas' => $data['etapas'],
                'etapa_labels' => $data['etapa_labels'],
                'professors' => $this->professorNamesForTurma($tid),
                'secretary_name' => $secretary,
            ];
        }

        return ['blocks' => $blocks];
    }

    /**
     * @return array<int, string>
     */
    public function professorNamesForTurma(int $turmaId): array
    {
        $anoTurma = (int) (DB::table('pmieducar.turma')->where('cod_turma', $turmaId)->value('ano') ?? 0);

        $names = DB::table('modules.professor_turma as pt')
            ->join('cadastro.pessoa as p', 'p.idpes', '=', 'pt.servidor_id')
            ->where('pt.turma_id', $turmaId)
            ->when($anoTurma > 0, fn ($q) => $q->where('pt.ano', $anoTurma))
            ->orderBy('p.nome')
            ->distinct()
            ->pluck('p.nome')
            ->map(fn ($n) => trim((string) $n))
            ->filter()
            ->values()
            ->all();

        return array_values(array_unique($names));
    }
}

