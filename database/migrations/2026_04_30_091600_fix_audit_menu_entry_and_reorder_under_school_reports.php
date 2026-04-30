<?php

use App\Menu;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $schoolReports = Menu::query()->where('old', 21126)->first(); // Escola → Relatórios
        if (! $schoolReports) {
            return;
        }

        // Algumas migrations antigas criaram Auditoria usando "old" que conflita com outros itens (ex.: 9999704/9999713).
        // Removemos apenas quando "Auditoria" está diretamente sob Escola → Relatórios.
        $conflictingAuditGroups = Menu::query()
            ->where('parent_id', $schoolReports->getKey())
            ->whereIn('old', [9999704, 9999713])
            ->where('title', 'ILIKE', '%auditor%')
            ->get();

        $conflictingAuditGroups->each(static function (Menu $group): void {
            Menu::query()->where('parent_id', $group->id)->delete();
            $group->delete();
        });

        // Garante um item único "Auditoria" após "Indicadores" (ordem 4).
        // Usamos um "old" dedicado e fora da faixa já ocupada nos outros submenus temáticos.
        Menu::query()->updateOrCreate(
            ['parent_id' => $schoolReports->getKey(), 'old' => 9999720],
            [
                'title' => 'Auditoria',
                'description' => 'Acessos, ações executadas e alterações de dados (trilha de auditoria).',
                'order' => 4,
                'parent_old' => (int) $schoolReports->old,
                'type' => 3,
                'active' => true,
                'link' => '/relatorios-avancados/auditoria/acessos-acoes',
            ]
        );
    }

    public function down(): void
    {
        Menu::query()->where('old', 9999720)->delete();
    }
};

