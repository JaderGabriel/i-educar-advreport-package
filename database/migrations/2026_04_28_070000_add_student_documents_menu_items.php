<?php

use App\Menu;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $docs = Menu::query()->where('old', 9999750)->first();
        if (!$docs) {
            return;
        }

        // Declarações (ordem padrão)
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
    }

    public function down(): void
    {
        Menu::query()->where('old', 9999754)->delete();
    }
};

