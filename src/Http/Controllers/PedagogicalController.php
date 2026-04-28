<?php

namespace iEducar\Packages\AdvancedReports\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PedagogicalController extends Controller
{
    private function issuesUrl(): string
    {
        return 'https://github.com/JaderGabriel/i-educar-advreport-package/issues';
    }

    /**
     * Tela placeholder para itens pedagógicos ainda não implementados.
     */
    public function show(Request $request, string $slug): View
    {
        $pages = [
            'mapa-notas' => [
                'title' => 'Mapa de notas (por turma/etapa)',
                'text' => 'Relatório pedagógico para acompanhamento por turma, etapa e componente.',
                'status' => 'Em desenvolvimento/ajustes/melhorias. Envie sugestões no issue do projeto.',
            ],
            'mapa-frequencia' => [
                'title' => 'Mapa de frequência (por turma/etapa)',
                'text' => 'Relatório pedagógico de frequência consolidada por turma/etapa.',
                'status' => 'Em desenvolvimento/ajustes/melhorias. Envie sugestões no issue do projeto.',
            ],
            'espelho-diario' => [
                'title' => 'Espelho de diário',
                'text' => 'Modelo para impressão/arquivo do diário de classe.',
                'status' => 'Em desenvolvimento/ajustes/melhorias. Envie sugestões no issue do projeto.',
            ],
            'pendencias-lancamento' => [
                'title' => 'Pendências de lançamento (notas/frequência)',
                'text' => 'Este item foi promovido para implementação real. Use o menu para acessar o relatório em: Relatórios Avançados → Pendências de lançamento.',
            ],
            'ata-conselho' => [
                'title' => 'Ata de conselho de classe (por etapa)',
                'text' => 'Documento formal (arquivo) com variação por rede.',
                'status' => 'Em desenvolvimento/ajustes/melhorias. Envie sugestões no issue do projeto.',
            ],
            'ata-entrega-resultados' => [
                'title' => 'Ata de entrega de resultados (assinaturas)',
                'text' => 'Documento formal (arquivo) para registro de entrega/ciência.',
                'status' => 'Em desenvolvimento/ajustes/melhorias. Envie sugestões no issue do projeto.',
            ],
        ];

        if (!isset($pages[$slug])) {
            abort(404);
        }

        return view('advanced-reports::pedagogical.placeholder', [
            'title' => $pages[$slug]['title'],
            'text' => $pages[$slug]['text'],
            'status' => $pages[$slug]['status'] ?? null,
            'issuesUrl' => $this->issuesUrl(),
        ]);
    }
}

