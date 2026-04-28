<?php

namespace iEducar\Packages\AdvancedReports\Http\Controllers;

use App\Http\Controllers\Controller;
use iEducar\Packages\AdvancedReports\Exports\SocioeconomicExport;
use iEducar\Packages\AdvancedReports\Services\ChartImageService;
use iEducar\Packages\AdvancedReports\Services\PdfRenderService;
use iEducar\Packages\AdvancedReports\Services\SocioeconomicReportService;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;

class SocioeconomicReportController extends Controller
{
    public function index(Request $request, SocioeconomicReportService $service): View
    {
        $ano = $request->get('ano');
        $instituicaoId = $request->get('ref_cod_instituicao');
        $escolaId = $request->get('ref_cod_escola');
        $cursoId = $request->get('ref_cod_curso');

        $filters = $service->getFilters((int) ($ano ?: 0), $instituicaoId ? (int) $instituicaoId : null, $escolaId ? (int) $escolaId : null, $cursoId ? (int) $cursoId : null);
        $data = [];
        if ($ano) {
            $data = $service->buildData((int) $ano, $instituicaoId ? (int) $instituicaoId : null, $escolaId ? (int) $escolaId : null, $cursoId ? (int) $cursoId : null);
        }

        return view('advanced-reports::socioeconomic.index', array_merge($filters, [
            'ano' => $ano,
            'instituicaoId' => $instituicaoId,
            'escolaId' => $escolaId,
            'cursoId' => $cursoId,
            'data' => $data,
        ]));
    }

    public function pdf(Request $request, SocioeconomicReportService $service)
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

        $payload = array_merge($filters, [
            'ano' => $ano,
            'instituicaoId' => $instituicaoId,
            'escolaId' => $escolaId,
            'cursoId' => $cursoId,
            'data' => $data,
            'withCharts' => $withCharts,
            'charts' => $charts,
        ]);

        return app(PdfRenderService::class)->download(
            'advanced-reports::socioeconomic.pdf',
            $payload,
            'relatorio-socioeconomico-' . $ano . '.pdf',
            'a4',
            'portrait'
        );
    }

    public function excel(Request $request, SocioeconomicReportService $service)
    {
        $ano = (int) $request->get('ano');
        $instituicaoId = $request->get('ref_cod_instituicao') ? (int) $request->get('ref_cod_instituicao') : null;
        $escolaId = $request->get('ref_cod_escola') ? (int) $request->get('ref_cod_escola') : null;
        $cursoId = $request->get('ref_cod_curso') ? (int) $request->get('ref_cod_curso') : null;

        if (! $ano) {
            abort(422, 'Ano letivo é obrigatório para exportar em Excel.');
        }

        $data = $service->buildData($ano, $instituicaoId, $escolaId, $cursoId);

        return Excel::download(new SocioeconomicExport($ano, $data), 'relatorio-socioeconomico-' . $ano . '.xlsx');
    }

    private function buildCharts(ChartImageService $charts, array $data): array
    {
        $race = [];
        foreach (($data['race'] ?? []) as $row) {
            $race[(string) ($row->raca ?? 'Não inf.')] = (int) ($row->total ?? 0);
        }

        $gender = [];
        foreach (($data['gender'] ?? []) as $row) {
            $gender[(string) ($row->sexo ?? 'N')] = (int) ($row->total ?? 0);
        }

        $benefits = [];
        foreach (array_slice(($data['benefits'] ?? [])->all() ?? [], 0, 10) as $row) {
            $benefits[(string) ($row->beneficio ?? 'Sem benefício')] = (int) ($row->total ?? 0);
        }

        $schools = [];
        foreach (array_slice(($data['schools'] ?? [])->all() ?? [], 0, 10) as $row) {
            $schools[(string) ($row->nome ?? 'Escola')] = (int) ($row->total ?? 0);
        }

        return [
            'race' => $charts->barPngDataUri($race, 'Distribuição por raça/cor'),
            'gender' => $charts->barPngDataUri($gender, 'Distribuição por gênero'),
            'benefits' => $charts->barPngDataUri($benefits, 'Top 10 benefícios/programas'),
            'schools' => $charts->barPngDataUri($schools, 'Top 10 escolas por quantidade de alunos'),
        ];
    }
}
