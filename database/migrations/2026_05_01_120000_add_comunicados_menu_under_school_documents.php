<?php

use App\Menu;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Escola → Documentos: grupo "Comunicados" antes de "Atas e registros formais",
 * com opções do item 8.6 (comunicados oficiais), exceto livro/ficha de ocorrências.
 */
return new class extends Migration
{
    public function up(): void
    {
        $schoolDocs = Menu::query()->where('old', 21127)->first();
        if (! $schoolDocs) {
            return;
        }

        Menu::query()->where('old', 9999751)->update(['order' => 3]);
        Menu::query()->where('old', 9999752)->update(['order' => 4]);

        // Faixa 999977x: evita conflito com 9999760 (item "Atas" sob 9999751).
        $comunicados = Menu::query()->updateOrCreate(
            ['parent_id' => $schoolDocs->getKey(), 'old' => 9999770],
            [
                'title' => 'Comunicados',
                'order' => 2,
                'parent_old' => 21127,
                'type' => 3,
                'active' => true,
            ]
        );

        Menu::query()->updateOrCreate(
            ['parent_id' => $comunicados->getKey(), 'old' => 9999771],
            [
                'title' => 'Convocações',
                'order' => 1,
                'parent_old' => 9999770,
                'type' => 3,
                'active' => true,
                'link' => '/relatorios-avancados/comunicados/convocacao',
            ]
        );

        Menu::query()->updateOrCreate(
            ['parent_id' => $comunicados->getKey(), 'old' => 9999772],
            [
                'title' => 'Reuniões',
                'order' => 2,
                'parent_old' => 9999770,
                'type' => 3,
                'active' => true,
                'link' => '/relatorios-avancados/comunicados/reuniao',
            ]
        );

        Menu::query()->updateOrCreate(
            ['parent_id' => $comunicados->getKey(), 'old' => 9999773],
            [
                'title' => 'Advertências',
                'order' => 3,
                'parent_old' => 9999770,
                'type' => 3,
                'active' => true,
                'link' => '/relatorios-avancados/comunicados/advertencia',
            ]
        );

        Menu::query()->updateOrCreate(
            ['parent_id' => $comunicados->getKey(), 'old' => 9999774],
            [
                'title' => 'Comunicado geral',
                'order' => 4,
                'parent_old' => 9999770,
                'type' => 3,
                'active' => true,
                'link' => '/relatorios-avancados/comunicados/comunicado-geral',
            ]
        );

        $this->grantChildrenOfParentOld(21127, [9999770, 9999771, 9999772, 9999773, 9999774]);
    }

    public function down(): void
    {
        $ids = Menu::query()->whereIn('old', [9999771, 9999772, 9999773, 9999774, 9999770])->pluck('id')->all();
        if (! empty($ids)) {
            DB::table('pmieducar.menu_tipo_usuario')->whereIn('menu_id', $ids)->delete();
            Menu::query()->whereIn('id', $ids)->delete();
        }

        Menu::query()->where('old', 9999751)->update(['order' => 2]);
        Menu::query()->where('old', 9999752)->update(['order' => 3]);
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
