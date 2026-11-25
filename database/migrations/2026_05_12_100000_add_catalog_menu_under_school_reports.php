<?php

use App\Menu;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Escola → Relatórios: entrada "Catálogo (editais)" apontando para o painel
 * /relatorios-avancados (visão de cobertura PDF/Excel/QR).
 */
return new class extends Migration
{
    public function up(): void
    {
        $schoolReports = Menu::query()->where('old', 21126)->first();
        if (! $schoolReports) {
            return;
        }

        Menu::query()->updateOrCreate(
            ['parent_id' => $schoolReports->getKey(), 'old' => 9999705],
            [
                'title' => 'Catálogo (editais)',
                'order' => 0,
                'parent_old' => 21126,
                'type' => 3,
                'active' => true,
                'link' => '/relatorios-avancados',
            ]
        );

        $this->grantChildrenOfParentOld(21126, [9999705]);
    }

    public function down(): void
    {
        $ids = Menu::query()->where('old', 9999705)->pluck('id')->all();
        if (! empty($ids)) {
            DB::table('pmieducar.menu_tipo_usuario')->whereIn('menu_id', $ids)->delete();
            Menu::query()->whereIn('id', $ids)->delete();
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
                    SELECT ref_cod_tipo_usuario, cadastra, visualiza, exclui, '.(int) $childId.'
                      FROM pmieducar.menu_tipo_usuario
                     WHERE menu_id = '.(int) $parentId.'
                       AND NOT EXISTS (
                         SELECT 1 FROM pmieducar.menu_tipo_usuario mtu
                          WHERE mtu.ref_cod_tipo_usuario = menu_tipo_usuario.ref_cod_tipo_usuario
                            AND mtu.menu_id = '.(int) $childId.'
                       )'
            );
        }
    }
};
