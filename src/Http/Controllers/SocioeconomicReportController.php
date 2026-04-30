<?php

namespace iEducar\Packages\AdvancedReports\Http\Controllers;

use App\Http\Controllers\Controller;
use iEducar\Packages\AdvancedReports\Exports\SocioeconomicExport;
use iEducar\Packages\AdvancedReports\Models\AdvancedReportsDocument;
use iEducar\Packages\AdvancedReports\Services\ChartImageService;
use iEducar\Packages\AdvancedReports\Services\DocumentSigningService;
use iEducar\Packages\AdvancedReports\Services\OfficialHeaderService;
use iEducar\Packages\AdvancedReports\Services\PdfRenderService;
use iEducar\Packages\AdvancedReports\Services\QrCodeService;
use iEducar\Packages\AdvancedReports\Services\SocioeconomicReportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\Response;

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

    public function pdf(Request $request, SocioeconomicReportService $service): Response
    {
        $ano = (int) $request->get('ano');
        $instituicaoId = $request->get('ref_cod_instituicao') ? (int) $request->get('ref_cod_instituicao') : null;
        $escolaId = $request->get('ref_cod_escola') ? (int) $request->get('ref_cod_escola') : null;
        $cursoId = $request->get('ref_cod_curso') ? (int) $request->get('ref_cod_curso') : null;
        $withCharts = (bool) $request->get('with_charts');

        if (! $ano) {
            abort(422, 'Ano letivo é obrigatório para gerar o PDF.');
        }

        if (! $instituicaoId && $escolaId) {
            $instituicaoId = (int) (DB::table('pmieducar.escola')->where('cod_escola', $escolaId)->value('ref_cod_instituicao') ?: 0) ?: null;
        }

        $filters = $service->getFilters($ano, $instituicaoId, $escolaId, $cursoId);
        $data = $service->buildData($ano, $instituicaoId, $escolaId, $cursoId);

        $charts = [];
        if ($withCharts) {
            $charts = $this->buildCharts(app(ChartImageService::class), $data);
        }

        $header = $escolaId
            ? app(OfficialHeaderService::class)->forSchool($instituicaoId, $escolaId)
            : ($instituicaoId
                ? app(OfficialHeaderService::class)->forSchool($instituicaoId, null)
                : ['municipality' => null, 'schoolName' => null, 'contact' => null]);

        $schoolInep = null;
        if ($escolaId) {
            $schoolInep = DB::table('modules.educacenso_cod_escola')
                ->where('cod_escola', $escolaId)
                ->value('cod_escola_inep');
        }

        $issuerName = auth()->user()?->name;
        $issuerRole = null;
        $cityUf = null;

        $issuedAt = now();
        $issuedAtHuman = $issuedAt->format('d/m/Y H:i');
        $issuedAtIso = DocumentSigningService::issuedAtForMac($issuedAt);

        $institutionName = $instituicaoId
            ? (string) (DB::table('pmieducar.instituicao')->where('cod_instituicao', $instituicaoId)->value('nm_instituicao') ?: '')
            : '';
        $cursoNome = $cursoId
            ? (string) (DB::table('pmieducar.curso')->where('cod_curso', $cursoId)->value('nm_curso') ?: '')
            : '';

        $payload = [
            'report' => 'socioeconomic',
            'year' => (string) $ano,
            'instituicao_id' => $instituicaoId,
            'escola_id' => $escolaId,
            'curso_id' => $cursoId,
            'with_charts' => $withCharts,
            'issuer_name' => $issuerName,
            'institution' => $institutionName,
            'school_display' => (string) ($header['schoolName'] ?? ''),
            'course' => $cursoNome,
        ];

        $signing = app(DocumentSigningService::class);
        $code = $signing->generateCode(8);
        $validationUrl = route('advanced-reports.documents.validate', ['code' => $code]);
        $qrDataUri = app(QrCodeService::class)->pngDataUri($validationUrl, 4);
        $payloadToStore = array_merge($payload, [
            'validation_url' => $validationUrl,
        ]);
        $mac = $signing->mac($code, 'socioeconomic_report', $issuedAtIso, $payloadToStore);

        AdvancedReportsDocument::query()->create([
            'code' => $code,
            'type' => 'socioeconomic_report',
            'issued_at' => $issuedAt,
            'issued_by_user_id' => auth()->id(),
            'issued_ip' => $request->ip(),
            'issued_user_agent' => substr((string) $request->userAgent(), 0, 255),
            'version' => DocumentSigningService::VERSION,
            'mac' => $mac,
            'payload' => $payloadToStore,
        ]);

        $viewData = array_merge($filters, [
            'ano' => $ano,
            'instituicaoId' => $instituicaoId,
            'escolaId' => $escolaId,
            'cursoId' => $cursoId,
            'data' => $data,
            'withCharts' => $withCharts,
            'charts' => $charts,
            'year' => (string) $ano,
            'issuedAt' => $issuedAtHuman,
            'validationCode' => $code,
            'validationUrl' => $validationUrl,
            'qrDataUri' => $qrDataUri,
            'issuerName' => $issuerName,
            'issuerRole' => $issuerRole,
            'cityUf' => $cityUf,
            'schoolInep' => $schoolInep ? (string) $schoolInep : null,
            'municipality' => $header['municipality'] ?? null,
            'schoolName' => $header['schoolName'] ?? null,
            'contact' => $header['contact'] ?? null,
        ]);

        return app(PdfRenderService::class)->download(
            'advanced-reports::socioeconomic.pdf',
            $viewData,
            'relatorio-socioeconomico-' . $ano . '.pdf',
            'a4',
            'portrait',
            'inline'
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
            $race[(string) ($row->raca_label ?? ($row->raca ?? 'Não informada'))] = (int) ($row->total ?? 0);
        }

        $gender = [];
        foreach (($data['gender'] ?? []) as $row) {
            $gender[(string) ($row->sexo_label ?? ($row->sexo ?? 'Não informado'))] = (int) ($row->total ?? 0);
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
