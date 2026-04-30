<?php

namespace iEducar\Packages\AdvancedReports\Services;

use Illuminate\Support\Facades\DB;

class MovementsGeneralReportService
{
    /**
     * Constrói os dados do relatório de movimento geral,
     * espelhando a lógica do MovimentoGeralQueryFactory.
     *
     * @param int         $year          Ano letivo
     * @param int|null    $institutionId Instituição (opcional)
     * @param array<int>|null $courseIds Cursos filtrados (opcional)
     * @param string      $startDate     Data inicial (YYYY-MM-DD)
     * @param string      $endDate       Data final (YYYY-MM-DD)
     * @return array<int, array<string, mixed>>
     */
    public function buildData(
        int $year,
        ?int $institutionId,
        ?array $courseIds,
        string $startDate,
        string $endDate
    ): array {
        // Query legacy exige instituição. Sem ela, não há como reproduzir a contagem corretamente.
        if (empty($institutionId) || $institutionId < 1) {
            return [];
        }

        // Por ora delegamos para a view SQL existente (MovimentoGeralQueryFactory) via consulta bruta.
        // Isso garante resultados idênticos ao report legado enquanto evoluímos
        // para uma versão totalmente em Query Builder/Eloquent.

        $params = [
            'instituicao' => $institutionId,
            'ano' => $year,
            'data_inicial' => $startDate,
            'data_final' => $endDate,
            'seleciona_curso' => empty($courseIds) ? 0 : 1,
            'curso' => empty($courseIds) ? null : implode(',', $courseIds),
        ];

        // Aqui poderia ser reimplementado em Query Builder. Mantemos simples por enquanto.
        $connection = DB::connection()->getPdo();

        // Reusa a mesma factory de queries do módulo legacy para garantir fidelidade.
        $factoryClass = '\\iEducar\\Modules\\Reports\\QueryFactory\\MovimentoGeralQueryFactory';

        if (class_exists($factoryClass)) {
            /** @var object $factory */
            $factory = new $factoryClass(connection: $connection, params: $params);

            return $factory->getData();
        }

        // Fallback seguro: sem dados se a factory legacy não estiver disponível.
        return [];
    }
}

