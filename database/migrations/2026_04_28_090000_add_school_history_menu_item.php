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

        Menu::query()->updateOrCreate(
            ['parent_id' => $docs->getKey(), 'old' => 9999756],
            [
                'title' => 'Histórico escolar (PDF)',
                'order' => 3,
                'parent_old' => 9999750,
                'type' => 3,
                'active' => true,
                'link' => '/relatorios-avancados/historico',
            ]
        );
    }

    public function down(): void
    {
        Menu::query()->where('old', 9999756)->delete();
    }
};

