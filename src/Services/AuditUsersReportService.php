<?php

namespace iEducar\Packages\AdvancedReports\Services;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class AuditUsersReportService
{
    /**
     * @return array<int,string>
     */
    public function operationOptions(): array
    {
        return [
            1 => 'Inclusão (INSERT)',
            2 => 'Alteração (UPDATE)',
            3 => 'Exclusão (DELETE)',
        ];
    }

    /**
     * @return array{accesses: Collection<int,array<string,mixed>>, changes: Collection<int,array<string,mixed>>, summary: array<string,mixed>}
     */
    public function build(
        string $dateStart,
        string $dateEnd,
        ?int $userId = null,
        ?string $originContains = null,
        ?string $tableContains = null,
        ?string $ipContains = null,
        ?int $accessSuccess = null,
        ?int $operation = null,
        int $limit = 2000,
    ): array {
        [$start, $end] = self::normalizeDates($dateStart, $dateEnd);

        $accesses = $this->buildAccesses($start, $end, $userId, $ipContains, $accessSuccess, $limit);
        $changes = $this->buildChanges($start, $end, $userId, $originContains, $tableContains, $ipContains, $operation, $limit);

        $summary = [
            'period' => [
                'start' => $start->format('d/m/Y H:i:s'),
                'end' => $end->format('d/m/Y H:i:s'),
            ],
            'accesses_total' => $accesses->count(),
            'accesses_success' => $accesses->where('success', true)->count(),
            'accesses_failed' => $accesses->where('success', false)->count(),
            'changes_total' => $changes->count(),
            'changes_insert' => $changes->where('operation', 'INSERT')->count(),
            'changes_update' => $changes->where('operation', 'UPDATE')->count(),
            'changes_delete' => $changes->where('operation', 'DELETE')->count(),
        ];

        return compact('accesses', 'changes', 'summary');
    }

    /**
     * @return array{0: Carbon, 1: Carbon}
     */
    public static function normalizeDates(string $start, string $end): array
    {
        $s = Carbon::parse($start)->startOfDay();
        $e = Carbon::parse($end)->endOfDay();

        if ($e->lessThan($s)) {
            [$s, $e] = [$e->copy()->startOfDay(), $s->copy()->endOfDay()];
        }

        return [$s, $e];
    }

    public static function resolveAuditOperation(mixed $before, mixed $after): string
    {
        if ($before === null) {
            return 'INSERT';
        }
        if ($after === null) {
            return 'DELETE';
        }

        return 'UPDATE';
    }

    /**
     * @return Collection<int,array<string,mixed>>
     */
    private function buildAccesses(
        Carbon $start,
        Carbon $end,
        ?int $userId,
        ?string $ipContains,
        ?int $success,
        int $limit,
    ): Collection {
        $q = DB::table('portal.acesso as a')
            ->leftJoin('cadastro.pessoa as p', 'p.idpes', '=', 'a.cod_pessoa')
            ->selectRaw('a.data_hora as date')
            ->selectRaw('a.cod_pessoa as user_id')
            ->selectRaw('p.nome as user_name')
            ->selectRaw('a.sucesso as success')
            ->selectRaw('a.ip_interno as internal_ip')
            ->selectRaw('a.ip_externo as external_ip')
            ->whereBetween('a.data_hora', [$start->toDateTimeString(), $end->toDateTimeString()])
            ->when($userId, fn ($qq) => $qq->where('a.cod_pessoa', $userId))
            ->when($success !== null, fn ($qq) => $qq->where('a.sucesso', $success))
            ->when($ipContains, function ($qq) use ($ipContains) {
                $like = '%' . str_replace('%', '', $ipContains) . '%';
                $qq->where(function ($w) use ($like) {
                    $w->where('a.ip_interno', 'ILIKE', $like)->orWhere('a.ip_externo', 'ILIKE', $like);
                });
            })
            ->orderByDesc('a.data_hora')
            ->limit($limit);

        return $q->get()->map(static function ($r) {
            return [
                'date' => (string) $r->date,
                'user_id' => $r->user_id !== null ? (int) $r->user_id : null,
                'user_name' => $r->user_name ? (string) $r->user_name : null,
                'success' => (bool) $r->success,
                'internal_ip' => $r->internal_ip ? (string) $r->internal_ip : null,
                'external_ip' => $r->external_ip ? (string) $r->external_ip : null,
            ];
        });
    }

    /**
     * @return Collection<int,array<string,mixed>>
     */
    private function buildChanges(
        Carbon $start,
        Carbon $end,
        ?int $userId,
        ?string $originContains,
        ?string $tableContains,
        ?string $ipContains,
        ?int $operation,
        int $limit,
    ): Collection {
        $q = DB::table('ieducar_audit as au')
            ->selectRaw('au.id')
            ->selectRaw('au.date as date')
            ->selectRaw('au.schema as schema')
            ->selectRaw('au.table as table')
            ->selectRaw("(au.context->>'user_id')::int as user_id")
            ->selectRaw("(au.context->>'user_name') as user_name")
            ->selectRaw("(au.context->>'origin') as origin")
            ->selectRaw("(au.context->>'ip') as ip")
            ->selectRaw("(au.context->>'user_agent') as user_agent")
            ->selectRaw("CASE WHEN au.before IS NULL THEN 'INSERT' WHEN au.after IS NULL THEN 'DELETE' ELSE 'UPDATE' END as operation")
            ->selectRaw('au.before as before')
            ->selectRaw('au.after as after')
            ->whereBetween('au.date', [$start->toDateTimeString(), $end->toDateTimeString()])
            ->when($userId, fn ($qq) => $qq->whereRaw("(au.context->>'user_id')::int = ?", [$userId]))
            ->when($originContains, fn ($qq) => $qq->whereRaw("(au.context->>'origin') ILIKE ?", ['%' . str_replace('%', '', $originContains) . '%']))
            ->when($ipContains, fn ($qq) => $qq->whereRaw("(au.context->>'ip') ILIKE ?", ['%' . str_replace('%', '', $ipContains) . '%']))
            ->when($tableContains, function ($qq) use ($tableContains) {
                $like = '%' . str_replace('%', '', $tableContains) . '%';
                $qq->where(function ($w) use ($like) {
                    $w->where('au.schema', 'ILIKE', $like)
                        ->orWhere('au.table', 'ILIKE', $like);
                });
            })
            ->when($operation, function ($qq) use ($operation) {
                $map = [
                    1 => 'INSERT',
                    2 => 'UPDATE',
                    3 => 'DELETE',
                ];
                $op = $map[$operation] ?? null;
                if ($op) {
                    $qq->whereRaw("CASE WHEN au.before IS NULL THEN 'INSERT' WHEN au.after IS NULL THEN 'DELETE' ELSE 'UPDATE' END = ?", [$op]);
                }
            })
            ->orderByDesc('au.date')
            ->limit($limit);

        return $q->get()->map(static function ($r) {
            return [
                'id' => (int) $r->id,
                'date' => (string) $r->date,
                'schema' => (string) $r->schema,
                'table' => (string) $r->table,
                'user_id' => $r->user_id !== null ? (int) $r->user_id : null,
                'user_name' => $r->user_name ? (string) $r->user_name : null,
                'origin' => $r->origin ? (string) $r->origin : null,
                'ip' => $r->ip ? (string) $r->ip : null,
                'user_agent' => $r->user_agent ? (string) $r->user_agent : null,
                'operation' => (string) $r->operation,
                // Mantemos JSON bruto (pode ser grande); PDFs listam resumido.
                'before' => $r->before,
                'after' => $r->after,
            ];
        });
    }
}

