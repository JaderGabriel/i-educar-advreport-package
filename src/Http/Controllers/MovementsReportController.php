<?php

namespace iEducar\Packages\AdvancedReports\Http\Controllers;

use App\Http\Controllers\Controller;
use iEducar\Packages\AdvancedReports\Exports\SimpleArraySheet;
use iEducar\Packages\AdvancedReports\Models\AdvancedReportsDocument;
use iEducar\Packages\AdvancedReports\Services\AdvancedReportsFilterService;
use iEducar\Packages\AdvancedReports\Services\DocumentSigningService;
use iEducar\Packages\AdvancedReports\Services\MovementsGeneralReportService;
use iEducar\Packages\AdvancedReports\Services\OfficialHeaderService;
use iEducar\Packages\AdvancedReports\Services\PdfRenderService;
use iEducar\Packages\AdvancedReports\Services\QrCodeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
        $escolaId = $request->get('ref_cod_escola') ? (int) $request->get('ref_cod_escola') : null;
        $courseIds = $request->get('ref_cod_curso') ? [(int) $request->get('ref_cod_curso')] : null;

        $startDate = (string) $request->get('data_inicial', '');
        $endDate = (string) $request->get('data_final', '');

        if (! $ano || $startDate === '' || $endDate === '') {
            abort(422, 'Ano letivo, data inicial e data final são obrigatórios para gerar o PDF.');
        }

        $filterData = $filters->getFilters($ano, $instituicaoId, null, $courseIds ? $courseIds[0] : null);
        $data = $service->buildData($ano, $instituicaoId, $courseIds, $startDate, $endDate);

        if (!$instituicaoId && $escolaId) {
            $instituicaoId = (int) (DB::table('pmieducar.escola')->where('cod_escola', $escolaId)->value('ref_cod_instituicao') ?: 0) ?: null;
        }

        $header = $escolaId
            ? app(OfficialHeaderService::class)->forSchool($instituicaoId, $escolaId)
            : ['municipality' => null, 'schoolName' => null, 'contact' => null];

        $issuedAt = now();
        $issuedAtHuman = $issuedAt->format('d/m/Y H:i');
        $issuedAtIso = DocumentSigningService::issuedAtForMac($issuedAt);

        $payload = [
            'report' => 'movements_general',
            'ano' => $ano,
            'year' => (string) $ano,
            'instituicao_id' => $instituicaoId,
            'escola_id' => $escolaId,
            'curso_id' => $courseIds ? (int) $courseIds[0] : null,
            'data_inicial' => $startDate,
            'data_final' => $endDate,
        ];

        $signing = app(DocumentSigningService::class);
        $code = $signing->generateCode(8);
        $validationUrl = route('advanced-reports.documents.validate', ['code' => $code]);
        $qrDataUri = app(QrCodeService::class)->pngDataUri($validationUrl, 4);
        $payloadToStore = array_merge($payload, [
            'validation_url' => $validationUrl,
            'issuer_name' => auth()->user()?->name,
        ]);
        $mac = $signing->mac($code, 'movements_general', $issuedAtIso, $payloadToStore);

        AdvancedReportsDocument::query()->create([
            'code' => $code,
            'type' => 'movements_general',
            'issued_at' => $issuedAt,
            'issued_by_user_id' => auth()->id(),
            'issued_ip' => $request->ip(),
            'issued_user_agent' => substr((string) $request->userAgent(), 0, 255),
            'version' => DocumentSigningService::VERSION,
            'mac' => $mac,
            'payload' => $payloadToStore,
        ]);

        $viewData = array_merge($filterData, [
            'ano' => $ano,
            'year' => (string) $ano,
            'instituicaoId' => $instituicaoId,
            'escolaId' => $escolaId,
            'data_inicial' => $startDate,
            'data_final' => $endDate,
            'data' => $data,
            'issuedAt' => $issuedAtHuman,
            'validationCode' => $code,
            'validationUrl' => $validationUrl,
            'qrDataUri' => $qrDataUri,
            'issuerName' => auth()->user()?->name,
            'issuerRole' => null,
            'cityUf' => null,
            'municipality' => $header['municipality'] ?? null,
            'schoolName' => $header['schoolName'] ?? null,
            'contact' => $header['contact'] ?? null,
            'schoolInep' => null,
        ]);

        return app(PdfRenderService::class)->download(
            'advanced-reports::movements.pdf',
            $viewData,
            'relatorio-movimentacoes-' . $ano . '.pdf',
            'a4',
            'landscape',
            'inline'
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
