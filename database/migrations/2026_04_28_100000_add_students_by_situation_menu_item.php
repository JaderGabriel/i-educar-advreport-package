<?php

use App\Menu;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $movementsGroup = Menu::query()->where('old', 9999701)->first(); // Escola → Relatórios → Movimentações
        if (!$movementsGroup) {
            return;
        }

        Menu::query()->updateOrCreate(
            ['parent_id' => $movementsGroup->getKey(), 'old' => 9999711],
            [
                'title' => 'Alunos por situação',
                'description' => 'Listagem/Resumo por situação de matrícula (cursando, transferido, abandono, etc.).',
                'order' => 3,
                'parent_old' => (int) $movementsGroup->old,
                'type' => 3,
                'active' => true,
                'link' => '/relatorios-avancados/alunos-por-situacao',
            ]
        );
    }

    public function down(): void
    {
        Menu::query()->where('old', 9999711)->delete();
    }
};

