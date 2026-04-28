<?php

use App\Menu;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $indicators = Menu::query()->where('old', 9999702)->first();
        if (! $indicators) {
            return;
        }

        Menu::query()->updateOrCreate(
            ['parent_id' => $indicators->getKey(), 'old' => 9999704],
            [
                'title' => 'Inclusão (NEE)',
                'order' => 2,
                'parent_old' => 9999702,
                'type' => 3,
                'active' => true,
                'link' => '/relatorios-avancados/inclusao',
            ]
        );
    }

    public function down(): void
    {
        Menu::query()->where('old', 9999704)->delete();
    }
};

