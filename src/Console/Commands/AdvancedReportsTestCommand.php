<?php

namespace iEducar\Packages\AdvancedReports\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Process\Process;

class AdvancedReportsTestCommand extends Command
{
    protected $signature = 'advanced-reports:test {--filter=AdvancedReports} {--suite=Unit}';

    protected $description = 'Roda os testes do pacote Advanced Reports (pest).';

    public function handle(): int
    {
        $pest = base_path('vendor/bin/pest');
        if (!file_exists($pest)) {
            $this->error('pest não encontrado em vendor/bin/pest');
            return self::FAILURE;
        }

        $suite = (string) $this->option('suite');
        $filter = (string) $this->option('filter');

        // Pest é o runner padrão do projeto, e já suporta arquivos PHPUnit clássicos.
        $args = [$pest, '--testsuite', $suite];
        if ($filter !== '') {
            $args[] = '--filter';
            $args[] = $filter;
        }

        $process = new Process($args, base_path());
        $process->setTimeout(null);
        $process->run(function ($type, $buffer) {
            $this->output->write($buffer);
        });

        return $process->isSuccessful() ? self::SUCCESS : self::FAILURE;
    }
}

