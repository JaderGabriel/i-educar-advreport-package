<?php

namespace iEducar\Packages\AdvancedReports\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CommunicationsController extends Controller
{
    private function issuesUrl(): string
    {
        return 'https://github.com/JaderGabriel/i-educar-advreport-package/issues';
    }

    /**
     * Modelos de comunicados oficiais (item 8.6 — sem livro/ocorrências).
     */
    public function show(Request $request, string $slug): View
    {
        $pages = [
            'convocacao' => [
                'title' => 'Comunicado oficial — Convocação',
                'text' => 'Modelo para convocações (reuniões, assembleias, eventos), com cabeçalho padrão da rede/escola quando implementado.',
                'status' => 'Em planejamento. Envie sugestões de texto e campos no issue do projeto.',
            ],
            'reuniao' => [
                'title' => 'Comunicado oficial — Reunião',
                'text' => 'Modelo para comunicados de reunião (pais, conselho, turma), com data, local e pauta quando implementado.',
                'status' => 'Em planejamento. Envie sugestões de texto e campos no issue do projeto.',
            ],
            'advertencia' => [
                'title' => 'Comunicado oficial — Advertência',
                'text' => 'Modelo para advertências formais, alinhado às normas da rede e ao registro institucional quando implementado.',
                'status' => 'Em planejamento. Envie sugestões de texto e campos no issue do projeto.',
            ],
            'comunicado-geral' => [
                'title' => 'Comunicado oficial — Comunicado geral',
                'text' => 'Modelo para comunicados gerais (avisos, campanhas, orientações), com cabeçalho padrão quando implementado.',
                'status' => 'Em planejamento. Envie sugestões de texto e campos no issue do projeto.',
            ],
        ];

        if (! isset($pages[$slug])) {
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
