<?php

namespace iEducar\Packages\AdvancedReports\Http\Controllers;

use App\Http\Controllers\Controller;
use iEducar\Packages\AdvancedReports\Exports\AuditUsersReportExport;
use iEducar\Packages\AdvancedReports\Services\AuditUsersReportService;
use iEducar\Packages\AdvancedReports\Services\PdfRenderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\Response;

class AuditUsersReportController extends Controller
{
    public function index(Request $request, AuditUsersReportService $service): View
    {
        $dateStart = (string) $request->get('date_start', '');
        $dateEnd = (string) $request->get('date_end', '');
        $userId = $request->get('user_id') ? (int) $request->get('user_id') : null;
        $origin = $request->get('origin') ? (string) $request->get('origin') : null;
        $table = $request->get('table') ? (string) $request->get('table') : null;
        $ip = $request->get('ip') ? (string) $request->get('ip') : null;
        $success = $request->get('success') !== null && $request->get('success') !== '' ? (int) $request->get('success') : null;
        $operation = $request->get('operation') ? (int) $request->get('operation') : null;

        $data = null;
        if ($dateStart !== '' && $dateEnd !== '') {
            $data = $service->build($dateStart, $dateEnd, $userId, $origin, $table, $ip, $success, $operation);
        }

        $users = DB::table('pmieducar.usuario as u')
            ->join('cadastro.pessoa as p', 'p.idpes', '=', 'u.cod_usuario')
            ->selectRaw('u.cod_usuario as id')
            ->selectRaw('p.nome as nome')
            ->orderBy('p.nome')
            ->get();

        return view('advanced-reports::audit/users-accesses-actions.index', [
            'operationOptions' => $service->operationOptions(),
            'users' => $users,
            'data' => $data,
        ]);
    }

    public function pdf(Request $request, AuditUsersReportService $service): Response
    {
        $dateStart = (string) $request->get('date_start', '');
        $dateEnd = (string) $request->get('date_end', '');
        if ($dateStart === '' || $dateEnd === '') {
            abort(422, 'Informe o período (data inicial e data final).');
        }

        $filters = [
            'date_start' => $dateStart,
            'date_end' => $dateEnd,
            'user_id' => $request->get('user_id') ? (int) $request->get('user_id') : null,
            'ip' => $request->get('ip') ? (string) $request->get('ip') : null,
            'success' => $request->get('success') !== null && $request->get('success') !== '' ? (int) $request->get('success') : null,
            'operation' => $request->get('operation') ? (int) $request->get('operation') : null,
            'table' => $request->get('table') ? (string) $request->get('table') : null,
            'origin' => $request->get('origin') ? (string) $request->get('origin') : null,
        ];

        $data = $service->build(
            $dateStart,
            $dateEnd,
            $filters['user_id'],
            $filters['origin'],
            $filters['table'],
            $filters['ip'],
            $filters['success'],
            $filters['operation'],
        );

        return app(PdfRenderService::class)->download('advanced-reports::audit/users-accesses-actions.pdf', [
            'data' => $data,
            'filters' => $filters,
            'operationOptions' => $service->operationOptions(),
        ], 'auditoria-acessos-acoes.pdf');
    }

    public function excel(Request $request, AuditUsersReportService $service)
    {
        $dateStart = (string) $request->get('date_start', '');
        $dateEnd = (string) $request->get('date_end', '');
        if ($dateStart === '' || $dateEnd === '') {
            abort(422, 'Informe o período (data inicial e data final).');
        }

        $data = $service->build(
            $dateStart,
            $dateEnd,
            $request->get('user_id') ? (int) $request->get('user_id') : null,
            $request->get('origin') ? (string) $request->get('origin') : null,
            $request->get('table') ? (string) $request->get('table') : null,
            $request->get('ip') ? (string) $request->get('ip') : null,
            $request->get('success') !== null && $request->get('success') !== '' ? (int) $request->get('success') : null,
            $request->get('operation') ? (int) $request->get('operation') : null,
        );

        return Excel::download(new AuditUsersReportExport($data, $service->operationOptions()), 'auditoria-acessos-acoes.xlsx');
    }
}

