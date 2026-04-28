<?php

namespace iEducar\Packages\AdvancedReports\Http\Controllers;

use App\Http\Controllers\Controller;
use iEducar\Packages\AdvancedReports\Exports\PendingEntriesExport;
use iEducar\Packages\AdvancedReports\Services\AdvancedReportsFilterService;
use iEducar\Packages\AdvancedReports\Services\PdfRenderService;
use iEducar\Packages\AdvancedReports\Services\PendingEntriesService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\Response;

class PendingEntriesController extends Controller
{
    private function mustHaveSchoolClass(?string $schoolClassId): void
    {
        if (!$schoolClassId || (int) $schoolClassId <= 0) {
            abort(422, 'Informe a turma.');
        }
    }

    public function index(Request $request, AdvancedReportsFilterService $filters): View
    {
        $ano = $request->get('ano');
        $instituicaoId = $request->get('ref_cod_instituicao');
        $escolaId = $request->get('ref_cod_escola');
        $cursoId = $request->get('ref_cod_curso');

        $serieId = $request->get('ref_cod_serie');
        $turmaId = $request->get('ref_cod_turma');

        $filtersData = $filters->getFilters(
            $ano ? (int) $ano : null,
            $instituicaoId ? (int) $instituicaoId : null,
            $escolaId ? (int) $escolaId : null,
            $cursoId ? (int) $cursoId : null
        );

        return view('advanced-reports::pending-entries.index', array_merge($filtersData, [
            'ano' => $ano,
            'instituicaoId' => $instituicaoId,
            'escolaId' => $escolaId,
            'cursoId' => $cursoId,
            'serieId' => $serieId,
            'turmaId' => $turmaId,
        ]));
    }

    public function pdf(Request $request, PendingEntriesService $service): Response
    {
        $this->mustHaveSchoolClass($request->get('ref_cod_turma'));
        $turmaId = (int) $request->get('ref_cod_turma');
        $etapa = $request->get('etapa') ? (int) $request->get('etapa') : null;
        $checkGrades = $request->boolean('check_grades', true);
        $checkFrequency = $request->boolean('check_frequency', true);

        $data = $service->build($turmaId, $etapa, $checkGrades, $checkFrequency);

        $filename = 'pendencias-lancamento-turma-' . $turmaId . '.pdf';
        if ($etapa) {
            $filename = Str::replaceLast('.pdf', '-etapa-' . $etapa . '.pdf', $filename);
        }

        return app(PdfRenderService::class)->download('advanced-reports::pending-entries.pdf', [
            'data' => $data,
            'filters' => [
                'etapa' => $etapa,
                'check_grades' => $checkGrades,
                'check_frequency' => $checkFrequency,
            ],
        ], $filename);
    }

    public function excel(Request $request, PendingEntriesService $service)
    {
        $this->mustHaveSchoolClass($request->get('ref_cod_turma'));
        $turmaId = (int) $request->get('ref_cod_turma');
        $etapa = $request->get('etapa') ? (int) $request->get('etapa') : null;
        $checkGrades = $request->boolean('check_grades', true);
        $checkFrequency = $request->boolean('check_frequency', true);

        $data = $service->build($turmaId, $etapa, $checkGrades, $checkFrequency);
        return Excel::download(new PendingEntriesExport($data), 'pendencias-lancamento-turma-' . $turmaId . '.xlsx');
    }
}

