<?php

namespace iEducar\Packages\AdvancedReports\Services;

use App\Models\LegacyIndividual;
use App\Models\LegacySchoolHistory;
use App\Models\LegacySchoolHistoryDiscipline;
use App\Models\Nationality;
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
            ->selectRaw('a.ref_idpes as person_id')
            ->where('a.cod_aluno', $alunoId)
            ->first();

        if (!$student) {
            abort(404, 'Aluno não encontrado.');
        }

        $person = LegacyIndividual::query()
            ->with(['document.issuingBody', 'cityBirth.state'])
            ->find($student->person_id);

        $document = $person?->document;
        $cityBirth = $person?->cityBirth;

        $nationalityMap = (new Nationality)->getDescriptiveValues();
        $nationalityName = $person?->nacionalidade
            ? ($nationalityMap[(int) $person->nacionalidade] ?? null)
            : null;

        $personData = [
            'birth_date' => $person?->data_nasc?->format('d/m/Y'),
            'birth_city' => $cityBirth?->name,
            'birth_uf' => $cityBirth?->state?->abbreviation,
            'nationality' => $nationalityName,
            'sex' => $person?->sexo === 'M' ? 'Masculino' : ($person?->sexo === 'F' ? 'Feminino' : null),
            // Filiação: prioriza nome_* (texto), mas se existirem vínculos idpes_* dá pra ampliar depois.
            'mother_name' => $person?->nome_mae,
            'father_name' => $person?->nome_pai,
            // Documento (RG)
            'rg' => $document?->rg,
            'rg_issuing_body' => $document?->issuingBody?->sigla,
            'rg_uf' => $document?->sigla_uf_exp_rg,
        ];

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
            'person' => $personData,
            'items' => $items,
        ];
    }
}

