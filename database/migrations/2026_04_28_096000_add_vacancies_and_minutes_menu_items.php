<?php

use App\Menu;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $movementsGroup = Menu::query()->where('old', 9999701)->first(); // Escola → Relatórios → Movimentações
        if ($movementsGroup) {
            Menu::query()->updateOrCreate(
                ['parent_id' => $movementsGroup->getKey(), 'old' => 9999710],
                [
                    'title' => 'Vagas por turma',
                    'order' => 2,
                    'parent_old' => (int) $movementsGroup->old,
                    'type' => 3,
                    'active' => true,
                    'link' => '/relatorios-avancados/vagas-turmas',
                ]
            );
        }

        $minutesGroup = Menu::query()->where('old', 9999751)->first(); // Escola → Documentos → Atas e registros formais
        if ($minutesGroup) {
            Menu::query()->updateOrCreate(
                ['parent_id' => $minutesGroup->getKey(), 'old' => 9999760],
                [
                    'title' => 'Atas (resultado final / assinaturas)',
                    'order' => 1,
                    'parent_old' => (int) $minutesGroup->old,
                    'type' => 3,
                    'active' => true,
                    'link' => '/relatorios-avancados/atas',
                ]
            );
        }
    }

    public function down(): void
    {
        Menu::query()->whereIn('old', [9999710, 9999760])->delete();
    }
};

