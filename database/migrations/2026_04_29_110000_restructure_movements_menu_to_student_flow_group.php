<?php

use App\Menu;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $group = Menu::query()->where('old', 9999701)->first(); // Escola → Relatórios → (Movimentações antigo)
        if (!$group) {
            return;
        }

        // Renomeia para virar apenas um chamador de submenu (sem link)
        $group->update([
            'title' => 'Fluxo de Alunos',
            'link' => null,
        ]);

        // Garante que exista a entrada inicial “Movimentações” dentro do grupo
        $movementsItem = Menu::query()->updateOrCreate(
            ['parent_id' => $group->getKey(), 'old' => 9999704],
            [
                'title' => 'Movimentações',
                'order' => 1,
                'parent_old' => (int) $group->old,
                'type' => 3,
                'active' => true,
                'link' => '/relatorios-avancados/movimentacoes',
            ]
        );

        // Ajusta ordem dos itens já existentes (se houver) para virem depois de “Movimentações”
        Menu::query()
            ->where('parent_id', $group->getKey())
            ->where('old', 9999710) // Vagas por turma
            ->update(['order' => 2]);

        Menu::query()
            ->where('parent_id', $group->getKey())
            ->where('old', 9999711) // Alunos por situação
            ->update(['order' => 3]);

        // Caso o grupo antigo possuísse link, assegura que apenas o item “Movimentações” mantém o link.
        // (Evita duplicidade de navegação no menu).
        if ($group->link === '/relatorios-avancados/movimentacoes') {
            $group->update(['link' => null]);
        }
    }

    public function down(): void
    {
        $group = Menu::query()->where('old', 9999701)->first();
        if (!$group) {
            return;
        }

        // Remove item filho “Movimentações” criado
        Menu::query()->where('old', 9999704)->delete();

        // Reverte o nome e o link do grupo para o comportamento anterior (link direto)
        $group->update([
            'title' => 'Movimentações',
            'link' => '/relatorios-avancados/movimentacoes',
        ]);

        // Restaura as ordens originais dos itens (se existirem)
        Menu::query()
            ->where('parent_id', $group->getKey())
            ->where('old', 9999710)
            ->update(['order' => 2]);

        Menu::query()
            ->where('parent_id', $group->getKey())
            ->where('old', 9999711)
            ->update(['order' => 3]);
    }
};

