<?php

use App\Process;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Para que os itens apareçam em "Configurações → Tipos de usuário", o Menu::processes()
        // exige que o campo `menus.process` esteja preenchido.
        // Aqui, marcamos apenas os itens com link do Advanced Reports.
        DB::table('public.menus')
            ->whereNotNull('link')
            ->where('link', 'like', '/relatorios-avancados%')
            ->update(['process' => Process::MENU_SCHOOL]);
    }

    public function down(): void
    {
        DB::table('public.menus')
            ->whereNotNull('link')
            ->where('link', 'like', '/relatorios-avancados%')
            ->update(['process' => null]);
    }
};

