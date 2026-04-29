<?php

use App\Menu;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Copia permissões do menu pai para os menus do pacote,
     * garantindo que eles apareçam na configuração de "Tipos de usuários".
     */
    public function up(): void
    {
        $this->grantByParentOld(21126, [
            9999700, 9999701, 9999702, 9999703, 9999704, 9999705, 9999707,
            9999710, 9999711, 9999712, 9999713, 9999714, 9999715, 9999716, 9999717, 9999720,
        ]);

        $this->grantByParentOld(21127, [
            9999750, 9999751, 9999752, 9999753, 9999754, 9999755, 9999756,
        ]);

        // Servidores → Relatórios (quando existir)
        $this->grantByParentOld(999913, [
            9999800,
        ]);
    }

    public function down(): void
    {
        $olds = [
            9999700, 9999701, 9999702, 9999703, 9999704, 9999705, 9999707,
            9999710, 9999711, 9999712, 9999713, 9999714, 9999715, 9999716, 9999717, 9999720,
            9999750, 9999751, 9999752, 9999753, 9999754, 9999755, 9999756,
            9999800,
        ];

        $menuIds = Menu::query()->whereIn('old', $olds)->pluck('id')->all();
        if (!empty($menuIds)) {
            DB::table('pmieducar.menu_tipo_usuario')->whereIn('menu_id', $menuIds)->delete();
        }
    }

    private function grantByParentOld(int $parentOld, array $childrenOlds): void
    {
        $parentId = Menu::query()->where('old', $parentOld)->value('id');
        if (!$parentId) {
            return;
        }

        foreach ($childrenOlds as $old) {
            $childId = Menu::query()->where('old', $old)->value('id');
            if (!$childId) {
                continue;
            }

            // Copia as permissões do pai para cada tipo de usuário que já tem o pai.
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

