<?php

namespace iEducar\Packages\AdvancedReports\Http\Controllers;

use App\Http\Controllers\Controller;
use iEducar\Packages\AdvancedReports\Services\AdvancedReportsFilterService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class IndicatorsPlaceholderController extends Controller
{
    private function issuesUrl(): string
    {
        return 'https://github.com/JaderGabriel/i-educar-advreport-package/issues';
    }

    public function show(Request $request, AdvancedReportsFilterService $filters, string $slug): View
    {
        $pages = [
            'baixo-desempenho' => [
                'title' => 'Alunos com baixo desempenho',
                'text' => 'Indicador para localizar alunos abaixo da média/limiar por turma/etapa/componente.',
            ],
            'alto-desempenho' => [
                'title' => 'Alunos com alto desempenho',
                'text' => 'Indicador para localizar alunos acima da média/limiar por turma/etapa/componente.',
            ],
            'sem-nota' => [
                'title' => 'Alunos sem nota lançada',
                'text' => 'Indicador de lançamentos pendentes (ausência de notas) por turma/etapa/componente.',
            ],
            'nao-enturmados' => [
                'title' => 'Matriculados e não enturmados',
                'text' => 'Indicador para localizar matrículas ativas no ano sem enturmação ativa.',
            ],
            'comparativo-turma' => [
                'title' => 'Comparativo de médias da turma',
                'text' => 'Indicador comparativo de médias/resultado por turma, componente e etapa.',
            ],
            'avaliacao-frequencia' => [
                'title' => 'Avaliação e frequência',
                'text' => 'Conjunto de relatórios e indicadores pedagógicos (notas, médias, frequência e pendências).',
            ],
        ];

        if (!isset($pages[$slug])) {
            abort(404);
        }

        $ano = $request->get('ano');
        $instituicaoId = $request->get('ref_cod_instituicao');
        $escolaId = $request->get('ref_cod_escola');
        $cursoId = $request->get('ref_cod_curso');

        $filterData = $filters->getFilters(
            $ano ? (int) $ano : null,
            $instituicaoId ? (int) $instituicaoId : null,
            $escolaId ? (int) $escolaId : null,
            $cursoId ? (int) $cursoId : null,
        );

        return view('advanced-reports::indicators.placeholder', [
            'slug' => $slug,
            'title' => $pages[$slug]['title'],
            'text' => $pages[$slug]['text'],
            'status' => 'Em desenvolvimento/ajustes/melhorias. Envie sugestões no issue do projeto.',
            'issuesUrl' => $this->issuesUrl(),
            'ano' => $ano,
            'instituicaoId' => $instituicaoId,
            'escolaId' => $escolaId,
            'cursoId' => $cursoId,
            ...$filterData,
        ]);
    }
}

