<?php

use App\Menu;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $indicators = Menu::query()->where('old', 9999702)->first();
        if (! $indicators) {
            return;
        }

        // Subgrupos temáticos dentro de Indicadores
        $context = Menu::query()->updateOrCreate(
            ['parent_id' => $indicators->getKey(), 'old' => 9999710],
            [
                'title' => 'Contexto social',
                'order' => 1,
                'parent_old' => 9999702,
                'type' => 3,
                'active' => true,
            ]
        );

        $flow = Menu::query()->updateOrCreate(
            ['parent_id' => $indicators->getKey(), 'old' => 9999711],
            [
                'title' => 'Fluxo escolar',
                'order' => 2,
                'parent_old' => 9999702,
                'type' => 3,
                'active' => true,
            ]
        );

        $performance = Menu::query()->updateOrCreate(
            ['parent_id' => $indicators->getKey(), 'old' => 9999712],
            [
                'title' => 'Desempenho e resultados',
                'order' => 3,
                'parent_old' => 9999702,
                'type' => 3,
                'active' => true,
            ]
        );

        // Move itens existentes para os grupos (mantém olds originais)
        Menu::query()->where('old', 9999703)->update([
            'parent_id' => $context->getKey(),
            'parent_old' => 9999710,
            'order' => 1,
        ]); // Socioeconômicos

        Menu::query()->where('old', 9999704)->update([
            'parent_id' => $context->getKey(),
            'parent_old' => 9999710,
            'order' => 2,
        ]); // Inclusão

        Menu::query()->where('old', 9999707)->update([
            'parent_id' => $context->getKey(),
            'parent_old' => 9999710,
            'order' => 3,
        ]); // Vulnerabilidade

        Menu::query()->where('old', 9999705)->update([
            'parent_id' => $flow->getKey(),
            'parent_old' => 9999711,
            'order' => 1,
        ]); // Distorção

        // Novos indicadores de desempenho/resultado
        Menu::query()->updateOrCreate(
            ['parent_id' => $performance->getKey(), 'old' => 9999713],
            [
                'title' => 'Alunos com baixo desempenho',
                'order' => 1,
                'parent_old' => 9999712,
                'type' => 3,
                'active' => true,
                'link' => '/relatorios-avancados/indicadores/baixo-desempenho',
            ]
        );

        Menu::query()->updateOrCreate(
            ['parent_id' => $performance->getKey(), 'old' => 9999714],
            [
                'title' => 'Alunos com alto desempenho',
                'order' => 2,
                'parent_old' => 9999712,
                'type' => 3,
                'active' => true,
                'link' => '/relatorios-avancados/indicadores/alto-desempenho',
            ]
        );

        Menu::query()->updateOrCreate(
            ['parent_id' => $performance->getKey(), 'old' => 9999715],
            [
                'title' => 'Alunos sem nota lançada',
                'order' => 3,
                'parent_old' => 9999712,
                'type' => 3,
                'active' => true,
                'link' => '/relatorios-avancados/indicadores/sem-nota',
            ]
        );

        Menu::query()->updateOrCreate(
            ['parent_id' => $flow->getKey(), 'old' => 9999716],
            [
                'title' => 'Matriculados e não enturmados',
                'order' => 2,
                'parent_old' => 9999711,
                'type' => 3,
                'active' => true,
                'link' => '/relatorios-avancados/indicadores/nao-enturmados',
            ]
        );

        Menu::query()->updateOrCreate(
            ['parent_id' => $performance->getKey(), 'old' => 9999717],
            [
                'title' => 'Comparativo de médias da turma',
                'order' => 4,
                'parent_old' => 9999712,
                'type' => 3,
                'active' => true,
                'link' => '/relatorios-avancados/indicadores/comparativo-turma',
            ]
        );

        // Removido: submenu “Indicadores (desempenho/resultado)” em “Avaliação e frequência”.
    }

    public function down(): void
    {
        Menu::query()->whereIn('old', [
            9999710, 9999711, 9999712,
            9999713, 9999714, 9999715, 9999716, 9999717,
        ])->delete();
    }
};

