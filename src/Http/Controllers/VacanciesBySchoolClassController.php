<?php

namespace iEducar\Packages\AdvancedReports\Http\Controllers;

use App\Http\Controllers\Controller;
use iEducar\Packages\AdvancedReports\Exports\VacanciesBySchoolClassExport;
use iEducar\Packages\AdvancedReports\Services\AdvancedReportsFilterService;
use iEducar\Packages\AdvancedReports\Services\PdfRenderService;
use iEducar\Packages\AdvancedReports\Services\VacanciesBySchoolClassService;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\Response;

class VacanciesBySchoolClassController extends Controller
{
    private function resolveFilterLabels(?int $instituicaoId, ?int $escolaId, ?int $cursoId, ?int $serieId, ?int $turmaId): array
    {
        $instituicao = $instituicaoId
            ? DB::table('pmieducar.instituicao')->where('cod_instituicao', $instituicaoId)->value('nm_instituicao')
            : null;

        $escola = $escolaId
            ? DB::table('pmieducar.escola as e')
                ->leftJoin('cadastro.pessoa as p', 'p.idpes', '=', 'e.ref_idpes')
                ->leftJoin('cadastro.juridica as j', 'j.idpes', '=', 'p.idpes')
                ->leftJoin('pmieducar.escola_complemento as ec', 'ec.ref_cod_escola', '=', 'e.cod_escola')
                ->where('e.cod_escola', $escolaId)
                ->value(DB::raw('COALESCE(j.fantasia, ec.nm_escola)'))
            : null;

        $curso = $cursoId
            ? DB::table('pmieducar.curso')->where('cod_curso', $cursoId)->value('nm_curso')
            : null;

        $serie = $serieId
            ? DB::table('pmieducar.serie')->where('cod_serie', $serieId)->value('nm_serie')
            : null;

        $turma = $turmaId
            ? DB::table('pmieducar.turma')->where('cod_turma', $turmaId)->value('nm_turma')
            : null;

        return [
            'instituicao' => $instituicao ? (string) $instituicao : null,
            'escola' => $escola ? (string) $escola : null,
            'curso' => $curso ? (string) $curso : null,
            'serie' => $serie ? (string) $serie : null,
            'turma' => $turma ? (string) $turma : null,
        ];
    }

    public function index(Request $request, AdvancedReportsFilterService $filters, VacanciesBySchoolClassService $service): View
    {
        $ano = $request->get('ano');
        $instituicaoId = $request->get('ref_cod_instituicao');
        $escolaId = $request->get('ref_cod_escola');
        $cursoId = $request->get('ref_cod_curso');
        $serieId = $request->get('ref_cod_serie');
        $turmaId = $request->get('ref_cod_turma');

        $filterData = $filters->getFilters(
            $ano ? (int) $ano : null,
            $instituicaoId ? (int) $instituicaoId : null,
            $escolaId ? (int) $escolaId : null,
            $cursoId ? (int) $cursoId : null
        );

        $data = null;
        if ($ano && $escolaId) {
            $data = $service->build(
                (int) $ano,
                $instituicaoId ? (int) $instituicaoId : null,
                (int) $escolaId,
                $cursoId ? (int) $cursoId : null,
                $serieId ? (int) $serieId : null,
                $turmaId ? (int) $turmaId : null
            );
        }

        return view('advanced-reports::vacancies/index', array_merge($filterData, [
            'ano' => $ano,
            'instituicaoId' => $instituicaoId,
            'escolaId' => $escolaId,
            'cursoId' => $cursoId,
            'serieId' => $serieId,
            'turmaId' => $turmaId,
            'data' => $data,
        ]));
    }

    public function pdf(Request $request, VacanciesBySchoolClassService $service): Response
    {
        $ano = (int) $request->get('ano');
        $instituicaoId = $request->get('ref_cod_instituicao') ? (int) $request->get('ref_cod_instituicao') : null;
        $escolaId = $request->get('ref_cod_escola') ? (int) $request->get('ref_cod_escola') : null;
        $cursoId = $request->get('ref_cod_curso') ? (int) $request->get('ref_cod_curso') : null;
        $serieId = $request->get('ref_cod_serie') ? (int) $request->get('ref_cod_serie') : null;
        $turmaId = $request->get('ref_cod_turma') ? (int) $request->get('ref_cod_turma') : null;

        if (!$ano || !$escolaId) {
            abort(422, 'Informe ano e escola.');
        }

        $data = $service->build($ano, $instituicaoId, $escolaId, $cursoId, $serieId, $turmaId);
        $filterLabels = $this->resolveFilterLabels($instituicaoId, $escolaId, $cursoId, $serieId, $turmaId);

        return app(PdfRenderService::class)->download('advanced-reports::vacancies/pdf', [
            'title' => 'Vagas por turma',
            'subtitle' => 'Capacidade, ocupação e vagas disponíveis',
            'year' => (string) $ano,
            'filters' => [
                'instituicao' => $instituicaoId,
                'escola' => $escolaId,
                'curso' => $cursoId,
                'serie' => $serieId,
                'turma' => $turmaId,
            ],
            'filterLabels' => $filterLabels,
            'data' => $data,
        ], 'vagas-por-turma-' . $ano . '.pdf', 'a4', 'landscape');
    }

    public function excel(Request $request, VacanciesBySchoolClassService $service)
    {
        $ano = (int) $request->get('ano');
        $instituicaoId = $request->get('ref_cod_instituicao') ? (int) $request->get('ref_cod_instituicao') : null;
        $escolaId = $request->get('ref_cod_escola') ? (int) $request->get('ref_cod_escola') : null;
        $cursoId = $request->get('ref_cod_curso') ? (int) $request->get('ref_cod_curso') : null;
        $serieId = $request->get('ref_cod_serie') ? (int) $request->get('ref_cod_serie') : null;
        $turmaId = $request->get('ref_cod_turma') ? (int) $request->get('ref_cod_turma') : null;

        if (!$ano || !$escolaId) {
            abort(422, 'Informe ano e escola.');
        }

        $data = $service->build($ano, $instituicaoId, $escolaId, $cursoId, $serieId, $turmaId);

        return Excel::download(
            new VacanciesBySchoolClassExport($data['items']),
            'vagas-por-turma-' . $ano . '.xlsx'
        );
    }
}

