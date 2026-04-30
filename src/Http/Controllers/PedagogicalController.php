<?php

namespace iEducar\Packages\AdvancedReports\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
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
    public function show(Request $request, string $slug): View|RedirectResponse
    {
        if ($slug === 'ata-entrega-resultados') {
            return redirect()->route('advanced-reports.minutes.index', array_merge(
                $request->query(),
                ['document' => 'delivery_results']
            ));
        }

        if ($slug === 'ata-conselho') {
            return redirect()->route('advanced-reports.minutes.index', array_merge(
                $request->query(),
                ['document' => 'council_class']
            ));
        }

        if ($slug === 'espelho-diario') {
            return redirect()->route('advanced-reports.diary-mirror.index', $request->query());
        }

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
            'pendencias-lancamento' => [
                'title' => 'Pendências de lançamento (notas/frequência)',
                'text' => 'Este item foi promovido para implementação real. Use o menu para acessar o relatório em: Relatórios Avançados → Pendências de lançamento.',
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

