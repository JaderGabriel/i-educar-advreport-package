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

        // Ordem desejada: Boletins, Declarações, Históricos, Diplomas e Certificados.
        Menu::query()->updateOrCreate(
            ['parent_id' => $docs->getKey(), 'old' => 9999755],
            [
                'title' => 'Boletins',
                'order' => 1,
                'parent_old' => 9999750,
                'type' => 3,
                'active' => true,
                'link' => '/relatorios-avancados/boletim',
            ]
        );

        Menu::query()->updateOrCreate(
            ['parent_id' => $docs->getKey(), 'old' => 9999754],
            [
                'title' => 'Declarações',
                'order' => 2,
                'parent_old' => 9999750,
                'type' => 3,
                'active' => true,
                'link' => '/relatorios-avancados/documentos',
            ]
        );

        Menu::query()->updateOrCreate(
            ['parent_id' => $docs->getKey(), 'old' => 9999756],
            [
                'title' => 'Históricos',
                'order' => 3,
                'parent_old' => 9999750,
                'type' => 3,
                'active' => true,
                'link' => '/relatorios-avancados/historico',
            ]
        );

        Menu::query()->updateOrCreate(
            ['parent_id' => $docs->getKey(), 'old' => 9999753],
            [
                'title' => 'Diplomas e Certificados',
                'order' => 4,
                'parent_old' => 9999750,
                'type' => 3,
                'active' => true,
                'link' => '/relatorios-avancados/diplomas',
            ]
        );
    }

    public function down(): void
    {
        // Mantém a ordem/títulos; não desfaz.
    }
};

