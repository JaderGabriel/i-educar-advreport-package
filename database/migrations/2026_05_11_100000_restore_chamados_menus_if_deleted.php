<?php

use App\Menu;
use App\Process;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Restaura menus do pacote chamados caso tenham sido removidos indevidamente
 * pela migration 2026_04_28_000000 (que incluía olds 999970–999974 na lista
 * de remoção do advanced reports legado).
 *
 * Se os menus do chamados já existem, não faz nada. Isso garante que em produção
 * o pacote chamados volte a funcionar após rodar `php artisan migrate`.
 */
return new class extends Migration
{
    private const MENU_CHAMADOS_ROOT = 999970;
    private const MENU_CHAMADOS_HOME = 999971;
    private const MENU_CHAMADOS_NOVO = 999972;
    private const MENU_CHAMADOS_CONFIG = 999973;
    private const MENU_CHAMADOS_RELATORIOS = 999974;

    public function up(): void
    {
        if (Menu::query()->where('old', self::MENU_CHAMADOS_ROOT)->exists()) {
            return;
        }

        $processId = (int) config('chamados.process_id', 72);

        $servidoresMenu = Menu::query()->where('process', Process::MENU_EMPLOYEES)->first();
        if (!$servidoresMenu) {
            return;
        }

        $chamadosRoot = Menu::query()->updateOrCreate(
            ['old' => self::MENU_CHAMADOS_ROOT],
            [
                'parent_id' => null,
                'title' => 'Chamados',
                'description' => 'Sistema de chamados e tickets',
                'link' => '/chamados',
                'icon' => 'fa-ticket',
                'order' => 6,
                'process' => $processId,
                'parent_old' => $processId,
                'type' => 1,
                'active' => true,
            ]
        );

        Menu::query()->updateOrCreate(
            ['old' => self::MENU_CHAMADOS_HOME],
            [
                'parent_id' => $chamadosRoot->getKey(),
                'title' => 'Meus chamados',
                'description' => 'Listagem de chamados',
                'link' => '/chamados',
                'order' => 1,
                'parent_old' => self::MENU_CHAMADOS_ROOT,
                'process' => $processId,
                'type' => 2,
                'active' => true,
            ]
        );

        Menu::query()->updateOrCreate(
            ['old' => self::MENU_CHAMADOS_NOVO],
            [
                'parent_id' => $chamadosRoot->getKey(),
                'title' => 'Novo chamado',
                'description' => 'Abrir novo chamado',
                'link' => '/chamados/novo',
                'order' => 2,
                'parent_old' => self::MENU_CHAMADOS_ROOT,
                'process' => $processId,
                'type' => 2,
                'active' => true,
            ]
        );

        Menu::query()->updateOrCreate(
            ['old' => self::MENU_CHAMADOS_RELATORIOS],
            [
                'parent_id' => $chamadosRoot->getKey(),
                'title' => 'Relatórios e gráficos',
                'description' => 'Gráficos e indicadores',
                'link' => '/chamados/relatorios',
                'order' => 97,
                'parent_old' => self::MENU_CHAMADOS_ROOT,
                'process' => $processId,
                'type' => 2,
                'active' => true,
            ]
        );

        Menu::query()->updateOrCreate(
            ['old' => self::MENU_CHAMADOS_CONFIG],
            [
                'parent_id' => $chamadosRoot->getKey(),
                'title' => 'Configurações',
                'description' => 'Setores e permissões',
                'link' => '/chamados/configuracoes',
                'order' => 98,
                'parent_old' => self::MENU_CHAMADOS_ROOT,
                'process' => $processId,
                'type' => 2,
                'active' => true,
            ]
        );

        $menuOlds = [
            self::MENU_CHAMADOS_ROOT,
            self::MENU_CHAMADOS_HOME,
            self::MENU_CHAMADOS_NOVO,
            self::MENU_CHAMADOS_CONFIG,
            self::MENU_CHAMADOS_RELATORIOS,
        ];

        foreach ($menuOlds as $old) {
            DB::statement(
                "INSERT INTO pmieducar.menu_tipo_usuario (ref_cod_tipo_usuario, cadastra, visualiza, exclui, menu_id)
                 SELECT
                     tu.cod_tipo_usuario,
                     CASE WHEN tu.nivel = " . \App\Models\LegacyUserType::LEVEL_ADMIN . " THEN 1 ELSE 0 END,
                     1,
                     CASE WHEN tu.nivel = " . \App\Models\LegacyUserType::LEVEL_ADMIN . " THEN 1 ELSE 0 END,
                     (SELECT id FROM public.menus WHERE old = {$old} LIMIT 1)
                 FROM pmieducar.tipo_usuario tu
                 WHERE (SELECT id FROM public.menus WHERE old = {$old} LIMIT 1) IS NOT NULL
                   AND NOT EXISTS (
                       SELECT 1 FROM pmieducar.menu_tipo_usuario x
                       WHERE x.ref_cod_tipo_usuario = tu.cod_tipo_usuario
                         AND x.menu_id = (SELECT id FROM public.menus WHERE old = {$old} LIMIT 1)
                   )"
            );
        }
    }

    public function down(): void
    {
        // Não remove menus do chamados no rollback — eles pertencem ao pacote chamados.
    }
};
