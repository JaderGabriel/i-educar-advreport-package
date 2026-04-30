<?php

namespace iEducar\Packages\AdvancedReports\Services;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\DB;

/**
 * Dados do emissor para linha de assinatura: INEP (pessoa/docente) e matrícula funcional / idpes.
 *
 * No legado, pmieducar.servidor.cod_servidor referencia cadastro.pessoa.idpes;
 * modules.educacenso_cod_docente.cod_servidor segue o mesmo vínculo; portal.funcionario.ref_cod_pessoa_fj é o idpes.
 */
class IssuerSignatureDetails
{
    /**
     * @return array{issuerPersonInep: ?string, issuerMatriculaFuncional: ?string, issuerPersonIdpes: ?int}
     */
    public function forPersonId(?int $idpes): array
    {
        if ($idpes === null || $idpes < 1) {
            return [
                'issuerPersonInep' => null,
                'issuerMatriculaFuncional' => null,
                'issuerPersonIdpes' => null,
            ];
        }

        $inep = DB::table('modules.educacenso_cod_docente')
            ->where('cod_servidor', $idpes)
            ->orderByDesc('cod_docente_inep')
            ->value('cod_docente_inep');

        $func = DB::table('portal.funcionario')
            ->where('ref_cod_pessoa_fj', $idpes)
            ->selectRaw('matricula_interna')
            ->selectRaw('matricula_new')
            ->first();

        $matriculaInterna = $func?->matricula_interna ?? null;
        $matriculaNew = $func?->matricula_new ?? null;

        // Preferir matrícula interna cadastrada na base (portal.funcionario.matricula_interna).
        // "matricula" costuma refletir credencial/identificação antiga de login em algumas instalações.
        $matriculaStr = null;
        if ($matriculaInterna !== null && trim((string) $matriculaInterna) !== '') {
            $matriculaStr = trim((string) $matriculaInterna);
        } elseif ($matriculaNew !== null && trim((string) $matriculaNew) !== '') {
            $matriculaStr = trim((string) $matriculaNew);
        }

        return [
            'issuerPersonInep' => $inep !== null ? (string) $inep : null,
            'issuerMatriculaFuncional' => $matriculaStr,
            'issuerPersonIdpes' => $idpes,
        ];
    }

    /**
     * @return array{issuerPersonInep: ?string, issuerMatriculaFuncional: ?string, issuerPersonIdpes: ?int}
     */
    public function forAuthenticatedUser(): array
    {
        $user = auth()->user();
        if (!$user instanceof Authenticatable) {
            return $this->forPersonId(null);
        }

        $codUsuario = $user->getAttribute('cod_usuario');
        if (!is_numeric($codUsuario)) {
            return $this->forPersonId(null);
        }

        return $this->forPersonId((int) $codUsuario);
    }
}
