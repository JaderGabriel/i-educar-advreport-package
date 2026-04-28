<?php

namespace iEducar\Packages\AdvancedReports\Providers;

use iEducar\Packages\AdvancedReports\Console\Commands\AdvancedReportsTestCommand;
use iEducar\Packages\AdvancedReports\Console\Commands\AdvancedReportsFlushMenusCommand;
use iEducar\Packages\AdvancedReports\Console\Commands\AdvancedReportsDeployCheckCommand;
use iEducar\Packages\AdvancedReports\Http\Middleware\EnsureAdvancedReportsMenu;
use Illuminate\Support\ServiceProvider;

class AdvancedReportsProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../../config/advanced-reports.php',
            'advanced-reports'
        );

        $this->loadViewsFrom(__DIR__ . '/../../resources/views', 'advanced-reports');
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->loadMigrationsFrom(__DIR__ . '/../../database/migrations');
            $this->commands([
                AdvancedReportsTestCommand::class,
                AdvancedReportsFlushMenusCommand::class,
                AdvancedReportsDeployCheckCommand::class,
            ]);
        }

        $this->loadRoutesFrom(__DIR__ . '/../../routes/web.php');

        // Middleware para garantir menu lateral/superior nas rotas do pacote
        $this->app['router']->aliasMiddleware('ieducar.advanced-reports.menu', EnsureAdvancedReportsMenu::class);

        // Publicação de assets (CSS) do pacote
        $this->publishes([
            __DIR__ . '/../../resources/assets/css/advanced-reports.css' => public_path('css/advanced-reports.css'),
        ], 'advanced-reports-assets');
    }
}
