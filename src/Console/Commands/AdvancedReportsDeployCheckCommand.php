<?php

namespace iEducar\Packages\AdvancedReports\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Process\Process;

class AdvancedReportsDeployCheckCommand extends Command
{
    protected $signature = 'advanced-reports:deploy-check {--all : Roda suites completas (Unit/Feature) ao invés de somente testes do pacote}';

    protected $description = 'Roda checks de deploy do Advanced Reports (testes + dicas de pós-deploy).';

    public function handle(): int
    {
        $pest = base_path('vendor/bin/pest');
        if (!file_exists($pest)) {
            $this->error('pest não encontrado em vendor/bin/pest');
            return self::FAILURE;
        }

        $onlyPackage = !$this->option('all');

        $this->line('');
        $this->info('Advanced Reports — deploy check');
        $this->line(str_repeat('-', 38));

        $args = [$pest];
        if ($onlyPackage) {
            $paths = array_values(array_filter([
                base_path('tests/Unit/AdvancedReports'),
                base_path('tests/Feature/AdvancedReports'),
            ], fn ($p) => is_dir($p)));

            if (empty($paths)) {
                $this->warn('Nenhum teste encontrado em tests/Unit/AdvancedReports ou tests/Feature/AdvancedReports.');
                $this->warn('Vou rodar o suite Unit completo (fallback).');
                $args = [$pest, '--testsuite', 'Unit'];
            } else {
                $args = array_merge($args, $paths);
            }
        } else {
            $args = [$pest, '--testsuite', 'Unit', '--testsuite', 'Feature'];
        }

        $this->line('');
        $this->line('Rodando testes...');

        $process = new Process($args, base_path());
        $process->setTimeout(null);
        $process->run(function ($type, $buffer) {
            $this->output->write($buffer);
        });

        $this->line('');
        if (!$process->isSuccessful()) {
            $this->error('Falha nos testes. Corrija antes do deploy.');
            return self::FAILURE;
        }

        $this->info('Testes OK.');
        $this->line('');
        $this->line('Pós-deploy (recomendado):');
        $this->line('- php artisan migrate --force');
        $this->line('- php artisan cache:clear');
        $this->line('- php artisan config:clear');
        $this->line('- php artisan route:clear');
        $this->line('- php artisan view:clear');
        $this->line('- php artisan advanced-reports:flush-menus');
        $this->line('- php artisan vendor:publish --tag=advanced-reports-assets --force');
        $this->line('');
        $this->line('Observação: o menu em Tipos de usuário depende de `menus.process` preenchido (o pacote seta para links `/relatorios-avancados%`).');

        return self::SUCCESS;
    }
}

