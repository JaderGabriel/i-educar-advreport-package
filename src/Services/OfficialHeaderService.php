<?php

namespace iEducar\Packages\AdvancedReports\Services;

use Illuminate\Support\Facades\DB;

class OfficialHeaderService
{
    /**
     * @return array{municipality: ?string, schoolName: ?string, contact: ?string}
     */
    public function forSchool(?int $institutionId, ?int $schoolId): array
    {
        $institutionName = null;
        if ($institutionId) {
            $institutionName = DB::table('pmieducar.instituicao')
                ->where('cod_instituicao', $institutionId)
                ->value('nm_instituicao');
        }

        $school = null;
        if ($schoolId) {
            $school = DB::table('pmieducar.escola as e')
                ->leftJoin('pmieducar.escola_complemento as ec', 'ec.ref_cod_escola', '=', 'e.cod_escola')
                ->selectRaw('e.cod_escola as id')
                ->selectRaw('COALESCE(ec.nm_escola, e.fantasia, \'\') as name')
                ->selectRaw('ec.logradouro as street')
                ->selectRaw('ec.numero as number')
                ->selectRaw('ec.bairro as neighborhood')
                ->selectRaw('ec.municipio as city')
                ->selectRaw('ec.cep as zip')
                ->selectRaw('ec.email as email')
                ->where('e.cod_escola', $schoolId)
                ->first();
        }

        $phone = null;
        if ($schoolId) {
            $phone = DB::selectOne('SELECT relatorio.get_telefone_escola(?) as fone', [$schoolId])?->fone ?? null;
        }

        $addressParts = [];
        if (!empty($school?->street)) {
            $addressParts[] = trim((string) $school->street);
        }
        if (!empty($school?->number)) {
            $addressParts[] = 'nº ' . trim((string) $school->number);
        }
        if (!empty($school?->neighborhood)) {
            $addressParts[] = trim((string) $school->neighborhood);
        }
        if (!empty($school?->city)) {
            $addressParts[] = trim((string) $school->city);
        }
        if (!empty($school?->zip)) {
            $addressParts[] = 'CEP ' . trim((string) $school->zip);
        }

        $contactParts = [];
        if (!empty($addressParts)) {
            $contactParts[] = implode(' - ', $addressParts);
        }
        if (!empty($phone)) {
            $contactParts[] = 'Tel: ' . (string) $phone;
        }
        if (!empty($school?->email)) {
            $contactParts[] = 'E-mail: ' . (string) $school->email;
        }

        return [
            // Linha “Município” do cabeçalho formal: aqui usamos o nome da instituição.
            'municipality' => $institutionName ? (string) $institutionName : null,
            'schoolName' => !empty($school?->name) ? (string) $school->name : null,
            'contact' => !empty($contactParts) ? implode(' • ', $contactParts) : null,
        ];
    }
}

