<?php

namespace iEducar\Packages\AdvancedReports\Http\Controllers;

use App\Http\Controllers\Controller;
use iEducar\Packages\AdvancedReports\Models\AdvancedReportsDocument;
use iEducar\Packages\AdvancedReports\Services\AdvancedReportsFilterService;
use iEducar\Packages\AdvancedReports\Services\DocumentSigningService;
use iEducar\Packages\AdvancedReports\Services\OfficialHeaderService;
use iEducar\Packages\AdvancedReports\Services\PdfRenderService;
use iEducar\Packages\AdvancedReports\Services\QrCodeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class DiplomaReportController extends Controller
{
    public function index(Request $request, AdvancedReportsFilterService $filters): View
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

        return view('advanced-reports::diplomas.index', array_merge($filterData, compact(
            'ano',
            'instituicaoId',
            'escolaId',
            'cursoId',
            'serieId',
            'turmaId'
        )));
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function resolveMatriculaIdsForClass(int $year, int $turmaId, array $requestedIds): array
    {
        if (!empty($requestedIds)) {
            return $requestedIds;
        }

        return DB::table('pmieducar.matricula_turma as mt')
            ->join('pmieducar.matricula as m', 'm.cod_matricula', '=', 'mt.ref_cod_matricula')
            ->where('mt.ref_cod_turma', $turmaId)
            ->where('mt.ativo', 1)
            ->where('m.ativo', 1)
            ->where('m.dependencia', false)
            ->where('m.ano', $year)
            ->orderBy('m.cod_matricula')
            ->limit(200)
            ->pluck('m.cod_matricula')
            ->map(fn ($v) => (int) $v)
            ->all();
    }

    /**
     * @return array{director_name: string, secretary_name: string, school_inep: ?string}
     */
    private function resolveSchoolSigners(int $escolaId): array
    {
        $row = DB::table('pmieducar.escola as e')
            ->leftJoin('modules.educacenso_cod_escola as ine', 'ine.cod_escola', '=', 'e.cod_escola')
            ->leftJoin('cadastro.pessoa as pg', 'pg.idpes', '=', 'e.ref_idpes_gestor')
            ->leftJoin('cadastro.pessoa as ps', 'ps.idpes', '=', 'e.ref_idpes_secretario_escolar')
            ->where('e.cod_escola', $escolaId)
            ->selectRaw('pg.nome as director_name')
            ->selectRaw('ps.nome as secretary_name')
            ->selectRaw('ine.cod_escola_inep::text as school_inep')
            ->first();

        if (!$row) {
            return [
                'director_name' => '',
                'secretary_name' => '',
                'school_inep' => null,
            ];
        }

        return [
            'director_name' => (string) ($row->director_name ?? ''),
            'secretary_name' => (string) ($row->secretary_name ?? ''),
            'school_inep' => $row->school_inep ?? null,
        ];
    }

    public function pdf(Request $request): Response
    {
        $document = (string) $request->get('document', 'diploma');
        if (!in_array($document, ['diploma', 'certificate'], true)) {
            $document = 'diploma';
        }
        $template = (string) $request->get('template', 'classic');
        $side = (string) $request->get('side', 'both');

        if ($request->query('preview') === '1') {
            $issuedAt = now()->format('d/m/Y H:i');

            $view = match ($document) {
                'certificate' => 'advanced-reports::diplomas.certificate',
                default => 'advanced-reports::diplomas.pdf',
            };

            $filename = match ($document) {
                'certificate' => 'certificado-previa.pdf',
                default => 'diploma-previa.pdf',
            };

            return app(PdfRenderService::class)->download($view, [
                'document' => $document,
                'template' => $template,
                'side' => $side,
                'year' => (string) ($request->get('ano') ?: date('Y')),
                'course' => null,
                'class' => null,
                'enrollment' => null,
                'studentName' => 'ALUNO(A) EXEMPLO',
                'students' => null,
                'issuedAt' => $issuedAt,
                'validationCode' => 'EXEMPLO',
                'validationUrl' => '#',
                'qrDataUri' => null,
                'issuerName' => auth()->user()?->name,
                'issuerRole' => null,
                'cityUf' => null,
                'municipality' => 'Prefeitura Municipal (Exemplo) • Secretaria de Educação',
                'schoolName' => 'Unidade Escolar (Exemplo)',
                'contact' => 'Endereço (Exemplo) • Tel: (00) 0000-0000 • E-mail: exemplo@rede.gov.br',
                'directorName' => 'Diretor(a) (Exemplo)',
                'secretaryName' => 'Secretário(a) (Exemplo)',
                'schoolInep' => '00000000',
            ], $filename, 'a4', 'landscape');
        }

        $ano = (int) $request->get('ano');
        $instituicaoId = (int) $request->get('ref_cod_instituicao');
        $escolaId = (int) $request->get('ref_cod_escola');
        $cursoId = (int) $request->get('ref_cod_curso');
        $serieId = (int) $request->get('ref_cod_serie');
        $turmaId = (int) $request->get('ref_cod_turma');

        if (!$ano || !$instituicaoId || !$escolaId || !$cursoId || !$serieId || !$turmaId) {
            abort(422, 'Preencha ano, instituição, escola, curso, série e turma.');
        }

        $requestedIds = array_values(array_filter(array_map('intval', (array) $request->get('matricula_ids', []))));
        if (empty($requestedIds)) {
            abort(422, 'Selecione ao menos um(a) aluno(a) para emitir o diploma/certificado.');
        }
        $ids = $this->resolveMatriculaIdsForClass($ano, $turmaId, $requestedIds);
        if (empty($ids)) {
            abort(404, 'Nenhuma matrícula encontrada para a turma/ano informados.');
        }

        if ($document === 'certificate' && count($ids) > 1) {
            abort(422, 'Para certificado (modelo), selecione apenas um(a) aluno(a).');
        }

        $signers = $this->resolveSchoolSigners($escolaId);
        $directorName = $signers['director_name'];
        $secretaryName = $signers['secretary_name'];
        $schoolInep = $signers['school_inep'];

        $header = app(OfficialHeaderService::class)->forSchool($instituicaoId, $escolaId);

        $courseName = (string) (DB::table('pmieducar.curso')->where('cod_curso', $cursoId)->value('nm_curso') ?: '');
        $className = (string) (DB::table('pmieducar.turma')->where('cod_turma', $turmaId)->value('nm_turma') ?: '');

        $students = [];
        foreach ($ids as $matriculaId) {
            $row = DB::table('pmieducar.matricula as m')
                ->join('pmieducar.aluno as a', 'a.cod_aluno', '=', 'm.ref_cod_aluno')
                ->join('cadastro.pessoa as p', 'p.idpes', '=', 'a.ref_idpes')
                ->where('m.cod_matricula', $matriculaId)
                ->where('m.ano', $ano)
                ->selectRaw('p.nome as aluno_nome')
                ->first();
            if (!$row) {
                continue;
            }

            $students[] = [
                'matricula_id' => $matriculaId,
                'studentName' => (string) $row->aluno_nome,
                'course' => $courseName,
                'class' => $className,
                'year' => (string) $ano,
            ];
        }

        if (empty($students)) {
            abort(404, 'Nenhuma matrícula válida para emissão.');
        }

        $issuedAt = now();
        $issuedAtHuman = $issuedAt->format('d/m/Y H:i');
        $issuedAtIso = $issuedAt->toISOString();

        $pages = [];
        foreach ($students as $stu) {
            $payload = [
                'document' => $document,
                'template' => $template,
                'side' => $side,
                'ano' => $ano,
                'year' => (string) $ano,
                'instituicao_id' => $instituicaoId,
                'escola_id' => $escolaId,
                'turma_id' => $turmaId,
                'matricula_id' => $stu['matricula_id'],
                'course' => $courseName,
                'class' => $className,
            ];

            $signing = app(DocumentSigningService::class);
            $code = $signing->generateCode(8);
            $validationUrl = route('advanced-reports.documents.validate', ['code' => $code]);
            $qrDataUri = app(QrCodeService::class)->pngDataUri($validationUrl, 4);
            $payloadToStore = array_merge($payload, [
                'validation_url' => $validationUrl,
                'issuer_name' => auth()->user()?->name,
            ]);
            $mac = $signing->mac($code, $document, $issuedAtIso, $payloadToStore);

            AdvancedReportsDocument::query()->create([
                'code' => $code,
                'type' => $document,
                'issued_at' => $issuedAt,
                'issued_by_user_id' => auth()->id(),
                'issued_ip' => $request->ip(),
                'issued_user_agent' => substr((string) $request->userAgent(), 0, 255),
                'version' => DocumentSigningService::VERSION,
                'mac' => $mac,
                'payload' => $payloadToStore,
            ]);

            $pages[] = array_merge($stu, [
                'issuedAt' => $issuedAtHuman,
                'validationCode' => $code,
                'validationUrl' => $validationUrl,
                'qrDataUri' => $qrDataUri,
            ]);
        }

        $issuerName = auth()->user()?->name;

        $view = match ($document) {
            'certificate' => 'advanced-reports::diplomas.certificate',
            default => 'advanced-reports::diplomas.pdf',
        };

        $filename = match ($document) {
            'certificate' => 'certificado-modelo.pdf',
            default => 'diploma-' . $template . '-' . $side . '.pdf',
        };

        $first = $students[0];

        return app(PdfRenderService::class)->download($view, [
            'document' => $document,
            'template' => $template,
            'side' => $side,
            'year' => (string) $ano,
            'course' => $courseName,
            'class' => $className,
            'enrollment' => (string) $first['matricula_id'],
            'studentName' => (string) $first['studentName'],
            'students' => $pages,
            'issuedAt' => $issuedAtHuman,
            'validationCode' => $pages[0]['validationCode'] ?? '',
            'validationUrl' => $pages[0]['validationUrl'] ?? '',
            'qrDataUri' => $pages[0]['qrDataUri'] ?? '',
            'issuerName' => $issuerName,
            'issuerRole' => null,
            'cityUf' => null,
            'municipality' => $header['municipality'] ?? null,
            'schoolName' => $header['schoolName'] ?? null,
            'contact' => $header['contact'] ?? null,
            'directorName' => $directorName ?: null,
            'secretaryName' => $secretaryName ?: null,
            'schoolInep' => $schoolInep,
        ], $filename, 'a4', 'landscape', 'inline');
    }
}
