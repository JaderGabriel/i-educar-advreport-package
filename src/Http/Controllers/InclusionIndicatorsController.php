<?php

namespace iEducar\Packages\AdvancedReports\Http\Controllers;

use App\Http\Controllers\Controller;
use iEducar\Packages\AdvancedReports\Exports\InclusionIndicatorsExport;
use iEducar\Packages\AdvancedReports\Services\ChartImageService;
use iEducar\Packages\AdvancedReports\Services\InclusionIndicatorsService;
use iEducar\Packages\AdvancedReports\Services\PdfRenderService;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;

class InclusionIndicatorsController extends Controller
{
    public function index(Request $request, InclusionIndicatorsService $service): View
    {
        $ano = $request->get('ano');
        $instituicaoId = $request->get('ref_cod_instituicao');
        $escolaId = $request->get('ref_cod_escola');
        $cursoId = $request->get('ref_cod_curso');

        $filters = $service->getFilters($ano ? (int) $ano : null, $instituicaoId ? (int) $instituicaoId : null, $escolaId ? (int) $escolaId : null, $cursoId ? (int) $cursoId : null);

        $data = [];
        if ($ano) {
            $data = $service->buildData((int) $ano, $instituicaoId ? (int) $instituicaoId : null, $escolaId ? (int) $escolaId : null, $cursoId ? (int) $cursoId : null);
        }

        return view('advanced-reports::inclusion.index', array_merge($filters, [
            'ano' => $ano,
            'instituicaoId' => $instituicaoId,
            'escolaId' => $escolaId,
            'cursoId' => $cursoId,
            'data' => $data,
        ]));
    }

    public function pdf(Request $request, InclusionIndicatorsService $service)
    {
        $ano = (int) $request->get('ano');
        $instituicaoId = $request->get('ref_cod_instituicao') ? (int) $request->get('ref_cod_instituicao') : null;
        $escolaId = $request->get('ref_cod_escola') ? (int) $request->get('ref_cod_escola') : null;
        $cursoId = $request->get('ref_cod_curso') ? (int) $request->get('ref_cod_curso') : null;
        $withCharts = (bool) $request->get('with_charts');

        if (! $ano) {
            abort(422, 'Ano letivo é obrigatório para gerar o PDF.');
        }

        $filters = $service->getFilters($ano, $instituicaoId, $escolaId, $cursoId);
        $data = $service->buildData($ano, $instituicaoId, $escolaId, $cursoId);

        $charts = [];
        if ($withCharts) {
            $charts = $this->buildCharts(app(ChartImageService::class), $data);
        }

        return app(PdfRenderService::class)->download('advanced-reports::inclusion.pdf', array_merge($filters, [
            'ano' => $ano,
            'instituicaoId' => $instituicaoId,
            'escolaId' => $escolaId,
            'cursoId' => $cursoId,
            'data' => $data,
            'withCharts' => $withCharts,
            'charts' => $charts,
        ]), 'indicadores-inclusao-' . $ano . '.pdf', 'a4', 'portrait');
    }

    public function excel(Request $request, InclusionIndicatorsService $service)
    {
        $ano = (int) $request->get('ano');
        $instituicaoId = $request->get('ref_cod_instituicao') ? (int) $request->get('ref_cod_instituicao') : null;
        $escolaId = $request->get('ref_cod_escola') ? (int) $request->get('ref_cod_escola') : null;
        $cursoId = $request->get('ref_cod_curso') ? (int) $request->get('ref_cod_curso') : null;

        if (! $ano) {
            abort(422, 'Ano letivo é obrigatório para exportar em Excel.');
        }

        $data = $service->buildData($ano, $instituicaoId, $escolaId, $cursoId);

        return Excel::download(new InclusionIndicatorsExport($ano, $data), 'indicadores-inclusao-' . $ano . '.xlsx');
    }

    private function buildCharts(ChartImageService $charts, array $data): array
    {
        $summary = $data['summary'] ?? [];

        $overview = [
            'Total' => (int) ($summary['total_students'] ?? 0),
            'Com deficiência' => (int) ($summary['with_disabilities'] ?? 0),
            'Com NIS' => (int) ($summary['with_nis'] ?? 0),
            'Com benefícios' => (int) ($summary['with_benefits'] ?? 0),
        ];

        $types = [];
        $items = $data['disability_by_type'] ?? [];
        if (is_object($items) && method_exists($items, 'all')) {
            $items = $items->all();
        }
        foreach (array_slice($items, 0, 10) as $row) {
            $types[(string) ($row->deficiencia ?? 'Deficiência')] = (int) ($row->total ?? 0);
        }

        return [
            'overview' => $charts->barPngDataUri($overview, 'Visão geral (recorte)'),
            'types' => $charts->barPngDataUri($types, 'Top 10 deficiências (cadastro)'),
        ];
    }
}

