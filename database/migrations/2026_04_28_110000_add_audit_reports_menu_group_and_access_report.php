<?php

use App\Menu;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $schoolReports = Menu::query()->where('old', 21126)->first(); // Escola → Relatórios
        if (!$schoolReports) {
            return;
        }

        $auditGroup = Menu::query()->updateOrCreate(
            ['parent_id' => $schoolReports->getKey(), 'old' => 9999704],
            [
                'title' => 'Auditoria',
                'description' => 'Acessos, ações executadas e alterações de dados (trilha de auditoria).',
                'order' => 4,
                'parent_old' => (int) $schoolReports->old,
                'type' => 3,
                'active' => true,
            ]
        );

        Menu::query()->updateOrCreate(
            ['parent_id' => $auditGroup->getKey(), 'old' => 9999713],
            [
                'title' => 'Acessos e ações de usuários',
                'description' => 'Relatório detalhado de acessos (login) e trilha de auditoria (URLs/rotinas + antes/depois).',
                'order' => 1,
                'parent_old' => (int) $auditGroup->old,
                'type' => 3,
                'active' => true,
                'link' => '/relatorios-avancados/auditoria/acessos-acoes',
            ]
        );
    }

    public function down(): void
    {
        Menu::query()->whereIn('old', [9999713, 9999704])->delete();
    }
};

