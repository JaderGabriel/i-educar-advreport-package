<?php

namespace iEducar\Packages\AdvancedReports\Http\Controllers;

use App\Http\Controllers\Controller;
use iEducar\Packages\AdvancedReports\Exports\AgeDistortionExport;
use iEducar\Packages\AdvancedReports\Services\AgeDistortionService;
use iEducar\Packages\AdvancedReports\Services\ChartImageService;
use iEducar\Packages\AdvancedReports\Services\PdfRenderService;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;

class AgeDistortionController extends Controller
{
    public function index(Request $request, AgeDistortionService $service): View
    {
        $ano = $request->get('ano');
        $instituicaoId = $request->get('ref_cod_instituicao');
        $escolaId = $request->get('ref_cod_escola');
        $cursoId = $request->get('ref_cod_curso');

        $filters = $service->getFilters($ano ? (int) $ano : null, $instituicaoId ? (int) $instituicaoId : null, $escolaId ? (int) $escolaId : null, $cursoId ? (int) $cursoId : null);

        $data = [];
        if ($ano && $instituicaoId && $cursoId) {
            $data = $service->buildData(
                (int) $ano,
                (int) $instituicaoId,
                (int) $cursoId,
                $escolaId ? (int) $escolaId : null,
                $request->get('serie') ? (int) $request->get('serie') : null,
                $request->get('situacao') ? (int) $request->get('situacao') : 0
            );
        }

        return view('advanced-reports::age-distortion.index', array_merge($filters, [
            'ano' => $ano,
            'instituicaoId' => $instituicaoId,
            'escolaId' => $escolaId,
            'cursoId' => $cursoId,
            'data' => $data,
        ]));
    }

    public function pdf(Request $request, AgeDistortionService $service)
    {
        $ano = (int) $request->get('ano');
        $instituicaoId = (int) $request->get('ref_cod_instituicao');
        $cursoId = (int) $request->get('ref_cod_curso');
        $escolaId = $request->get('ref_cod_escola') ? (int) $request->get('ref_cod_escola') : null;
        $withCharts = (bool) $request->get('with_charts');

        if (! $ano || ! $instituicaoId || ! $cursoId) {
            abort(422, 'Ano, instituição e curso são obrigatórios para gerar o PDF.');
        }

        $filters = $service->getFilters($ano, $instituicaoId, $escolaId, $cursoId);
        $data = $service->buildData($ano, $instituicaoId, $cursoId, $escolaId);

        $charts = [];
        if ($withCharts) {
            $series = [];
            foreach (($data['grades'] ?? []) as $g) {
                $series[(string) ($g['grade_name'] ?? 'Série')] = (float) ($g['distortion_pct'] ?? 0);
            }
            $charts['distortion_pct_by_grade'] = app(ChartImageService::class)->barPngDataUri($series, 'Distorção (%) por série (recorte)');
        }

        return app(PdfRenderService::class)->download('advanced-reports::age-distortion.pdf', array_merge($filters, [
            'ano' => $ano,
            'instituicaoId' => $instituicaoId,
            'escolaId' => $escolaId,
            'cursoId' => $cursoId,
            'data' => $data,
            'withCharts' => $withCharts,
            'charts' => $charts,
        ]), 'distorcao-idade-serie-' . $ano . '.pdf', 'a4', 'portrait');
    }

    public function excel(Request $request, AgeDistortionService $service)
    {
        $ano = (int) $request->get('ano');
        $instituicaoId = (int) $request->get('ref_cod_instituicao');
        $cursoId = (int) $request->get('ref_cod_curso');
        $escolaId = $request->get('ref_cod_escola') ? (int) $request->get('ref_cod_escola') : null;

        if (! $ano || ! $instituicaoId || ! $cursoId) {
            abort(422, 'Ano, instituição e curso são obrigatórios para exportar Excel.');
        }

        $data = $service->buildData($ano, $instituicaoId, $cursoId, $escolaId);

        return Excel::download(new AgeDistortionExport($ano, $data), 'distorcao-idade-serie-' . $ano . '.xlsx');
    }
}

