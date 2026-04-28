<?php

namespace iEducar\Packages\AdvancedReports\Services;

use App\Models\LegacySchoolHistory;
use App\Models\LegacySchoolHistoryDiscipline;
use Illuminate\Support\Facades\DB;

class SchoolHistoryService
{
    /**
     * @return array<string, mixed>
     */
    public function build(int $alunoId): array
    {
        $student = DB::table('pmieducar.aluno as a')
            ->join('cadastro.pessoa as p', 'p.idpes', '=', 'a.ref_idpes')
            ->selectRaw('a.cod_aluno as aluno_id')
            ->selectRaw('p.nome as aluno_nome')
            ->where('a.cod_aluno', $alunoId)
            ->first();

        if (!$student) {
            abort(404, 'Aluno não encontrado.');
        }

        $histories = LegacySchoolHistory::query()
            ->where('ref_cod_aluno', $alunoId)
            ->where('ativo', 1)
            ->orderByRaw('COALESCE(posicao, 999999) ASC')
            ->orderByDesc('ano')
            ->get();

        $items = [];
        foreach ($histories as $h) {
            $disciplines = LegacySchoolHistoryDiscipline::query()
                ->where('ref_ref_cod_aluno', $alunoId)
                ->where('ref_sequencial', $h->sequencial)
                ->orderByRaw('COALESCE(ordenamento, 999999) ASC')
                ->orderBy('nm_disciplina')
                ->get();

            $items[] = [
                'history' => $h,
                'disciplines' => $disciplines,
            ];
        }

        return [
            'student' => $student,
            'items' => $items,
        ];
    }
}

