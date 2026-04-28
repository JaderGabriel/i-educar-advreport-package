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

        // Frequência consolidada (regra global ou por componente) já implementada na função SQL do core.
        $freq = DB::selectOne('SELECT modules.frequencia_da_matricula(?) as frequencia', [$matriculaId]);

        $rows = [];
        foreach ($componentes as $componenteId => $componente) {
            $nome = $componente->nome ?? ('Componente ' . $componenteId);

            $byStage = [];
            $list = $mediasComponentes[$componenteId] ?? [];
            foreach ($list as $m) {
                // Cada item é uma entidade/obj com ->etapa e ->media (ou ->mediaArredondada em alguns casos)
                $stageKey = (string) ($m->etapa ?? '');
                $value = $m->mediaArredondada ?? ($m->media ?? null);
                $byStage[$stageKey] = $value;
            }

            $row = [
                'id' => (int) $componenteId,
                'nome' => (string) $nome,
                'etapas' => [],
            ];

            for ($i = 1; $i <= max($etapas, 0); $i++) {
                $row['etapas'][(string) $i] = $byStage[(string) $i] ?? null;
            }

            // Recuperação (quando existir) costuma ser 'Rc' (ver regras/fluxo do boletim).
            if (array_key_exists('Rc', $byStage)) {
                $row['etapas']['Rc'] = $byStage['Rc'];
            }

            $rows[] = $row;
        }

        usort($rows, fn ($a, $b) => strcmp($a['nome'], $b['nome']));

        return [
            'matricula' => $matricula,
            'etapas_count' => $etapas,
            'frequencia' => $freq?->frequencia,
            'rows' => $rows,
        ];
    }
}

