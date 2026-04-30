<?php

use App\Menu;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $docs = Menu::query()->where('old', 9999750)->first();
        if (! $docs) {
            return;
        }

        // Fichas (grupo) — primeiro item, antes de Boletins
        $forms = Menu::query()->updateOrCreate(
            ['parent_id' => $docs->getKey(), 'old' => 9999757],
            [
                'title' => 'Fichas',
                'order' => 1,
                'parent_old' => 9999750,
                'type' => 3,
                'active' => true,
            ]
        );

        // Opções dentro de Fichas
        Menu::query()->updateOrCreate(
            ['parent_id' => $forms->getKey(), 'old' => 9999758],
            [
                'title' => 'Ficha individual',
                'order' => 1,
                'parent_old' => 9999757,
                'type' => 3,
                'active' => true,
                'link' => '/relatorios-avancados/fichas/ficha-individual',
            ]
        );

        Menu::query()->updateOrCreate(
            ['parent_id' => $forms->getKey(), 'old' => 9999759],
            [
                'title' => 'Ficha de matrícula',
                'order' => 2,
                'parent_old' => 9999757,
                'type' => 3,
                'active' => true,
                'link' => '/relatorios-avancados/fichas/ficha-matricula',
            ]
        );

        // Reordena os itens já existentes em "Documentos do aluno"
        Menu::query()->where('old', 9999755)->update(['order' => 2]); // Boletins
        Menu::query()->where('old', 9999754)->update(['order' => 3]); // Declarações
        Menu::query()->where('old', 9999756)->update(['order' => 4]); // Históricos
        Menu::query()->where('old', 9999753)->update(['order' => 5]); // Diplomas e Certificados
    }

    public function down(): void
    {
        Menu::query()->whereIn('old', [9999758, 9999759])->delete();
        Menu::query()->where('old', 9999757)->delete();
    }
};

