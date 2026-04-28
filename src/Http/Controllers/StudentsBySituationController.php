<?php

namespace iEducar\Packages\AdvancedReports\Http\Controllers;

use App\Http\Controllers\Controller;
use iEducar\Packages\AdvancedReports\Exports\StudentsBySituationExport;
use iEducar\Packages\AdvancedReports\Services\AdvancedReportsFilterService;
use iEducar\Packages\AdvancedReports\Services\PdfRenderService;
use iEducar\Packages\AdvancedReports\Services\StudentsBySituationService;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\Response;

class StudentsBySituationController extends Controller
{
    public function index(Request $request, AdvancedReportsFilterService $filters, StudentsBySituationService $service): View
    {
        $ano = $request->get('ano');
        $instituicaoId = $request->get('ref_cod_instituicao');
        $escolaId = $request->get('ref_cod_escola');
        $cursoId = $request->get('ref_cod_curso');

        $serieId = $request->get('ref_cod_serie');
        $turmaId = $request->get('ref_cod_turma');
        $situacao = $request->get('situacao') ? (int) $request->get('situacao') : null;

        $filterData = $filters->getFilters(
            $ano ? (int) $ano : null,
            $instituicaoId ? (int) $instituicaoId : null,
            $escolaId ? (int) $escolaId : null,
            $cursoId ? (int) $cursoId : null,
        );

        $data = null;
        if ($ano) {
            $data = $service->build(
                (int) $ano,
                $instituicaoId ? (int) $instituicaoId : null,
                $escolaId ? (int) $escolaId : null,
                $cursoId ? (int) $cursoId : null,
                $serieId ? (int) $serieId : null,
                $turmaId ? (int) $turmaId : null,
                $situacao,
            );
        }

        return view('advanced-reports::students-by-situation.index', array_merge($filterData, [
            'ano' => $ano,
            'instituicaoId' => $instituicaoId,
            'escolaId' => $escolaId,
            'cursoId' => $cursoId,
            'serieId' => $serieId,
            'turmaId' => $turmaId,
            'situacao' => $situacao,
            'situacaoOptions' => $service->situationOptions(),
            'data' => $data,
        ]));
    }

    public function pdf(Request $request, StudentsBySituationService $service): Response
    {
        $ano = (int) $request->get('ano');
        if (!$ano) {
            abort(422, 'Informe o ano.');
        }

        $data = $service->build(
            $ano,
            $request->get('ref_cod_instituicao') ? (int) $request->get('ref_cod_instituicao') : null,
            $request->get('ref_cod_escola') ? (int) $request->get('ref_cod_escola') : null,
            $request->get('ref_cod_curso') ? (int) $request->get('ref_cod_curso') : null,
            $request->get('ref_cod_serie') ? (int) $request->get('ref_cod_serie') : null,
            $request->get('ref_cod_turma') ? (int) $request->get('ref_cod_turma') : null,
            $request->get('situacao') ? (int) $request->get('situacao') : null,
        );

        return app(PdfRenderService::class)->download('advanced-reports::students-by-situation.pdf', [
            'year' => $ano,
            'data' => $data,
            'labels' => $service->situationOptions(),
        ], 'alunos-por-situacao-' . $ano . '.pdf');
    }

    public function excel(Request $request, StudentsBySituationService $service)
    {
        $ano = (int) $request->get('ano');
        if (!$ano) {
            abort(422, 'Informe o ano.');
        }

        $data = $service->build(
            $ano,
            $request->get('ref_cod_instituicao') ? (int) $request->get('ref_cod_instituicao') : null,
            $request->get('ref_cod_escola') ? (int) $request->get('ref_cod_escola') : null,
            $request->get('ref_cod_curso') ? (int) $request->get('ref_cod_curso') : null,
            $request->get('ref_cod_serie') ? (int) $request->get('ref_cod_serie') : null,
            $request->get('ref_cod_turma') ? (int) $request->get('ref_cod_turma') : null,
            $request->get('situacao') ? (int) $request->get('situacao') : null,
        );

        return Excel::download(new StudentsBySituationExport($data, $service->situationOptions()), 'alunos-por-situacao-' . $ano . '.xlsx');
    }
}

