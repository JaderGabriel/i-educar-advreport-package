<?php

namespace iEducar\Packages\AdvancedReports\Services;

use App\Models\RegistrationStatus;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class MinutesService
{
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
                $details = $this->buildRegistrationDetails($registrationId);
            }

            return [
                'student' => (string) $r->student,
                'registration_id' => $registrationId,
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
     * @return array<string,mixed>|null
     */
    private function buildRegistrationDetails(int $registrationId): ?array
    {
        try {
            /** @var \Avaliacao_Service_Boletim $boletim */
            $boletim = new \Avaliacao_Service_Boletim([
                'matricula' => $registrationId,
            ]);

            $etapas = (int) ($boletim->getOption('etapas') ?? 0);
            $componentes = $boletim->getComponentes() ?? [];
            $mediasComponentes = $boletim->getMediasComponentes() ?? [];

            $rows = [];
            foreach ($componentes as $componenteId => $componente) {
                $nome = $componente->nome ?? ('Componente ' . $componenteId);

                $byStage = [];
                for ($i = 1; $i <= max($etapas, 0); $i++) {
                    $nota = $boletim->getNotaComponente((int) $componenteId, $i);
                    $value = $nota?->notaArredondada ?? ($nota?->nota ?? null);
                    $byStage[(string) $i] = $value;
                }

                $notaRc = $boletim->getNotaComponente((int) $componenteId, 'Rc'); // @phpstan-ignore-line
                if ($notaRc) {
                    $byStage['Rc'] = $notaRc->notaArredondada ?? ($notaRc->nota ?? null);
                }

                // fallback para médias quando nota não existir
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

                $rows[] = [
                    'id' => (int) $componenteId,
                    'nome' => (string) $nome,
                    'etapas' => $byStage,
                ];
            }

            usort($rows, fn ($a, $b) => strcmp($a['nome'], $b['nome']));

            return [
                'etapas_count' => $etapas,
                'rows' => $rows,
            ];
        } catch (\Throwable $e) {
            return null;
        }
    }
}

