<?php

use App\Menu;
use App\Process;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Reestrutura menus do pacote Advanced Reports, e remove itens do pacote Portabilis (Jasper).
 *
 * Importante:
 * - NÃO remove os nós padrão "Escola → Relatórios/Documentos" (old 21126/21127), apenas remove
 *   os submenus clássicos criados pelo i-educar-reports-package (Portabilis).
 * - Remove também a árvore antiga "Relatórios Avançados" deste pacote, para aderir aos menus temáticos.
 */
return new class extends Migration
{
    /**
     * Olds do pacote Portabilis (submenus de relatórios/documentos e funcionários) que queremos remover.
     * Baseados em `packages/portabilis/i-educar-reports-package/database/migrations/2020_01_01_191000_base_menus.php`.
     */
    private array $portabilisMenuOlds = [
        // Escola → Relatórios (submenus)
        999301, // Movimentações
        999922, // Lançamentos
        999300, // Cadastrais
        999923, // Matrículas
        999303, // Indicadores

        // Escola → Documentos (submenus)
        999400, // Atestados
        999450, // Boletins
        999925, // Resultados
        999861, // Fichas
        999460, // Históricos
        999500, // Registros

        // Servidores → Relatórios/Documentos
        999913, // Relatórios (funcionários)
        999916, // Documentos (funcionários)
        999914, // Cadastrais (funcionários)
    ];

    /**
     * Olds da árvore antiga do advanced reports (faixa 999960+).
     */
    private array $advancedLegacyOlds = [
        999960, 999961, 999962, 999963, 999964, 999965, 999966, 999967, 999968, 999969,
        999970, 999971, 999972, 999973, 999974, 999975,
        999980, 999981, 999982, 999983,
    ];

    public function up(): void
    {
        $this->deleteMenusByOld($this->portabilisMenuOlds);
        $this->deleteMenusByOld($this->advancedLegacyOlds);

        // A partir daqui, cria menus temáticos apontando para as rotas deste pacote.
        $schoolReports = Menu::query()->where('old', 21126)->first(); // Escola → Relatórios
        $schoolDocs = Menu::query()->where('old', 21127)->first(); // Escola → Documentos
        $employeesReports = Menu::query()->where('old', 999913)->first(); // pode não existir; o seed padrão tem nó, mas o Portabilis criava

        // Se o nó de funcionários não existir (porque removemos 999913), mantemos apenas Escola.
        $employeesRoot = Menu::query()->where('process', Process::MENU_EMPLOYEES)->first();
        if ($employeesRoot && !$employeesReports) {
            // recria o nó Servidores → Relatórios (padrão do seed i-Educar)
            $employeesReports = Menu::query()->updateOrCreate(
                ['parent_id' => $employeesRoot->getKey(), 'old' => 999913],
                ['title' => 'Relatórios', 'order' => 2, 'parent_old' => 71, 'type' => 2, 'active' => true]
            );
        }

        if ($schoolReports) {
            $groupPedagogical = $this->createChild($schoolReports, 9999700, 'Avaliação e frequência', 1, null);
            $groupMovements = $this->createChild($schoolReports, 9999701, 'Fluxo de Alunos', 2, null);
            // Entrada inicial do submenu “Fluxo de Alunos”
            $this->createChild($groupMovements, 9999704, 'Movimentações', 1, '/relatorios-avancados/movimentacoes');
            $groupIndicators = $this->createChild($schoolReports, 9999702, 'Indicadores', 3, null);

            // Indicadores → Socioeconômicos
            $this->createChild($groupIndicators, 9999703, 'Socioeconômicos', 1, '/relatorios-avancados/socioeconomicos');
        }

        if ($schoolDocs) {
            $groupStudentDocs = $this->createChild($schoolDocs, 9999750, 'Documentos do aluno (oficiais)', 1, null);
            $groupMinutes = $this->createChild($schoolDocs, 9999751, 'Atas e registros formais', 2, null);
            $groupBlankForms = $this->createChild($schoolDocs, 9999752, 'Fichas e formulários (em branco)', 3, null);

            // Exemplos iniciais apontando para o módulo “Diplomas”
            $this->createChild($groupStudentDocs, 9999753, 'Diplomas/Certificados (modelos)', 1, '/relatorios-avancados/diplomas');

            // As demais telas/documents serão adicionadas incrementalmente conforme o roadmap do DOC executivo.
        }

        if ($employeesReports) {
            $this->createChild($employeesReports, 9999800, 'Cadastrais', 1, null);
        }
    }

    public function down(): void
    {
        // Mantemos reversão simples: remove apenas a árvore nova deste pacote (old 99997xx/99998xx).
        $this->deleteMenusByOld([
            9999700, 9999701, 9999702, 9999703, 9999704,
            9999750, 9999751, 9999752, 9999753,
            9999800,
        ]);
    }

    private function createChild(Menu $parent, int $old, string $title, int $order, ?string $link): Menu
    {
        $data = [
            'title' => $title,
            'order' => $order,
            'parent_old' => (int) $parent->old,
            'type' => 3,
            'active' => true,
        ];

        if ($link) {
            $data['link'] = $link;
        }

        return Menu::query()->updateOrCreate(
            ['parent_id' => $parent->getKey(), 'old' => $old],
            $data
        );
    }

    private function deleteMenusByOld(array $olds): void
    {
        $ids = Menu::query()->whereIn('old', $olds)->pluck('id')->all();
        if (empty($ids)) {
            return;
        }

        DB::table('pmieducar.menu_tipo_usuario')->whereIn('menu_id', $ids)->delete();
        Menu::query()->whereIn('id', $ids)->delete();
    }
};

