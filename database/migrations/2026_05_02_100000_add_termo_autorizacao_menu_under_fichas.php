<?php

use App\Menu;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Documentos do aluno → Fichas: item "Termo de Autorização" (uso de imagem e voz), separado da ficha de matrícula.
 */
return new class extends Migration
{
    public function up(): void
    {
        $fichas = Menu::query()->where('old', 9999757)->first();
        if (! $fichas) {
            return;
        }

        Menu::query()->updateOrCreate(
            ['parent_id' => $fichas->getKey(), 'old' => 9999761],
            [
                'title' => 'Termo de Autorização',
                'order' => 3,
                'parent_old' => 9999757,
                'type' => 3,
                'active' => true,
                'link' => '/relatorios-avancados/fichas/termo-autorizacao',
            ]
        );

        $this->grantChildrenOfParentOld(9999750, [9999761]);
    }

    public function down(): void
    {
        $id = Menu::query()->where('old', 9999761)->value('id');
        if ($id) {
            DB::table('pmieducar.menu_tipo_usuario')->where('menu_id', $id)->delete();
            Menu::query()->where('id', $id)->delete();
        }
    }

    private function grantChildrenOfParentOld(int $parentOld, array $childrenOlds): void
    {
        $parentId = Menu::query()->where('old', $parentOld)->value('id');
        if (! $parentId) {
            return;
        }

        foreach ($childrenOlds as $old) {
            $childId = Menu::query()->where('old', $old)->value('id');
            if (! $childId) {
                continue;
            }

            DB::statement(
                'INSERT INTO pmieducar.menu_tipo_usuario (ref_cod_tipo_usuario, cadastra, visualiza, exclui, menu_id)
                    SELECT ref_cod_tipo_usuario, cadastra, visualiza, exclui, ' . (int) $childId . '
                      FROM pmieducar.menu_tipo_usuario
                     WHERE menu_id = ' . (int) $parentId . '
                       AND NOT EXISTS (
                         SELECT 1 FROM pmieducar.menu_tipo_usuario mtu
                          WHERE mtu.ref_cod_tipo_usuario = menu_tipo_usuario.ref_cod_tipo_usuario
                            AND mtu.menu_id = ' . (int) $childId . '
                       )'
            );
        }
    }
};
