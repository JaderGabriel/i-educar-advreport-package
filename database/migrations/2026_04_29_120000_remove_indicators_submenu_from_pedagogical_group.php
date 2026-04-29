<?php

use App\Menu;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $menu = Menu::query()->where('old', 9999720)->first();
        if (! $menu) {
            return;
        }

        DB::table('pmieducar.menu_tipo_usuario')->where('menu_id', $menu->getKey())->delete();
        $menu->delete();
    }

    public function down(): void
    {
        // Não recria o item removido.
    }
};

