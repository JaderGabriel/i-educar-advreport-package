<?php

use App\Menu;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        // Vagas por turma
        Menu::query()
            ->where('old', 9999710)
            ->update([
                'title' => 'Vagas por turma (capacidade/ocupação)',
                'description' => 'Capacidade (max_aluno), matriculados e vagas disponíveis por turma.',
            ]);

        // Atas
        Menu::query()
            ->where('old', 9999760)
            ->update([
                'title' => 'Atas (resultado final / assinaturas)',
                'description' => 'Emissão de atas em PDF com validação pública (QR Code).',
            ]);
    }

    public function down(): void
    {
        // best-effort: volta apenas o título anterior
        Menu::query()
            ->where('old', 9999710)
            ->update([
                'title' => 'Vagas por turma',
                'description' => null,
            ]);

        Menu::query()
            ->where('old', 9999760)
            ->update([
                'title' => 'Atas (resultado final / assinaturas)',
                'description' => null,
            ]);
    }
};

