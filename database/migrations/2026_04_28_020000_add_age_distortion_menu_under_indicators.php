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
            ['parent_id' => $indicators->getKey(), 'old' => 9999705],
            [
                'title' => 'Distorção idade/série',
                'order' => 3,
                'parent_old' => 9999702,
                'type' => 3,
                'active' => true,
                'link' => '/relatorios-avancados/distorcao-idade-serie',
            ]
        );
    }

    public function down(): void
    {
        Menu::query()->where('old', 9999705)->delete();
    }
};

