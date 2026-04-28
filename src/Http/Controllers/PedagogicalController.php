<?php

namespace iEducar\Packages\AdvancedReports\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PedagogicalController extends Controller
{
    /**
     * Tela placeholder para itens pedagógicos ainda não implementados.
     */
    public function show(Request $request, string $slug): View
    {
        $pages = [
            'mapa-notas' => [
                'title' => 'Mapa de notas (por turma/etapa)',
                'text' => 'Relatório pedagógico para acompanhamento por turma, etapa e componente. Previsto no roadmap; será disponibilizado aqui.',
            ],
            'mapa-frequencia' => [
                'title' => 'Mapa de frequência (por turma/etapa)',
                'text' => 'Relatório pedagógico de frequência consolidada por turma/etapa. Previsto no roadmap; será disponibilizado aqui.',
            ],
            'espelho-diario' => [
                'title' => 'Espelho de diário',
                'text' => 'Modelo para impressão/arquivo do diário de classe. Previsto no roadmap; será disponibilizado aqui.',
            ],
            'pendencias-lancamento' => [
                'title' => 'Pendências de lançamento (notas/frequência)',
                'text' => 'Este item foi promovido para implementação real. Use o menu para acessar o relatório em: Relatórios Avançados → Pendências de lançamento.',
            ],
            'ata-conselho' => [
                'title' => 'Ata de conselho de classe (por etapa)',
                'text' => 'Documento formal (arquivo) com variação por rede. Previsto no roadmap; será disponibilizado aqui.',
            ],
            'ata-entrega-resultados' => [
                'title' => 'Ata de entrega de resultados (assinaturas)',
                'text' => 'Documento formal (arquivo) para registro de entrega/ciência. Previsto no roadmap; será disponibilizado aqui.',
            ],
        ];

        if (!isset($pages[$slug])) {
            abort(404);
        }

        return view('advanced-reports::pedagogical.placeholder', [
            'title' => $pages[$slug]['title'],
            'text' => $pages[$slug]['text'],
        ]);
    }
}

