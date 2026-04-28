<?php

use App\Menu;
use App\Process;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $pedagogicalGroup = Menu::query()->where('old', 9999700)->first(); // Escola → Relatórios → Avaliação e frequência
        if ($pedagogicalGroup) {
            $this->createChild($pedagogicalGroup, 9999704, 'Mapa de notas (turma/etapa)', 1, '/relatorios-avancados/pedagogico/mapa-notas');
            $this->createChild($pedagogicalGroup, 9999705, 'Mapa de frequência (turma/etapa)', 2, '/relatorios-avancados/pedagogico/mapa-frequencia');
            $this->createChild($pedagogicalGroup, 9999706, 'Espelho de diário', 3, '/relatorios-avancados/pedagogico/espelho-diario');
            $this->createChild($pedagogicalGroup, 9999707, 'Pendências de lançamento (notas/frequência)', 4, '/relatorios-avancados/pendencias-lancamento');
        }

        $minutesGroup = Menu::query()->where('old', 9999751)->first(); // Escola → Documentos → Atas e registros formais
        if ($minutesGroup) {
            $this->createChild($minutesGroup, 9999761, 'Ata de conselho de classe (por etapa)', 2, '/relatorios-avancados/pedagogico/ata-conselho');
            $this->createChild($minutesGroup, 9999762, 'Ata de entrega de resultados (assinaturas)', 3, '/relatorios-avancados/pedagogico/ata-entrega-resultados');
        }
    }

    public function down(): void
    {
        Menu::query()->whereIn('old', [
            9999704, 9999705, 9999706, 9999707,
            9999761, 9999762,
        ])->delete();
    }

    private function createChild(Menu $parent, int $old, string $title, int $order, string $link): Menu
    {
        return Menu::query()->updateOrCreate(
            ['parent_id' => $parent->getKey(), 'old' => $old],
            [
                'title' => $title,
                'order' => $order,
                'parent_old' => (int) $parent->old,
                'type' => 3,
                'active' => true,
                'link' => $link,
                'process' => Process::MENU_SCHOOL,
            ]
        );
    }
};

