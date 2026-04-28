<?php

namespace iEducar\Packages\AdvancedReports\Console\Commands;

use App\Models\LegacyUserType;
use App\Services\MenuCacheService;
use Illuminate\Console\Command;

class AdvancedReportsFlushMenusCommand extends Command
{
    protected $signature = 'advanced-reports:flush-menus {--type= : cod_tipo_usuario específico (opcional)}';

    protected $description = 'Limpa o cache de menus (por tipo de usuário) após instalar/atualizar os Relatórios Avançados.';

    public function handle(MenuCacheService $menus): int
    {
        $type = $this->option('type');

        if ($type) {
            $menus->flushMenuTag((int) $type);
            $this->info('Cache de menus limpo para o tipo: ' . (int) $type);

            return self::SUCCESS;
        }

        $types = LegacyUserType::query()->pluck('cod_tipo_usuario')->all();
        foreach ($types as $codTipo) {
            $menus->flushMenuTag((int) $codTipo);
        }

        $this->info('Cache de menus limpo para todos os tipos de usuário.');

        return self::SUCCESS;
    }
}

