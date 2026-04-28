<?php

namespace iEducar\Packages\AdvancedReports\Http\Controllers;

use App\Http\Controllers\Controller;
use iEducar\Packages\AdvancedReports\Exports\SimpleArraySheet;
use iEducar\Packages\AdvancedReports\Services\AdvancedReportsFilterService;
use iEducar\Packages\AdvancedReports\Services\MovementsGeneralReportService;
use iEducar\Packages\AdvancedReports\Services\PdfRenderService;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;

class MovementsReportController extends Controller
{
    public function index(Request $request, AdvancedReportsFilterService $filters): View
    {
        $ano = $request->get('ano');
        $instituicaoId = $request->get('ref_cod_instituicao');
        $escolaId = $request->get('ref_cod_escola');
        $cursoId = $request->get('ref_cod_curso');

        $filterData = $filters->getFilters($ano ? (int) $ano : null, $instituicaoId ? (int) $instituicaoId : null, $escolaId ? (int) $escolaId : null, $cursoId ? (int) $cursoId : null);

        $data = [];

        return view('advanced-reports::movements.index', array_merge($filterData, compact('ano', 'instituicaoId', 'escolaId', 'cursoId', 'data')));
    }

    public function pdf(Request $request, AdvancedReportsFilterService $filters, MovementsGeneralReportService $service): Response
    {
        $ano = (int) $request->get('ano');
        $instituicaoId = $request->get('ref_cod_instituicao') ? (int) $request->get('ref_cod_instituicao') : null;
        $courseIds = $request->get('ref_cod_curso') ? [(int) $request->get('ref_cod_curso')] : null;

        $startDate = (string) $request->get('data_inicial', '');
        $endDate = (string) $request->get('data_final', '');

        if (! $ano || $startDate === '' || $endDate === '') {
            abort(422, 'Ano letivo, data inicial e data final são obrigatórios para gerar o PDF.');
        }

        $filterData = $filters->getFilters($ano, $instituicaoId, null, $courseIds ? $courseIds[0] : null);
        $data = $service->buildData($ano, $instituicaoId, $courseIds, $startDate, $endDate);

        $payload = array_merge($filterData, [
            'ano' => $ano,
            'instituicaoId' => $instituicaoId,
            'data_inicial' => $startDate,
            'data_final' => $endDate,
            'data' => $data,
        ]);

        return app(PdfRenderService::class)->download(
            'advanced-reports::movements.pdf',
            $payload,
            'relatorio-movimentacoes-' . $ano . '.pdf',
            'a4',
            'landscape'
        );
    }

    public function excel(Request $request, MovementsGeneralReportService $service): BinaryFileResponse
    {
        $ano = (int) $request->get('ano');
        $instituicaoId = $request->get('ref_cod_instituicao') ? (int) $request->get('ref_cod_instituicao') : null;
        $courseIds = $request->get('ref_cod_curso') ? [(int) $request->get('ref_cod_curso')] : null;

        $startDate = (string) $request->get('data_inicial', '');
        $endDate = (string) $request->get('data_final', '');

        if (! $ano || $startDate === '' || $endDate === '') {
            abort(422, 'Ano letivo, data inicial e data final são obrigatórios para exportar Excel.');
        }

        $data = $service->buildData($ano, $instituicaoId, $courseIds, $startDate, $endDate);

        $headings = [
            'Escola',
            'Ed. Inf. Int.',
            'Ed. Inf. Parc.',
            '1º Ano',
            '2º Ano',
            '3º Ano',
            '4º Ano',
            '5º Ano',
            '6º Ano',
            '7º Ano',
            '8º Ano',
            '9º Ano',
            'Admitidos',
            'Aband.',
            'Transf.',
            'Rem.',
            'Recla.',
            'Óbito',
            'Localização',
        ];

        $rows = [];
        foreach ($data as $row) {
            $rows[] = [
                trim(($row['escola'] ?? '') . ' ' . ($row['ciclo'] ?? '') . ($row['aee'] ?? '')),
                (int) ($row['ed_inf_int'] ?? 0),
                (int) ($row['ed_inf_parc'] ?? 0),
                (int) ($row['ano_1'] ?? 0),
                (int) ($row['ano_2'] ?? 0),
                (int) ($row['ano_3'] ?? 0),
                (int) ($row['ano_4'] ?? 0),
                (int) ($row['ano_5'] ?? 0),
                (int) ($row['ano_6'] ?? 0),
                (int) ($row['ano_7'] ?? 0),
                (int) ($row['ano_8'] ?? 0),
                (int) ($row['ano_9'] ?? 0),
                (int) ($row['admitidos'] ?? 0),
                (int) ($row['aband'] ?? 0),
                (int) ($row['transf'] ?? 0),
                (int) ($row['rem'] ?? 0),
                (int) ($row['recla'] ?? 0),
                (int) ($row['obito'] ?? 0),
                (string) ($row['localizacao'] ?? ''),
            ];
        }

        return Excel::download(
            new class($headings, $rows) implements \Maatwebsite\Excel\Concerns\WithMultipleSheets {
                public function __construct(private array $headings, private array $rows) {}
                public function sheets(): array
                {
                    return [new SimpleArraySheet('Movimentações', $this->headings, $this->rows)];
                }
            },
            'relatorio-movimentacoes-' . $ano . '.xlsx'
        );
    }
}
